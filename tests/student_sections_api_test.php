<?php
require __DIR__ . '/../api/db.php';

function sectionApiRequest(string $url, ?array $payload = null, ?string $token = null): array {
    $request = curl_init($url);
    $headers = ['Content-Type: application/json'];
    if ($token !== null) $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt_array($request, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => $headers]);
    if ($payload !== null) curl_setopt_array($request, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($payload)]);
    $body = curl_exec($request);
    $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
    $error = curl_error($request);
    curl_close($request);
    if ($body === false) throw new RuntimeException('Request failed: ' . $error);
    $data = json_decode($body, true);
    if (!is_array($data) || !array_key_exists('success', $data)) throw new RuntimeException('API did not return JSON.');
    return [$status, $data];
}

$accountId = null;
$studentId = null;
try {
    $year = $conn->query('SELECT year_label FROM school_years WHERE is_active = 1 LIMIT 1')->fetch_assoc()['year_label'] ?? null;
    if (!$year) throw new RuntimeException('An active school year is required for this test.');
    $email = 'section-test-' . bin2hex(random_bytes(8)) . '@example.invalid';
    $password = 'Test!Nurse1' . bin2hex(random_bytes(8));
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $name = 'Temporary Section Test Nurse';
    $role = 'Clinic Nurse'; $status = 'Active';
    $insert = $conn->prepare('INSERT INTO local_accounts (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)');
    $insert->bind_param('sssss', $name, $email, $hash, $role, $status);
    $insert->execute();
    $accountId = $insert->insert_id;
    $insert->close();

    $base = rtrim(getenv('CLINICDESK_TEST_URL') ?: 'http://localhost/ClinicDesk', '/');
    [$statusCode, $login] = sectionApiRequest($base . '/api/local_login.php', ['email' => $email, 'password' => $password, 'role' => $role]);
    if ($statusCode !== 200 || empty($login['token'])) throw new RuntimeException('Temporary nurse sign-in failed.');
    $token = $login['token'];
    $reportUrl = $base . '/api/get_box5_box6_report.php?school_year=' . rawurlencode($year);
    [$statusCode, $before] = sectionApiRequest($reportUrl, null, $token);
    if ($statusCode !== 200 || !$before['success']) throw new RuntimeException('Report baseline failed.');

    $lrn = (string)random_int(800000000000, 899999999999);
    $payload = ['lrn' => $lrn, 'learner_name' => 'Temporary Section Test Student', 'sex' => 'Female',
        'age' => '13', 'weight_kg' => 48, 'height_m' => 1.5, 'school_year' => $year,
        'grade_level' => '8'];
    [$statusCode, $denied] = sectionApiRequest($base . '/api/add_manual_student.php', $payload);
    if ($statusCode !== 401 || $denied['success']) throw new RuntimeException('Unauthenticated manual add was allowed.');
    [$statusCode, $added] = sectionApiRequest($base . '/api/add_manual_student.php', $payload, $token);
    if ($statusCode !== 200 || !$added['success']) throw new RuntimeException('Manual add failed: ' . ($added['message'] ?? 'unknown'));
    $studentId = (int)$added['record']['record_id'];
    foreach (['deworming_wifa_records', 'okd_lhas_records', 'immunization_records', 'tobacco_control_records', 'arh_records'] as $table) {
        $rows = $conn->query("SELECT upload_id FROM `$table` WHERE student_record_id = $studentId")->fetch_all(MYSQLI_ASSOC);
        if (count($rows) !== 1 || $rows[0]['upload_id'] !== null) throw new RuntimeException('Missing placeholder in ' . $table);
    }
    [$statusCode, $after] = sectionApiRequest($reportUrl, null, $token);
    if ($statusCode !== 200 || $after['arh'] !== $before['arh'] || $after['tobacco'] !== $before['tobacco']) {
        throw new RuntimeException('Empty sections changed report totals.');
    }
    $categoryPayload = ['category' => 'immunization', 'school_year' => $year,
        'record' => ['lrn' => $lrn, 'learner_name' => $payload['learner_name'], 'sex' => 'Female',
            'vaccine' => 'Tetanus Diphtheria', 'dose' => '1', 'immunized' => 1, 'remarks' => 'Initial value']];
    [$statusCode, $filled] = sectionApiRequest($base . '/api/add_category_record.php', $categoryPayload, $token);
    if ($statusCode !== 200 || !$filled['success']) throw new RuntimeException('Manual category update failed: ' . ($filled['message'] ?? 'unknown'));
    $row = $conn->query("SELECT upload_id, vaccine, immunized, remarks FROM immunization_records WHERE student_record_id = $studentId")->fetch_assoc();
    if (!$row || $row['upload_id'] === null || $row['vaccine'] !== 'Tetanus Diphtheria' || (int)$row['immunized'] !== 1) {
        throw new RuntimeException('Manual category entry did not fill the placeholder.');
    }
    $categoryPayload['record']['immunized'] = 0;
    $categoryPayload['record']['remarks'] = 'Later value';
    [$statusCode, $filled] = sectionApiRequest($base . '/api/add_category_record.php', $categoryPayload, $token);
    $row = $conn->query("SELECT COUNT(*) AS count_rows, MAX(immunized) AS immunized, MAX(remarks) AS remarks FROM immunization_records WHERE student_record_id = $studentId")->fetch_assoc();
    if ($statusCode !== 200 || !$filled['success'] || (int)$row['count_rows'] !== 1 || (int)$row['immunized'] !== 1 || $row['remarks'] !== 'Initial value') {
        throw new RuntimeException('Manual category update duplicated or overwrote populated data.');
    }
    echo "PASS: nurse manual add creates five empty sections, rejects anonymous writes, leaves report totals unchanged, and manual category data fills blanks once.\n";
} finally {
    if ($studentId !== null) {
        foreach (['deworming_wifa_records', 'okd_lhas_records', 'immunization_records', 'tobacco_control_records', 'arh_records'] as $table) {
            $conn->query("DELETE FROM `$table` WHERE student_record_id = $studentId");
        }
        $conn->query("DELETE FROM sf8_student_records WHERE record_id = $studentId");
    }
    if ($accountId !== null) $conn->query("DELETE FROM local_accounts WHERE account_id = $accountId");
    $conn->close();
}
