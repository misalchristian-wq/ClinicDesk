<?php
// api/update_category_records.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST");
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);

include "../db.php";
include "record_categories.php";

$data = json_decode(file_get_contents("php://input"), true);
$category = trim($data["category"] ?? "");

$cats = clinicRecordCategories();
if (!isset($cats[$category])) {
    echo json_encode(["success" => false, "message" => "Unknown category."]);
    exit;
}

$cfg    = $cats[$category];
$table  = $cfg["table"];
$pk     = $cfg["pk"];
$fields = $cfg["fields"];

function castValue($type, $value) {
    switch ($type) {
        case "int":
        case "bool":
            return ["i", (int)$value];
        case "float":
            return ["d", (float)$value];
        default:
            return ["s", (string)$value];
    }
}

function computeBmiCategory($bmi) {
    if (is_null($bmi) || $bmi <= 0) return null;
    if ($bmi < 16) return 'Severely Wasted';
    if ($bmi < 18.5) return 'Wasted';
    if ($bmi < 25) return 'Normal';
    if ($bmi < 30) return 'Overweight';
    return 'Obese';
}

$updated = 0;

// ---- BULK (unchanged) ----
if (isset($data["bulk"])) {
    // ... (keep as before)
}

// ---- ROWS ----
$rows = $data["rows"] ?? [];
if (!is_array($rows) || count($rows) === 0) {
    echo json_encode(["success" => false, "message" => "No rows to update."]);
    exit;
}

$conn->begin_transaction();
try {
    foreach ($rows as $row) {
        $pkVal = isset($row[$pk]) ? (int)$row[$pk] : 0;
        if ($pkVal <= 0) continue;

        // The generic editor cannot safely rename a learner across every linked
        // SF8 category. Category LRN corrections may join an existing master.
        $identity = $conn->prepare("SELECT lrn, learner_name, school_year FROM `$table` WHERE `$pk` = ? LIMIT 1 FOR UPDATE");
        $identity->bind_param('i', $pkVal);
        $identity->execute();
        $original = $identity->get_result()->fetch_assoc();
        $identity->close();
        if (!$original) throw new DomainException('The record was removed. Refresh the table before saving.');
        if (array_key_exists('lrn', $row) && (string)$row['lrn'] !== (string)$original['lrn']) {
            if ($category === 'nutrition') {
                throw new DomainException('A Student Information LRN is linked to other SF8 records. Contact the administrator to correct it safely.');
            }
            if (!ctype_digit((string)$row['lrn'])) throw new InvalidArgumentException('LRN must contain digits only.');
            $newLrn = (string)$row['lrn'];
            $master = $conn->prepare('SELECT record_id, learner_name FROM sf8_student_records WHERE lrn = ? AND school_year = ? LIMIT 1 FOR UPDATE');
            $master->bind_param('ss', $newLrn, $original['school_year']);
            $master->execute();
            $target = $master->get_result()->fetch_assoc();
            $master->close();
            if (!$target) throw new DomainException('Add Student Information for the corrected LRN before changing this health record.');
            $expectedName = trim((string)($row['learner_name'] ?? $original['learner_name']));
            if (strcasecmp(preg_replace('/\s+/', ' ', trim((string)$target['learner_name'])),
                    preg_replace('/\s+/', ' ', $expectedName)) !== 0) {
                throw new DomainException('The corrected LRN belongs to a learner with a different name. Review both records.');
            }
            $row['student_record_id'] = (int)$target['record_id'];
        }

        // --- Auto-compute for nutrition ---
        if ($category === 'nutrition') {
            $weight = isset($row['weight_kg']) ? (float)$row['weight_kg'] : null;
            $height = isset($row['height_m']) ? (float)$row['height_m'] : null;
            $bmi = isset($row['bmi']) ? (float)$row['bmi'] : null;

            // If weight and height are provided, compute everything
            if ($weight > 0 && $height > 0) {
                $bmi = round($weight / ($height * $height), 2);
                $row['bmi'] = $bmi;
                $row['height_squared'] = round($height * $height, 4);
            }

            // If bmi is set (either from above or from request), compute category
            if (isset($row['bmi']) && $row['bmi'] > 0) {
                $row['bmi_category'] = computeBmiCategory((float)$row['bmi']);
            }
        }

        // Build the SET clause: include ALL fields that exist in $row AND are defined in $fields.
        // This includes bmi, bmi_category, height_squared even if they are marked edit=false.
        $setParts = [];
        $bindTypes = "";
        $bindVals = [];

        foreach ($fields as $fname => $meta) {
            if (!array_key_exists($fname, $row)) continue;
            if ($fname === $pk) continue; // skip primary key
            if (empty($meta['edit']) && !in_array($fname, ['bmi', 'height_squared', 'bmi_category'], true)) continue;
            list($t, $v) = castValue($meta["type"], $row[$fname]);
            $setParts[] = "`$fname` = ?";
            $bindTypes .= $t;
            $bindVals[] = $v;
        }

        if (isset($row['student_record_id'])) {
            $setParts[] = '`student_record_id` = ?';
            $bindTypes .= 'i';
            $bindVals[] = $row['student_record_id'];
        }

        if (empty($setParts)) continue;

        $sql = "UPDATE `$table` SET " . implode(", ", $setParts) . " WHERE `$pk` = ?";
        $stmt = $conn->prepare($sql);
        $bindTypes .= "i";
        $bindVals[] = $pkVal;
        $stmt->bind_param($bindTypes, ...$bindVals);
        $stmt->execute();
        $updated += $stmt->affected_rows;
        $stmt->close();
    }
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Saved $updated change(s).", "updated" => $updated]);
} catch (Throwable $e) {
    $conn->rollback();
    $duplicate = $e instanceof mysqli_sql_exception && $e->getCode() === 1062;
    http_response_code($duplicate || $e instanceof DomainException ? 409 : ($e instanceof InvalidArgumentException ? 400 : 503));
    error_log('ClinicDesk category update failed: ' . $e->getMessage());
    echo json_encode(["success" => false, "message" => $duplicate
        ? 'This learner already has that health entry for this school year.'
        : ($e instanceof DomainException || $e instanceof InvalidArgumentException ? $e->getMessage() : 'Could not update the record. Please try again.')]);
}
?>
