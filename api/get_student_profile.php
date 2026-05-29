<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

ini_set("display_errors", 0);
error_reporting(E_ALL);

try {
    include __DIR__ . "/../db.php";

    function hasText($value, $keyword) {
        return str_contains(strtolower(trim($value ?? "")), strtolower($keyword));
    }

    function getTemporaryDeficiency($bmiCategory, $hfaCategory) {
        if (hasText($bmiCategory, "severely wasted")) {
            return "Possible severe undernutrition";
        }

        if (hasText($bmiCategory, "wasted")) {
            return "Possible undernutrition";
        }

        if (hasText($bmiCategory, "overweight")) {
            return "Possible overnutrition risk";
        }

        if (hasText($bmiCategory, "obese")) {
            return "Possible obesity-related nutritional risk";
        }

        if (hasText($hfaCategory, "severely stunted")) {
            return "Possible chronic undernutrition";
        }

        if (hasText($hfaCategory, "stunted")) {
            return "Possible growth delay";
        }

        if (hasText($bmiCategory, "normal") && hasText($hfaCategory, "normal")) {
            return "No evident nutritional deficiency";
        }

        return "For further nutritional assessment";
    }

    function getRiskLevel($bmiCategory, $hfaCategory) {
        if (
            hasText($bmiCategory, "severely wasted") ||
            hasText($hfaCategory, "severely stunted") ||
            hasText($bmiCategory, "obese")
        ) {
            return "High";
        }

        if (
            hasText($bmiCategory, "wasted") ||
            hasText($bmiCategory, "overweight") ||
            hasText($hfaCategory, "stunted")
        ) {
            return "Moderate";
        }

        if (hasText($bmiCategory, "normal") && hasText($hfaCategory, "normal")) {
            return "Low";
        }

        return "For Review";
    }

    function getRecommendation($bmiCategory, $hfaCategory) {
        if (hasText($bmiCategory, "severely wasted")) {
            return "Refer the learner to the clinic nurse for immediate monitoring, dietary assessment, and possible nutrition intervention.";
        }

        if (hasText($bmiCategory, "wasted")) {
            return "Provide nutrition counseling, encourage balanced meals, and monitor weight improvement regularly.";
        }

        if (hasText($bmiCategory, "overweight")) {
            return "Recommend healthy eating habits, reduce excessive sugary or fatty foods, and encourage regular physical activity.";
        }

        if (hasText($bmiCategory, "obese")) {
            return "Recommend close monitoring, healthy lifestyle counseling, and referral if further medical assessment is needed.";
        }

        if (hasText($hfaCategory, "severely stunted")) {
            return "Refer for further growth and nutrition assessment and monitor height progress regularly.";
        }

        if (hasText($hfaCategory, "stunted")) {
            return "Monitor growth pattern and provide nutrition guidance to support healthy development.";
        }

        if (hasText($bmiCategory, "normal") && hasText($hfaCategory, "normal")) {
            return "Continue regular monitoring and maintain a balanced diet and healthy school activities.";
        }

        return "Review the learner record and validate measurements before giving a final recommendation.";
    }

    $record_id = $_GET["record_id"] ?? "";

    if ($record_id === "") {
        echo json_encode([
            "success" => false,
            "message" => "Record ID is required."
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT 
            record_id,
            upload_id,
            school_name,
            district,
            division,
            region,
            school_id,
            grade_level,
            section,
            track_strand,
            school_year,
            learner_name,
            birthdate,
            age,
            sex,
            weight_kg,
            height_m,
            height_squared,
            bmi,
            bmi_category,
            height_for_age,
            remarks,
            date_saved
        FROM sf8_student_records
        WHERE record_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Database prepare failed: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param("i", $record_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        echo json_encode([
            "success" => false,
            "message" => "Student record not found."
        ]);
        exit;
    }

    $student["temporary_deficiency"] = getTemporaryDeficiency($student["bmi_category"], $student["height_for_age"]);
    $student["risk_level"] = getRiskLevel($student["bmi_category"], $student["height_for_age"]);
    $student["temporary_recommendation"] = getRecommendation($student["bmi_category"], $student["height_for_age"]);

    echo json_encode([
        "success" => true,
        "student" => $student
    ]);

    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}
?>