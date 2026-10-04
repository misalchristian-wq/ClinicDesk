<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);
require __DIR__ . '/../db.php';

$year = trim((string)($_GET['school_year'] ?? ''));
if (!preg_match('/^(\\d{4})-(\\d{4})$/', $year, $parts) || (int)$parts[2] !== (int)$parts[1] + 1) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Select a valid school year.']);
    exit;
}

function clinicReportCount(mysqli $conn, string $sql, string $year, int $yearParameters = 1): int {
    $stmt = $conn->prepare($sql);
    if ($yearParameters === 2) $stmt->bind_param('ss', $year, $year);
    else $stmt->bind_param('s', $year);
    $stmt->execute();
    $value = (int)$stmt->get_result()->fetch_row()[0];
    $stmt->close();
    return $value;
}

try {
    $metrics = [];
    $metrics['learners'] = clinicReportCount($conn,
        "SELECT COUNT(DISTINCT lrn) FROM sf8_student_records WHERE school_year=? AND lrn<>''", $year);
    $metrics['nutrition_followup'] = clinicReportCount($conn,
        "SELECT COUNT(DISTINCT lrn) FROM sf8_student_records WHERE school_year=? AND lrn<>''
         AND LOWER(bmi_category) IN ('wasted','severely wasted','overweight','obese')", $year);
    $metrics['immunized'] = clinicReportCount($conn,
        "SELECT COUNT(DISTINCT lrn) FROM immunization_records WHERE school_year=? AND lrn<>'' AND immunized=1", $year);
    $metrics['screened'] = clinicReportCount($conn,
        "SELECT COUNT(DISTINCT lrn) FROM okd_lhas_records WHERE school_year=? AND lrn<>'' AND screened>0", $year);
    $metrics['wifa_given'] = clinicReportCount($conn,
        "SELECT COUNT(*) FROM (
            SELECT lrn FROM deworming_wifa_records WHERE school_year=? AND lrn<>'' AND wifa=1
            UNION
            SELECT s.lrn FROM wifa_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id
            WHERE s.school_year=? AND s.lrn<>'' AND e.outcome='Given'
         ) AS learners", $year, 2);
    $metrics['dewormed'] = clinicReportCount($conn,
        "SELECT COUNT(*) FROM (
            SELECT lrn FROM deworming_wifa_records WHERE school_year=? AND lrn<>''
              AND (dewormed_sbfp=1 OR dewormed_other=1)
            UNION
            SELECT s.lrn FROM deworming_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id
            WHERE s.school_year=? AND s.lrn<>'' AND e.outcome='Given'
         ) AS learners", $year, 2);
    $metrics['feeding_followup'] = clinicReportCount($conn,
        "SELECT COUNT(DISTINCT m.student_record_id) FROM feeding_measurements m
         JOIN sf8_student_records s ON s.record_id=m.student_record_id WHERE s.school_year=?", $year);
    echo json_encode(['success' => true, 'school_year' => $year, 'metrics' => $metrics]);
} catch (Throwable $e) {
    error_log('ClinicDesk report summary: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Could not load the clinic activity summary. Please try again.']);
} finally {
    $conn->close();
}
