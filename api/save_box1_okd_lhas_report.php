<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$school_year = trim($data["school_year"] ?? "default");
$report_data = json_encode($data["report_data"] ?? []);
$saved_by = trim($data["saved_by"] ?? "Clinic Nurse");

if ($report_data === "[]") {
    echo json_encode([
        "success" => false,
        "message" => "No report data received."
    ]);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO box1_okd_lhas_reports (
        school_year,
        report_data,
        saved_by
    )
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
        report_data = VALUES(report_data),
        saved_by = VALUES(saved_by),
        updated_at = NOW()
");

$stmt->bind_param("sss", $school_year, $report_data, $saved_by);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Box 1 report saved successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Save failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>