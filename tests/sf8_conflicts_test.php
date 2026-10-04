<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../api/arh_parser.php';
require __DIR__ . '/../api/sf8_parser.php';
require __DIR__ . '/../api/sf8_conflicts.php';
require __DIR__ . '/../api/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function conflictAssert(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

$file = tempnam(sys_get_temp_dir(), 'sf8_conflict_') . '.xlsx';
$studentFile = tempnam(sys_get_temp_dir(), 'sf8_student_conflict_') . '.xlsx';
$book = new Spreadsheet();
$sheet = $book->getActiveSheet();
$lrn = (string)random_int(800000000000, 899999999999);
$year = '2098-2099';
$sheet->setCellValue('O7', $year);
$sheet->setCellValue('B11', $lrn);
$sheet->setCellValue('C11', 'Temporary Conflict Test Learner');
$sheet->setCellValue('G11', 'Female');
$sheet->setCellValue('J11', 'Pregnant');
$sheet->setCellValue('K11', 'In School');
$sheet->setCellValue('L11', 1);
(new Xlsx($book))->save($file);
$book->disconnectWorksheets();

$conn->begin_transaction();
try {
    $name = 'TEMP_CONFLICT_TEST_' . bin2hex(random_bytes(4));
    $upload = $conn->prepare("INSERT INTO sf8_uploads (file_name, cloudinary_url, uploaded_by_email, status) VALUES (?, '', 'test@example.invalid', 'Approved')");
    $upload->bind_param('s', $name);
    $upload->execute();
    $uploadId = $upload->insert_id;
    $upload->close();
    $existing = $conn->prepare('INSERT INTO arh_records (upload_id, lrn, learner_name, school_year, pregnancy_status, peer_educator) VALUES (?, ?, ?, ?, ?, ?)');
    $learner = 'Temporary Conflict Test Learner'; $status = 'Not Pregnant'; $peer = 0;
    $existing->bind_param('issssi', $uploadId, $lrn, $learner, $year, $status, $peer);
    $existing->execute();
    $existing->close();

    $conflicts = clinicSf8ReviewConflicts($conn, $file, 'adolescent_reproductive_health_arh', $year);
    conflictAssert(count($conflicts) === 1 && $conflicts[0]['existing_count'] === 1, 'Changed ARH row was not detected.');
    $fields = array_column($conflicts[0]['changes'], 'field');
    conflictAssert(in_array('pregnancy_status', $fields, true) && in_array('peer_educator', $fields, true),
        'Changed ARH values were not shown for review.');

    $incoming = ['lrn' => $lrn, 'learner_name' => $learner, 'school_year' => $year,
        'pregnancy_status' => 'Pregnant', 'delivery_mode' => 'In School', 'peer_educator' => 1];
    conflictAssert(clinicFillExistingSection($conn, 'arh', $incoming, $uploadId), 'Existing ARH record was not matched.');
    $row = $conn->query("SELECT COUNT(*) AS n, MAX(pregnancy_status) AS status FROM arh_records WHERE lrn = '$lrn' AND school_year = '$year'")->fetch_assoc();
    conflictAssert((int)$row['n'] === 1 && $row['status'] === 'Not Pregnant', 'Unconfirmed data was overwritten or duplicated.');

    conflictAssert(clinicFillExistingSection($conn, 'arh', $incoming, $uploadId, true), 'Confirmed ARH override failed.');
    $row = $conn->query("SELECT COUNT(*) AS n, MAX(pregnancy_status) AS status FROM arh_records WHERE lrn = '$lrn' AND school_year = '$year'")->fetch_assoc();
    conflictAssert((int)$row['n'] === 1 && $row['status'] === 'Pregnant', 'Confirmed override created a duplicate.');
    $identical = clinicSf8ReviewConflicts($conn, $file, 'adolescent_reproductive_health_arh', $year);
    conflictAssert(count($identical) === 1 && $identical[0]['changes'] === [],
        'An identical existing upload was silently accepted instead of shown as a duplicate.');
    $duplicateBlocked = false;
    $duplicate = $conn->prepare('INSERT INTO arh_records (upload_id, lrn, learner_name, school_year, pregnancy_status, peer_educator) VALUES (?, ?, ?, ?, ?, ?)');
    $status = 'Not Pregnant';
    $duplicate->bind_param('issssi', $uploadId, $lrn, $learner, $year, $status, $peer);
    try {
        $duplicate->execute();
    } catch (mysqli_sql_exception $e) {
        $duplicateBlocked = $e->getCode() === 1062;
    }
    $duplicate->close();
    conflictAssert($duplicateBlocked, 'The database allowed two ARH rows for one LRN and school year.');
    conflictAssert(count(clinicSf8ReviewConflicts($conn, $file, 'adolescent_reproductive_health_arh', '2097-2098')) === 0,
        'Another school year incorrectly matched.');

    // Manual student rows have no upload_id, but are the rows approval updates.
    $manualLrn = (string)random_int(800000000000, 899999999999);
    $manualStudent = $conn->prepare('INSERT INTO sf8_student_records (upload_id, lrn, learner_name, age, school_year) VALUES (NULL, ?, ?, ?, ?)');
    $manualName = 'Existing Manual Learner'; $manualAge = 12;
    $manualStudent->bind_param('ssis', $manualLrn, $manualName, $manualAge, $year);
    $manualStudent->execute();
    $manualStudentId = $manualStudent->insert_id;
    $manualStudent->close();
    $studentBook = new Spreadsheet();
    $studentSheet = $studentBook->getActiveSheet();
    $studentSheet->setTitle('Nutritional Status');
    $studentSheet->setCellValue('B10', $manualLrn);
    $studentSheet->setCellValue('C10', 'Updated Manual Learner');
    $studentSheet->setCellValue('I10', 13);
    (new Xlsx($studentBook))->save($studentFile);
    $studentBook->disconnectWorksheets();
    $manualConflicts = clinicSf8ReviewConflicts($conn, $studentFile, 'students_information', $year);
    conflictAssert(count($manualConflicts) === 1 && in_array('learner_name', array_column($manualConflicts[0]['changes'], 'field'), true),
        'Manual master record was not shown in the override modal.');
    $manualIncoming = ['lrn' => $manualLrn, 'school_year' => $year, 'learner_name' => 'Updated Manual Learner', 'age' => 13];
    clinicFillExistingStudent($conn, $manualIncoming);
    $row = $conn->query("SELECT learner_name, age FROM sf8_student_records WHERE record_id = $manualStudentId")->fetch_assoc();
    conflictAssert($row['learner_name'] === $manualName, 'Manual master data changed before confirmation.');
    clinicFillExistingStudent($conn, $manualIncoming, true);
    $row = $conn->query("SELECT learner_name, age FROM sf8_student_records WHERE record_id = $manualStudentId")->fetch_assoc();
    conflictAssert($row['learner_name'] === 'Updated Manual Learner' && (int)$row['age'] === 13,
        'Confirmed manual master override did not update the stored row.');

    // A populated section with upload_id NULL must also be reviewed before merging.
    $placeholderLrn = (string)random_int(800000000000, 899999999999);
    $placeholder = $conn->prepare('INSERT INTO arh_records (upload_id, lrn, learner_name, school_year, pregnancy_status, peer_educator) VALUES (NULL, ?, ?, ?, ?, 0)');
    $placeholderName = 'Manual Section Learner'; $placeholderStatus = 'Not Pregnant';
    $placeholder->bind_param('ssss', $placeholderLrn, $placeholderName, $year, $placeholderStatus);
    $placeholder->execute();
    $placeholderId = $placeholder->insert_id;
    $placeholder->close();
    $sectionBook = new Spreadsheet();
    $sectionSheet = $sectionBook->getActiveSheet();
    $sectionSheet->setCellValue('B11', $placeholderLrn);
    $sectionSheet->setCellValue('C11', $placeholderName);
    $sectionSheet->setCellValue('J11', 'Pregnant');
    $sectionSheet->setCellValue('L11', 1);
    $sectionFile = tempnam(sys_get_temp_dir(), 'sf8_section_conflict_') . '.xlsx';
    (new Xlsx($sectionBook))->save($sectionFile);
    $sectionBook->disconnectWorksheets();
    try {
        $sectionConflicts = clinicSf8ReviewConflicts($conn, $sectionFile, 'adolescent_reproductive_health_arh', $year);
        conflictAssert(count($sectionConflicts) === 1 && in_array('pregnancy_status', array_column($sectionConflicts[0]['changes'], 'field'), true),
            'Populated manual section was not shown in the override modal.');
        $sectionIncoming = ['lrn' => $placeholderLrn, 'school_year' => $year, 'learner_name' => $placeholderName,
            'pregnancy_status' => 'Pregnant', 'peer_educator' => 1];
        clinicFillExistingSection($conn, 'arh', $sectionIncoming, $uploadId);
        $row = $conn->query("SELECT pregnancy_status FROM arh_records WHERE arh_record_id = $placeholderId")->fetch_assoc();
        conflictAssert($row['pregnancy_status'] === 'Not Pregnant', 'Manual section changed before confirmation.');
        clinicFillExistingSection($conn, 'arh', $sectionIncoming, $uploadId, true);
        $row = $conn->query("SELECT pregnancy_status, peer_educator FROM arh_records WHERE arh_record_id = $placeholderId")->fetch_assoc();
        conflictAssert($row['pregnancy_status'] === 'Pregnant' && (int)$row['peer_educator'] === 1,
            'Confirmed manual section override did not update the stored row.');
    } finally {
        @unlink($sectionFile);
    }
    echo "PASS: SF8 conflicts include uploaded and manual records; confirmed overrides update existing rows.\n";
} finally {
    $conn->rollback();
    $conn->close();
    @unlink($file);
    @unlink($studentFile);
}
