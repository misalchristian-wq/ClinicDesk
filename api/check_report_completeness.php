<?php
// api/check_report_completeness.php
//
// Returns which required report boxes are saved (in report_saved_data) for a
// given school year, and which are still missing. Used to gate report
// generation: all boxes must be saved first.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "../db.php";

$schoolYear = trim($_REQUEST["school_year"] ?? "");
if ($schoolYear === "" || !preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
    echo json_encode(["success" => false, "message" => "A valid school_year (YYYY-YYYY) is required."]);
    exit;
}

// The 8 required boxes: key => [label, page].
$required = [
    "box1"     => ["label" => "Box 1 – OKD & LHAS",                    "page" => "report-box1-lhas.php"],
    "table1_a" => ["label" => "Table 1.A – Immunization & Nutrition",   "page" => "report-table1-health-nutrition-a.php"],
    "table1_b" => ["label" => "Table 1.B – Deworming & WIFA",           "page" => "report-table1-health-nutrition-b.php"],
    "box2_3"   => ["label" => "Boxes 2 & 3",                            "page" => "report-box2-box3.php"],
    "box4"     => ["label" => "Box 4 / Table 2 – Mental Health",        "page" => "report-table2-box4-mental-health.php"],
    "box5_6"   => ["label" => "Boxes 5 & 6 – ARH & Tobacco",            "page" => "report-box5-box6.php"],
    "box8_9"   => ["label" => "Boxes 7 to 9",                           "page" => "report-box8-box9.php"],
    "box10_11" => ["label" => "Boxes 10 & 11",                          "page" => "report-box10-box11.php"],
];

// Find which keys already have a saved row for this school year.
$savedKeys = [];
$stmt = $conn->prepare("SELECT report_key FROM report_saved_data WHERE school_year = ?");
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $savedKeys[$row["report_key"]] = true;
}
$stmt->close();

$missing = [];
$saved   = [];
foreach ($required as $key => $info) {
    $entry = ["key" => $key, "label" => $info["label"], "page" => $info["page"]];
    if (isset($savedKeys[$key])) {
        $saved[] = $entry;
    } else {
        $missing[] = $entry;
    }
}

echo json_encode([
    "success"      => true,
    "school_year"  => $schoolYear,
    "complete"     => count($missing) === 0,
    "total"        => count($required),
    "saved_count"  => count($saved),
    "missing"      => $missing,
    "saved"        => $saved
]);
?>