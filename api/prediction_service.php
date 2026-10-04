<?php
declare(strict_types=1);

header('Content-Type: application/json');
header('Cache-Control: no-store');
ini_set('display_errors', '0');

require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);

function predictionServiceStatus(): array {
    $ch = curl_init('http://127.0.0.1:5001/health');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 1,
        CURLOPT_TIMEOUT => 2,
        CURLOPT_PROXY => '',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $health = is_string($body) ? json_decode($body, true) : null;
    if ($code === 200 && is_array($health) && ($health['status'] ?? '') === 'ok') {
        return [
            'state' => 'ready',
            'model' => (string)($health['model'] ?? 'Model'),
            'version' => (string)($health['version'] ?? ''),
            'message' => 'Prediction service is ready.',
        ];
    }
    $stamp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'clinicdesk-ml-start.time';
    $startedAt = is_file($stamp) ? (int)@file_get_contents($stamp) : 0;
    if ($startedAt > 0 && time() - $startedAt < 30) {
        return ['state' => 'starting', 'message' => 'The model is starting. This can take a few moments.'];
    }
    return ['state' => 'offline', 'message' => 'Prediction service is offline. Activate it to run predictions.'];
}

function predictionServiceReply(int $code, array $data): void {
    http_response_code($code);
    echo json_encode(['success' => $code < 400] + $data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    predictionServiceReply(200, predictionServiceStatus());
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    predictionServiceReply(405, ['message' => 'Use GET for status or POST to activate.']);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $nursePassword = is_array($data) ? (string)($data['password'] ?? '') : '';
    if ($nursePassword === '') {
        predictionServiceReply(400, ['message' => 'Enter your account password to activate predictions.']);
    }

    $user = getCurrentUser();
    if (($user['type'] ?? '') !== 'local') {
        predictionServiceReply(403, ['message' => 'A Clinic Nurse account is required.']);
    }
    $accountId = (int)$user['account_id'];
    $attemptFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'clinicdesk-ml-auth-' . $accountId . '.json';
    $attemptHandle = @fopen($attemptFile, 'c+');
    if (!$attemptHandle || !flock($attemptHandle, LOCK_EX)) {
        predictionServiceReply(503, ['message' => 'Password verification is temporarily unavailable.']);
    }
    $attemptState = json_decode(stream_get_contents($attemptHandle), true);
    if (!is_array($attemptState)) $attemptState = ['count' => 0, 'until' => 0];
    if ((int)($attemptState['until'] ?? 0) > time()) {
        predictionServiceReply(429, ['message' => 'Too many incorrect passwords. Try again in 15 minutes.']);
    }
    require __DIR__ . '/db.php';
    $stmt = $conn->prepare("SELECT password_hash FROM local_accounts WHERE account_id = ? AND role = 'Clinic Nurse' AND status = 'Active' LIMIT 1");
    $stmt->bind_param('i', $accountId);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    if (!$account || !password_verify($nursePassword, $account['password_hash'])) {
        $attemptState['count'] = (int)($attemptState['count'] ?? 0) + 1;
        $attemptState['until'] = $attemptState['count'] >= 5 ? time() + 900 : 0;
        ftruncate($attemptHandle, 0);
        rewind($attemptHandle);
        fwrite($attemptHandle, json_encode($attemptState));
        fflush($attemptHandle);
        predictionServiceReply(401, ['message' => 'Password is incorrect. Please try again.']);
    }
    ftruncate($attemptHandle, 0);
    rewind($attemptHandle);
    fwrite($attemptHandle, json_encode(['count' => 0, 'until' => 0]));
    fflush($attemptHandle);
    flock($attemptHandle, LOCK_UN);
    fclose($attemptHandle);

    if (PHP_OS_FAMILY !== 'Windows') {
        predictionServiceReply(503, ['message' => 'In-app activation currently requires the Windows deployment setup.']);
    }
    $python = getenv('CLINICDESK_ML_PYTHON') ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . '.venv-ml' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe';
    $python = realpath($python);
    $app = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'ml_model' . DIRECTORY_SEPARATOR . 'app.py');
    if (!$python || !is_file($python) || !$app) {
        predictionServiceReply(503, ['message' => 'The prediction runtime is not installed. Ask the system maintainer to complete the one-time model setup.']);
    }

    $lock = @fopen(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'clinicdesk-ml-start.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX)) {
        predictionServiceReply(503, ['message' => 'Could not reserve the model starter. Please try again.']);
    }
    $status = predictionServiceStatus();
    if ($status['state'] !== 'offline') {
        flock($lock, LOCK_UN);
        fclose($lock);
        predictionServiceReply(200, $status);
    }

    $temp = sys_get_temp_dir();
    $quote = static fn(string $value): string => "'" . str_replace("'", "''", $value) . "'";
    $ps = 'Start-Process -FilePath ' . $quote($python)
        . ' -ArgumentList @(' . $quote($app) . ')'
        . ' -WorkingDirectory ' . $quote(dirname($app))
        . ' -WindowStyle Hidden'
        . ' -RedirectStandardOutput ' . $quote($temp . DIRECTORY_SEPARATOR . 'clinicdesk-ml.out.log')
        . ' -RedirectStandardError ' . $quote($temp . DIRECTORY_SEPARATOR . 'clinicdesk-ml.err.log')
        . ' | Out-Null';
    $systemRoot = getenv('SystemRoot') ?: 'C:\\Windows';
    $powershell = $systemRoot . '\\System32\\WindowsPowerShell\\v1.0\\powershell.exe';
    if (!is_file($powershell)) {
        flock($lock, LOCK_UN);
        fclose($lock);
        predictionServiceReply(503, ['message' => 'Windows PowerShell is unavailable to start the model.']);
    }
    $process = @proc_open(
        [$powershell, '-NoProfile', '-NonInteractive', '-Command', $ps],
        [0 => ['file', 'NUL', 'r'],
         1 => ['file', $temp . DIRECTORY_SEPARATOR . 'clinicdesk-ml-launch.out.log', 'a'],
         2 => ['file', $temp . DIRECTORY_SEPARATOR . 'clinicdesk-ml-launch.err.log', 'a']],
        $pipes,
        dirname($app),
        null,
        ['bypass_shell' => true]
    );
    if (!is_resource($process)) {
        flock($lock, LOCK_UN);
        fclose($lock);
        predictionServiceReply(503, ['message' => 'The model could not be started by this web server. Check its Windows permissions.']);
    }
    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
        error_log('ClinicDesk model start failed: launcher exited with ' . $exitCode);
        flock($lock, LOCK_UN);
        fclose($lock);
        predictionServiceReply(503, ['message' => 'The model did not start. Check the Python installation and server log.']);
    }
    file_put_contents($temp . DIRECTORY_SEPARATOR . 'clinicdesk-ml-start.time', (string)time());
    flock($lock, LOCK_UN);
    fclose($lock);

    for ($i = 0; $i < 12; $i++) {
        usleep(500000);
        $status = predictionServiceStatus();
        if ($status['state'] === 'ready') {
            predictionServiceReply(200, $status);
        }
    }
    predictionServiceReply(202, ['state' => 'starting', 'message' => 'The model is loading. Status will refresh automatically.']);
} catch (Throwable $e) {
    error_log('ClinicDesk model activation error: ' . $e->getMessage());
    predictionServiceReply(503, ['message' => 'Prediction service activation failed. Please contact the system maintainer.']);
}
