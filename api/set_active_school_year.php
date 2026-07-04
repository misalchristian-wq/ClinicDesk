<?php
// api/set_active_school_year.php
// Marks one school year as active. Clears the flag on all others so
// exactly one row is ever active.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = (int)($data["id"] ?? 0);

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "A valid school year id is required."]);
    exit;
}

// Confirm the row exists first.
$check = $conn->prepare("SELECT year_label FROM school_years WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "School year not found."]);
    exit;
}

$conn->begin_transaction();
try {
    $conn->query("UPDATE school_years SET is_active = 0");
    $stmt = $conn->prepare("UPDATE school_years SET is_active = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Active school year updated."]);
} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Could not update active year."]);
}
