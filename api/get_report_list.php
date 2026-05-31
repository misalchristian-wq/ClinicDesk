<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$report_key = $_GET["report_key"] ?? "";

if ($report_key === "") {
    echo json_encode(["success" => false, "message" => "report_key is required."]);
    exit;
}

$stmt = $conn->prepare("
    SELECT school_year, saved_by, saved_at, report_data
    FROM report_saved_data
    WHERE report_key = ?
    ORDER BY saved_at DESC
");
$stmt->bind_param("s", $report_key);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = [
        "school_year" => $row["school_year"],
        "saved_by" => $row["saved_by"],
        "saved_at" => $row["saved_at"],
        "report_data" => json_decode($row["report_data"], true)
    ];
}

echo json_encode(["success" => true, "reports" => $reports]);

$stmt->close();
$conn->close();
?>