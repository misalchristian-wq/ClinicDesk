<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
$account_id = $data["account_id"] ?? "";

if ($account_id === "") {
    echo json_encode([
        "success" => false,
        "message" => "Account ID is required."
    ]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM local_accounts WHERE account_id = ?");
$stmt->bind_param("i", $account_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Local account deleted successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Delete failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>