<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['IT Admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to update an account.']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) $data = [];
    $accountId = filter_var($data['account_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $fullName = trim((string)($data['full_name'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $role = trim((string)($data['role'] ?? ''));
    $status = trim((string)($data['status'] ?? ''));
    $newPassword = (string)($data['password'] ?? '');

    if (!$accountId || $fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || !in_array($role, ['Clinic Nurse', 'School Admin', 'IT Admin'], true)
        || !in_array($status, ['Active', 'Disabled', 'Inactive'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Enter a valid account, name, email, role, and status.']);
        exit;
    }
    if ($newPassword !== '' && (strlen($newPassword) < 12
        || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[A-Z]/', $newPassword)
        || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[^a-zA-Z0-9]/', $newPassword))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'The new password must have at least 12 characters, upper and lower case, a number, and a symbol.']);
        exit;
    }

    require __DIR__ . '/db.php';
    $exists = $conn->prepare('SELECT account_id FROM local_accounts WHERE account_id = ?');
    $exists->bind_param('i', $accountId);
    $exists->execute();
    $found = (bool)$exists->get_result()->fetch_assoc();
    $exists->close();
    if (!$found) {
        $conn->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Account not found.']);
        exit;
    }
    if ($newPassword === '') {
        $stmt = $conn->prepare('UPDATE local_accounts SET full_name = ?, email = ?, role = ?, status = ? WHERE account_id = ?');
        $stmt->bind_param('ssssi', $fullName, $email, $role, $status, $accountId);
    } else {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE local_accounts SET full_name = ?, email = ?, role = ?, status = ?, password_hash = ? WHERE account_id = ?');
        $stmt->bind_param('sssssi', $fullName, $email, $role, $status, $passwordHash, $accountId);
    }
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'message' => 'Local account updated successfully.']);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Account update is unavailable. Please try again.']);
}
