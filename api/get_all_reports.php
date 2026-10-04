<?php
header("Content-Type: application/json");
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);
include __DIR__ . "/../db.php";

$school_year = $_GET["school_year"] ?? "";

if ($school_year === "") {
    echo json_encode(["success" => false, "message" => "school_year required"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT report_key, report_data, saved_by, saved_at
    FROM report_saved_data
    WHERE school_year = ?
");
$stmt->bind_param("s", $school_year);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[$row["report_key"]] = [
        "data" => json_decode($row["report_data"], true),
        "saved_by" => $row["saved_by"],
        "saved_at" => $row["saved_at"]
    ];
}

echo json_encode(["success" => true, "reports" => $reports]);
$stmt->close();
$conn->close();
?>
