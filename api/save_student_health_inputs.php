<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

ini_set("display_errors", 0);
error_reporting(E_ALL);

try {
    include __DIR__ . "/../db.php";

    $data = json_decode(file_get_contents("php://input"), true);

    $record_id = $data["record_id"] ?? "";

    if ($record_id === "") {
        echo json_encode([
            "success" => false,
            "message" => "Record ID is required."
        ]);
        exit;
    }

    $diet_type = trim($data["diet_type"] ?? "");
    $sun_exposure = trim($data["sun_exposure"] ?? "");
    $exercise_level = trim($data["exercise_level"] ?? "");
    $symptoms = trim($data["symptoms"] ?? "");

    $has_fatigue = $data["has_fatigue"] ?? "No";
    $has_bone_pain = $data["has_bone_pain"] ?? "No";
    $has_bleeding_gums = $data["has_bleeding_gums"] ?? "No";
    $has_pale_skin = $data["has_pale_skin"] ?? "No";
    $has_night_blindness = $data["has_night_blindness"] ?? "No";

    $has_low_appetite = $data["has_low_appetite"] ?? "No";
    $has_irregular_meals = $data["has_irregular_meals"] ?? "No";
    $has_weight_changes = $data["has_weight_changes"] ?? "No";
    $has_headache = $data["has_headache"] ?? "No";
    $has_poor_concentration = $data["has_poor_concentration"] ?? "No";

    $has_vision_problem = $data["has_vision_problem"] ?? "No";
    $has_hearing_problem = $data["has_hearing_problem"] ?? "No";
    $has_dental_problem = $data["has_dental_problem"] ?? "No";
    $has_skin_problem = $data["has_skin_problem"] ?? "No";
    $has_breathing_problem = $data["has_breathing_problem"] ?? "No";
    $has_recent_illness = $data["has_recent_illness"] ?? "No";
    $has_current_medication = $data["has_current_medication"] ?? "No";

    $immunization_updated = $data["immunization_updated"] ?? "Unknown";
    $has_known_allergy = $data["has_known_allergy"] ?? "No";
    $allergy_details = trim($data["allergy_details"] ?? "");

    $family_history_diabetes = $data["family_history_diabetes"] ?? "No";
    $family_history_heart_disease = $data["family_history_heart_disease"] ?? "No";
    $family_history_anemia = $data["family_history_anemia"] ?? "No";

    $existing_medical_condition = trim($data["existing_medical_condition"] ?? "");
    $needs_followup = $data["needs_followup"] ?? "No";
    $needs_referral = $data["needs_referral"] ?? "No";
    $clinic_notes = trim($data["clinic_notes"] ?? "");

    $checkStmt = $conn->prepare("SELECT input_id FROM student_health_inputs WHERE record_id = ? LIMIT 1");
    $checkStmt->bind_param("i", $record_id);
    $checkStmt->execute();

    $result = $checkStmt->get_result();
    $existing = $result->fetch_assoc();
    $checkStmt->close();

    if ($existing) {
        $stmt = $conn->prepare("
            UPDATE student_health_inputs
            SET diet_type = ?,
                sun_exposure = ?,
                exercise_level = ?,
                symptoms = ?,
                has_fatigue = ?,
                has_bone_pain = ?,
                has_bleeding_gums = ?,
                has_pale_skin = ?,
                has_night_blindness = ?,
                has_low_appetite = ?,
                has_irregular_meals = ?,
                has_weight_changes = ?,
                has_headache = ?,
                has_poor_concentration = ?,
                has_vision_problem = ?,
                has_hearing_problem = ?,
                has_dental_problem = ?,
                has_skin_problem = ?,
                has_breathing_problem = ?,
                has_recent_illness = ?,
                has_current_medication = ?,
                immunization_updated = ?,
                has_known_allergy = ?,
                allergy_details = ?,
                family_history_diabetes = ?,
                family_history_heart_disease = ?,
                family_history_anemia = ?,
                existing_medical_condition = ?,
                needs_followup = ?,
                needs_referral = ?,
                clinic_notes = ?
            WHERE record_id = ?
        ");

        $stmt->bind_param(
            "sssssssssssssssssssssssssssssssi",
            $diet_type,
            $sun_exposure,
            $exercise_level,
            $symptoms,
            $has_fatigue,
            $has_bone_pain,
            $has_bleeding_gums,
            $has_pale_skin,
            $has_night_blindness,
            $has_low_appetite,
            $has_irregular_meals,
            $has_weight_changes,
            $has_headache,
            $has_poor_concentration,
            $has_vision_problem,
            $has_hearing_problem,
            $has_dental_problem,
            $has_skin_problem,
            $has_breathing_problem,
            $has_recent_illness,
            $has_current_medication,
            $immunization_updated,
            $has_known_allergy,
            $allergy_details,
            $family_history_diabetes,
            $family_history_heart_disease,
            $family_history_anemia,
            $existing_medical_condition,
            $needs_followup,
            $needs_referral,
            $clinic_notes,
            $record_id
        );
    } else {
        $stmt = $conn->prepare("
            INSERT INTO student_health_inputs (
                record_id,
                diet_type,
                sun_exposure,
                exercise_level,
                symptoms,
                has_fatigue,
                has_bone_pain,
                has_bleeding_gums,
                has_pale_skin,
                has_night_blindness,
                has_low_appetite,
                has_irregular_meals,
                has_weight_changes,
                has_headache,
                has_poor_concentration,
                has_vision_problem,
                has_hearing_problem,
                has_dental_problem,
                has_skin_problem,
                has_breathing_problem,
                has_recent_illness,
                has_current_medication,
                immunization_updated,
                has_known_allergy,
                allergy_details,
                family_history_diabetes,
                family_history_heart_disease,
                family_history_anemia,
                existing_medical_condition,
                needs_followup,
                needs_referral,
                clinic_notes
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isssssssssssssssssssssssssssssss",
            $record_id,
            $diet_type,
            $sun_exposure,
            $exercise_level,
            $symptoms,
            $has_fatigue,
            $has_bone_pain,
            $has_bleeding_gums,
            $has_pale_skin,
            $has_night_blindness,
            $has_low_appetite,
            $has_irregular_meals,
            $has_weight_changes,
            $has_headache,
            $has_poor_concentration,
            $has_vision_problem,
            $has_hearing_problem,
            $has_dental_problem,
            $has_skin_problem,
            $has_breathing_problem,
            $has_recent_illness,
            $has_current_medication,
            $immunization_updated,
            $has_known_allergy,
            $allergy_details,
            $family_history_diabetes,
            $family_history_heart_disease,
            $family_history_anemia,
            $existing_medical_condition,
            $needs_followup,
            $needs_referral,
            $clinic_notes
        );
    }

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Health assessment inputs saved successfully."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Save failed: " . $stmt->error
        ]);
    }

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