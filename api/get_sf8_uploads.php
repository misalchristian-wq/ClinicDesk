<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$sql = "
    SELECT 
        upload_id,
        file_name,
        file_type,
        report_purpose,
        report_code,
        cloudinary_public_id,
        cloudinary_url,
        uploaded_by_email,
        upload_date,
        status,
        reviewed_by,
        reviewed_date,
        remarks
    FROM sf8_uploads
    ORDER BY upload_id DESC
";

$result = $conn->query($sql);

$uploads = [];

while ($row = $result->fetch_assoc()) {
    $row["cloudinary_exists"] = !empty($row["cloudinary_url"]);
    $uploads[] = $row;
}

echo json_encode([
    "success" => true,
    "uploads" => $uploads
]);

$conn->close();
?>