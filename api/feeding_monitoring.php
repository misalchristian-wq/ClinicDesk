<?php
// Dated nurse observations; the imported student measurements stay unchanged.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);

try {
    require __DIR__ . '/db.php';
    $conn->set_charset('utf8mb4');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $data = $method === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;
    if (!is_array($data)) throw new InvalidArgumentException('Send a valid JSON request.');
    $studentId = (int)($data['record_id'] ?? 0);
    if ($studentId <= 0) throw new InvalidArgumentException('Select a learner.');
    $studentStmt = $conn->prepare('SELECT record_id, learner_name, school_year, weight_kg, bmi FROM sf8_student_records WHERE record_id=? LIMIT 1');
    $studentStmt->bind_param('i', $studentId);
    $studentStmt->execute();
    $student = $studentStmt->get_result()->fetch_assoc();
    $studentStmt->close();
    if (!$student) throw new OutOfBoundsException('Learner record was not found. Refresh the list.');

    if ($method === 'GET') {
        $stmt = $conn->prepare('SELECT m.measurement_id, m.measured_on, m.weight_kg, m.bmi, m.progress_status, m.notes,
            m.recorded_at, m.updated_at, a.full_name AS recorded_by
            FROM feeding_measurements m LEFT JOIN local_accounts a ON a.account_id=m.recorded_by_account_id
            WHERE m.student_record_id=? ORDER BY m.measured_on ASC, m.measurement_id ASC');
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $measurements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode(['success' => true, 'student' => $student, 'measurements' => $measurements]);
        exit;
    }
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Use GET or POST.']);
        exit;
    }

    $action = (string)($data['action'] ?? '');
    if (!in_array($action, ['add', 'update'], true)) throw new InvalidArgumentException('Choose a valid measurement action.');
    $date = trim((string)($data['measured_on'] ?? ''));
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone('Asia/Manila'));
    if (!$parsed || $parsed->format('Y-m-d') !== $date) throw new InvalidArgumentException('Enter a valid measurement date.');
    if ($parsed > new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'))) {
        throw new InvalidArgumentException('The measurement date cannot be in the future.');
    }
    $weightText = trim((string)($data['weight_kg'] ?? ''));
    $bmiText = trim((string)($data['bmi'] ?? ''));
    if ($weightText === '' && $bmiText === '') throw new InvalidArgumentException('Enter weight, BMI, or both.');
    if ($weightText !== '' && (!is_numeric($weightText) || (float)$weightText < 2 || (float)$weightText > 500)) {
        throw new InvalidArgumentException('Enter a valid weight in kilograms.');
    }
    if ($bmiText !== '' && (!is_numeric($bmiText) || (float)$bmiText < 5 || (float)$bmiText > 100)) {
        throw new InvalidArgumentException('Enter a valid BMI.');
    }
    $weight = $weightText === '' ? null : round((float)$weightText, 2);
    $bmi = $bmiText === '' ? null : round((float)$bmiText, 2);
    $status = trim((string)($data['progress_status'] ?? ''));
    if ($status !== '' && !in_array($status, ['Monitoring', 'Improving', 'Recovered', 'Needs follow-up'], true)) {
        throw new InvalidArgumentException('Select a valid nurse progress assessment.');
    }
    $status = $status === '' ? null : $status;
    $notes = trim((string)($data['notes'] ?? ''));
    if (mb_strlen($notes) > 2000) throw new InvalidArgumentException('Notes are too long.');
    if ($status === 'Recovered' && $notes === '') {
        throw new InvalidArgumentException('Describe the nurse assessment before marking recovery.');
    }
    $actor = (int)(getCurrentUser()['account_id'] ?? 0);
    if ($actor <= 0) throw new RuntimeException('A nurse account is required.');

    $conn->begin_transaction();
    $before = null;
    if ($action === 'add') {
        $stmt = $conn->prepare('INSERT INTO feeding_measurements
            (student_record_id, measured_on, weight_kg, bmi, progress_status, notes, recorded_by_account_id)
            VALUES (?,?,?,?,?,?,?)');
        $stmt->bind_param('isddssi', $studentId, $date, $weight, $bmi, $status, $notes, $actor);
        $stmt->execute();
        $measurementId = $stmt->insert_id;
        $stmt->close();
    } else {
        $measurementId = (int)($data['measurement_id'] ?? 0);
        $stmt = $conn->prepare('SELECT measured_on, weight_kg, bmi, progress_status, notes FROM feeding_measurements
            WHERE measurement_id=? AND student_record_id=? LIMIT 1 FOR UPDATE');
        $stmt->bind_param('ii', $measurementId, $studentId);
        $stmt->execute();
        $before = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$before) throw new OutOfBoundsException('Measurement was not found. Refresh its history.');
        $stmt = $conn->prepare('UPDATE feeding_measurements SET measured_on=?, weight_kg=?, bmi=?, progress_status=?, notes=?
            WHERE measurement_id=? AND student_record_id=?');
        $stmt->bind_param('sddssii', $date, $weight, $bmi, $status, $notes, $measurementId, $studentId);
        $stmt->execute();
        $stmt->close();
    }
    $beforeJson = $before === null ? null : json_encode($before, JSON_THROW_ON_ERROR);
    $afterJson = json_encode(['measured_on' => $date, 'weight_kg' => $weight, 'bmi' => $bmi,
        'progress_status' => $status, 'notes' => $notes], JSON_THROW_ON_ERROR);
    $auditAction = $action === 'add' ? 'Created' : 'Corrected';
    $audit = $conn->prepare('INSERT INTO feeding_measurement_audit
        (measurement_id, student_record_id, action, before_json, after_json, actor_account_id) VALUES (?,?,?,?,?,?)');
    $audit->bind_param('iisssi', $measurementId, $studentId, $auditAction, $beforeJson, $afterJson, $actor);
    $audit->execute();
    $audit->close();
    $conn->commit();
    echo json_encode(['success' => true, 'message' => $action === 'add'
        ? 'Feeding follow-up recorded.' : 'Feeding follow-up corrected; the change was logged.']);
} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof mysqli) {
        try { $conn->rollback(); } catch (Throwable $ignored) {}
    }
    $duplicate = $e instanceof mysqli_sql_exception && (int)$e->getCode() === 1062;
    $missingSchema = $e instanceof mysqli_sql_exception && (int)$e->getCode() === 1146;
    http_response_code($duplicate ? 409 : ($e instanceof OutOfBoundsException ? 404 : ($e instanceof InvalidArgumentException ? 422 : 503)));
    if (!$duplicate && !$missingSchema && !($e instanceof InvalidArgumentException) && !($e instanceof OutOfBoundsException)) {
        error_log('ClinicDesk feeding monitoring: ' . $e->getMessage());
    }
    echo json_encode(['success' => false, 'message' => $duplicate
        ? 'A follow-up already exists for this learner and date. Open it to correct it.'
        : ($missingSchema ? 'Feeding monitoring is unavailable until its database migration is installed.'
        : ($e instanceof InvalidArgumentException || $e instanceof OutOfBoundsException
            ? $e->getMessage() : 'Could not load or save feeding follow-up. Please try again.'))]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
