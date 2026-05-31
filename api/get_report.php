<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$report_key = $_GET["report_key"] ?? "";
$school_year = $_GET["school_year"] ?? "";

if ($report_key === "" || $school_year === "") {
    echo json_encode(["success" => false, "message" => "report_key and school_year are required."]);
    exit;
}

$stmt = $conn->prepare("SELECT report_data, saved_by, saved_at FROM report_saved_data WHERE report_key = ? AND school_year = ? LIMIT 1");
$stmt->bind_param("ss", $report_key, $school_year);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    echo json_encode([
        "success" => true,
        "has_saved" => true,
        "report_data" => json_decode($row["report_data"], true),
        "saved_by" => $row["saved_by"],
        "saved_at" => $row["saved_at"]
    ]);
} else {
    echo json_encode(["success" => true, "has_saved" => false]);
}

$stmt->close();
$conn->close();
?>