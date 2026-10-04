<?php
declare(strict_types=1);
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
    $result = $conn->query('SELECT s.record_id, s.learner_name, s.grade_level, s.section, s.bmi_category,
        (SELECT COUNT(*) FROM consultations c WHERE c.record_id=s.record_id) AS consult_count
        FROM sf8_student_records s ORDER BY s.learner_name ASC');
    echo json_encode(['success' => true, 'students' => $result->fetch_all(MYSQLI_ASSOC)]);
} catch (Throwable $e) {
    error_log('ClinicDesk consultation students: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Student list could not be loaded.']);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
