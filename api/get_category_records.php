<?php
// api/get_category_records.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "../db.php";
include "record_categories.php";

$category   = trim($_REQUEST["category"] ?? "nutrition");
$schoolYear = trim($_REQUEST["school_year"] ?? "");

$cats = clinicRecordCategories();
if (!isset($cats[$category])) {
    echo json_encode(["success" => false, "message" => "Unknown category: $category"]);
    exit;
}

$cfg   = $cats[$category];
$table = $cfg["table"];
$pk    = $cfg["pk"];
$fieldKeys = array_keys($cfg["fields"]);

// Build SELECT: include all fields, even non-editable ones
$cols = array_merge([$pk], $fieldKeys);
// Also include school_year if present in table (some may not, but we keep it)
if (in_array('school_year', array_keys($cfg['fields'] ?? [])) || $category === 'nutrition') {
    // For nutrition, school_year is already in fields, but we'll add it explicitly if not present
    if (!in_array('school_year', $fieldKeys)) {
        $cols[] = 'school_year';
    }
}
$colList = implode(", ", array_map(function ($c) { return "`$c`"; }, $cols));

$where = "";
$params = [];
$types = "";
if ($schoolYear !== "" && preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
    $where = "WHERE `school_year` = ?";
    $params[] = $schoolYear;
    $types .= "s";
}

$sql = "SELECT $colList FROM `$table` $where ORDER BY `$pk` DESC";
$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$records = [];
while ($row = $res->fetch_assoc()) {
    // Ensure numeric values are cast to numbers
    foreach ($row as $key => $val) {
        if (is_numeric($val) && !is_null($val)) {
            $row[$key] = (float) $val;
        }
    }
    $records[] = $row;
}
$stmt->close();

echo json_encode([
    "success"  => true,
    "category" => $category,
    "label"    => $cfg["label"],
    "pk"       => $pk,
    "fields"   => $cfg["fields"],
    "records"  => $records
]);
?>