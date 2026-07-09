<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ini_set('display_errors', 1);
error_reporting(E_ALL);

ob_start();

try {
    include __DIR__ . '/../db.php';
    include __DIR__ . '/record_categories.php';

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
    $pk = $cats[$category]['pk'];

    // Tables that require an upload_id
    $tablesWithUploadId = ['sf8_student_records', 'immunization_records', 'okd_lhas_records', 'deworming_wifa_records', 'arh_records', 'tobacco_control_records'];

    // Get or create a default upload record
    if (in_array($table, $tablesWithUploadId)) {
        $defaultUploadId = null;
        $stmt = $conn->prepare("SELECT upload_id FROM sf8_uploads WHERE file_name = 'DEFAULT_SYSTEM' LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $defaultUploadId = $row['upload_id'];
        } else {
            // Insert a default upload record
            $stmt = $conn->prepare("INSERT INTO sf8_uploads (file_name, cloudinary_url, uploaded_by_email, status) VALUES ('DEFAULT_SYSTEM', '', 'system@clinicdesk.com', 'Approved')");
            $stmt->execute();
            $defaultUploadId = $conn->insert_id;
        }
        $stmt->close();

        // Add upload_id to the data
        $recordData['upload_id'] = $defaultUploadId;
    }

    // Only editable fields
    $editableKeys = array_keys(array_filter($fields, function($f) {
        return !empty($f['edit']);
    }));

    // For nutrition, we also need to include bmi, bmi_category, height_squared if computed
    // But we'll let the frontend or backend handle that. For now, include all fields that are in $recordData and exist in $fields.
    // This ensures upload_id is included even though it's not editable.
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

    // If this is nutrition, we might want to compute BMI if weight and height are provided
    if ($category === 'nutrition') {
        $weight = isset($recordData['weight_kg']) ? (float)$recordData['weight_kg'] : null;
        $height = isset($recordData['height_m']) ? (float)$recordData['height_m'] : null;
        if ($weight > 0 && $height > 0) {
            $bmi = round($weight / ($height * $height), 2);
            $insertData['bmi'] = $bmi;
            $insertData['height_squared'] = round($height * $height, 4);
            $bmiCat = '';
            if ($bmi < 16) $bmiCat = 'Severely Wasted';
            else if ($bmi < 18.5) $bmiCat = 'Wasted';
            else if ($bmi < 25) $bmiCat = 'Normal';
            else if ($bmi < 30) $bmiCat = 'Overweight';
            else $bmiCat = 'Obese';
            $insertData['bmi_category'] = $bmiCat;
        }
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
        $meta = $fields[$col];
        $val = $insertData[$col];
        if ($meta['type'] === 'bool' || $meta['type'] === 'int') {
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
        echo json_encode(['success' => true, 'message' => 'Record added.', 'id' => $stmt->insert_id]);
    } else {
        throw new Exception('Insert failed: ' . $stmt->error);
    }
    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

ob_end_flush();
?>