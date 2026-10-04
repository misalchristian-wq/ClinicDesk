<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../api/sf8_workbook.php';
$tests = 0;
function check(bool $condition, string $label): void {
    global $tests;
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    $tests++;
    echo "PASS: $label\n";
}
function rejects(callable $operation, string $label): void {
    try { $operation(); } catch (RuntimeException $e) { check(true, $label); return; }
    check(false, $label);
}
$previousKey = getenv('SF8_ENCRYPTION_KEY');
try {
    putenv('SF8_ENCRYPTION_KEY=' . base64_encode(random_bytes(32)));
    $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $book->getActiveSheet()->setTitle('Nutritional Status');
    foreach (['A1'=>'Students Information','E5'=>'Encryption Test School','O7'=>'School Year','P7'=>'2026-2027',
        'F7'=>'Grade 7','I7'=>'Test Section','B10'=>'999999999991','C10'=>'Encryption Test Learner',
        'G10'=>'Male','H10'=>'2013-01-01','I10'=>13,'J10'=>40,'K10'=>1.5,'L10'=>2.25,
        'M10'=>17.78,'N10'=>'Normal','O10'=>'Normal'] as $cell=>$value) $sheet->setCellValue($cell,$value);
    $sourceFile = sf8TemporaryFile('fixture');
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($sourceFile);
    $book->disconnectWorksheets();
    $plaintext = file_get_contents($sourceFile);
    $encrypted = sf8Encrypt($plaintext);
    check(!str_starts_with($encrypted, "PK\x03\x04"), 'cloud bytes are not a readable Excel ZIP');
    check(!str_contains($encrypted, 'Encryption Test Learner'), 'cloud bytes do not expose learner text');
    check(sf8Decrypt($encrypted) === $plaintext, 'decryption restores every original workbook byte');
    check(sf8Encrypt($plaintext) !== $encrypted, 'each encryption uses a fresh random nonce');
    $metadata = sf8ReadUploadMetadata($sourceFile);
    check($metadata['report_code']==='students_information' && $metadata['school_year']==='2026-2027', 'workbook metadata is validated on the server');
    $decodedFile = sf8TemporaryFile(sf8DecodeStoredFile($encrypted, ['file_type'=>SF8_ENCRYPTED_TYPE]));
    $parsed = sf8ParseWorkbook($decodedFile, 'students_information');
    check(count($parsed['records'])===1 && $parsed['records'][0]['lrn']==='999999999991', 'decrypted workbook reaches the existing student parser');
    check(sf8DecodeStoredFile($plaintext, ['file_type'=>'xlsx'])===$plaintext, 'legacy readable uploads remain previewable');
    rejects(fn()=>sf8DecodeStoredFile($plaintext,['file_type'=>SF8_ENCRYPTED_TYPE]), 'encrypted metadata cannot downgrade to plaintext');
    foreach ([0,strlen(SF8_MAGIC),strlen(SF8_MAGIC)+16,strlen(SF8_MAGIC)+28,strlen($encrypted)-1] as $offset) {
        $damaged=$encrypted;
        $damaged[$offset]=chr(ord($damaged[$offset])^1);
        rejects(fn()=>sf8Decrypt($damaged), 'modified encrypted component is rejected (offset '.$offset.')');
    }
    rejects(fn()=>sf8Decrypt(substr($encrypted,0,strlen(SF8_MAGIC)+44)), 'truncated envelope is rejected');
    rejects(fn()=>sf8Encrypt(''), 'empty files are rejected');
    rejects(fn()=>sf8Encrypt(str_repeat('x',SF8_MAX_BYTES+1)), 'oversized files are rejected');
    putenv('SF8_ENCRYPTION_KEY=' . base64_encode(random_bytes(32)));
    rejects(fn()=>sf8Decrypt($encrypted), 'wrong encryption key is rejected');
    putenv('SF8_ENCRYPTION_KEY=bad-key');
    rejects(fn()=>sf8Encrypt('fixture'), 'invalid key configuration fails closed');
    sf8ValidateCloudinaryUrl('https://res.cloudinary.com/demo/raw/upload/v1/clinicdesk/sf8_encrypted/test.xlsx.enc','demo','clinicdesk/sf8_encrypted/test.xlsx.enc');
    check(true,'the expected Cloudinary file URL is allowed');
    foreach (['http://res.cloudinary.com/demo/raw/upload/v1/test.xlsx.enc','https://evil.example/demo/raw/upload/test.xlsx.enc',
        'https://res.cloudinary.com/other/raw/upload/test.xlsx.enc','https://res.cloudinary.com/demo/raw/upload/test.xlsx.enc?redirect=1',
        'https://res.cloudinary.com/demo/raw/upload/different.xlsx.enc','https://user@res.cloudinary.com/demo/raw/upload/test.xlsx.enc'] as $url) {
        rejects(fn()=>sf8ValidateCloudinaryUrl($url,'demo','test.xlsx.enc'),'untrusted cloud URL is rejected');
    }
    echo "$tests encryption and validation checks passed.\n";
} finally {
    if (isset($sourceFile)) @unlink($sourceFile);
    if (isset($decodedFile)) @unlink($decodedFile);
    putenv($previousKey===false?'SF8_ENCRYPTION_KEY':'SF8_ENCRYPTION_KEY='.$previousKey);
}
