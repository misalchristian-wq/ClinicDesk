<?php
require_once __DIR__ . '/../api/sf8_workbook.php';

$expected = [
    'SF8_StudentInformation.xlsx' => 'students_information',
    'SF8_StudentInformation2.xlsx' => 'students_information',
    'SF8_OKD_LHAS.xlsx' => 'okd_lhas',
    'SF8_immunization_nutritional_status.xlsx' => 'immunization_nutritional_status',
    'SF8_Deworming & WIFA.xlsx' => 'deworming_wifa',
    'adolescent_reproductive_health_arh.xlsx' => 'adolescent_reproductive_health_arh',
    'adolescent_reproductive_health_arh-1.xlsx' => 'adolescent_reproductive_health_arh',
    'comprehensive_tobacco_control.xlsx' => 'comprehensive_tobacco_control',
];
foreach ($expected as $name => $code) {
    $metadata = sf8ReadUploadMetadata(__DIR__ . '/../SF8_FORMATS/' . $name);
    if ($metadata['report_code'] !== $code) throw new RuntimeException($name . ' was detected as the wrong type.');
}

$source = __DIR__ . '/../SF8_FORMATS/SF8_StudentInformation.xlsx';
$book = \PhpOffice\PhpSpreadsheet\IOFactory::load($source);
$sheet = $book->getSheetByName('Nutritional Status') ?: $book->getActiveSheet();
$temp = tempnam(sys_get_temp_dir(), 'clinicdesk_sf8_detect_');
@unlink($temp);
$temp .= '.xlsx';
try {
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book);
    $writer->setPreCalculateFormulas(false);
    $sheet->setCellValue('A1', 'comprehensive_tobacco_control');
    $writer->save($temp);
    $metadata = sf8ReadUploadMetadata($temp);
    if ($metadata['report_code'] !== 'students_information') throw new RuntimeException('A1 overrode the actual student information columns.');

    $sheet->setCellValue('A1', '');
    $writer->save($temp);
    $metadata = sf8ReadUploadMetadata($temp);
    if ($metadata['report_code'] !== 'students_information') throw new RuntimeException('Blank A1 blocked column detection.');

    $sheet->setCellValue('J9', 'unrelated column');
    $writer->save($temp);
    try {
        sf8ReadUploadMetadata($temp);
        throw new RuntimeException('Unsupported column layout was accepted.');
    } catch (Sf8Exception $expectedFailure) {
        // A1 is deliberately blank; headers must be sufficient.
    }
} finally {
    $book->disconnectWorksheets();
    @unlink($temp);
}
echo 'PASS: all sample SF8 types detected from columns; wrong/blank A1 ignored; unsupported headers rejected.' . PHP_EOL;
