<?php
declare(strict_types=1);

header('Content-Type: application/json');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to test the model.']);
    exit;
}
try {
    $body = file_get_contents('php://input');
    if (strlen($body) > 8192 || !is_array(json_decode($body, true))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Enter valid test inputs.']);
        exit;
    }
    $ch = curl_init('http://127.0.0.1:5001/predict');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_PROXY => '',
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($response === false || !$code) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Prediction service is offline. Activate it in Prediction Settings.']);
        exit;
    }
    $result = json_decode($response, true);
    if (!is_array($result)) throw new RuntimeException('Model returned an invalid response.');
    http_response_code($code >= 400 && $code < 500 ? 422 : ($code >= 500 ? 502 : 200));
    echo json_encode($result);
} catch (Throwable $e) {
    error_log('ClinicDesk model test: ' . $e->getMessage());
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'The model test could not be completed.']);
}
