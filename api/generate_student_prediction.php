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
    if (!$input) $input = [];

    // Build payload for Flask API
    $payload = [
        "age" => is_numeric($student["age"]) ? (int)$student["age"] : 0,
        "gender" => $student["sex"] ?? "Unknown",
        "bmi" => is_numeric($student["bmi"]) ? (float)$student["bmi"] : 0,
        "smoking_status" => "No",
        "alcohol_consumption" => "No",
        "exercise_level" => $input["exercise_level"] ?? "Moderate",
        "diet_type" => $input["diet_type"] ?? "Balanced",
        "sun_exposure" => $input["sun_exposure"] ?? "Moderate",
        "income_level" => "Middle",
        "latitude_region" => "Tropical",
        "vitamin_a_percent_rda" => 70,
        "vitamin_c_percent_rda" => 70,
        "vitamin_d_percent_rda" => 70,
        "vitamin_e_percent_rda" => 70,
        "vitamin_b12_percent_rda" => 70,
        "folate_percent_rda" => 70,
        "calcium_percent_rda" => 70,
        "iron_percent_rda" => 70,
        "hemoglobin_g_dl" => 12.5,
        "serum_vitamin_d_ng_ml" => 25,
        "serum_vitamin_b12_pg_ml" => 350,
        "serum_folate_ng_ml" => 8,
        "has_night_blindness" => (($input["has_night_blindness"] ?? "No") === "Yes") ? 1 : 0,
        "has_fatigue" => (($input["has_fatigue"] ?? "No") === "Yes") ? 1 : 0,
        "has_bleeding_gums" => (($input["has_bleeding_gums"] ?? "No") === "Yes") ? 1 : 0,
        "has_bone_pain" => (($input["has_bone_pain"] ?? "No") === "Yes") ? 1 : 0,
        "has_muscle_weakness" => 0,
        "has_numbness_tingling" => 0,
        "has_memory_problems" => 0,
        "has_pale_skin" => (($input["has_pale_skin"] ?? "No") === "Yes") ? 1 : 0,
        "has_multiple_deficiencies" => 0
    ];

    // Count symptoms
    $symptomFields = ["has_night_blindness", "has_fatigue", "has_bleeding_gums", "has_bone_pain", "has_pale_skin"];
    $symptomCount = 0;
    foreach ($symptomFields as $field) {
        if (($payload[$field] ?? 0) == 1) $symptomCount++;
    }
    $payload["symptoms_count"] = $symptomCount;

    // Call Flask API
    $ch = curl_init("http://127.0.0.1:5001/predict");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $mlResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Logging (safe version)
    $logFile = __DIR__ . "/../ml_curl_log.txt";
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    $log = @fopen($logFile, "a");
    if ($log) {
        fwrite($log, date("Y-m-d H:i:s") . " - HTTP: $httpCode, Error: $curlError, Response: $mlResponse\n");
        fclose($log);
    }

    if ($mlResponse === false || $httpCode < 200 || $httpCode >= 300) {
        echo json_encode([
            "success" => false,
            "message" => "ML API request failed.",
            "http_code" => $httpCode,
            "curl_error" => $curlError,
            "ml_response" => $mlResponse
        ]);
        exit;
    }

    // Decode JSON response from Flask
    $mlResult = json_decode($mlResponse, true);
    if (!$mlResult || !isset($mlResult["success"]) || $mlResult["success"] !== true) {
        echo json_encode([
            "success" => false,
            "message" => "ML API returned an error: " . ($mlResult["message"] ?? "Unknown"),
            "raw_response" => $mlResponse
        ]);
        exit;
    }

    // Extract values
    $predictedDeficiency = $mlResult["predicted_deficiency"] ?? "For further assessment";
    $riskLevel = $mlResult["predicted_risk_level"] ?? "Model-Based";
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
        ],
        "sent_payload" => $payload
    ]);

    $conn->close();

} catch (Throwable $e) {
    if (isset($conn) && $conn) {
        try { $conn->rollback(); } catch (Throwable $rollbackError) {}
    }
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}
?>