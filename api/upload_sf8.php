<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Teacher', 'Clinic Nurse']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to upload an SF8 file.']);
    exit;
}
try {
    require_once __DIR__ . '/sf8_workbook.php';
    include __DIR__ . '/db.php';
    $result = sf8StoreSubmittedUpload($conn, $_FILES['file'] ?? [], getCurrentUser());
    echo json_encode(['success' => true, 'message' => 'Encrypted SF8 file submitted for clinic nurse approval.'] + $result);
    $conn->close();
} catch (Throwable $e) {
    http_response_code($e instanceof Sf8Exception ? 400 : 503);
    echo json_encode(['success' => false, 'message' => $e instanceof Sf8Exception ? $e->getMessage() : 'The encrypted upload could not be completed. Check the server configuration or try again.']);
}
