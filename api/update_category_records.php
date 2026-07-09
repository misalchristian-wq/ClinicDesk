<?php
// api/update_category_records.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

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
            list($t, $v) = castValue($meta["type"], $row[$fname]);
            $setParts[] = "`$fname` = ?";
            $bindTypes .= $t;
            $bindVals[] = $v;
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
    echo json_encode(["success" => false, "message" => "Update failed: " . $e->getMessage()]);
}
?>