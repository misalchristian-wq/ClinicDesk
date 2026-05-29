<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$full_name = trim($data["full_name"] ?? "");
$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");
$role = trim($data["role"] ?? "");
$status = "Active";

if ($full_name === "" || $email === "" || $password === "" || $role === "") {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO local_accounts (full_name, email, password_hash, role, status)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("sssss", $full_name, $email, $password_hash, $role, $status);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Local XAMPP account created successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Create failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>