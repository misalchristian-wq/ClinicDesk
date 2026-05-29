<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";
include "send_email_notification.php";

$data = json_decode(file_get_contents("php://input"), true);

$upload_id = $data["upload_id"] ?? "";
$reviewed_by = trim($data["reviewed_by"] ?? "Clinic Nurse");
$remarks = trim($data["remarks"] ?? "Rejected by clinic nurse.");

if ($upload_id === "") {
    echo json_encode([
        "success" => false,
        "message" => "Upload ID is required."
    ]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE sf8_uploads
    SET status = 'Rejected',
        reviewed_by = ?,
        reviewed_date = NOW(),
        remarks = ?
    WHERE upload_id = ?
");

$stmt->bind_param("ssi", $reviewed_by, $remarks, $upload_id);

if ($stmt->execute()) {
    $getStmt = $conn->prepare("
        SELECT file_name, uploaded_by_email 
        FROM sf8_uploads 
        WHERE upload_id = ?
    ");

    $getStmt->bind_param("i", $upload_id);
    $getStmt->execute();

    $result = $getStmt->get_result();
    $upload = $result->fetch_assoc();

    $getStmt->close();

    $emailResult = null;

    if ($upload) {
        $teacherEmail = $upload["uploaded_by_email"];
        $fileName = $upload["file_name"];

        $emailSubject = "ClinicDesk SF8 Upload Rejected";

        $emailBody = "
            <h2>SF8 Upload Rejected</h2>
            <p>Good day,</p>
            <p>Your uploaded SF8 Excel file has been reviewed and rejected by the clinic nurse.</p>
            <p><strong>File Name:</strong> {$fileName}</p>
            <p><strong>Status:</strong> Rejected</p>
            <p><strong>Reviewed By:</strong> {$reviewed_by}</p>
            <p><strong>Remarks:</strong> {$remarks}</p>
            <p>Please check the file and upload a corrected version if needed.</p>
            <br>
            <p>This is an automated message from ClinicDesk.</p>
        ";

        $emailResult = sendStatusEmail($teacherEmail, $emailSubject, $emailBody);
    }

    echo json_encode([
        "success" => true,
        "message" => "Upload rejected successfully. Email notification sent to teacher.",
        "email_status" => $emailResult
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Reject failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>