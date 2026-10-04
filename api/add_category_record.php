<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to add a record.']);
    exit;
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();

try {
    include __DIR__ . '/../db.php';
    include __DIR__ . '/record_categories.php';
    require_once __DIR__ . '/student_sections.php';

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON input.');
    }

    $category = $input['category'] ?? '';
    $recordData = $input['record'] ?? [];

    if (!$category || empty($recordData)) {
        throw new Exception('Missing category or record data.');
    }

    $cats = clinicRecordCategories();
    if (!isset($cats[$category])) {
        throw new Exception('Invalid category.');
    }

    $table = $cats[$category]['table'];
    $fields = $cats[$category]['fields'];
    if ($category === 'nutrition') throw new InvalidArgumentException('Add students through the manual student form.');
    $schoolYear = trim((string)($input['school_year'] ?? ''));
    $active = $conn->query('SELECT year_label FROM school_years WHERE is_active = 1 LIMIT 1')->fetch_assoc()['year_label'] ?? '';
    if ($schoolYear === '' || $schoolYear !== $active) throw new InvalidArgumentException('Select the active school year before adding a record.');
    $lrn = trim((string)($recordData['lrn'] ?? ''));
    $learnerName = trim((string)($recordData['learner_name'] ?? ''));
    if ($lrn === '' || !ctype_digit($lrn) || $learnerName === '') throw new InvalidArgumentException('A numeric LRN and learner name are required.');
    foreach (['lhas' => ['screening_type'], 'immunization' => ['vaccine', 'dose'],
        'tobacco' => ['violation_type']] as $type => $requiredFields) {
        if ($category !== $type) continue;
        foreach ($requiredFields as $field) {
            if (trim((string)($recordData[$field] ?? '')) === '') {
                throw new InvalidArgumentException(str_replace('_', ' ', ucfirst($field)) . ' is required.');
            }
        }
    }

    $conn->begin_transaction();
    require_once __DIR__ . '/sf8_approval_validation.php';
    if (clinicSf8IdentityCandidates($conn, $schoolYear, $learnerName, $lrn)) {
        throw new DomainException('A learner with this name and school year has a different LRN. Review the existing record before saving.');
    }
    $learner = clinicEnsureLearnerForCategory($conn, ['lrn' => $lrn, 'school_year' => $schoolYear,
        'learner_name' => $learnerName, 'sex' => $recordData['sex'] ?? '',
        'grade_level' => $recordData['grade_level'] ?? '']);

    // Associate manually entered category records with the system upload.
    $stmt = $conn->prepare("SELECT upload_id FROM sf8_uploads WHERE file_name = 'DEFAULT_SYSTEM' LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $defaultUploadId = $row['upload_id'];
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO sf8_uploads (file_name, cloudinary_url, uploaded_by_email, status) VALUES ('DEFAULT_SYSTEM', '', 'system@clinicdesk.com', 'Approved')");
        $stmt->execute();
        $defaultUploadId = $conn->insert_id;
    }
    $stmt->close();
    $recordData['upload_id'] = $defaultUploadId;

    $insertData = [];
    foreach ($fields as $key => $meta) {
        if (array_key_exists($key, $recordData)) {
            // For safety, we only insert fields that are either editable or are upload_id
            if (!empty($meta['edit']) || $key === 'upload_id') {
                $insertData[$key] = $recordData[$key];
            }
        }
    }

    if (empty($insertData)) {
        throw new Exception('No fields to insert.');
    }
    $insertData['upload_id'] = $defaultUploadId;
    $insertData['school_year'] = $schoolYear;
    $insertData['student_record_id'] = $learner['record_id'];

    $sectionNames = ['lhas' => 'okd_lhas', 'deworming' => 'deworming_wifa',
        'immunization' => 'immunization', 'arh' => 'arh', 'tobacco' => 'tobacco'];
    $definition = clinicSectionDefinitions()[$sectionNames[$category]];
    $matchConditions = ['lrn = ?', 'school_year = ?'];
    $matchValues = [$lrn, $schoolYear];
    foreach ($definition['key'] as $field) {
        $matchConditions[] = "`$field` <=> ?";
        $matchValues[] = $insertData[$field] ?? null;
    }
    $existing = $conn->prepare("SELECT * FROM `{$definition['table']}` WHERE " . implode(' AND ', $matchConditions) . ' LIMIT 1 FOR UPDATE');
    clinicSectionBind($existing, $matchValues);
    $existing->execute();
    $existingRow = $existing->get_result()->fetch_assoc();
    $existing->close();
    if ($existingRow) {
        $hasHealthValues = false;
        foreach ($definition['fields'] as $field) {
            $value = $existingRow[$field] ?? null;
            if ($value !== null && $value !== '' && !(is_numeric($value) && (float)$value === 0.0)) {
                $hasHealthValues = true;
                break;
            }
        }
        if ($existingRow['upload_id'] !== null || $hasHealthValues) {
            throw new DomainException('This learner already has that health entry for the school year. Open the existing record to edit it.');
        }
    }
    if (clinicFillExistingSection($conn, $sectionNames[$category], $insertData, $defaultUploadId)) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Health record saved in the existing learner section.']);
        $conn->close();
        ob_end_flush();
        exit;
    }

    // Build INSERT
    $columns = array_keys($insertData);
    $placeholders = array_fill(0, count($columns), '?');
    $sql = "INSERT INTO `$table` (" . implode(', ', array_map(function($c) { return "`$c`"; }, $columns)) . ") VALUES (" . implode(', ', $placeholders) . ")";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    // Bind parameters
    $types = '';
    $values = [];
    foreach ($columns as $col) {
        $meta = $fields[$col] ?? null;
        $val = $insertData[$col];
        if ($meta === null) {
            $types .= in_array($col, ['upload_id', 'student_record_id'], true) ? 'i' : 's';
            $values[] = $val;
        } elseif ($meta['type'] === 'bool' || $meta['type'] === 'int') {
            $types .= 'i';
            $values[] = (int)$val;
        } elseif ($meta['type'] === 'float') {
            $types .= 'd';
            $values[] = (float)$val;
        } else {
            $types .= 's';
            $values[] = (string)$val;
        }
    }

    $stmt->bind_param($types, ...$values);
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Record added.', 'id' => $stmt->insert_id]);
    } else {
        throw new Exception('Insert failed: ' . $stmt->error);
    }
    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof mysqli) $conn->rollback();
    ob_clean();
    $duplicate = $e instanceof mysqli_sql_exception && $e->getCode() === 1062;
    http_response_code($e instanceof DomainException || $duplicate ? 409 : ($e instanceof InvalidArgumentException ? 400 : 503));
    echo json_encode(['success' => false, 'message' => $e instanceof DomainException || $e instanceof InvalidArgumentException
        ? $e->getMessage() : ($duplicate ? 'This learner already has that health entry for the school year.'
            : 'Could not add the health record. Please try again.')]);
}

ob_end_flush();
?>
