<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);
try {
    require_once __DIR__ . '/sf8_workbook.php';
    include __DIR__ . '/db.php';
    $id = filter_input(INPUT_GET, 'upload_id', FILTER_VALIDATE_INT);
    if (!$id || $id < 1) throw new Sf8Exception('A valid upload ID is required.');
    $stmt = $conn->prepare('SELECT * FROM sf8_uploads WHERE upload_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $upload = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$upload) throw new Sf8Exception('Upload record not found.');
    $tempFile = sf8DownloadForParsing($upload);
    $parsed = sf8ParseWorkbook($tempFile, $upload['report_code']);
    echo json_encode(['success' => true, 'upload' => $upload, 'report_code' => $upload['report_code'],
        'encrypted' => sf8IsEncryptedUpload($upload), 'header' => $parsed['header'],
        'records' => $parsed['records'], 'students' => $parsed['records'], 'total_rows' => count($parsed['records'])]);
    $conn->close();
} catch (Throwable $e) {
    http_response_code($e instanceof Sf8Exception ? 400 : 503);
    echo json_encode(['success' => false, 'message' => $e instanceof Sf8Exception ? $e->getMessage() : 'Unable to preview the SF8 file. Check the server configuration or try again.']);
} finally {
    if (isset($tempFile)) @unlink($tempFile);
}
