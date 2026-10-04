<?php
header("Content-Type: application/json");
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);

include __DIR__ . "/../db.php";
$schoolYear = trim((string)($_GET['school_year'] ?? ''));
if (!preg_match('/^(\\d{4})-(\\d{4})$/', $schoolYear, $yearParts) || (int)$yearParts[2] !== (int)$yearParts[1] + 1) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Select a valid school year.']);
    exit;
}

try {
$response = [
    "success" => true,
    "immunization" => [],
    "nutrition" => []
];

/*
|--------------------------------------------------------------------------
| A. Immunization
|--------------------------------------------------------------------------
*/

$immunizationSql = "
    SELECT
        vaccine,
        sex,
        COUNT(DISTINCT COALESCE(NULLIF(lrn, ''), CONCAT('record:', immunization_id))) AS total_immunized
    FROM immunization_records
    WHERE immunized = 1 AND school_year = ?
    GROUP BY vaccine, sex
    ORDER BY vaccine, sex
";

$immunizationStmt = $conn->prepare($immunizationSql);
$immunizationStmt->bind_param('s', $schoolYear);
$immunizationStmt->execute();
$immunizationResult = $immunizationStmt->get_result();

    if (!$immunizationResult) {
    echo json_encode([
        "success" => false,
        "message" => "Could not load immunization records. Please try again."
    ]);
    exit;
}

while ($row = $immunizationResult->fetch_assoc()) {
    $response["immunization"][] = $row;
}

/*
|--------------------------------------------------------------------------
| B. Nutritional Status
|--------------------------------------------------------------------------
*/

$nutritionSql = "
    SELECT
        grade_level,
        sex,
        bmi_category,
        COUNT(DISTINCT COALESCE(NULLIF(lrn, ''), CONCAT('record:', record_id))) AS total
    FROM sf8_student_records
    WHERE school_year = ? AND bmi_category IS NOT NULL AND bmi_category <> ''
    GROUP BY grade_level, sex, bmi_category
    ORDER BY grade_level, sex, bmi_category
";

$nutritionStmt = $conn->prepare($nutritionSql);
$nutritionStmt->bind_param('s', $schoolYear);
$nutritionStmt->execute();
$nutritionResult = $nutritionStmt->get_result();

if (!$nutritionResult) {
    echo json_encode([
        "success" => false,
        "message" => "Could not load nutrition records. Please try again."
    ]);
    exit;
}

while ($row = $nutritionResult->fetch_assoc()) {
    $response["nutrition"][] = $row;
}

echo json_encode($response);

$conn->close();
} catch (Throwable $e) {
    error_log('ClinicDesk Table 1 report: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Could not load health and nutrition records. Please try again.']);
}
?>
