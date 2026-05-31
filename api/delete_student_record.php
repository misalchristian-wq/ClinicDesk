<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$record_id = intval($data["record_id"] ?? 0);
$password = trim($data["password"] ?? "");

if ($record_id <= 0 || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Record ID and password are required."
    ]);
    exit;
}

function verifyPasswordFlexible($inputPassword, $storedPassword) {
    if ($storedPassword === "" || $storedPassword === null) return false;
    if (password_verify($inputPassword, $storedPassword)) return true;
    if ($inputPassword === $storedPassword) return true;
    return false;
}

try {
    // Verify password against any local account (nurse/admin)
    $accountVerified = false;
    $stmt = $conn->prepare("SELECT password_hash AS password FROM local_accounts");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (verifyPasswordFlexible($password, $row["password"])) {
            $accountVerified = true;
            break;
        }
    }
    $stmt->close();

    if (!$accountVerified) {
        echo json_encode(["success" => false, "message" => "Incorrect password."]);
        exit;
    }

    // Check if record exists
    $checkStmt = $conn->prepare("SELECT record_id FROM sf8_student_records WHERE record_id = ?");
    $checkStmt->bind_param("i", $record_id);
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->num_rows > 0;
    $checkStmt->close();

    if (!$exists) {
        echo json_encode(["success" => false, "message" => "Student record not found."]);
        exit;
    }

    // Delete (foreign key cascades will handle related tables)
    $deleteStmt = $conn->prepare("DELETE FROM sf8_student_records WHERE record_id = ?");
    $deleteStmt->bind_param("i", $record_id);
    $deleteStmt->execute();

    if ($deleteStmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Student record deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Deletion failed."]);
    }
    $deleteStmt->close();

} catch (Throwable $e) {
    echo json_encode(["success" => false, "message" => "Delete failed: " . $e->getMessage()]);
}

$conn->close();
?>