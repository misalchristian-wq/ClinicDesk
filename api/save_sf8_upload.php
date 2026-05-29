<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$file_name = trim($data["file_name"] ?? "");
$file_type = trim($data["file_type"] ?? "xlsx");
$report_purpose = trim($data["report_purpose"] ?? "");
$report_code = trim($data["report_code"] ?? "");
$cloudinary_public_id = trim($data["cloudinary_public_id"] ?? "");
$cloudinary_url = trim($data["cloudinary_url"] ?? "");
$uploaded_by_email = trim($data["uploaded_by_email"] ?? "");

if ($file_name === "" || $cloudinary_url === "" || $uploaded_by_email === "") {
    echo json_encode([
        "success" => false,
        "message" => "File name, Cloudinary URL, and uploader email are required."
    ]);
    exit;
}

if ($report_code === "") {
    echo json_encode([
        "success" => false,
        "message" => "Report code is required. Please check cell A1."
    ]);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO sf8_uploads (
        file_name,
        file_type,
        report_purpose,
        report_code,
        cloudinary_public_id,
        cloudinary_url,
        uploaded_by_email,
        status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
");

$stmt->bind_param(
    "sssssss",
    $file_name,
    $file_type,
    $report_purpose,
    $report_code,
    $cloudinary_public_id,
    $cloudinary_url,
    $uploaded_by_email
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Upload metadata saved successfully.",
        "upload_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Database save failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>