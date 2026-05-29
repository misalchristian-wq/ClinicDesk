<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$account_id = $data["account_id"] ?? "";
$full_name = trim($data["full_name"] ?? "");
$email = trim($data["email"] ?? "");
$role = trim($data["role"] ?? "");
$status = trim($data["status"] ?? "");

if ($account_id === "" || $full_name === "" || $email === "" || $role === "" || $status === "") {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE local_accounts
    SET full_name = ?, email = ?, role = ?, status = ?
    WHERE account_id = ?
");

$stmt->bind_param("ssssi", $full_name, $email, $role, $status, $account_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Local account updated successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Update failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>