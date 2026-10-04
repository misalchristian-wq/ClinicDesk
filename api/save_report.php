<?php
header("Content-Type: application/json");
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);
include __DIR__ . '/../db.php';
require_once __DIR__ . '/report_sources.php';

$data = json_decode(file_get_contents("php://input"), true);

$report_key = trim($data["report_key"] ?? "");
$school_year = trim($data["school_year"] ?? "");
$report_data = $data["report_data"] ?? null;
$saved_by = trim((string)(getCurrentUser()['full_name'] ?? 'Clinic Nurse'));

if (!in_array($report_key, ['box1','table1_a','table1_b','box2_3','box4','box5_6','box8_9','box10_11'], true)
    || !preg_match('/^\\d{4}-\\d{4}$/', $school_year) || !is_array($report_data)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields: report_key, school_year, report_data"
    ]);
    exit;
}

$requiredAnswers = [
    'box2_3' => ['hasSchoolClinic' => 'school clinic', 'visitedBySDO' => 'SDO visit', 'waterForDrinking' => 'drinking water'],
    'box4' => ['hasGuidanceOffice' => 'guidance office', 'hasMentalHealthTraining' => 'mental health training'],
    'box5_6' => ['hasSupportCenter' => 'learner support center'],
    'box8_9' => ['drugEducation' => 'drug education', 'hasCanteen' => 'canteen', 'hasKitchen' => 'kitchen'],
];
foreach ($requiredAnswers[$report_key] ?? [] as $field => $label) {
    if (!in_array($report_data[$field] ?? null, ['Yes', 'No'], true)) {
        echo json_encode(['success' => false, 'message' => 'Please answer the ' . $label . ' question before saving.']);
        exit;
    }
}

$json_data = json_encode($report_data);

try {
    $sourceSnapshot = reportSourceSnapshot($conn, $report_key, $school_year);
    $snapshotJson = json_encode($sourceSnapshot);
    $conn->begin_transaction();
    $oldStmt = $conn->prepare('SELECT * FROM report_saved_data WHERE report_key=? AND school_year=? FOR UPDATE');
    $oldStmt->bind_param('ss', $report_key, $school_year);
    $oldStmt->execute();
    $old = $oldStmt->get_result()->fetch_assoc();
    $oldStmt->close();
    if ($old && $sourceSnapshot !== [] && $old['source_snapshot'] !== $snapshotJson
        && !hash_equals(hash('sha256', $snapshotJson), (string)($data['source_review_token'] ?? ''))) {
        $conn->rollback();
        http_response_code(409);
        echo json_encode(['success'=>false,'message'=>'Student records changed. Open the saved report, review the latest counts, then save again.']);
        exit;
    }
    if ($old) {
        $archive = $conn->prepare("INSERT INTO report_saved_revisions (report_id,report_key,school_year,report_data,source_snapshot,saved_by,saved_at,action,acted_by) VALUES (?,?,?,?,?,?,?,'replaced',?)");
        $archive->bind_param('isssssss', $old['report_id'], $old['report_key'], $old['school_year'], $old['report_data'], $old['source_snapshot'], $old['saved_by'], $old['saved_at'], $saved_by);
        $archive->execute();
        $archive->close();
        $stmt = $conn->prepare('UPDATE report_saved_data SET report_data=?,source_snapshot=?,saved_by=?,saved_at=NOW() WHERE report_id=?');
        $stmt->bind_param('sssi', $json_data, $snapshotJson, $saved_by, $old['report_id']);
    } else {
        $stmt = $conn->prepare('INSERT INTO report_saved_data (report_key,school_year,report_data,source_snapshot,saved_by) VALUES (?,?,?,?,?)');
        $stmt->bind_param('sssss', $report_key, $school_year, $json_data, $snapshotJson, $saved_by);
    }
    $stmt->execute();
    $stmt->close();
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Report saved successfully.']);
} catch (Throwable $e) {
    try { $conn->rollback(); } catch (Throwable $ignored) {}
    error_log('ClinicDesk save report: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Could not save the report. Please try again.']);
}
?>
