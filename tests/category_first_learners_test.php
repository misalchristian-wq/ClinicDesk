<?php
require __DIR__ . '/../api/db.php';
require __DIR__ . '/../api/student_sections.php';
require __DIR__ . '/../api/sf8_approval_validation.php';

function categoryFirstAssert(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

$conn->begin_transaction();
try {
    $lrn = (string)random_int(800000000000, 899999999999);
    $year = '2098-2099';
    $name = 'Synthetic Category First ' . bin2hex(random_bytes(4));
    $identity = ['lrn' => $lrn, 'school_year' => $year, 'learner_name' => $name,
        'sex' => '', 'grade_level' => '8'];
    $first = clinicEnsureLearnerForCategory($conn, $identity);
    categoryFirstAssert($first['created'] && $first['profile_status'] === 'Provisional',
        'Category-first upload did not create a provisional learner.');
    $studentId = $first['record_id'];
    foreach (clinicSectionDefinitions() as $definition) {
        $table = $definition['table'];
        $count = $conn->query("SELECT COUNT(*) AS n FROM `$table` WHERE student_record_id = $studentId")->fetch_assoc()['n'];
        categoryFirstAssert((int)$count === 0, "Blank $table row was created for a provisional learner.");
    }

    $again = clinicEnsureLearnerForCategory($conn, $identity);
    categoryFirstAssert(!$again['created'] && $again['record_id'] === $studentId,
        'A second category upload created another learner row.');
    $uploadName = 'TEMP_CATEGORY_FIRST_' . bin2hex(random_bytes(4));
    $upload = $conn->prepare("INSERT INTO sf8_uploads (file_name, cloudinary_url, uploaded_by_email, status) VALUES (?, '', 'test@example.invalid', 'Approved')");
    $upload->bind_param('s', $uploadName);
    $upload->execute();
    $uploadId = $upload->insert_id;
    $upload->close();
    $immunization = $conn->prepare("INSERT INTO immunization_records
        (upload_id, student_record_id, lrn, learner_name, school_year, vaccine, dose, immunized)
        VALUES (?, ?, ?, ?, ?, 'HPV', '1', 1)");
    $immunization->bind_param('iisss', $uploadId, $studentId, $lrn, $name, $year);
    $immunization->execute();
    $immunization->close();
    clinicEnsureStudentSections($conn, $studentId);
    $count = $conn->query("SELECT COUNT(*) AS n FROM immunization_records WHERE lrn = '$lrn' AND school_year = '$year'")->fetch_assoc()['n'];
    categoryFirstAssert((int)$count === 1, 'Linking a learner created an extra immunization row.');

    $records = [['lrn' => $lrn, 'learner_name' => $name, 'vaccine' => 'HPV', 'dose' => '1'],
        ['lrn' => $lrn, 'learner_name' => $name, 'vaccine' => 'MMR', 'dose' => '1']];
    $valid = clinicSf8ApprovalPreflight($conn, $records, 'immunization_nutritional_status', $year);
    categoryFirstAssert(!$valid['errors'] && !$valid['identity_conflicts'],
        'Distinct vaccines for one LRN were incorrectly rejected.');
    $invalid = clinicSf8ApprovalPreflight($conn, [$records[0], $records[0]], 'immunization_nutritional_status', $year);
    categoryFirstAssert((bool)$invalid['errors'], 'Duplicate vaccine and dose in one file were accepted.');
    $differentLrn = (string)random_int(800000000000, 899999999999);
    $identityProblem = clinicSf8ApprovalPreflight($conn,
        [['lrn' => $differentLrn, 'learner_name' => $name]], 'students_information', $year);
    categoryFirstAssert(count($identityProblem['identity_conflicts']) > 0,
        'Same-name, different-LRN record was not held for identity review.');

    $completed = clinicFillExistingStudent($conn, ['lrn' => $lrn, 'school_year' => $year,
        'learner_name' => $name, 'sex' => 'Female', 'grade_level' => '8', 'age' => '13']);
    categoryFirstAssert($completed === $studentId, 'Student Information did not reuse the provisional row.');
    $row = $conn->query("SELECT profile_status, sex, age FROM sf8_student_records WHERE record_id = $studentId")->fetch_assoc();
    categoryFirstAssert($row['profile_status'] === 'Complete' && $row['sex'] === 'Female' && $row['age'] === '13',
        'Student Information did not complete the provisional profile.');
    echo "PASS: category-first learner identity, no blank sections, keyed health rows, identity review, and later completion.\n";
} finally {
    $conn->rollback();
    $conn->close();
}
