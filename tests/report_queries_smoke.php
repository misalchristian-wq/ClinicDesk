<?php
// Read-only schema/query check against the configured local database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../db.php';
$year = '2025-2026';
$queries = [
    "SELECT vaccine, sex, COUNT(DISTINCT COALESCE(NULLIF(lrn, ''), CONCAT('record:', immunization_id))) AS total_immunized FROM immunization_records WHERE immunized=1 AND school_year=? GROUP BY vaccine, sex",
    "SELECT grade_level, sex, bmi_category, COUNT(DISTINCT COALESCE(NULLIF(lrn, ''), CONCAT('record:', record_id))) AS total FROM sf8_student_records WHERE school_year=? AND bmi_category IS NOT NULL AND bmi_category<>'' GROUP BY grade_level, sex, bmi_category",
    "SELECT screening_type, SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 11 AND 12 THEN screened ELSE 0 END) AS shs_screened, SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 7 AND 10 THEN screened ELSE 0 END) AS jhs_screened FROM okd_lhas_records WHERE school_year=? GROUP BY screening_type",
    "SELECT i.lrn, COALESCE(NULLIF(i.grade_level, ''), s.grade_level) AS grade_level, i.sex, i.vaccine, i.immunized FROM immunization_records i LEFT JOIN sf8_student_records s ON s.school_year=i.school_year AND s.lrn=i.lrn WHERE i.school_year=?",
    "SELECT COUNT(*) FROM (SELECT lrn FROM deworming_wifa_records WHERE school_year=? AND lrn<>'' AND wifa=1 UNION SELECT s.lrn FROM wifa_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id WHERE s.school_year=? AND s.lrn<>'' AND e.outcome='Given') AS learners",
    "SELECT COUNT(*) FROM (SELECT lrn FROM deworming_wifa_records WHERE school_year=? AND lrn<>'' AND (dewormed_sbfp=1 OR dewormed_other=1) UNION SELECT s.lrn FROM deworming_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id WHERE s.school_year=? AND s.lrn<>'' AND e.outcome='Given') AS learners",
    "SELECT COUNT(DISTINCT m.student_record_id) FROM feeding_measurements m JOIN sf8_student_records s ON s.record_id=m.student_record_id WHERE s.school_year=?",
];
foreach ($queries as $query) {
    $stmt = $conn->prepare($query);
    if (substr_count($query, '?') === 2) $stmt->bind_param('ss', $year, $year);
    else $stmt->bind_param('s', $year);
    $stmt->execute();
    $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$conn->close();
echo "Report queries passed\n";
