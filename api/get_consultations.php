<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);

try {
    $recordId = filter_var($_GET['record_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$recordId || $recordId < 1) throw new InvalidArgumentException('Select a student first.');
    require __DIR__ . '/db.php';
    $conn->set_charset('utf8mb4');
    $stmt = $conn->prepare('SELECT c.consultation_id, c.symptoms, c.care_given, c.follow_up_date,
        c.notes, c.recorded_at, a.full_name AS recorded_by
        FROM consultations c LEFT JOIN local_accounts a ON a.account_id=c.recorded_by_account_id
        WHERE c.record_id=? ORDER BY c.recorded_at DESC, c.consultation_id DESC LIMIT 20');
    $stmt->bind_param('i', $recordId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['success' => true, 'consultations' => $rows]);
} catch (Throwable $e) {
    if (!($e instanceof InvalidArgumentException)) error_log('ClinicDesk consultation read: ' . $e->getMessage());
    http_response_code($e instanceof InvalidArgumentException ? 422 : 503);
    echo json_encode(['success' => false, 'message' => $e instanceof InvalidArgumentException
        ? $e->getMessage() : 'Consultations could not be loaded.']);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
