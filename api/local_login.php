<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to sign in.']  );
    exit;
}
try {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = trim($data['email'] ?? '');
    $submittedPassword = (string)($data['password'] ?? '');
    $selectedRole = trim($data['role'] ?? '');
    if ($email === '' || $submittedPassword === '' || $selectedRole === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email, password, and role are required.']);
        exit;
    }
    include __DIR__ . '/db.php';
    $stmt = $conn->prepare('SELECT account_id, full_name, email, password_hash, role, status FROM local_accounts WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    if (!$user || $user['status'] !== 'Active' || $user['role'] !== $selectedRole || !password_verify($submittedPassword, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials or inactive account for the selected role.']);
        exit;
    }
    $token = clinicIssueLocalToken($user);
    unset($user['password_hash']);
    echo json_encode(['success' => true, 'message' => 'Login successful.', 'token' => $token, 'expires_in' => 3600, 'user' => $user]);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Sign-in is currently unavailable. Check the database and SF8 security configuration.']);
}
