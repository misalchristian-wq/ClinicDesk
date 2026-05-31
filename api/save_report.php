<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$report_key = trim($data["report_key"] ?? "");
$school_year = trim($data["school_year"] ?? "");
$report_data = $data["report_data"] ?? null;
$saved_by = trim($data["saved_by"] ?? "Clinic Nurse");

if ($report_key === "" || $school_year === "" || $report_data === null) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields: report_key, school_year, report_data"
    ]);
    exit;
}

$json_data = json_encode($report_data);

$stmt = $conn->prepare("
    INSERT INTO report_saved_data (report_key, school_year, report_data, saved_by)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        report_data = VALUES(report_data),
        saved_by = VALUES(saved_by),
        saved_at = NOW()
");

$stmt->bind_param("ssss", $report_key, $school_year, $json_data, $saved_by);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Report saved successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Save failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>