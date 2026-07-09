<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
ini_set('display_errors', 0);
error_reporting(E_ALL);

include __DIR__ . '/../db.php';
include __DIR__ . '/record_categories.php';

$data = json_decode(file_get_contents('php://input'), true);
$table = $data['table'] ?? '';
$pk = $data['pk'] ?? 'id';
$id = $data['id'] ?? 0;
$password = trim($data['password'] ?? '');

if (!$table || !$id || !$password) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Verify password against any local account
$accountVerified = false;
$stmt = $conn->prepare("SELECT password_hash FROM local_accounts");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password_hash']) || $password === $row['password_hash']) {
        $accountVerified = true;
        break;
    }
}
$stmt->close();

if (!$accountVerified) {
    echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
    exit;
}

// Map table name to actual table
$cats = clinicRecordCategories();
if (!isset($cats[$table])) {
    echo json_encode(['success' => false, 'message' => 'Invalid category.']);
    exit;
}
$realTable = $cats[$table]['table'];

// Delete
$stmt = $conn->prepare("DELETE FROM `$realTable` WHERE `$pk` = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Record deleted.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $stmt->error]);
}
$stmt->close();
$conn->close();