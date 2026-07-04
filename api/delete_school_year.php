<?php
// api/delete_school_year.php
// Removes a school year. Refuses to delete the active one so the
// system always has an active year to default to.
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

$check = $conn->prepare("SELECT is_active FROM school_years WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$check->bind_result($isActive);
if (!$check->fetch()) {
    echo json_encode(["success" => false, "message" => "School year not found."]);
    exit;
}
$check->close();

if ((int)$isActive === 1) {
    echo json_encode(["success" => false, "message" => "Cannot delete the active school year. Set another year active first."]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM school_years WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "School year removed."]);
} else {
    echo json_encode(["success" => false, "message" => "Could not delete: " . $conn->error]);
}
