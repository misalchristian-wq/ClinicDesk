<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");
$role = trim($data["role"] ?? "");

if ($email === "" || $password === "" || $role === "") {
    echo json_encode([
        "success" => false,
        "message" => "Email, password, and role are required."
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT account_id, full_name, email, password_hash, role, status
    FROM local_accounts
    WHERE email = ? AND role = ?
    LIMIT 1
");

$stmt->bind_param("ss", $email, $role);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "No local account found for the selected role."
    ]);
    exit;
}

if ($user["status"] !== "Active") {
    echo json_encode([
        "success" => false,
        "message" => "This account is inactive."
    ]);
    exit;
}

if (!password_verify($password, $user["password_hash"])) {
    echo json_encode([
        "success" => false,
        "message" => "Incorrect password."
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Login successful.",
    "user" => [
        "account_id" => $user["account_id"],
        "full_name" => $user["full_name"],
        "email" => $user["email"],
        "role" => $user["role"],
        "status" => $user["status"]
    ]
]);

$stmt->close();
$conn->close();
?>