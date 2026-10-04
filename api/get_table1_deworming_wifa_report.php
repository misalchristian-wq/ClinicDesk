<?php
// Aggregated Table 1.C/D counts. Each learner is counted once per channel or
// WIFA reporting period even when multiple actual administrations are logged.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);

$year = trim((string)($_GET['school_year'] ?? ''));
if (!preg_match('/^\d{4}-\d{4}$/', $year)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Select a valid school year.']);
    exit;
}

try {
    require __DIR__ . '/db.php';
    $sql = "SELECT grade_level, sex,
        COUNT(DISTINCT CASE WHEN dewormed_sbfp=1 THEN learner_key END) AS sbfp_total,
        COUNT(DISTINCT CASE WHEN dewormed_other=1 THEN learner_key END) AS other_total,
        COUNT(DISTINCT CASE WHEN wifa=1 AND LOWER(sex)='female'
            AND MONTH(wifa_date) BETWEEN 7 AND 9 THEN learner_key END) AS wifa_jul_sep,
        COUNT(DISTINCT CASE WHEN wifa=1 AND LOWER(sex)='female'
            AND MONTH(wifa_date) BETWEEN 1 AND 3 THEN learner_key END) AS wifa_jan_mar
        FROM (
            SELECT COALESCE(CONCAT(d.lrn,'|',d.school_year), CONCAT('legacy:',d.deworming_wifa_id)) AS learner_key,
                d.grade_level, d.sex, d.dewormed_sbfp, d.dewormed_other, d.wifa,
                NULLIF(d.wifa_date,'0000-00-00') AS wifa_date
            FROM deworming_wifa_records d WHERE d.school_year=? AND d.upload_id IS NOT NULL
            UNION ALL
            SELECT CONCAT(s.lrn,'|',s.school_year),
                COALESCE((SELECT d2.grade_level FROM deworming_wifa_records d2
                    WHERE d2.lrn=s.lrn AND d2.school_year=s.school_year LIMIT 1),s.grade_level),
                s.sex, 0, 0, 1, e.event_date
            FROM wifa_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id
            WHERE s.school_year=? AND e.outcome='Given'
            UNION ALL
            SELECT CONCAT(s.lrn,'|',s.school_year),
                COALESCE((SELECT d2.grade_level FROM deworming_wifa_records d2
                    WHERE d2.lrn=s.lrn AND d2.school_year=s.school_year LIMIT 1),s.grade_level),
                s.sex,
                CASE WHEN e.channel='SBFP' THEN 1 ELSE 0 END,
                CASE WHEN e.channel='Other' THEN 1 ELSE 0 END, 0, NULL
            FROM deworming_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id
            WHERE s.school_year=? AND e.outcome='Given'
        ) entries GROUP BY grade_level, sex ORDER BY grade_level, sex";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $year, $year, $year);
    $stmt->execute();
    $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['success' => true, 'school_year' => $year, 'records' => $records]);
} catch (Throwable $e) {
    error_log('ClinicDesk Table 1.C/D report: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Could not load the WIFA report. Please try again.']);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
