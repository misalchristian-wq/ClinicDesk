<?php
// Workbook preflight shared by nurse uploads and the normal SF8 preview flow.
// LRN is an identifier, never a number: no casts or leading-zero correction.
function clinicSf8NormalizeName(string $name): string {
    return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
}

function clinicSf8IdentityIndex(mysqli $conn, string $year): array {
    $tables = [
        'sf8_student_records' => 'Student Information',
        'deworming_wifa_records' => 'Deworming & WIFA',
        'okd_lhas_records' => 'OKD & LHAS',
        'immunization_records' => 'Immunization',
        'tobacco_control_records' => 'Tobacco Control',
        'arh_records' => 'ARH',
    ];
    $index = [];
    foreach ($tables as $table => $source) {
        $stmt = $conn->prepare("SELECT DISTINCT lrn, learner_name FROM `$table` WHERE school_year = ? AND lrn IS NOT NULL AND lrn <> ''");
        $stmt->bind_param('s', $year);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
            $existingLrn = trim((string)$row['lrn']);
            $normalized = clinicSf8NormalizeName((string)$row['learner_name']);
            if ($normalized === '') continue;
            $index[$normalized][$existingLrn] ??= ['existing_lrn' => $existingLrn, 'source' => $source];
        }
        $stmt->close();
    }
    return $index;
}

function clinicSf8IdentityCandidates(mysqli $conn, string $year, string $name, string $incomingLrn): array {
    $index = clinicSf8IdentityIndex($conn, $year);
    $normalized = clinicSf8NormalizeName($name);
    $candidates = $index[$normalized] ?? [];
    unset($candidates[$incomingLrn]);
    return array_values($candidates);
}

function clinicSf8ApprovalPreflight(mysqli $conn, array $records, string $reportCode, string $year): array {
    $keysByReport = [
        'students_information' => [],
        'deworming_wifa' => [],
        'okd_lhas' => ['screening_type'],
        'immunization_nutritional_status' => ['vaccine', 'dose'],
        'comprehensive_tobacco_control' => [],
        'adolescent_reproductive_health_arh' => [],
    ];
    if (!isset($keysByReport[$reportCode])) throw new InvalidArgumentException('Unsupported SF8 file type.');
    if (!preg_match('/^\d{4}-\d{4}$/', $year)) throw new InvalidArgumentException('A valid school year is required.');
    if (!$records) return ['errors' => ['The workbook contains no learner records.'], 'identity_conflicts' => []];

    $errors = [];
    $identityConflicts = [];
    $seenKeys = [];
    $seenNames = [];
    $identityIndex = clinicSf8IdentityIndex($conn, $year);

    foreach ($records as $index => $record) {
        $rowNumber = $index + 1;
        $lrn = trim((string)($record['lrn'] ?? ''));
        $name = trim((string)($record['learner_name'] ?? ''));
        if ($lrn === '' || !ctype_digit($lrn) || $name === '') {
            $errors[] = "Record {$rowNumber}: a numeric LRN and learner name are required.";
            continue;
        }
        $parts = [$lrn];
        foreach ($keysByReport[$reportCode] as $field) {
            $value = trim((string)($record[$field] ?? ''));
            if ($value === '') $errors[] = "Record {$rowNumber}: " . str_replace('_', ' ', $field) . ' is required.';
            $parts[] = strtolower($value);
        }
        if ($reportCode === 'comprehensive_tobacco_control' && trim((string)($record['violation_type'] ?? '')) === '') {
            $errors[] = "Record {$rowNumber}: violation type is required.";
        }
        $key = implode("\x1f", $parts);
        if (isset($seenKeys[$key])) $errors[] = "Record {$rowNumber}: the same learner and SF8 entry appears more than once in this file.";
        $seenKeys[$key] = true;

        $normalizedName = clinicSf8NormalizeName($name);
        if (isset($seenNames[$normalizedName]) && $seenNames[$normalizedName] !== $lrn) {
            $identityConflicts[$lrn . ':' . $seenNames[$normalizedName]] = [
                'learner_name' => $name, 'school_year' => $year,
                'incoming_lrn' => $lrn, 'existing_lrn' => $seenNames[$normalizedName],
                'source' => 'Another row in this file',
            ];
        }
        $seenNames[$normalizedName] = $lrn;
        foreach ($identityIndex[$normalizedName] ?? [] as $candidate) {
            if ($candidate['existing_lrn'] === $lrn) continue;
            $identityConflicts[$lrn . ':' . $candidate['existing_lrn']] = [
                'learner_name' => $name, 'school_year' => $year,
                'incoming_lrn' => $lrn, 'existing_lrn' => $candidate['existing_lrn'],
                'source' => $candidate['source'],
            ];
        }
    }
    return ['errors' => $errors, 'identity_conflicts' => array_values($identityConflicts)];
}
