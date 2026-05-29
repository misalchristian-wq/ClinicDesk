<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

ini_set("display_errors", 0);
error_reporting(E_ALL);

try {
    include __DIR__ . "/../db.php";

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
            pr.prediction_id,
            pr.record_id,
            pr.predicted_deficiency,
            pr.predicted_risk_level,
            pr.confidence_score,
            pr.algorithm_used,
            pr.prediction_date,
            r.recommendation_text,
            r.recommended_foods,
            r.intervention_type
        FROM prediction_results pr
        LEFT JOIN recommendations r ON pr.prediction_id = r.prediction_id
        WHERE pr.record_id = ?
        ORDER BY pr.prediction_id DESC
        LIMIT 1
    ");

    $stmt->bind_param("i", $record_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $prediction = $result->fetch_assoc();

    echo json_encode([
        "success" => true,
        "prediction" => $prediction
    ]);

    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage()
    ]);
}
?>