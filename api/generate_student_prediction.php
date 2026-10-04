<?php
header("Content-Type: application/json");

ini_set("display_errors", 0);
error_reporting(E_ALL);
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to generate a prediction.']);
    exit;
}

try {
    include __DIR__ . "/../db.php";

    $data = json_decode(file_get_contents("php://input"), true);
    $record_id = filter_var($data["record_id"] ?? null, FILTER_VALIDATE_INT);

    if (!$record_id || $record_id < 1) {
        echo json_encode(["success" => false, "message" => "Record ID is required."]);
        exit;
    }

    // Get student record
    $studentStmt = $conn->prepare("SELECT * FROM sf8_student_records WHERE record_id = ? LIMIT 1");
    $studentStmt->bind_param("i", $record_id);
    $studentStmt->execute();
    $student = $studentStmt->get_result()->fetch_assoc();
    $studentStmt->close();

    if (!$student) {
        echo json_encode(["success" => false, "message" => "Student record not found."]);
        exit;
    }

    // Get health inputs
    $inputStmt = $conn->prepare("SELECT * FROM student_health_inputs WHERE record_id = ? LIMIT 1");
    $inputStmt->bind_param("i", $record_id);
    $inputStmt->execute();
    $input = $inputStmt->get_result()->fetch_assoc();
    $inputStmt->close();
    if (!$input) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Save the health assessment before running a prediction.']);
        exit;
    }

    $missing = [];
    if (!is_numeric($student['age'] ?? null) || (int)$student['age'] < 5 || (int)$student['age'] > 69) $missing[] = 'age (5–69 in this dataset)';
    if (!in_array($student['sex'] ?? '', ['Male', 'Female'], true)) $missing[] = 'sex';
    foreach (['diet_type' => 'diet type', 'living_environment' => 'living environment',
              'skin_condition' => 'skin condition', 'sun_exposure' => 'sun exposure'] as $key => $label) {
        if (empty($input[$key])) $missing[] = $label;
    }
    $flagMap = [
        'Night Blindness' => 'has_night_blindness', 'Dry Eyes' => 'has_dry_eyes',
        'Bleeding Gums' => 'has_bleeding_gums', 'Fatigue' => 'has_fatigue',
        'Tingling Sensation' => 'has_numbness_tingling',
        'Reduced Memory Capacity' => 'has_memory_problems',
        'Shortness of Breath' => 'has_shortness_of_breath',
        'Loss of Appetite' => 'has_low_appetite',
        'Fast Heart Rate' => 'has_fast_heart_rate',
        'Brittle Nails' => 'has_brittle_nails', 'Weight Loss' => 'has_weight_loss',
        'Reduced Wound Healing Capacity' => 'has_reduced_wound_healing',
    ];
    foreach ($flagMap as $key) {
        if (!in_array($input[$key] ?? null, ['Yes', 'No'], true)) $missing[] = str_replace('_', ' ', $key);
    }
    if (!in_array($input['diet_type'] ?? '', ['Vegetarian', 'Non-Vegetarian'], true)) $missing[] = 'dataset diet type';
    if (!in_array($input['living_environment'] ?? '', ['Rural', 'Urban'], true)) $missing[] = 'living environment selection';
    if (!in_array($input['skin_condition'] ?? '', ['Normal', 'Dry Skin', 'Rough Skin', 'Pale/Yellow Skin'], true)) $missing[] = 'skin condition selection';
    if (!in_array($input['sun_exposure'] ?? '', ['Low', 'Moderate', 'High'], true)) $missing[] = 'sun exposure selection';
    if ($missing) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Complete the dataset screening inputs: ' . implode(', ', array_unique($missing)) . '.']);
        exit;
    }

    // Build payload for Flask API
    $payload = [
        'Age' => (int)$student['age'], 'Gender' => $student['sex'],
        'Diet Type' => $input['diet_type'],
        'Living Environment' => $input['living_environment'],
        'Skin Condition' => $input['skin_condition'],
        'Low Sun Exposure' => $input['sun_exposure'] === 'Low' ? 1 : 0,
    ];
    foreach ($flagMap as $feature => $field) {
        $payload[$feature] = $input[$field] === 'Yes' ? 1 : 0;
    }

    // Call Flask API
    $ch = curl_init("http://127.0.0.1:5001/predict");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_PROXY, '');

    $mlResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($mlResponse === false || $httpCode === 0 || $httpCode >= 500) {
        http_response_code(503);
        echo json_encode([
            "success" => false,
            "message" => "Prediction service is unavailable. Open Prediction Settings and check its status."
        ]);
        exit;
    }

    if ($httpCode >= 400) {
        $modelError = json_decode($mlResponse, true);
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => is_array($modelError) && !empty($modelError['message'])
                ? $modelError['message'] : 'Review the dataset screening inputs and try again.'
        ]);
        exit;
    }

    // Decode JSON response from Flask
    $mlResult = json_decode($mlResponse, true);
    if (!$mlResult || !isset($mlResult["success"]) || $mlResult["success"] !== true) {
        http_response_code(502);
        echo json_encode([
            "success" => false,
            "message" => $mlResult['message'] ?? "The model could not process this assessment. Review the recorded screening inputs and try again."
        ]);
        exit;
    }

    // Extract values
    $predictedDeficiency = $mlResult["predicted_deficiency"] ?? "For further assessment";
    $riskLevel = $mlResult["predicted_risk_level"] ?? "Model-Based";
    $bmiCategory = strtolower(trim((string)($student['bmi_category'] ?? '')));
    if (str_contains($bmiCategory, 'severely wasted') || str_contains($bmiCategory, 'obese')) {
        $riskLevel = 'High';
    } elseif ((str_contains($bmiCategory, 'wasted') || str_contains($bmiCategory, 'overweight')) && $riskLevel === 'Low') {
        $riskLevel = 'Moderate';
    }
    $confidenceScore = $mlResult["confidence_score"] ?? 0;
    $algorithmUsed = $mlResult["algorithm_used"] ?? "Decision Tree";
    $recommendationText = $mlResult["recommendation_text"] ?? "";
    $recommendedFoods = $mlResult["recommended_foods"] ?? "";
    $interventionType = $mlResult["intervention_type"] ?? "";

    $conn->begin_transaction();

    // Save prediction
    $predictionStmt = $conn->prepare("
        INSERT INTO prediction_results (record_id, predicted_deficiency, predicted_risk_level, confidence_score, algorithm_used)
        VALUES (?, ?, ?, ?, ?)
    ");
    $predictionStmt->bind_param("issds", $record_id, $predictedDeficiency, $riskLevel, $confidenceScore, $algorithmUsed);
    $predictionStmt->execute();
    $prediction_id = $predictionStmt->insert_id;
    $predictionStmt->close();

    // Save recommendation
    $recommendationStmt = $conn->prepare("
        INSERT INTO recommendations (prediction_id, recommendation_text, recommended_foods, intervention_type)
        VALUES (?, ?, ?, ?)
    ");
    $recommendationStmt->bind_param("isss", $prediction_id, $recommendationText, $recommendedFoods, $interventionType);
    $recommendationStmt->execute();
    $recommendationStmt->close();

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "ML prediction generated successfully.",
        "prediction" => [
            "prediction_id" => $prediction_id,
            "predicted_deficiency" => $predictedDeficiency,
            "predicted_risk_level" => $riskLevel,
            "confidence_score" => $confidenceScore,
            "algorithm_used" => $algorithmUsed,
            "recommendation_text" => $recommendationText,
            "recommended_foods" => $recommendedFoods,
            "intervention_type" => $interventionType
        ]
    ]);

    $conn->close();

} catch (Throwable $e) {
    error_log('ClinicDesk prediction error: ' . $e->getMessage());
    http_response_code(500);
    if (isset($conn) && $conn) {
        try { $conn->rollback(); } catch (Throwable $rollbackError) {}
    }
    echo json_encode([
        "success" => false,
        "message" => "Prediction could not be saved. Please try again."
    ]);
}
?>
