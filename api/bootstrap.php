<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Secrets live outside the repository and Apache's document root.
function clinicSecurityConfigPath(): string {
    return getenv('CLINICDESK_SECURITY_CONFIG') ?: dirname(__DIR__, 3) . '/clinicdesk-private/sf8-security.json';
}

function clinicSecurityConfig(): array {
    static $config;
    if ($config !== null) return $config;
    $path = clinicSecurityConfigPath();
    if (!is_file($path)) return $config = [];
    $resolved = realpath($path);
    $webRoot = realpath(dirname(__DIR__, 2));
    if (!$resolved || ($webRoot && str_starts_with(strtolower(str_replace('\\', '/', $resolved)), strtolower(str_replace('\\', '/', $webRoot)) . '/'))) {
        throw new RuntimeException('Security configuration must be outside the web root.');
    }
    $config = json_decode(file_get_contents($path), true);
    if (!is_array($config)) throw new RuntimeException('Security configuration is invalid.');
    return $config;
}

function clinicSecret(string $environmentName, string $configName): string {
    $value = getenv($environmentName);
    return $value !== false && $value !== '' ? $value : (string)(clinicSecurityConfig()[$configName] ?? '');
}

function clinicJwtSecret(): string {
    $secret = clinicSecret('LOCAL_JWT_SECRET', 'local_jwt_secret');
    if (strlen($secret) < 32) throw new RuntimeException('Secure upload configuration is missing. Ask the administrator to run the SF8 security setup.');
    return $secret;
}
