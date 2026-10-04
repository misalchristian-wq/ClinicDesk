<?php
// Integration test: requires local Apache, MySQL, and the SF8 JWT configuration.
require_once __DIR__ . '/../api/auth.php';
require __DIR__ . '/../api/db.php';

function updateTestRequest(string $url, array $payload, ?string $token = null): array {
    $request = curl_init($url);
    $headers = ['Content-Type: application/json'];
    if ($token !== null) $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt_array($request, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $body = curl_exec($request);
    $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
    if ($body === false) throw new RuntimeException('HTTP request failed: ' . curl_error($request));
    curl_close($request);
    $decoded = json_decode($body, true);
    if (!is_array($decoded) || !isset($decoded['success'])) throw new RuntimeException('API did not return JSON with success.');
    return [$status, $decoded];
}

$accountId = null;
try {
    $email = 'clinicdesk-update-test-' . bin2hex(random_bytes(8)) . '@example.test';
    $oldPassword = 'Temporary!Aa1' . bin2hex(random_bytes(8));
    $newPassword = 'Changed!Bb2' . bin2hex(random_bytes(8));
    $name = 'Temporary Test Administrator';
    $oldHash = password_hash($oldPassword, PASSWORD_DEFAULT);
    $role = 'IT Admin';
    $status = 'Active';
    $insert = $conn->prepare('INSERT INTO local_accounts (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)');
    $insert->bind_param('sssss', $name, $email, $oldHash, $role, $status);
    $insert->execute();
    $accountId = $conn->insert_id;
    $insert->close();

    $base = rtrim(getenv('CLINICDESK_TEST_URL') ?: 'http://localhost/ClinicDesk', '/');
    $payload = ['account_id' => $accountId, 'full_name' => $name, 'email' => $email,
        'role' => $role, 'status' => $status, 'password' => $newPassword];
    [$http, $result] = updateTestRequest($base . '/api/update_local_account.php', $payload);
    if ($http !== 401 || $result['success'] !== false) throw new RuntimeException('Unauthenticated update was allowed.');

    $token = clinicIssueLocalToken(['account_id' => $accountId]);
    [$http, $result] = updateTestRequest($base . '/api/upload_sf8.php', [], $token);
    if ($http !== 403 || $result['success'] !== false) throw new RuntimeException('SF8 upload did not recognize the Bearer token and reject the wrong role.');
    [$http, $result] = updateTestRequest($base . '/api/update_local_account.php', $payload, $token);
    if ($http !== 200 || $result['success'] !== true) throw new RuntimeException('Authenticated password update failed: HTTP ' . $http . ', ' . ($result['message'] ?? 'unknown error'));

    $select = $conn->prepare('SELECT password_hash FROM local_accounts WHERE account_id = ?');
    $select->bind_param('i', $accountId);
    $select->execute();
    $hash = $select->get_result()->fetch_assoc()['password_hash'] ?? '';
    $select->close();
    if (!password_verify($newPassword, $hash) || password_verify($oldPassword, $hash)) {
        throw new RuntimeException('Updated hash did not replace the old password.');
    }
    [$http, $result] = updateTestRequest($base . '/api/local_login.php',
        ['email' => $email, 'password' => $newPassword, 'role' => $role]);
    if ($http !== 200 || $result['success'] !== true) throw new RuntimeException('Sign-in with new password failed.');

    $payload['password'] = 'weak';
    [$http, $result] = updateTestRequest($base . '/api/update_local_account.php', $payload, $token);
    if ($http !== 400 || $result['success'] !== false) throw new RuntimeException('Weak password was accepted.');
    echo "PASS: authenticated IT Admin password update, new-password sign-in, unauthorized and weak-password rejection, SF8 Bearer header delivery.\n";
} finally {
    if ($accountId !== null) {
        $delete = $conn->prepare('DELETE FROM local_accounts WHERE account_id = ?');
        $delete->bind_param('i', $accountId);
        $delete->execute();
        $delete->close();
    }
    $conn->close();
}
