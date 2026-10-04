<?php
declare(strict_types=1);

header('Content-Type: application/json');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);

try {
    $recordId = filter_var($_GET['record_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$recordId || $recordId < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Select a student record first.']);
        exit;
    }
    require __DIR__ . '/db.php';
    $stmt = $conn->prepare('SELECT * FROM student_health_inputs WHERE record_id = ? LIMIT 1');
    $stmt->bind_param('i', $recordId);
    $stmt->execute();
    $input = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'health_input' => $input ?: null]);
} catch (Throwable $e) {
    error_log('ClinicDesk health assessment read: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Health assessment could not be loaded.']);
}
