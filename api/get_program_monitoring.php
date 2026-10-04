<?php
// One school-year snapshot for the nurse's tabbed monitoring page.
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
    $year = trim((string)($_GET['school_year'] ?? ''));
    if (!preg_match('/^\d{4}-\d{4}$/', $year)) throw new InvalidArgumentException('Select a valid school year.');

    $stmt = $conn->prepare('SELECT record_id, lrn FROM sf8_student_records WHERE school_year=?');
    $stmt->bind_param('s', $year);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $programs = [];
    $byLrn = [];
    foreach ($students as $student) {
        $id = (int)$student['record_id'];
        $programs[$id] = [
            'wifa_baseline' => null, 'wifa_events' => [],
            'deworming_baseline' => null, 'deworming_events' => [],
            'arh' => null, 'immunizations' => [], 'screenings' => [],
            'tobacco' => null, 'feeding_measurements' => []
        ];
        if (trim((string)$student['lrn']) !== '') $byLrn[(string)$student['lrn']] = $id;
    }
    $resolve = static function (array $row) use (&$programs, $byLrn, $year): ?int {
        $linked = (int)($row['student_record_id'] ?? 0);
        if ($linked > 0) return isset($programs[$linked]) ? $linked : null;
        if (($row['school_year'] ?? '') !== $year) return null;
        $lrn = (string)($row['lrn'] ?? '');
        return $byLrn[$lrn] ?? null;
    };
    $categoryQueries = [
        'baseline' => 'SELECT student_record_id, lrn, school_year, deworming_wifa_id,
            dewormed_sbfp, dewormed_other, wifa, NULLIF(wifa_date, \'0000-00-00\') AS wifa_date
            FROM deworming_wifa_records WHERE school_year=?',
        'arh' => 'SELECT student_record_id, lrn, school_year, arh_record_id, pregnancy_status, delivery_mode, peer_educator
            FROM arh_records WHERE school_year=?',
        'immunization' => 'SELECT student_record_id, lrn, school_year, immunization_id, vaccine, dose, immunized
            FROM immunization_records WHERE school_year=?',
        'screening' => 'SELECT student_record_id, lrn, school_year, okd_lhas_id, screening_type, screened, findings,
            referred_school, referred_lgu, referred_private, referred_others
            FROM okd_lhas_records WHERE school_year=?',
        'tobacco' => 'SELECT student_record_id, lrn, school_year, tobacco_id, violation_type, referred_to_care
            FROM tobacco_control_records WHERE school_year=?'
    ];
    foreach ($categoryQueries as $category => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $year);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
            $id = $resolve($row);
            if ($id === null) continue;
            if ($category === 'baseline') {
                if (!$programs[$id]['wifa_baseline'] || (int)$row['deworming_wifa_id'] > (int)$programs[$id]['wifa_baseline']['deworming_wifa_id']) {
                    $programs[$id]['wifa_baseline'] = $row;
                    $programs[$id]['deworming_baseline'] = $row;
                }
            } elseif ($category === 'arh' || $category === 'tobacco') {
                $field = $category;
                $pk = $category === 'arh' ? 'arh_record_id' : 'tobacco_id';
                if (!$programs[$id][$field] || (int)$row[$pk] > (int)$programs[$id][$field][$pk]) $programs[$id][$field] = $row;
            } elseif ($category === 'immunization') {
                $programs[$id]['immunizations'][] = $row;
            } else {
                $programs[$id]['screenings'][] = $row;
            }
        }
        $stmt->close();
    }
    $linkedQueries = [
        'wifa_events' => 'SELECT e.student_record_id, e.event_date, e.outcome FROM wifa_events e
            JOIN sf8_student_records s ON s.record_id=e.student_record_id WHERE s.school_year=? AND e.outcome=\'Given\' ORDER BY e.event_date',
        'deworming_events' => 'SELECT e.student_record_id, e.event_date, e.channel, e.outcome FROM deworming_events e
            JOIN sf8_student_records s ON s.record_id=e.student_record_id WHERE s.school_year=? ORDER BY e.event_date',
        'feeding_measurements' => 'SELECT m.student_record_id, m.measurement_id, m.measured_on, m.weight_kg, m.bmi,
            m.progress_status FROM feeding_measurements m JOIN sf8_student_records s ON s.record_id=m.student_record_id
            WHERE s.school_year=? ORDER BY m.measured_on, m.measurement_id'
    ];
    foreach ($linkedQueries as $field => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $year);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
            $id = (int)$row['student_record_id'];
            if (!isset($programs[$id])) continue;
            $programs[$id][$field][] = $row;
        }
        $stmt->close();
    }
    echo json_encode(['success' => true, 'school_year' => $year, 'programs' => $programs]);
} catch (Throwable $e) {
    http_response_code($e instanceof InvalidArgumentException ? 422 : 503);
    if (!($e instanceof InvalidArgumentException)) error_log('ClinicDesk program monitoring: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e instanceof InvalidArgumentException
        ? $e->getMessage() : 'Could not load program monitoring data. Please try again.']);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
