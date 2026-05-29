<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "firebase_init.php";

$data = json_decode(file_get_contents("php://input"), true);

$full_name = trim($data["full_name"] ?? "");
$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");

if ($full_name === "" || $email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Full name, email, and password are required."
    ]);
    exit;
}

try {
    $createdUser = $auth->createUser([
        "email" => $email,
        "emailVerified" => false,
        "password" => $password,
        "displayName" => $full_name,
        "disabled" => false
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Firebase account created successfully.",
        "uid" => $createdUser->uid
    ]);
} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "message" => "Firebase create failed: " . $e->getMessage()
    ]);
}
?>