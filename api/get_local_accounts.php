<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$result = $conn->query("
    SELECT account_id, full_name, email, role, status, created_at 
    FROM local_accounts 
    ORDER BY account_id DESC
");

$accounts = [];

while ($row = $result->fetch_assoc()) {
    $accounts[] = $row;
}

echo json_encode([
    "success" => true,
    "accounts" => $accounts
]);

$conn->close();
?>