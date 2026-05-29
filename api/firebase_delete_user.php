<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "firebase_init.php";

$data = json_decode(file_get_contents("php://input"), true);

$uid = trim($data["uid"] ?? "");

if ($uid === "") {
    echo json_encode([
        "success" => false,
        "message" => "UID is required."
    ]);
    exit;
}

try {
    $auth->deleteUser($uid);

    echo json_encode([
        "success" => true,
        "message" => "Firebase account deleted successfully."
    ]);
} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "message" => "Firebase delete failed: " . $e->getMessage()
    ]);
}
?>