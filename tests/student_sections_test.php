<?php
require __DIR__ . '/../api/db.php';
require __DIR__ . '/../api/student_sections.php';

function sectionAssert(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

$conn->begin_transaction();
try {
    $lrn = (string)random_int(800000000000, 899999999999);
    $year = '2098-2099';
    $name = 'Synthetic Section Learner';
    $student = $conn->prepare("INSERT INTO sf8_student_records (upload_id, lrn, learner_name, sex, school_year, profile_status) VALUES (NULL, ?, ?, 'Female', ?, 'Complete')");
    $student->bind_param('sss', $lrn, $name, $year);
    $student->execute();
    $studentId = $student->insert_id;
    $student->close();
    clinicEnsureStudentSections($conn, $studentId);
    clinicEnsureStudentSections($conn, $studentId);
    foreach (clinicSectionDefinitions() as $definition) {
        $table = $definition['table'];
        $row = $conn->query("SELECT COUNT(*) AS n FROM `$table` WHERE student_record_id = $studentId")->fetch_assoc();
        sectionAssert((int)$row['n'] === 0, "Blank $table row was created for a student.");
    }

    $uploadName = 'TEMP_SECTION_TEST_' . bin2hex(random_bytes(4));
    $upload = $conn->prepare("INSERT INTO sf8_uploads (file_name, cloudinary_url, uploaded_by_email, status) VALUES (?, '', 'test@example.invalid', 'Approved')");
    $upload->bind_param('s', $uploadName);
    $upload->execute();
    $uploadId = $upload->insert_id;
    $upload->close();
    sectionAssert(!clinicFillExistingSection($conn, 'deworming_wifa', [
        'student_record_id' => $studentId, 'lrn' => $lrn, 'learner_name' => $name,
        'school_year' => $year, 'wifa' => 1,
    ], $uploadId), 'A missing category row was falsely treated as an existing section.');

    // Old installations can still have a blank placeholder; importing should reuse it.
    $placeholder = $conn->prepare("INSERT INTO deworming_wifa_records (upload_id, student_record_id, lrn, learner_name, school_year) VALUES (NULL, ?, ?, ?, ?)");
    $placeholder->bind_param('isss', $studentId, $lrn, $name, $year);
    $placeholder->execute();
    $placeholderId = $placeholder->insert_id;
    $placeholder->close();
    sectionAssert(clinicFillExistingSection($conn, 'deworming_wifa', [
        'student_record_id' => $studentId, 'lrn' => $lrn, 'learner_name' => $name,
        'school_year' => $year, 'wifa' => 1,
    ], $uploadId), 'Legacy placeholder was not reused.');
    $row = $conn->query("SELECT upload_id, wifa FROM deworming_wifa_records WHERE deworming_wifa_id = $placeholderId")->fetch_assoc();
    sectionAssert((int)$row['upload_id'] === $uploadId && (int)$row['wifa'] === 1,
        'Legacy placeholder did not receive its real health data.');
    echo "PASS: new students create no empty health rows; legacy placeholders are reused safely.\n";
} finally {
    $conn->rollback();
    $conn->close();
}
