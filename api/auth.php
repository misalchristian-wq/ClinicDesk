<?php
require_once __DIR__ . '/bootstrap.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Kreait\Firebase\Factory;

function authenticate(): void {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer ([^\s]+)$/', $header, $match)) sendUnauthorized('Please sign in again.');
    $token = $match[1];
    try {
        $parts = explode('.', $token);
        $jwtHeader = json_decode(base64_decode(strtr($parts[0] ?? '', '-_', '+/')), true);
        $user = ($jwtHeader['alg'] ?? '') === 'HS256' ? verifyLocalJWT($token) : verifyFirebaseToken($token);
    } catch (Throwable $e) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Secure authentication is not configured. Contact the administrator.']);
        exit;
    }
    if (!$user) sendUnauthorized('Your session is invalid or expired. Please sign in again.');
    $_SERVER['user_data'] = $user;
}

function verifyFirebaseToken(string $token): ?array {
    $credentials = getenv('FIREBASE_CREDENTIALS') ?: __DIR__ . '/firebase-service-account.json';
    if (!is_file($credentials)) return null;
    try {
        $auth = (new Factory)->withServiceAccount($credentials)->createAuth();
        $verified = $auth->verifyIdToken($token, true);
        $email = (string)$verified->claims()->get('email');
        if ($email === '') return null;
        return ['type' => 'firebase', 'uid' => $verified->claims()->get('sub'), 'email' => $email, 'role' => 'Teacher'];
    } catch (Throwable $e) {
        return null;
    }
}

function verifyLocalJWT(string $token): ?array {
    $secret = clinicJwtSecret();
    try {
        $claims = (array)JWT::decode($token, new Key($secret, 'HS256'));
        if (($claims['iss'] ?? '') !== 'clinicdesk' || ($claims['aud'] ?? '') !== 'clinicdesk-sf8' || !isset($claims['exp'], $claims['account_id'])) return null;
    } catch (Throwable $e) {
        return null;
    }
    // Re-read the role/status so account deactivation takes effect immediately.
    require __DIR__ . '/db.php';
    $stmt = $conn->prepare('SELECT account_id, full_name, email, role, status FROM local_accounts WHERE account_id = ? LIMIT 1');
    $stmt->bind_param('i', $claims['account_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    if (!$user || $user['status'] !== 'Active') return null;
    $user['type'] = 'local';
    return $user;
}

function clinicIssueLocalToken(array $user): string {
    $now = time();
    return JWT::encode(['iss' => 'clinicdesk', 'aud' => 'clinicdesk-sf8', 'iat' => $now, 'exp' => $now + 3600,
        'account_id' => (int)$user['account_id']], clinicJwtSecret(), 'HS256');
}

function sendUnauthorized(string $message): void {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function getCurrentUser(): ?array { return $_SERVER['user_data'] ?? null; }

function requireRole(array $roles): void {
    $user = getCurrentUser();
    if (!$user || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission to perform this action.']);
        exit;
    }
}
