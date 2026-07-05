<?php
// api/get_deworming_wifa_raw.php
//
// Returns raw deworming/WIFA records for a school year, using the table's own
// school_year + grade_level columns. The frontend aggregates deworming counts
// and auto-detects WIFA periods from each record's wifa_date.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "../db.php";

$schoolYear = trim($_REQUEST["school_year"] ?? "");
if ($schoolYear === "" || !preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
    echo json_encode(["success" => false, "message" => "A valid school_year (YYYY-YYYY) is required."]);
    exit;
}

$records = [];
$stmt = $conn->prepare(
    "SELECT grade_level, sex, dewormed_sbfp, dewormed_other, wifa, wifa_date
     FROM deworming_wifa_records
     WHERE school_year = ?"
);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $records[] = [
        "grade_level"    => $row["grade_level"],
        "sex"            => $row["sex"],
        "dewormed_sbfp"  => (int)$row["dewormed_sbfp"],
        "dewormed_other" => (int)$row["dewormed_other"],
        "wifa"           => (int)$row["wifa"],
        "wifa_date"      => $row["wifa_date"]
    ];
}
$stmt->close();

echo json_encode(["success" => true, "school_year" => $schoolYear, "records" => $records]);
?>