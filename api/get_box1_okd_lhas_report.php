<?php
header("Content-Type: application/json");
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);

include __DIR__ . '/../db.php';
$schoolYear = trim((string)($_GET['school_year'] ?? ''));
if (!preg_match('/^(\\d{4})-(\\d{4})$/', $schoolYear, $yearParts) || (int)$yearParts[2] !== (int)$yearParts[1] + 1) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Select a valid school year.']);
    exit;
}

try {
$sql = "
    SELECT
        screening_type,

        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 11 AND 12 THEN masterlisted ELSE 0 END) AS shs_masterlisted,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 11 AND 12 THEN screened ELSE 0 END) AS shs_screened,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 11 AND 12 THEN findings ELSE 0 END) AS shs_findings,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 11 AND 12 THEN referred_school ELSE 0 END) AS shs_referred_school,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 11 AND 12 THEN referred_lgu ELSE 0 END) AS shs_referred_lgu,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 11 AND 12 THEN referred_private ELSE 0 END) AS shs_referred_private,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 11 AND 12 THEN referred_others ELSE 0 END) AS shs_referred_others,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 7 AND 10 THEN masterlisted ELSE 0 END) AS jhs_masterlisted,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 7 AND 10 THEN screened ELSE 0 END) AS jhs_screened,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 7 AND 10 THEN findings ELSE 0 END) AS jhs_findings,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 7 AND 10 THEN referred_school ELSE 0 END) AS jhs_referred_school,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 7 AND 10 THEN referred_lgu ELSE 0 END) AS jhs_referred_lgu,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 7 AND 10 THEN referred_private ELSE 0 END) AS jhs_referred_private,
        SUM(CASE WHEN CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) BETWEEN 7 AND 10 THEN referred_others ELSE 0 END) AS jhs_referred_others

    FROM okd_lhas_records
    WHERE school_year = ?
    GROUP BY screening_type
    ORDER BY screening_type
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $schoolYear);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Could not load screening records. Please try again."
    ]);
    exit;
}

$records = [];

while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

echo json_encode([
    "success" => true,
    "records" => $records
]);

$conn->close();
} catch (Throwable $e) {
    error_log('ClinicDesk Box 1 report: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Could not load screening records. Please try again.']);
}
?>
