<?php
// api/update_student_profile.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$record_id = intval($data["record_id"] ?? 0);
$learner_name = trim($data["learner_name"] ?? "");
$birthdate = trim($data["birthdate"] ?? "");
$age = trim($data["age"] ?? "");
$sex = trim($data["sex"] ?? "");
$grade_level = trim($data["grade_level"] ?? "");
$section = trim($data["section"] ?? "");
$weight_kg = floatval($data["weight_kg"] ?? 0);
$height_m = floatval($data["height_m"] ?? 0);
$bmi_category = trim($data["bmi_category"] ?? "");
$height_for_age = trim($data["height_for_age"] ?? "");
$remarks = trim($data["remarks"] ?? "");

if ($record_id <= 0 || $learner_name === "") {
    echo json_encode(["success" => false, "message" => "Record ID and learner name are required."]);
    exit;
}

// Recalculate BMI and height_squared if weight and height provided
if ($weight_kg > 0 && $height_m > 0) {
    $height_squared = $height_m * $height_m;
    $bmi = round($weight_kg / $height_squared, 2);
    // Recalc BMI category if not provided or keep provided one
    if (empty($bmi_category)) {
        if ($bmi < 16) $bmi_category = "Severely Wasted";
        elseif ($bmi < 18.5) $bmi_category = "Wasted";
        elseif ($bmi < 25) $bmi_category = "Normal";
        elseif ($bmi < 30) $bmi_category = "Overweight";
        else $bmi_category = "Obese";
    }
} else {
    // Keep existing BMI and height_squared
    $stmt = $conn->prepare("SELECT bmi, height_squared FROM sf8_student_records WHERE record_id = ?");
    $stmt->bind_param("i", $record_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $bmi = $existing['bmi'] ?? null;
    $height_squared = $existing['height_squared'] ?? null;
    $stmt->close();
}

$stmt = $conn->prepare("UPDATE sf8_student_records SET learner_name=?, birthdate=?, age=?, sex=?, grade_level=?, section=?, weight_kg=?, height_m=?, height_squared=?, bmi=?, bmi_category=?, height_for_age=?, remarks=? WHERE record_id=?");
$stmt->bind_param("ssssssddddsssi", $learner_name, $birthdate, $age, $sex, $grade_level, $section, $weight_kg, $height_m, $height_squared, $bmi, $bmi_category, $height_for_age, $remarks, $record_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Student profile updated successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>