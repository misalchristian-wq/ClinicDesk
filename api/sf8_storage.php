<?php
require_once __DIR__ . '/sf8_crypto.php';

class Sf8Exception extends RuntimeException {}

function sf8CloudinarySettings(): array {
    $settings = [
        'cloud' => clinicSecret('CLOUDINARY_CLOUD_NAME', 'cloudinary_cloud_name'),
        'key' => clinicSecret('CLOUDINARY_API_KEY', 'cloudinary_api_key'),
        'secret' => clinicSecret('CLOUDINARY_API_SECRET', 'cloudinary_api_secret'),
    ];
    if (!preg_match('/^[a-z0-9_-]+$/i', $settings['cloud']) || !$settings['key'] || !$settings['secret']) {
        throw new Sf8Exception('Secure cloud uploads are not configured. Contact the administrator.');
    }
    return $settings;
}

function sf8CurlOptions(): array {
    $options = [CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => 90,
        CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_FOLLOWLOCATION => false];
    $ca = getenv('CURL_CA_BUNDLE') ?: ini_get('curl.cainfo');
    if (!$ca) {
        $bundled = dirname(__DIR__, 3) . '/apache/bin/curl-ca-bundle.crt';
        if (is_file($bundled)) $ca = $bundled;
    }
    if ($ca) $options[CURLOPT_CAINFO] = $ca;
    return $options;
}

function sf8ValidateCloudinaryUrl(string $url, string $cloud, string $publicId): void {
    $parts = parse_url($url);
    if (!$parts || ($parts['scheme'] ?? '') !== 'https' || ($parts['host'] ?? '') !== 'res.cloudinary.com'
        || isset($parts['user'], $parts['pass']) || isset($parts['user']) || isset($parts['pass'])
        || isset($parts['query']) || isset($parts['fragment']) || (isset($parts['port']) && $parts['port'] !== 443)) {
        throw new Sf8Exception('The SF8 cloud storage URL is invalid.');
    }
    $path = rawurldecode($parts['path'] ?? '');
    $prefix = '/' . $cloud . '/raw/upload/';
    if (!str_starts_with($path, $prefix)) throw new Sf8Exception('The SF8 file must belong to this Cloudinary account.');
    $storedId = preg_replace('/^v\d+\//', '', substr($path, strlen($prefix)));
    if ($publicId === '' || $storedId !== $publicId || str_contains($storedId, '..') || str_contains($storedId, '\\')) {
        throw new Sf8Exception('The SF8 cloud file does not match its upload record.');
    }
}

function sf8UploadEncryptedFile(string $plaintext): array {
    $settings = sf8CloudinarySettings();
    // No learner names or original filename are exposed in the cloud asset identifier.
    $publicId = 'clinicdesk/sf8_encrypted/' . bin2hex(random_bytes(16)) . '.xlsx.enc';
    $encrypted = sf8Encrypt($plaintext);
    $temp = sf8TemporaryFile($encrypted);
    try {
        $timestamp = (string)time();
        $signature = sha1('overwrite=false&public_id=' . $publicId . '&timestamp=' . $timestamp . $settings['secret']);
        $ch = curl_init('https://api.cloudinary.com/v1_1/' . $settings['cloud'] . '/raw/upload');
        $response = '';
        curl_setopt_array($ch, sf8CurlOptions() + [CURLOPT_POST => true, CURLOPT_POSTFIELDS => [
            'file' => new CURLFile($temp, 'application/octet-stream', basename($publicId)),
            'api_key' => $settings['key'], 'timestamp' => $timestamp,
            'public_id' => $publicId, 'overwrite' => 'false', 'signature' => $signature,
        ], CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$response) {
            if (strlen($response) + strlen($chunk) > 1024 * 1024) return 0;
            $response .= $chunk;
            return strlen($chunk);
        }]);
        $ok = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        if ($ok === false || $status < 200 || $status >= 300 || !is_array($result) || ($result['public_id'] ?? '') !== $publicId) {
            throw new Sf8Exception('Cloudinary could not store the encrypted SF8 file. Please try again.');
        }
        // Verify Cloudinary's signed response before storing its URL in the database.
        $expected = sha1('public_id=' . $publicId . '&version=' . ($result['version'] ?? '') . $settings['secret']);
        if (!hash_equals($expected, (string)($result['signature'] ?? ''))) throw new Sf8Exception('Cloud upload verification failed.');
        sf8ValidateCloudinaryUrl($result['secure_url'] ?? '', $settings['cloud'], $publicId);
        return ['public_id' => $publicId, 'secure_url' => $result['secure_url']];
    } finally {
        @unlink($temp);
    }
}

function sf8RemoveCloudinaryFile(string $publicId): void {
    $settings = sf8CloudinarySettings();
    $timestamp = (string)time();
    $ch = curl_init('https://api.cloudinary.com/v1_1/' . $settings['cloud'] . '/raw/destroy');
    curl_setopt_array($ch, sf8CurlOptions() + [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => ['public_id' => $publicId, 'timestamp' => $timestamp, 'api_key' => $settings['key'],
            'signature' => sha1('public_id=' . $publicId . '&timestamp=' . $timestamp . $settings['secret'])]]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $result = json_decode($response ?: '', true);
    if ($status !== 200 || !in_array($result['result'] ?? '', ['ok', 'not found'], true)) throw new Sf8Exception('Unable to remove the unused cloud file.');
}

function sf8DownloadForParsing(array $upload): string {
    $settings = sf8CloudinarySettings();
    sf8ValidateCloudinaryUrl($upload['cloudinary_url'] ?? '', $settings['cloud'], $upload['cloudinary_public_id'] ?? '');
    $bytes = '';
    $ch = curl_init($upload['cloudinary_url']);
    curl_setopt_array($ch, sf8CurlOptions() + [CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$bytes) {
        if (strlen($bytes) + strlen($chunk) > SF8_MAX_BYTES + 128) return 0;
        $bytes .= $chunk;
        return strlen($chunk);
    }]);
    $ok = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($ok === false || $status < 200 || $status >= 300) throw new Sf8Exception('Unable to download the stored SF8 file. Please try again.');
    try {
        $plaintext = sf8DecodeStoredFile($bytes, $upload);
    } catch (RuntimeException $e) {
        throw new Sf8Exception($e->getMessage(), 0, $e);
    }
    if (!str_starts_with($plaintext, "PK\x03\x04")) throw new Sf8Exception('The decrypted SF8 file is not an Excel workbook.');
    return sf8TemporaryFile($plaintext);
}
