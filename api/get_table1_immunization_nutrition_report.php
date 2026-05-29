<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

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
        dose,
        COUNT(*) AS total_immunized
    FROM immunization_records
    WHERE immunized = 1
    GROUP BY vaccine, dose
    ORDER BY vaccine, dose
";

$immunizationResult = $conn->query($immunizationSql);

if (!$immunizationResult) {
    echo json_encode([
        "success" => false,
        "message" => "Immunization query failed: " . $conn->error
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
        COUNT(*) AS total
    FROM sf8_student_records
    GROUP BY grade_level, sex, bmi_category
    ORDER BY grade_level, sex, bmi_category
";

$nutritionResult = $conn->query($nutritionSql);

if (!$nutritionResult) {
    echo json_encode([
        "success" => false,
        "message" => "Nutrition query failed: " . $conn->error
    ]);
    exit;
}

while ($row = $nutritionResult->fetch_assoc()) {
    $response["nutrition"][] = $row;
}

echo json_encode($response);

$conn->close();
?>