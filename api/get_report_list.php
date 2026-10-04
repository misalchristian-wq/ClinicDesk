<?php
header("Content-Type: application/json");
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);
include __DIR__ . "/../db.php";
require_once __DIR__ . '/report_sources.php';

$report_key = $_GET["report_key"] ?? "";
$schoolYear = trim((string)($_GET['school_year'] ?? ''));

if ($report_key === "" || !preg_match('/^\\d{4}-\\d{4}$/', $schoolYear)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Report section and school year are required."]);
    exit;
}

$stmt = $conn->prepare("
    SELECT report_id, school_year, saved_by, saved_at, report_data, source_snapshot
    FROM report_saved_data
    WHERE report_key = ? AND school_year = ?
    ORDER BY saved_at DESC
");
$stmt->bind_param("ss", $report_key, $schoolYear);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
try {
$currentSnapshot = reportSourceSnapshot($conn, $report_key, $schoolYear);
$currentHash = hash('sha256', json_encode($currentSnapshot));
while ($row = $result->fetch_assoc()) {
    $savedSnapshot = $row['source_snapshot'] === null ? null : json_decode($row['source_snapshot'], true);
    $changes = $currentSnapshot === [] ? [] : reportSourceChanges($savedSnapshot, $currentSnapshot);
    $reports[] = [
        "report_id" => (int)$row['report_id'],
        "school_year" => $row["school_year"],
        "saved_by" => $row["saved_by"],
        "saved_at" => $row["saved_at"],
        "report_data" => json_decode($row["report_data"], true),
        "needs_update" => count($changes) > 0,
        "current_hash" => $currentHash,
        "changes" => $changes
    ];
}
echo json_encode(["success" => true, "reports" => $reports]);
} catch (Throwable $e) {
    error_log('ClinicDesk report status: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Could not check the saved report. Please try again.']);
}

$stmt->close();
$conn->close();
?>
