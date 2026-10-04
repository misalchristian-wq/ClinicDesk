<?php
header("Content-Type: application/json");

ini_set("display_errors", 0);
error_reporting(E_ALL);
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to save an assessment.']);
    exit;
}

try {
    include __DIR__ . "/../db.php";

    $data = json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Enter valid assessment data.']);
        exit;
    }

    $record_id = filter_var($data["record_id"] ?? null, FILTER_VALIDATE_INT);

    if (!$record_id || $record_id < 1) {
        echo json_encode([
            "success" => false,
            "message" => "Record ID is required."
        ]);
        exit;
    }

    foreach (["has_fatigue", "has_bone_pain", "has_bleeding_gums", "has_pale_skin", "has_night_blindness",
              "has_muscle_weakness", "has_numbness_tingling", "has_memory_problems", "has_dry_eyes",
              "has_shortness_of_breath", "has_fast_heart_rate", "has_brittle_nails", "has_weight_loss",
              "has_reduced_wound_healing", "has_low_appetite"] as $requiredFlag) {
        if (!array_key_exists($requiredFlag, $data) ||
            !in_array($data[$requiredFlag], [true, false, 1, 0, 'Yes', 'No'], true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Review all symptom questions before saving.']);
            exit;
        }
    }

    $diet_type = trim($data["diet_type"] ?? "");
    $livingEnvironment = trim((string)($data['living_environment'] ?? ''));
    $skinCondition = trim((string)($data['skin_condition'] ?? ''));
    if ($diet_type !== '' && !in_array($diet_type, ['Vegetarian', 'Non-Vegetarian'], true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Select Vegetarian or Non-Vegetarian for the dataset diet field.']);
        exit;
    }
    if ($livingEnvironment !== '' && !in_array($livingEnvironment, ['Rural', 'Urban'], true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Select Rural or Urban for living environment.']);
        exit;
    }
    if ($skinCondition !== '' && !in_array($skinCondition, ['Normal', 'Dry Skin', 'Rough Skin', 'Pale/Yellow Skin'], true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Select a valid skin condition.']);
        exit;
    }
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
    $allergy_details = $has_known_allergy === 'Yes' ? trim($data["allergy_details"] ?? "") : "";

    $family_history_diabetes = $data["family_history_diabetes"] ?? "No";
    $family_history_heart_disease = $data["family_history_heart_disease"] ?? "No";
    $family_history_anemia = $data["family_history_anemia"] ?? "No";

    $existing_medical_condition = trim($data["existing_medical_condition"] ?? "");
    $needs_followup = $data["needs_followup"] ?? "No";
    $needs_referral = $data["needs_referral"] ?? "No";
    $clinic_notes = trim($data["clinic_notes"] ?? "");

    $modelFlags = [];
    foreach (["has_muscle_weakness", "has_numbness_tingling", "has_memory_problems", "has_dry_eyes",
              "has_shortness_of_breath", "has_fast_heart_rate", "has_brittle_nails", "has_weight_loss",
              "has_reduced_wound_healing"] as $flag) {
        $value = $data[$flag] ?? false;
        $modelFlags[$flag] = ($value === true || $value === 1 || $value === 'Yes') ? 'Yes' : 'No';
    }

    $conn->begin_transaction();
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
        $extraStmt = $conn->prepare("UPDATE student_health_inputs SET
            living_environment = ?, skin_condition = ?,
            has_muscle_weakness = ?, has_numbness_tingling = ?, has_memory_problems = ?,
            has_dry_eyes = ?, has_shortness_of_breath = ?, has_fast_heart_rate = ?,
            has_brittle_nails = ?, has_weight_loss = ?, has_reduced_wound_healing = ?
            WHERE record_id = ?");
        $extraStmt->bind_param('sssssssssssi',
            $livingEnvironment, $skinCondition, $modelFlags['has_muscle_weakness'],
            $modelFlags['has_numbness_tingling'], $modelFlags['has_memory_problems'],
            $modelFlags['has_dry_eyes'], $modelFlags['has_shortness_of_breath'],
            $modelFlags['has_fast_heart_rate'], $modelFlags['has_brittle_nails'],
            $modelFlags['has_weight_loss'], $modelFlags['has_reduced_wound_healing'], $record_id);
        $extraStmt->execute();
        $extraStmt->close();
        $conn->commit();
        echo json_encode([
            "success" => true,
            "message" => "Health assessment inputs saved successfully."
        ]);
    } else {
        $conn->rollback();
        echo json_encode([
            "success" => false,
            "message" => "Save failed: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof mysqli) {
        try { $conn->rollback(); } catch (Throwable $ignored) {}
    }
    error_log('ClinicDesk health assessment save: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "The assessment could not be saved. Check the database migration and try again."
    ]);
}
?>
