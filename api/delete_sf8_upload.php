<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

ini_set("display_errors", 0);
error_reporting(E_ALL);

include "db.php";
include "cloudinary_config.php";

$data = json_decode(file_get_contents("php://input"), true);

$upload_id = intval($data["upload_id"] ?? 0);
$password = trim($data["password"] ?? "");

if ($upload_id <= 0 || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Upload ID and password are required."
    ]);
    exit;
}

function verifyPasswordFlexible($inputPassword, $storedPassword) {
    if ($storedPassword === "" || $storedPassword === null) {
        return false;
    }

    if (password_verify($inputPassword, $storedPassword)) {
        return true;
    }

    if ($inputPassword === $storedPassword) {
        return true;
    }

    return false;
}

try {
    $accountVerified = false;

    $stmt = $conn->prepare("
        SELECT password_hash AS password 
        FROM local_accounts
    ");

    if (!$stmt) {
        throw new Exception("Account query failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if (verifyPasswordFlexible($password, $row["password"])) {
            $accountVerified = true;
            break;
        }
    }

    $stmt->close();

    if (!$accountVerified) {
        echo json_encode([
            "success" => false,
            "message" => "Incorrect password."
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT upload_id, file_name, cloudinary_public_id, cloudinary_url
        FROM sf8_uploads
        WHERE upload_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        throw new Exception("Upload query failed: " . $conn->error);
    }

    $stmt->bind_param("i", $upload_id);
    $stmt->execute();
    $upload = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$upload) {
        echo json_encode([
            "success" => false,
            "message" => "Upload record not found."
        ]);
        exit;
    }

    $publicId = trim($upload["cloudinary_public_id"] ?? "");

    if ($publicId !== "") {
        $encodedPublicId = str_replace("%2F", "/", rawurlencode($publicId));

        $cloudinaryUrl =
            "https://api.cloudinary.com/v1_1/" .
            $cloudinary_cloud_name .
            "/resources/raw/upload/" .
            $encodedPublicId;

        $ch = curl_init($cloudinaryUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $cloudinary_api_key . ":" . $cloudinary_api_secret);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $cloudResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($httpCode !== 200 && $httpCode !== 404) {
            echo json_encode([
                "success" => false,
                "message" => "Cloudinary delete failed.",
                "http_code" => $httpCode,
                "curl_error" => $curlError,
                "cloudinary_response" => $cloudResponse
            ]);
            exit;
        }
    }

    $conn->begin_transaction();

    $deleteUpload = $conn->prepare("
        DELETE FROM sf8_uploads
        WHERE upload_id = ?
    ");

    if (!$deleteUpload) {
        throw new Exception("Delete query failed: " . $conn->error);
    }

    $deleteUpload->bind_param("i", $upload_id);
    $deleteUpload->execute();
    $deleteUpload->close();

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Upload deleted successfully."
    ]);

} catch (Throwable $e) {
    if (isset($conn) && $conn) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {}
    }

    echo json_encode([
        "success" => false,
        "message" => "Delete failed: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}

$conn->close();
?>