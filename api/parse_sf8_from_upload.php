<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

ini_set("display_errors", 0);
error_reporting(E_ALL);

try {
    include __DIR__ . "/../db.php";
    include __DIR__ . "/sf8_parser.php";
    include __DIR__ . "/deworming_wifa_parser.php";
    include __DIR__ . "/okd_lhas_parser.php";  
    include __DIR__ . "/immunization_parser.php";
    include __DIR__ . "/tobacco_parser.php";
    include __DIR__ . "/arh_parser.php";

    $upload_id = $_GET["upload_id"] ?? "";

    if ($upload_id === "") {
        echo json_encode([
            "success" => false,
            "message" => "Upload ID is required."
        ]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM sf8_uploads WHERE upload_id = ?");
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Database prepare failed: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param("i", $upload_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $upload = $result->fetch_assoc();

    if (!$upload) {
        echo json_encode([
            "success" => false,
            "message" => "Upload record not found."
        ]);
        exit;
    }

    $url = $upload["cloudinary_url"] ?? "";
    $report_code = $upload["report_code"] ?? "";

    if ($url === "") {
        echo json_encode([
            "success" => false,
            "message" => "Cloudinary URL is empty."
        ]);
        exit;
    }

    $tempDir = __DIR__ . "/../temp_uploads";
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    if (!is_writable($tempDir)) {
        echo json_encode([
            "success" => false,
            "message" => "temp_uploads folder is not writable."
        ]);
        exit;
    }

    $tempFile = $tempDir . "/sf8_upload_" . $upload_id . "_" . time() . ".xlsx";

    $ch = curl_init($url);
    if (!$ch) {
        echo json_encode([
            "success" => false,
            "message" => "Failed to initialize cURL."
        ]);
        exit;
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $fileData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($fileData === false || $httpCode < 200 || $httpCode >= 300) {
        echo json_encode([
            "success" => false,
            "message" => "Unable to download Excel file from Cloudinary.",
            "http_code" => $httpCode,
            "curl_error" => $curlError
        ]);
        exit;
    }

    file_put_contents($tempFile, $fileData);

    if (!file_exists($tempFile) || filesize($tempFile) === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Downloaded Excel file is missing or empty."
        ]);
        exit;
    }

    // Parse based on report type
    if ($report_code === "deworming_wifa") {
        $parsed = parseDewormingWifaExcelFile($tempFile);
        $header = $parsed["header"] ?? [];
        $records = $parsed["records"] ?? [];
    } elseif ($report_code === "okd_lhas") {
        $parsed = parseOkdLhasExcelFile($tempFile);
        $header = $parsed["header"] ?? [];
        $records = $parsed["records"] ?? [];
    } elseif ($report_code === "immunization_nutritional_status") {
        $parsed = parseImmunizationExcelFile($tempFile);
        $header = $parsed["header"] ?? [];
        $records = $parsed["records"] ?? [];
    } elseif ($report_code === "comprehensive_tobacco_control") {
        $parsed = parseTobaccoExcelFile($tempFile);
        $header = $parsed["header"] ?? [];
        $records = $parsed["records"] ?? [];
    } elseif ($report_code === "adolescent_reproductive_health_arh") {
        $parsed = parseArhExcelFile($tempFile);
        $header = $parsed["header"] ?? [];
        $records = $parsed["records"] ?? [];
    } else {
        // Default: SF8 Nutritional Status
        $parsed = parseSf8ExcelFile($tempFile);
        $header = $parsed["header"] ?? [];
        $records = $parsed["students"] ?? [];
    }

    // Clean up temp file
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }

    echo json_encode([
        "success" => true,
        "upload" => $upload,
        "header" => $header,
        "report_code" => $report_code,
        "records" => $records,
        "students" => $records,
        "total_rows" => count($records)
    ]);

    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    if (isset($tempFile) && file_exists($tempFile)) {
        unlink($tempFile);
    }
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}
?>