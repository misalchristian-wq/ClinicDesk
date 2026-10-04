<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to upload an SF8 file.']);
    exit;
}
try {
    require_once __DIR__ . '/sf8_workbook.php';
    include __DIR__ . '/db.php';
    $result = sf8StoreSubmittedUpload($conn, $_FILES['file'] ?? [], getCurrentUser());
    $stmt = $conn->prepare('SELECT * FROM sf8_uploads WHERE upload_id = ?');
    $stmt->bind_param('i', $result['upload_id']);
    $stmt->execute();
    $upload = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $tempFile = sf8DownloadForParsing($upload);
    $parsed = sf8ParseWorkbook($tempFile, $upload['report_code']);
    echo json_encode(['success' => true, 'message' => 'Encrypted file uploaded. Review the decrypted records below.'] + $result + $parsed);
    $conn->close();
} catch (Throwable $e) {
    http_response_code($e instanceof Sf8Exception ? 400 : 503);
    echo json_encode(['success' => false, 'message' => $e instanceof Sf8Exception ? $e->getMessage() : 'The secure upload or preview could not be completed. You can reopen a submitted file from SF8 Uploads.']);
} finally {
    if (isset($tempFile)) @unlink($tempFile);
}
