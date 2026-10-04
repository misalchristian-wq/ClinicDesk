<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../api/sf8_workbook.php';
$fixture = null;
$decrypted = null;
$cloudFile = null;
try {
    $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $book->getActiveSheet()->setTitle('Nutritional Status');
    foreach (['A1'=>'Students Information','E5'=>'Encryption Test School','O7'=>'School Year','P7'=>'2026-2027',
        'F7'=>'Grade 7','B10'=>'999999999991','C10'=>'Encryption Test Learner','G10'=>'Male','H10'=>'2013-01-01',
        'I10'=>13,'J10'=>40,'K10'=>1.5,'L10'=>2.25,'M10'=>17.78,'N10'=>'Normal'] as $cell=>$value) $sheet->setCellValue($cell,$value);
    $fixture=sf8TemporaryFile('fixture');
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($fixture);
    $book->disconnectWorksheets();
    $original=file_get_contents($fixture);
    $cloudFile=sf8UploadEncryptedFile($original);
    $settings=sf8CloudinarySettings();
    $ch=curl_init($cloudFile['secure_url']);
    curl_setopt_array($ch,sf8CurlOptions()+[CURLOPT_RETURNTRANSFER=>true]);
    $download=curl_exec($ch);
    $http=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($http!==200 || !is_string($download) || !str_starts_with($download,SF8_MAGIC) || str_starts_with($download,"PK\x03\x04")) {
        throw new RuntimeException('Cloudinary did not return the encrypted file.');
    }
    echo "PASS: an actual Cloudinary download contains ciphertext, not an Excel workbook.\n";
    $decrypted=sf8DownloadForParsing(['file_type'=>SF8_ENCRYPTED_TYPE,'cloudinary_url'=>$cloudFile['secure_url'],'cloudinary_public_id'=>$cloudFile['public_id']]);
    if(file_get_contents($decrypted)!==$original)throw new RuntimeException('Cloud round-trip changed the original workbook.');
    $parsed=sf8ParseWorkbook($decrypted,'students_information');
    if(count($parsed['records'])!==1 || $parsed['records'][0]['lrn']!=='999999999991')throw new RuntimeException('Cloud round-trip did not produce the expected learner record.');
    echo "PASS: the encrypted cloud file decrypts to the original workbook and parses correctly.\n";
} finally {
    if($fixture)@unlink($fixture);
    if($decrypted)@unlink($decrypted);
    if($cloudFile) {
        sf8RemoveCloudinaryFile($cloudFile['public_id']);
        echo "Cloud verification file removed.\n";
    }
}
