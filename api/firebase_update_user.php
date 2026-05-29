<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "firebase_init.php";

$data = json_decode(file_get_contents("php://input"), true);

$uid = trim($data["uid"] ?? "");
$full_name = trim($data["full_name"] ?? "");
$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");
$status = trim($data["status"] ?? "Active");

if ($uid === "" || $full_name === "" || $email === "") {
    echo json_encode([
        "success" => false,
        "message" => "UID, full name, and email are required."
    ]);
    exit;
}

$properties = [
    "displayName" => $full_name,
    "email" => $email,
    "disabled" => $status === "Disabled"
];

if ($password !== "") {
    if (strlen($password) < 6) {
        echo json_encode([
            "success" => false,
            "message" => "Password must be at least 6 characters."
        ]);
        exit;
    }

    $properties["password"] = $password;
}

try {
    $auth->updateUser($uid, $properties);

    echo json_encode([
        "success" => true,
        "message" => "Firebase account updated successfully."
    ]);
} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "message" => "Firebase update failed: " . $e->getMessage()
    ]);
}
?>