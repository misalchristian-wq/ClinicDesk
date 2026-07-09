<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Include parser files
    require_once __DIR__ . '/sf8_parser.php';
    require_once __DIR__ . '/deworming_wifa_parser.php';
    require_once __DIR__ . '/okd_lhas_parser.php';
    require_once __DIR__ . '/immunization_parser.php';
    require_once __DIR__ . '/tobacco_parser.php';
    require_once __DIR__ . '/arh_parser.php';

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error.');
    }

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'xlsx') {
        throw new Exception('Only .xlsx files are allowed.');
    }

    // Save to temp
    $tempDir = __DIR__ . '/../temp_uploads/';
    if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
    $tempFile = $tempDir . 'nurse_upload_' . time() . '_' . uniqid() . '.xlsx';
    if (!move_uploaded_file($file['tmp_name'], $tempFile)) {
        throw new Exception('Failed to save uploaded file.');
    }

    // Detect report code from cell A1
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempFile);
    $sheet = $spreadsheet->getActiveSheet();
    $a1 = trim((string) $sheet->getCell('A1')->getValue());
    $reportCode = $a1;

    // Map to known codes
    $reportMap = [
        'students_information' => 'students_information',
        'okd_lhas' => 'okd_lhas',
        'immunization_nutritional_status' => 'immunization_nutritional_status',
        'deworming_wifa' => 'deworming_wifa',
        'adolescent_reproductive_health_arh' => 'adolescent_reproductive_health_arh',
        'comprehensive_tobacco_control' => 'comprehensive_tobacco_control'
    ];
    // Also check if A1 contains any of these keywords
    $found = null;
    foreach ($reportMap as $key => $code) {
        if (stripos($a1, $key) !== false || stripos($a1, str_replace('_', ' ', $key)) !== false) {
            $found = $code;
            break;
        }
    }
    if (!$found) {
        // fallback to students_information
        $found = 'students_information';
    }
    $reportCode = $found;

    // Parse using appropriate parser
    $parsed = null;
    if ($reportCode === 'deworming_wifa') {
        $parsed = parseDewormingWifaExcelFile($tempFile);
    } elseif ($reportCode === 'okd_lhas') {
        $parsed = parseOkdLhasExcelFile($tempFile);
    } elseif ($reportCode === 'immunization_nutritional_status') {
        $parsed = parseImmunizationExcelFile($tempFile);
    } elseif ($reportCode === 'comprehensive_tobacco_control') {
        $parsed = parseTobaccoExcelFile($tempFile);
    } elseif ($reportCode === 'adolescent_reproductive_health_arh') {
        $parsed = parseArhExcelFile($tempFile);
    } else {
        // Default: nutritional status
        $parsed = parseSf8ExcelFile($tempFile);
        // Re-shape to have 'header' and 'records'
        $parsed = [
            'header' => $parsed['header'],
            'records' => $parsed['students']
        ];
    }

    // Clean up temp file
    @unlink($tempFile);

    if (!$parsed || empty($parsed['records'])) {
        throw new Exception('No records found in the file.');
    }

    echo json_encode([
        'success' => true,
        'report_code' => $reportCode,
        'header' => $parsed['header'] ?? [],
        'records' => $parsed['records'] ?? []
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}