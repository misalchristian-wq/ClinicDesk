<?php
// Nurse-only longitudinal WIFA and deworming history. SF8 values are read as
// imported baseline data and never rewritten by this endpoint.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);

function programDate($value, bool $actual = false): ?string {
    $value = trim((string)$value);
    if ($value === '') return null;
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('Asia/Manila'));
    if (!$date || $date->format('Y-m-d') !== $value) throw new InvalidArgumentException('Enter a valid date in YYYY-MM-DD format.');
    if ($actual && $date > new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'))) {
        throw new InvalidArgumentException('An actual administration date cannot be in the future.');
    }
    return $value;
}

function programKnownDate($value): ?string {
    try { return programDate($value); } catch (InvalidArgumentException $e) { return null; }
}

function programText($value, int $max, string $label): string {
    $text = trim((string)$value);
    if (mb_strlen($text) > $max) throw new InvalidArgumentException("$label is too long.");
    return $text;
}

function programStudent(mysqli $conn, int $id): array {
    if ($id <= 0) throw new InvalidArgumentException('Select a learner first.');
    $stmt = $conn->prepare('SELECT record_id, lrn, learner_name, school_year, grade_level, sex FROM sf8_student_records WHERE record_id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) throw new OutOfBoundsException('Learner record was not found. Refresh the student list.');
    return $row;
}

function programRows(mysqli $conn, string $sql, int $id): array {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function programAudit(mysqli $conn, int $studentId, string $program, int $eventId, string $action,
    ?array $before, array $after, int $actor): void {
    $old = $before === null ? null : json_encode($before, JSON_THROW_ON_ERROR);
    $new = json_encode($after, JSON_THROW_ON_ERROR);
    $stmt = $conn->prepare('INSERT INTO health_program_event_audit
        (student_record_id, program, event_id, action, before_json, after_json, actor_account_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isisssi', $studentId, $program, $eventId, $action, $old, $new, $actor);
    $stmt->execute();
    $stmt->close();
}

try {
    require __DIR__ . '/db.php';
    $conn->set_charset('utf8mb4');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET' && ($_GET['scope'] ?? '') === 'followups') {
        $year = trim((string)($_GET['school_year'] ?? ''));
        if (!preg_match('/^\d{4}-\d{4}$/', $year)) throw new InvalidArgumentException('Select a valid school year.');
        $stmt = $conn->prepare("SELECT s.record_id, s.lrn, s.learner_name, s.grade_level, s.section, s.sex, s.school_year,
            COALESCE(d.wifa, 0) AS sf8_wifa, NULLIF(d.wifa_date, '0000-00-00') AS sf8_wifa_date,
            NULLIF(GREATEST(COALESCE(CASE WHEN d.wifa=1 THEN NULLIF(d.wifa_date, '0000-00-00') END, '0000-00-00'),
                COALESCE((SELECT MAX(e.event_date) FROM wifa_events e WHERE e.student_record_id=s.record_id AND e.outcome='Given'), '0000-00-00')), '0000-00-00') AS last_given_date
            FROM sf8_student_records s
            LEFT JOIN deworming_wifa_records d ON d.lrn=s.lrn AND d.school_year=s.school_year
            WHERE s.school_year=? AND (s.sex='Female' OR d.wifa=1
                OR EXISTS(SELECT 1 FROM wifa_events e WHERE e.student_record_id=s.record_id AND e.outcome='Given'))
            ORDER BY s.learner_name, s.record_id");
        $stmt->bind_param('s', $year);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode(['success' => true, 'school_year' => $year, 'learners' => $rows]);
        exit;
    }
    if ($method === 'GET') {
        $student = programStudent($conn, (int)($_GET['record_id'] ?? 0));
        $stmt = $conn->prepare('SELECT deworming_wifa_id, wifa, wifa_date, dewormed_sbfp, dewormed_other, remarks
            FROM deworming_wifa_records WHERE student_record_id = ? OR
            (student_record_id IS NULL AND lrn = ? AND school_year = ?) ORDER BY deworming_wifa_id DESC LIMIT 1');
        $stmt->bind_param('iss', $student['record_id'], $student['lrn'], $student['school_year']);
        $stmt->execute();
        $baseline = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($baseline) $baseline['wifa_date'] = programKnownDate($baseline['wifa_date'] ?? '');
        $id = (int)$student['record_id'];
        $wifa = programRows($conn, 'SELECT e.*, a.full_name AS recorded_by FROM wifa_events e
            LEFT JOIN local_accounts a ON a.account_id=e.recorded_by_account_id
            WHERE e.student_record_id=? AND e.outcome=\'Given\' ORDER BY e.event_date DESC, e.wifa_event_id DESC', $id);
        $deworming = programRows($conn, 'SELECT e.*, a.full_name AS recorded_by FROM deworming_events e
            LEFT JOIN local_accounts a ON a.account_id=e.recorded_by_account_id
            WHERE e.student_record_id=? ORDER BY e.event_date DESC, e.deworming_event_id DESC', $id);
        $audit = programRows($conn, 'SELECT h.program, h.event_id, h.action, h.changed_at, a.full_name AS actor
            FROM health_program_event_audit h LEFT JOIN local_accounts a ON a.account_id=h.actor_account_id
            WHERE h.student_record_id=? ORDER BY h.audit_id DESC LIMIT 20', $id);
        echo json_encode(['success' => true, 'student' => $student, 'sf8_baseline' => $baseline,
            'wifa_events' => $wifa, 'deworming_events' => $deworming,
            'event_audit' => $audit]);
        exit;
    }
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Use GET or POST.']);
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) throw new InvalidArgumentException('Send a valid JSON request.');
    $action = (string)($input['action'] ?? '');
    $student = programStudent($conn, (int)($input['record_id'] ?? 0));
    $id = (int)$student['record_id'];
    $actor = (int)(getCurrentUser()['account_id'] ?? 0);
    if ($actor <= 0) throw new RuntimeException('A nurse account is required.');
    $conn->begin_transaction();

    if ($action === 'add_wifa' || $action === 'update_wifa') {
        $date = programDate($input['event_date'] ?? '', true);
        if ($date === null) throw new InvalidArgumentException('Enter the date iron sulfate was taken.');
        $baselineCheck = $conn->prepare('SELECT deworming_wifa_id FROM deworming_wifa_records
            WHERE wifa = 1 AND wifa_date = ? AND
            (student_record_id = ? OR (student_record_id IS NULL AND lrn = ? AND school_year = ?)) LIMIT 1');
        $baselineCheck->bind_param('siss', $date, $id, $student['lrn'], $student['school_year']);
        $baselineCheck->execute();
        $alreadyInSf8 = (bool)$baselineCheck->get_result()->fetch_assoc();
        $baselineCheck->close();
        if ($alreadyInSf8) throw new DomainException('This date is already recorded in the SF8 WIFA baseline.');
        $outcome = 'Given';
        $reason = '';
        $remarks = '';
        if ($action === 'add_wifa') {
            $stmt = $conn->prepare('INSERT INTO wifa_events (student_record_id,event_date,outcome,reason_code,remarks,recorded_by_account_id)
                VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('issssi', $id, $date, $outcome, $reason, $remarks, $actor);
            $stmt->execute();
            $eventId = $stmt->insert_id;
            $stmt->close();
            programAudit($conn, $id, 'WIFA', $eventId, 'Created', null,
                compact('date', 'outcome', 'reason', 'remarks'), $actor);
        } else {
            $eventId = (int)($input['event_id'] ?? 0);
            $stmt = $conn->prepare('SELECT event_date, outcome, reason_code, remarks FROM wifa_events
                WHERE wifa_event_id=? AND student_record_id=? LIMIT 1 FOR UPDATE');
            $stmt->bind_param('ii', $eventId, $id);
            $stmt->execute();
            $before = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$before) throw new OutOfBoundsException('WIFA entry was not found. Refresh its history.');
            $stmt = $conn->prepare('UPDATE wifa_events SET event_date=?, outcome=?, reason_code=?, remarks=? WHERE wifa_event_id=?');
            $stmt->bind_param('ssssi', $date, $outcome, $reason, $remarks, $eventId);
            $stmt->execute();
            $stmt->close();
            programAudit($conn, $id, 'WIFA', $eventId, 'Corrected', $before,
                compact('date', 'outcome', 'reason', 'remarks'), $actor);
        }
        $message = $action === 'add_wifa' ? 'Iron sulfate intake date recorded.' : 'Iron sulfate intake date corrected; the change was logged.';
    } elseif ($action === 'save_review') {
        throw new InvalidArgumentException('WIFA now records iron sulfate intake dates only.');
    } elseif ($action === 'add_deworming' || $action === 'update_deworming') {
        $date = programDate($input['event_date'] ?? '', true);
        if ($date === null) throw new InvalidArgumentException('Enter the actual deworming date.');
        $channel = (string)($input['channel'] ?? '');
        $outcome = (string)($input['outcome'] ?? '');
        if (!in_array($channel, ['SBFP', 'Other'], true)) throw new InvalidArgumentException('Select the deworming source.');
        if (!in_array($outcome, ['Given', 'Not given'], true)) throw new InvalidArgumentException('Select whether deworming was given.');
        $remarks = programText($input['remarks'] ?? '', 2000, 'Remarks');
        if ($outcome === 'Not given' && $remarks === '') throw new InvalidArgumentException('Explain why deworming was not given.');
        if ($action === 'add_deworming') {
            $stmt = $conn->prepare('INSERT INTO deworming_events (student_record_id,event_date,channel,outcome,remarks,recorded_by_account_id)
                VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('issssi', $id, $date, $channel, $outcome, $remarks, $actor);
            $stmt->execute();
            $eventId = $stmt->insert_id;
            $stmt->close();
            programAudit($conn, $id, 'Deworming', $eventId, 'Created', null,
                compact('date', 'channel', 'outcome', 'remarks'), $actor);
        } else {
            $eventId = (int)($input['event_id'] ?? 0);
            $stmt = $conn->prepare('SELECT event_date, channel, outcome, remarks FROM deworming_events
                WHERE deworming_event_id=? AND student_record_id=? LIMIT 1 FOR UPDATE');
            $stmt->bind_param('ii', $eventId, $id);
            $stmt->execute();
            $before = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$before) throw new OutOfBoundsException('Deworming entry was not found. Refresh its history.');
            $stmt = $conn->prepare('UPDATE deworming_events SET event_date=?, channel=?, outcome=?, remarks=? WHERE deworming_event_id=?');
            $stmt->bind_param('ssssi', $date, $channel, $outcome, $remarks, $eventId);
            $stmt->execute();
            $stmt->close();
            programAudit($conn, $id, 'Deworming', $eventId, 'Corrected', $before,
                compact('date', 'channel', 'outcome', 'remarks'), $actor);
        }
        $message = $action === 'add_deworming' ? 'Deworming entry recorded.' : 'Deworming entry corrected; the change was logged.';
    } else {
        throw new InvalidArgumentException('Choose a valid monitoring action.');
    }
    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof mysqli) {
        try { $conn->rollback(); } catch (Throwable $ignored) {}
    }
    $duplicate = $e instanceof mysqli_sql_exception && (int)$e->getCode() === 1062;
    $missingSchema = $e instanceof mysqli_sql_exception && (int)$e->getCode() === 1146;
    http_response_code($duplicate || $e instanceof DomainException ? 409 : ($e instanceof OutOfBoundsException ? 404 : ($e instanceof InvalidArgumentException ? 422 : 503)));
    if (!$duplicate && !$missingSchema && !($e instanceof InvalidArgumentException) && !($e instanceof OutOfBoundsException) && !($e instanceof DomainException)) {
        error_log('ClinicDesk monitoring: ' . $e->getMessage());
    }
    echo json_encode(['success' => false, 'message' => $duplicate
        ? 'An entry for this learner and date already exists. Open it to correct it.'
        : ($missingSchema ? 'Monitoring is unavailable until its database migration is installed.'
        : ($e instanceof InvalidArgumentException || $e instanceof OutOfBoundsException || $e instanceof DomainException
            ? $e->getMessage() : 'Could not load or save monitoring data. Please try again.'))]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
