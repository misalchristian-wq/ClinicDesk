<?php
header('Content-Type: application/json');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Use POST to delete a saved report.']);
    exit;
}
include __DIR__ . '/../db.php';
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$reportId = filter_var($input['report_id'] ?? null, FILTER_VALIDATE_INT);
$key = trim((string)($input['report_key'] ?? ''));
$year = trim((string)($input['school_year'] ?? ''));
if (!$reportId || !preg_match('/^\d{4}-\d{4}$/', $year) || !in_array($key, ['box1','table1_a','table1_b','box2_3','box4','box5_6','box8_9','box10_11'], true)) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Select a valid saved report.']);
    exit;
}
try {
    $conn->begin_transaction();
    $stmt = $conn->prepare('SELECT * FROM report_saved_data WHERE report_id=? AND report_key=? AND school_year=? FOR UPDATE');
    $stmt->bind_param('iss', $reportId, $key, $year);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$old) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Saved report no longer exists.']);
        exit;
    }
    $actor = (string)(getCurrentUser()['full_name'] ?? 'Clinic Nurse');
    $archive = $conn->prepare("INSERT INTO report_saved_revisions (report_id,report_key,school_year,report_data,source_snapshot,saved_by,saved_at,action,acted_by) VALUES (?,?,?,?,?,?,?,'deleted',?)");
    $archive->bind_param('isssssss', $old['report_id'], $old['report_key'], $old['school_year'], $old['report_data'], $old['source_snapshot'], $old['saved_by'], $old['saved_at'], $actor);
    $archive->execute();
    $archive->close();
    $delete = $conn->prepare('DELETE FROM report_saved_data WHERE report_id=?');
    $delete->bind_param('i', $reportId);
    $delete->execute();
    $delete->close();
    $conn->commit();
    echo json_encode(['success'=>true,'message'=>'Saved report deleted. Its previous version was archived.']);
} catch (Throwable $e) {
    try { $conn->rollback(); } catch (Throwable $ignored) {}
    error_log('ClinicDesk delete report: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['success'=>false,'message'=>'Could not delete the saved report. Please try again.']);
}
