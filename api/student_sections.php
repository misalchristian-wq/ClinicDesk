<?php
// Category table names and column names are fixed here; no SQL identifiers come from requests.
function clinicSectionDefinitions(): array {
    return [
        'deworming_wifa' => ['table' => 'deworming_wifa_records', 'pk' => 'deworming_wifa_id', 'key' => [],
            'fields' => ['dewormed_sbfp', 'dewormed_other', 'wifa', 'wifa_date', 'remarks']],
        'okd_lhas' => ['table' => 'okd_lhas_records', 'pk' => 'okd_lhas_id', 'key' => ['screening_type'],
            'fields' => ['screening_type', 'masterlisted', 'screened', 'findings', 'referred_school', 'referred_lgu', 'referred_private', 'referred_others', 'remarks']],
        'immunization' => ['table' => 'immunization_records', 'pk' => 'immunization_id', 'key' => ['vaccine', 'dose'],
            'fields' => ['vaccine', 'dose', 'immunized', 'remarks']],
        'tobacco' => ['table' => 'tobacco_control_records', 'pk' => 'tobacco_id', 'key' => [],
            'fields' => ['violation_type', 'referred_to_care', 'remarks']],
        'arh' => ['table' => 'arh_records', 'pk' => 'arh_record_id', 'key' => [],
            'fields' => ['pregnancy_status', 'delivery_mode', 'peer_educator', 'remarks']],
    ];
}

function clinicSectionColumns(array $definition): array {
    return array_merge(['student_record_id', 'lrn', 'learner_name', 'sex', 'birthdate', 'age', 'school_year', 'grade_level'], $definition['fields']);
}

function clinicSectionBind(mysqli_stmt $stmt, array $values): void {
    $types = str_repeat('s', count($values));
    $stmt->bind_param($types, ...$values);
}

// Link category records that arrived before Student Information. A learner no
// longer creates blank rows in every health table.
function clinicEnsureStudentSections(mysqli $conn, int $studentId): void {
    $master = $conn->prepare('SELECT record_id, lrn, learner_name, sex, birthdate, age, school_year, grade_level FROM sf8_student_records WHERE record_id = ?');
    $master->bind_param('i', $studentId);
    $master->execute();
    $student = $master->get_result()->fetch_assoc();
    $master->close();
    if (!$student || !$student['school_year']) throw new RuntimeException('Student and school year are required to create health sections.');

    foreach (clinicSectionDefinitions() as $definition) {
        $table = $definition['table'];
        // A category file may have been approved before the master student was added.
        $link = $conn->prepare("UPDATE `$table` SET student_record_id = ? WHERE student_record_id IS NULL AND lrn = ? AND school_year = ?");
        $link->bind_param('iss', $studentId, $student['lrn'], $student['school_year']);
        $link->execute();
        $link->close();

    }
}

// A category may arrive first. Create only the learner identity; later Student
// Information or manual entry completes this same LRN + school-year row.
function clinicEnsureLearnerForCategory(mysqli $conn, array $incoming): array {
    $lrn = trim((string)($incoming['lrn'] ?? ''));
    $year = trim((string)($incoming['school_year'] ?? ''));
    $name = trim((string)($incoming['learner_name'] ?? ''));
    if ($lrn === '' || !ctype_digit($lrn) || $name === '' || !preg_match('/^\d{4}-\d{4}$/', $year)) {
        throw new InvalidArgumentException('A numeric LRN, learner name, and valid school year are required.');
    }
    $sex = trim((string)($incoming['sex'] ?? '')) ?: null;
    $birthdate = trim((string)($incoming['birthdate'] ?? '')) ?: null;
    $age = trim((string)($incoming['age'] ?? '')) ?: null;
    $grade = trim((string)($incoming['grade_level'] ?? '')) ?: null;

    $insert = $conn->prepare("INSERT INTO sf8_student_records
        (upload_id, lrn, school_year, learner_name, sex, birthdate, age, grade_level, profile_status)
        VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, 'Provisional')
        ON DUPLICATE KEY UPDATE record_id = LAST_INSERT_ID(record_id)");
    $insert->bind_param('sssssss', $lrn, $year, $name, $sex, $birthdate, $age, $grade);
    $insert->execute();
    $studentId = (int)$conn->insert_id;
    $created = $insert->affected_rows === 1;
    $insert->close();

    $find = $conn->prepare('SELECT record_id, grade_level, profile_status FROM sf8_student_records WHERE record_id = ?');
    $find->bind_param('i', $studentId);
    $find->execute();
    $student = $find->get_result()->fetch_assoc();
    $find->close();
    if (!$student) throw new RuntimeException('Learner identity could not be loaded after saving.');
    clinicEnsureStudentSections($conn, $studentId);
    return ['record_id' => $studentId, 'grade_level' => $student['grade_level'],
        'profile_status' => $student['profile_status'], 'created' => $created];
}

// Returns true when an existing placeholder or actual category row was filled.
// Rows from real uploads retain their existing nonempty values, including an intentional numeric zero.
function clinicFillExistingSection(mysqli $conn, string $category, array $incoming, int $uploadId, bool $overrideExisting = false): bool {
    $definitions = clinicSectionDefinitions();
    if (!isset($definitions[$category])) throw new InvalidArgumentException('Unsupported student section.');
    $definition = $definitions[$category];
    $table = $definition['table'];
    $pk = $definition['pk'];
    $lrn = (string)($incoming['lrn'] ?? '');
    $year = (string)($incoming['school_year'] ?? '');
    if ($lrn === '' || $year === '') throw new InvalidArgumentException('LRN and school year are required.');

    $conditions = ['lrn = ?', 'school_year = ?', 'upload_id IS NOT NULL'];
    $values = [$lrn, $year];
    foreach ($definition['key'] as $field) {
        $conditions[] = "`$field` <=> ?";
        $values[] = $incoming[$field] ?? null;
    }
    $find = $conn->prepare("SELECT * FROM `$table` WHERE " . implode(' AND ', $conditions) . " ORDER BY `$pk` DESC LIMIT 1 FOR UPDATE");
    clinicSectionBind($find, $values);
    $find->execute();
    $current = $find->get_result()->fetch_assoc();
    $find->close();
    $placeholder = false;

    if (!$current) {
        $manualConditions = ['lrn = ?', 'school_year = ?', 'upload_id IS NULL'];
        $manualValues = [$lrn, $year];
        foreach ($definition['key'] as $field) {
            $manualConditions[] = "`$field` <=> ?";
            $manualValues[] = $incoming[$field] ?? null;
        }
        $find = $conn->prepare("SELECT * FROM `$table` WHERE " . implode(' AND ', $manualConditions) . " ORDER BY `$pk` LIMIT 1 FOR UPDATE");
        clinicSectionBind($find, $manualValues);
        $find->execute();
        $current = $find->get_result()->fetch_assoc();
        $find->close();
        $placeholder = (bool)$current;
    }
    if (!$current && $definition['key']) {
        $blankConditions = ['lrn = ?', 'school_year = ?', 'upload_id IS NULL'];
        foreach ($definition['key'] as $field) $blankConditions[] = "(`$field` IS NULL OR `$field` = '')";
        $find = $conn->prepare("SELECT * FROM `$table` WHERE " . implode(' AND ', $blankConditions) . " ORDER BY `$pk` LIMIT 1 FOR UPDATE");
        $find->bind_param('ss', $lrn, $year);
        $find->execute();
        $current = $find->get_result()->fetch_assoc();
        $find->close();
        $placeholder = (bool)$current;
    }
    if (!$current) return false;

    $changes = [];
    if ($placeholder) $changes['upload_id'] = $uploadId;
    foreach (clinicSectionColumns($definition) as $field) {
        $new = $incoming[$field] ?? null;
        if ($new === null || $new === '') continue;
        $old = $current[$field] ?? null;
        $empty = $old === null || $old === '';
        if ($placeholder && in_array($field, $definition['fields'], true) && is_numeric($old) && (float)$old === 0.0) $empty = true;
        if ($empty || ($overrideExisting && in_array($field, $definition['fields'], true))) $changes[$field] = $new;
    }
    if (!$changes) return true;
    $assignments = array_map(fn($field) => "`$field` = ?", array_keys($changes));
    $values = array_values($changes);
    $values[] = $current[$pk];
    $update = $conn->prepare("UPDATE `$table` SET " . implode(', ', $assignments) . " WHERE `$pk` = ?");
    clinicSectionBind($update, $values);
    $update->execute();
    $update->close();
    return true;
}

// Re-importing Student Information fills gaps in an existing manual/SF8 master row.
// The original upload association is retained so deleting the new upload cannot delete the student.
function clinicFillExistingStudent(mysqli $conn, array $incoming, bool $overrideExisting = false, ?bool &$completedProvisional = null): ?int {
    $lrn = (string)($incoming['lrn'] ?? '');
    $year = (string)($incoming['school_year'] ?? '');
    $find = $conn->prepare('SELECT * FROM sf8_student_records WHERE lrn = ? AND school_year = ? LIMIT 1 FOR UPDATE');
    $find->bind_param('ss', $lrn, $year);
    $find->execute();
    $current = $find->get_result()->fetch_assoc();
    $find->close();
    if (!$current) return null;
    $completedProvisional = ($current['profile_status'] ?? 'Complete') === 'Provisional';
    $fields = ['school_name', 'district', 'division', 'region', 'school_id', 'grade_level',
        'section', 'track_strand', 'learner_name', 'birthdate', 'age', 'sex', 'weight_kg',
        'height_m', 'height_squared', 'bmi', 'bmi_category', 'height_for_age', 'remarks'];
    $changes = [];
    if (($current['profile_status'] ?? 'Complete') !== 'Complete') $changes['profile_status'] = 'Complete';
    foreach ($fields as $field) {
        $new = $incoming[$field] ?? null;
        if ($new === null || $new === '') continue;
        $old = $current[$field] ?? null;
        if ($old === null || $old === '' || $overrideExisting) $changes[$field] = $new;
    }
    if ($changes) {
        $assignments = array_map(fn($field) => "`$field` = ?", array_keys($changes));
        $values = array_values($changes);
        $values[] = $current['record_id'];
        $update = $conn->prepare('UPDATE sf8_student_records SET ' . implode(', ', $assignments) . ' WHERE record_id = ?');
        clinicSectionBind($update, $values);
        $update->execute();
        $update->close();
    }
    return (int)$current['record_id'];
}
