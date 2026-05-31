<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$school_year = trim($data["school_year"] ?? "");
$report_html = trim($data["report_html"] ?? "");
$generated_by = trim($data["generated_by"] ?? "");

if ($school_year === "" || $report_html === "" || $generated_by === "") {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
    exit;
}

// Cloudinary configuration
$cloud_name = "du3qpurjj";
$upload_preset = "atansproject-prod-unsigned";

// Save HTML to a temporary file with .html extension
$temp_dir = sys_get_temp_dir();
$temp_file = $temp_dir . "/report_" . uniqid() . ".html";
file_put_contents($temp_file, $report_html);

// Verify file was written
if (!file_exists($temp_file) || filesize($temp_file) === 0) {
    echo json_encode(["success" => false, "message" => "Failed to create temporary HTML file."]);
    exit;
}

// Prepare cURL upload to Cloudinary
$cloudinary_url = "https://api.cloudinary.com/v1_1/$cloud_name/raw/upload";
$ch = curl_init($cloudinary_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'file' => new CURLFile($temp_file, 'text/html', basename($temp_file)),
    'upload_preset' => $upload_preset,
]);
// Optional: add API key and secret if required (but unsigned preset should work)
// curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$api_secret");

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Clean up temp file
unlink($temp_file);

if ($http_code !== 200) {
    $error_msg = "Cloudinary upload failed. HTTP $http_code";
    if ($curl_error) $error_msg .= ", cURL error: $curl_error";
    if ($response) $error_msg .= ", Response: " . substr($response, 0, 500);
    echo json_encode(["success" => false, "message" => $error_msg]);
    exit;
}

$cloud_result = json_decode($response, true);
if (!isset($cloud_result["secure_url"])) {
    echo json_encode(["success" => false, "message" => "Cloudinary response missing secure_url", "response" => $response]);
    exit;
}

$cloudinary_url_saved = $cloud_result["secure_url"];
$public_id = $cloud_result["public_id"];

// Save to database
$stmt = $conn->prepare("INSERT INTO generated_reports (school_year, report_type, cloudinary_url, cloudinary_public_id, generated_by) VALUES (?, 'consolidated', ?, ?, ?)");
$stmt->bind_param("ssss", $school_year, $cloudinary_url_saved, $public_id, $generated_by);
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Report sent to Cloudinary successfully.", "url" => $cloudinary_url_saved]);
} else {
    echo json_encode(["success" => false, "message" => "Database save failed: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>