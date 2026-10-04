<?php
require_once __DIR__ . '/student_sections.php';

// The approval endpoint calls this before its first write, and again when the nurse confirms.
function clinicSf8ReviewConflicts(mysqli $conn, string $path, string $reportCode, string $year): array {
    $categories = [
        'deworming_wifa' => ['deworming_wifa', 'parseDewormingWifaExcelFile'],
        'okd_lhas' => ['okd_lhas', 'parseOkdLhasExcelFile'],
        'immunization_nutritional_status' => ['immunization', 'parseImmunizationExcelFile'],
        'comprehensive_tobacco_control' => ['tobacco', 'parseTobaccoExcelFile'],
        'adolescent_reproductive_health_arh' => ['arh', 'parseArhExcelFile'],
    ];
    if (!preg_match('/^\d{4}-\d{4}$/', $year)) throw new InvalidArgumentException('The SF8 file has no valid school year.');
    if (isset($categories[$reportCode])) {
        [$category, $parser] = $categories[$reportCode];
        $records = $parser($path)['records'] ?? [];
        $definition = clinicSectionDefinitions()[$category];
        $table = $definition['table'];
        $pk = $definition['pk'];
        $fields = $definition['fields'];
    } else {
        $category = 'students_information';
        $records = parseSf8ExcelFile($path)['students'] ?? [];
        $table = 'sf8_student_records';
        $pk = 'record_id';
        $fields = ['school_name', 'district', 'division', 'region', 'school_id', 'grade_level',
            'section', 'track_strand', 'learner_name', 'birthdate', 'age', 'sex', 'weight_kg',
            'height_m', 'height_squared', 'bmi', 'bmi_category', 'height_for_age', 'remarks'];
    }

    $conflicts = [];
    foreach ($records as $record) {
        $lrn = trim((string)($record['lrn'] ?? ''));
        if ($lrn === '' || trim((string)($record['learner_name'] ?? '')) === '') continue;
        $incoming = ['lrn' => $lrn, 'school_year' => $year];
        foreach ($fields as $field) {
            $source = $field === 'sex' && $category !== 'students_information' ? 'gender' : $field;
            if (array_key_exists($source, $record)) $incoming[$field] = $record[$source];
        }
        // Use the same match order as clinicFillExistingSection(): an uploaded
        // category row first, then a master student's unclaimed section row.
        // Student Information also matches manual master rows (upload_id NULL).
        $conditions = ['lrn = ?', 'school_year = ?'];
        if ($category !== 'students_information') $conditions[] = 'upload_id IS NOT NULL';
        $values = [$lrn, $year];
        if ($category !== 'students_information') {
            foreach ($definition['key'] as $field) {
                $conditions[] = "`$field` <=> ?";
                $values[] = $incoming[$field] ?? null;
            }
        }
        $stmt = $conn->prepare("SELECT * FROM `$table` WHERE " . implode(' AND ', $conditions) . " ORDER BY `$pk` DESC");
        clinicSectionBind($stmt, $values);
        $stmt->execute();
        $existingRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if (!$existingRows && $category !== 'students_information') {
            $manualConditions = ['lrn = ?', 'school_year = ?', 'upload_id IS NULL'];
            $manualValues = [$lrn, $year];
            foreach ($definition['key'] as $field) {
                $manualConditions[] = "`$field` <=> ?";
                $manualValues[] = $incoming[$field] ?? null;
            }
            $stmt = $conn->prepare("SELECT * FROM `$table` WHERE " . implode(' AND ', $manualConditions) . " ORDER BY `$pk`");
            clinicSectionBind($stmt, $manualValues);
            $stmt->execute();
            $existingRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        if (!$existingRows && $category !== 'students_information' && $definition['key']) {
            $blankConditions = ['lrn = ?', 'school_year = ?', 'upload_id IS NULL'];
            foreach ($definition['key'] as $field) $blankConditions[] = "(`$field` IS NULL OR `$field` = '')";
            $stmt = $conn->prepare("SELECT * FROM `$table` WHERE " . implode(' AND ', $blankConditions) . " ORDER BY `$pk`");
            $stmt->bind_param('ss', $lrn, $year);
            $stmt->execute();
            $existingRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        if (!$existingRows) continue;

        $current = $existingRows[0];
        $changes = [];
        $hasOverlappingValue = false;
        foreach ($fields as $field) {
            $new = $incoming[$field] ?? null;
            if ($new === null || $new === '') continue;
            $old = $current[$field] ?? null;
            if ($old === null || $old === '') continue; // Empty values fill automatically.
            if ($category !== 'students_information' && $current['upload_id'] === null
                && is_numeric($old) && (float)$old === 0.0) continue; // Default section zero is a blank placeholder.
            $hasOverlappingValue = true;
            if (trim((string)$old) !== trim((string)$new) && !(is_numeric($old) && is_numeric($new) && (float)$old === (float)$new)) {
                $changes[] = ['field' => $field, 'existing' => $old, 'incoming' => $new];
            }
        }
        $provisionalStudent = $category === 'students_information'
            && ($current['profile_status'] ?? 'Complete') === 'Provisional';
        if ($changes || count($existingRows) > 1
            || (!$provisionalStudent && ($hasOverlappingValue || $current['upload_id'] !== null))) {
            $existingSummary = [];
            foreach ($existingRows as $existingRow) {
                $values = [];
                foreach ($fields as $field) $values[$field] = $existingRow[$field] ?? null;
                $existingSummary[] = ['id' => (int)$existingRow[$pk], 'values' => $values];
            }
            $conflicts[] = ['lrn' => $lrn, 'school_year' => $year,
                'learner_name' => $incoming['learner_name'] ?? $current['learner_name'],
                'category' => $category, 'existing_count' => count($existingRows),
                'existing_rows' => $existingSummary, 'changes' => $changes];
        }
    }
    return $conflicts;
}
