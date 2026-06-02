<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

// Get or create default upload_id for consultations
$defaultUploadId = null;
$checkDefault = $conn->query("SELECT upload_id FROM sf8_uploads WHERE file_name = 'CONSULTATION_DEFAULT' LIMIT 1");
if ($checkDefault && $checkDefault->num_rows > 0) {
    $row = $checkDefault->fetch_assoc();
    $defaultUploadId = $row['upload_id'];
} else {
    // Create default upload record
    $conn->query("INSERT INTO sf8_uploads (file_name, cloudinary_url, uploaded_by_email, status) VALUES ('CONSULTATION_DEFAULT', '', 'system@clinicdesk.com', 'Approved')");
    $defaultUploadId = $conn->insert_id;
}

$raw_input = file_get_contents("php://input");
if (empty($raw_input)) {
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit;
}

$data = json_decode($raw_input, true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    exit;
}

$record_id = isset($data["record_id"]) ? intval($data["record_id"]) : 0;
$common_illnesses = isset($data["common_illnesses"]) ? trim($data["common_illnesses"]) : "";
$symptoms = isset($data["symptoms"]) ? trim($data["symptoms"]) : "";
$medication = isset($data["medication"]) ? trim($data["medication"]) : "";
$notes = isset($data["notes"]) ? trim($data["notes"]) : "";

if ($record_id <= 0) {
    echo json_encode(["success" => false, "message" => "Record ID required"]);
    exit;
}

// Get student info
$studentStmt = $conn->prepare("SELECT learner_name, sex, age, grade_level, section FROM sf8_student_records WHERE record_id = ?");
$studentStmt->bind_param("i", $record_id);
$studentStmt->execute();
$student = $studentStmt->get_result()->fetch_assoc();
$studentStmt->close();

if (!$student) {
    echo json_encode(["success" => false, "message" => "Student not found"]);
    exit;
}

$hasFindings = (!empty($common_illnesses) || !empty($symptoms)) ? 1 : 0;

// Save to consultations table
$stmt = $conn->prepare("INSERT INTO consultations (record_id, common_illnesses, symptoms, medication, notes) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $record_id, $common_illnesses, $symptoms, $medication, $notes);
$stmt->execute();
$stmt->close();

// Check if OKD/LHAS record already exists for this student
$checkStmt = $conn->prepare("SELECT okd_lhas_id FROM okd_lhas_records WHERE student_record_id = ? AND screening_type = 'Consultation' LIMIT 1");
$checkStmt->bind_param("i", $record_id);
$checkStmt->execute();
$existing = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if ($existing) {
    // Update existing record - use default upload_id
    $updateStmt = $conn->prepare("
        UPDATE okd_lhas_records 
        SET upload_id = ?,
            masterlisted = masterlisted + 1,
            screened = screened + 1,
            findings = findings + ?,
            remarks = CONCAT(IFNULL(remarks, ''), '\nConsultation: ', ?)
        WHERE okd_lhas_id = ?
    ");
    $updateStmt->bind_param("iisi", $defaultUploadId, $hasFindings, $notes, $existing['okd_lhas_id']);
    $updateStmt->execute();
    $updateStmt->close();
} else {
    // Insert new OKD/LHAS record with default upload_id
    $learnerName = $student['learner_name'];
    $sex = $student['sex'];
    $age = $student['age'];
    $screeningType = "Consultation";
    $masterlisted = 1;
    $screened = 1;
    $findings = $hasFindings;
    $remarks = "Consultation: " . $notes;
    
    $insertStmt = $conn->prepare("
        INSERT INTO okd_lhas_records (
            upload_id, student_record_id, lrn, learner_name, sex, age, screening_type,
            masterlisted, screened, findings, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insertStmt->bind_param("iisssssiiis", 
        $defaultUploadId, $record_id, $record_id, $learnerName, $sex, $age, $screeningType,
        $masterlisted, $screened, $findings, $remarks
    );
    $insertStmt->execute();
    $insertStmt->close();
}

echo json_encode([
    "success" => true,
    "message" => "Consultation saved successfully!",
    "has_findings" => $hasFindings
]);

$conn->close();
?>