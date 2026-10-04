<?php
// Configuration and secret values must never be exposed over HTTP.
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Not found.']);
