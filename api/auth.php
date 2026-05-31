<?php
// api/auth.php – Authentication Middleware
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/bootstrap.php'; // loads .env and Composer autoload

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;

// Secret key for local JWT (must be defined in .env)
define('LOCAL_JWT_SECRET', $_ENV['LOCAL_JWT_SECRET'] ?? '');

if (empty(LOCAL_JWT_SECRET)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server configuration error: JWT secret missing']);
    exit;
}

/**
 * Main authentication function – call at the beginning of every protected endpoint.
 */
function authenticate() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (empty($authHeader)) {
        sendUnauthorized('Missing Authorization header');
    }

    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        sendUnauthorized('Invalid Authorization header format. Use: Bearer <token>');
    }
    $token = $matches[1];

    // Try Firebase token (teachers)
    $userData = verifyFirebaseToken($token);
    if ($userData) {
        $_SERVER['user_data'] = $userData;
        return;
    }

    // Try local JWT (nurses, admins)
    $userData = verifyLocalJWT($token);
    if ($userData) {
        $_SERVER['user_data'] = $userData;
        return;
    }

    sendUnauthorized('Invalid or expired token');
}

/**
 * Verify Firebase ID token.
 */
function verifyFirebaseToken($token) {

if (!file_exists(FIREBASE_CREDENTIALS)) {
        // No Firebase credentials – skip
        return null;
    }
    try {

    
        $factory = (new Factory)
            ->withServiceAccount(FIREBASE_CREDENTIALS);
        $auth = $factory->createAuth();
        $verifiedIdToken = $auth->verifyIdToken($token);
        $uid = $verifiedIdToken->claims()->get('sub');
        $email = $verifiedIdToken->claims()->get('email');
        
        return [
            'type' => 'firebase',
            'uid' => $uid,
            'email' => $email,
            'role' => 'Teacher'
        ];
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Verify local JWT issued after local login.
 */
function verifyLocalJWT($token) {
    try {
        $decoded = JWT::decode($token, new Key(LOCAL_JWT_SECRET, 'HS256'));
        $decodedArray = (array) $decoded;
        
        if (isset($decodedArray['exp']) && $decodedArray['exp'] < time()) {
            return null;
        }
        
        return [
            'type' => 'local',
            'account_id' => $decodedArray['account_id'],
            'full_name' => $decodedArray['full_name'],
            'email' => $decodedArray['email'],
            'role' => $decodedArray['role']
        ];
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Send 401 JSON response and exit.
 */
function sendUnauthorized($message) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

/**
 * Get current authenticated user data.
 */
function getCurrentUser() {
    return $_SERVER['user_data'] ?? null;
}

/**
 * Check if current user has one of the allowed roles.
 * If not, sends 403 and exits.
 */
function requireRole($allowedRoles) {
    $user = getCurrentUser();
    if (!$user) {
        sendUnauthorized('Not authenticated');
    }
    if (!in_array($user['role'], (array)$allowedRoles)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Forbidden: insufficient privileges']);
        exit;
    }
}