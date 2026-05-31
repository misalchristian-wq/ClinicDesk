<?php
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: application/json');
echo json_encode([
    'LOCAL_JWT_SECRET' => LOCAL_JWT_SECRET,
    'FIREBASE_CREDENTIALS' => FIREBASE_CREDENTIALS,
    'file_exists' => file_exists(FIREBASE_CREDENTIALS)
]);