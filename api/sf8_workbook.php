<?php
require_once __DIR__ . '/sf8_storage.php';
require_once __DIR__ . '/../lib/sf8_detection.php';
require_once __DIR__ . '/sf8_parser.php';
require_once __DIR__ . '/deworming_wifa_parser.php';
require_once __DIR__ . '/okd_lhas_parser.php';
require_once __DIR__ . '/immunization_parser.php';
require_once __DIR__ . '/tobacco_parser.php';
require_once __DIR__ . '/arh_parser.php';
require_once __DIR__ . '/who_classifier.php';

function sf8ReadUploadMetadata(string $path): array {
    if (!class_exists('ZipArchive')) throw new Sf8Exception('The server needs the PHP ZIP extension to read SF8 files. Contact the administrator.');
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) throw new Sf8Exception('Only valid .xlsx workbooks are accepted.');
    try {
        if ($zip->locateName('[Content_Types].xml') === false || $zip->locateName('xl/workbook.xml') === false || $zip->numFiles > 10000) {
            throw new Sf8Exception('The uploaded file is not a supported Excel workbook.');
        }
        $uncompressed = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->statIndex($i);
            $uncompressed += $entry['size'];
            if ($uncompressed > 100 * 1024 * 1024) throw new Sf8Exception('The Excel workbook is too large to process safely.');
        }
    } finally { $zip->close(); }
    $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
    try {
        $sheet = $book->getSheetByName('Nutritional Status') ?: $book->getActiveSheet();
        $detected = sf8DetectReportType(fn(string $cell) => $sheet->getCell($cell)->getValue());
        if (!$detected) throw new Sf8Exception('The SF8 type could not be identified from its column headers. Check that this is a supported SF8 template.');
        $year = '';
        for ($col = 1; $col <= 40; $col++) {
            $value = trim((string)$sheet->getCell([$col, 7])->getValue());
            if (stripos($value, 'school year') !== false) {
                for ($next = $col + 1; $next <= 40; $next++) {
                    $value = trim((string)$sheet->getCell([$next, 7])->getCalculatedValue());
                    if ($value !== '') { $year = $value; break; }
                }
                break;
            }
        }
        if ($year === '') $year = trim((string)$sheet->getCell('P7')->getCalculatedValue());
        if (!preg_match('/^(\d{4})-(\d{4})$/', $year, $parts) || (int)$parts[2] !== (int)$parts[1] + 1) {
            throw new Sf8Exception('The workbook must contain a valid school year in row 7 (YYYY-YYYY).');
        }
        return $detected + ['school_year' => $year];
    } finally { $book->disconnectWorksheets(); }
}

function sf8ParseWorkbook(string $path, string $code): array {
    $parsers = ['deworming_wifa' => 'parseDewormingWifaExcelFile', 'okd_lhas' => 'parseOkdLhasExcelFile',
        'immunization_nutritional_status' => 'parseImmunizationExcelFile', 'comprehensive_tobacco_control' => 'parseTobaccoExcelFile',
        'adolescent_reproductive_health_arh' => 'parseArhExcelFile', 'students_information' => 'parseSf8ExcelFile'];
    if (!isset($parsers[$code])) throw new Sf8Exception('Unsupported SF8 report code.');
    $parsed = $parsers[$code]($path);
    $records = $parsed['records'] ?? $parsed['students'] ?? [];
    if ($code === 'students_information') {
        foreach ($records as &$student) {
            $student['height_for_age'] = is_numeric($student['age'] ?? null) && is_numeric($student['height_m'] ?? null)
                ? whoHeightForAge($student['height_m'], (float)$student['age'] * 12, $student['sex'] ?? '') : '';
        }
        unset($student);
    }
    return ['header' => $parsed['header'] ?? [], 'records' => $records];
}

function sf8StoreSubmittedUpload(mysqli $conn, array $file, array $user): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new Sf8Exception('Choose an SF8 .xlsx file to upload (maximum 10 MB).');
    }
    $name = basename(str_replace('\\', '/', $file['name'] ?? ''));
    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'xlsx' || strlen($name) > 255) throw new Sf8Exception('Only .xlsx files with a valid filename are allowed.');
    $size = filesize($file['tmp_name']);
    if (!$size || $size > SF8_MAX_BYTES) throw new Sf8Exception('The SF8 file must be between 1 byte and 10 MB.');
    $metadata = sf8ReadUploadMetadata($file['tmp_name']);
    $active = $conn->query('SELECT year_label FROM school_years WHERE is_active = 1 LIMIT 1')->fetch_assoc();
    if (!$active || $metadata['school_year'] !== $active['year_label']) throw new Sf8Exception('The workbook school year must match the active school year set by the clinic nurse.');
    $cloud = sf8UploadEncryptedFile(file_get_contents($file['tmp_name']));
    try {
        $type = SF8_ENCRYPTED_TYPE;
        $stmt = $conn->prepare("INSERT INTO sf8_uploads (file_name, file_type, report_purpose, report_code, cloudinary_public_id, cloudinary_url, uploaded_by_email, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param('sssssss', $name, $type, $metadata['report_purpose'], $metadata['report_code'], $cloud['public_id'], $cloud['secure_url'], $user['email']);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
    } catch (Throwable $e) {
        try { sf8RemoveCloudinaryFile($cloud['public_id']); } catch (Throwable $cleanupError) { error_log('ClinicDesk: failed to remove an unregistered encrypted SF8 asset.'); }
        throw $e;
    }
    return ['upload_id' => $id, 'report_code' => $metadata['report_code'], 'encrypted' => true];
}
