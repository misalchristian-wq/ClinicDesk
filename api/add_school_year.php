<?php
// api/add_school_year.php
// Adds a new school year. Validates format strictly: "YYYY-YYYY"
// where the second year is exactly one after the first (e.g. 2024-2025).
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);
$label = trim($data["year_label"] ?? "");

// Format check: two 4-digit years joined by a hyphen.
if (!preg_match('/^(\d{4})-(\d{4})$/', $label, $m)) {
    echo json_encode(["success" => false, "message" => "Format must be YYYY-YYYY, e.g. 2024-2025."]);
    exit;
}

$start = (int)$m[1];
$end   = (int)$m[2];

// Second year must be exactly start + 1.
if ($end !== $start + 1) {
    echo json_encode(["success" => false, "message" => "Second year must be one after the first (e.g. 2024-2025)."]);
    exit;
}

// Sanity range so typos like 9999 don't slip in.
if ($start < 2000 || $start > 2100) {
    echo json_encode(["success" => false, "message" => "Year is out of the allowed range."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO school_years (year_label, is_active) VALUES (?, 0)");
$stmt->bind_param("s", $label);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "School year $label added."]);
} else {
    if ($conn->errno === 1062) {
        echo json_encode(["success" => false, "message" => "That school year already exists."]);
    } else {
        echo json_encode(["success" => false, "message" => "Could not add year: " . $conn->error]);
    }
}
