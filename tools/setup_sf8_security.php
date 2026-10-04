<?php
// CLI only. Never display or regenerate an existing encryption key.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../api/bootstrap.php';

try {
    $path = clinicSecurityConfigPath();
    $directory = dirname($path);
    $webRoot = realpath(dirname(__DIR__, 2));
    $normalized = strtolower(str_replace('\\', '/', $path));
    if ($webRoot && str_starts_with($normalized, strtolower(str_replace('\\', '/', $webRoot)) . '/')) {
        throw new RuntimeException('Private configuration must be outside the web root.');
    }
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) throw new RuntimeException('Unable to create the private configuration directory.');
    if (is_file($path)) {
        clinicSecurityConfig();
        echo "Existing security configuration retained; no keys were changed.\n";
    } else {
        $key = random_bytes(32);
        $id = substr(hash('sha256', $key), 0, 16);
        // Import current Cloudinary credentials without printing or adding them to Git.
        require __DIR__ . '/../api/cloudinary_config.php';
        $configuration = ['active_sf8_key_id' => $id, 'sf8_encryption_keys' => [$id => base64_encode($key)],
            'local_jwt_secret' => base64_encode(random_bytes(48)),
            'cloudinary_cloud_name' => $cloudinary_cloud_name, 'cloudinary_api_key' => $cloudinary_api_key,
            'cloudinary_api_secret' => $cloudinary_api_secret];
        $handle = fopen($path, 'x');
        if (!$handle) throw new RuntimeException('Unable to create private security configuration.');
        @chmod($path, 0600);
        $json = json_encode($configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (fwrite($handle, $json) !== strlen($json)) throw new RuntimeException('Unable to save private security configuration.');
        fclose($handle);
        echo "SF8 encryption and login signing keys created outside the web root.\n";
    }
    if (in_array('--enable-xampp-zip', $argv, true)) {
        $ini = php_ini_loaded_file();
        $contents = file_get_contents($ini);
        $updated = preg_replace('/^;\s*extension=zip\s*$/m', 'extension=zip', $contents);
        if ($updated !== $contents) {
            $backup = $ini . '.clinicdesk-sf8-backup';
            if (!is_file($backup)) copy($ini, $backup);
            if (file_put_contents($ini, $updated) === false) throw new RuntimeException('Unable to enable the PHP ZIP extension.');
            echo "PHP ZIP extension enabled. Restart Apache if it is already running.\n";
        }
    }
    if (in_array('--verify-cloud', $argv, true)) require __DIR__ . '/../tests/sf8_cloud_test.php';
    echo "Setup complete. Keep a secure backup of the private configuration; it is required to decrypt uploaded files.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "SF8 security setup failed: " . $e->getMessage() . "\n");
    exit(1);
}
