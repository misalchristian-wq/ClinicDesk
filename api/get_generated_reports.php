<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$result = $conn->query("SELECT * FROM generated_reports ORDER BY generated_at DESC");
$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}
echo json_encode(["success" => true, "reports" => $reports]);
?>