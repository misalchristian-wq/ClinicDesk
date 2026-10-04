<?php
require_once __DIR__ . '/bootstrap.php';

const SF8_ENCRYPTED_TYPE = 'sf8_enc_v1';
const SF8_MAGIC = "CLINICDESK-SF8\x01";
const SF8_MAX_BYTES = 10 * 1024 * 1024;

function sf8EncryptionKeys(): array {
    $config = clinicSecurityConfig();
    $keys = $config['sf8_encryption_keys'] ?? [];
    $active = $config['active_sf8_key_id'] ?? '';
    $environmentKey = getenv('SF8_ENCRYPTION_KEY');
    if ($environmentKey !== false && $environmentKey !== '') {
        $raw = base64_decode($environmentKey, true);
        if ($raw === false || strlen($raw) !== 32) throw new RuntimeException('SF8 encryption key must be a base64-encoded 32-byte key.');
        $active = substr(hash('sha256', $raw), 0, 16);
        $keys[$active] = $environmentKey;
    }
    if (!$active || !isset($keys[$active])) throw new RuntimeException('SF8 encryption is not configured. Ask the administrator to run the SF8 security setup.');
    $decoded = [];
    foreach ($keys as $id => $encoded) {
        $key = base64_decode($encoded, true);
        if (!preg_match('/^[a-f0-9]{16}$/', (string)$id) || $key === false || strlen($key) !== 32 || substr(hash('sha256', $key), 0, 16) !== $id) {
            throw new RuntimeException('SF8 encryption configuration is invalid.');
        }
        $decoded[$id] = $key;
    }
    return ['active' => $active, 'keys' => $decoded];
}

function sf8Encrypt(string $plaintext): string {
    if ($plaintext === '' || strlen($plaintext) > SF8_MAX_BYTES) throw new RuntimeException('SF8 file is empty or exceeds the 10 MB limit.');
    $keyring = sf8EncryptionKeys();
    $header = SF8_MAGIC . $keyring['active'];
    $nonce = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $keyring['keys'][$keyring['active']], OPENSSL_RAW_DATA, $nonce, $tag, $header, 16);
    if ($ciphertext === false || strlen($tag) !== 16) throw new RuntimeException('Unable to encrypt the SF8 file.');
    return $header . $nonce . $tag . $ciphertext;
}

function sf8Decrypt(string $encrypted): string {
    $headerLength = strlen(SF8_MAGIC) + 16;
    if (strlen($encrypted) <= $headerLength + 28 || strlen($encrypted) > SF8_MAX_BYTES + $headerLength + 28 || !str_starts_with($encrypted, SF8_MAGIC)) {
        throw new RuntimeException('The encrypted SF8 file is invalid or damaged.');
    }
    $header = substr($encrypted, 0, $headerLength);
    $id = substr($header, strlen(SF8_MAGIC), 16);
    $keyring = sf8EncryptionKeys();
    if (!isset($keyring['keys'][$id])) throw new RuntimeException('The encryption key for this SF8 file is unavailable.');
    $nonce = substr($encrypted, $headerLength, 12);
    $tag = substr($encrypted, $headerLength + 12, 16);
    $ciphertext = substr($encrypted, $headerLength + 28);
    $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $keyring['keys'][$id], OPENSSL_RAW_DATA, $nonce, $tag, $header);
    if ($plaintext === false) throw new RuntimeException('SF8 integrity verification failed. The file could not be decrypted.');
    return $plaintext;
}

function sf8IsEncryptedUpload(array $upload): bool {
    return ($upload['file_type'] ?? '') === SF8_ENCRYPTED_TYPE || str_ends_with(strtolower($upload['cloudinary_public_id'] ?? ''), '.enc');
}

function sf8DecodeStoredFile(string $bytes, array $upload): string {
    if (sf8IsEncryptedUpload($upload)) return sf8Decrypt($bytes);
    // Read compatibility for pre-encryption uploads only. Never downgrade an encrypted row.
    if (strlen($bytes) > SF8_MAX_BYTES || !str_starts_with($bytes, "PK\x03\x04")) throw new RuntimeException('The stored SF8 file is not a valid Excel workbook.');
    return $bytes;
}

function sf8TemporaryFile(string $bytes): string {
    $dir = realpath(sys_get_temp_dir());
    $webRoot = realpath(dirname(__DIR__, 2));
    if (!$dir || ($webRoot && (strtolower($dir) === strtolower($webRoot) || str_starts_with(strtolower(str_replace('\\', '/', $dir)), strtolower(str_replace('\\', '/', $webRoot)) . '/')))) {
        throw new RuntimeException('SF8 temporary storage must be outside the web root.');
    }
    $path = tempnam($dir, 'clinicdesk_sf8_');
    if ($path === false) throw new RuntimeException('Unable to create secure SF8 temporary storage.');
    @chmod($path, 0600);
    if (file_put_contents($path, $bytes, LOCK_EX) !== strlen($bytes)) {
        @unlink($path);
        throw new RuntimeException('Unable to write the SF8 temporary file.');
    }
    return $path;
}
