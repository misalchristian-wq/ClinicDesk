<?php
// api/check_report_completeness.php
//
// Returns which required report boxes are saved (in report_saved_data) for a
// given school year, and which are still missing. Used to gate report
// generation: all boxes must be saved first.
header("Content-Type: application/json");
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);
include __DIR__ . "/../db.php";
require_once __DIR__ . '/report_sources.php';

$schoolYear = trim($_REQUEST["school_year"] ?? "");
if ($schoolYear === "" || !preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
    echo json_encode(["success" => false, "message" => "A valid school_year (YYYY-YYYY) is required."]);
    exit;
}

// The 8 required boxes: key => [label, page].
$required = [
    "box1"     => ["label" => "Box 1 – OKD & LHAS",                    "page" => "report-box1-lhas.php"],
    "table1_a" => ["label" => "Tables 1.A–1.B – Immunization & Nutrition",   "page" => "report-table1-health-nutrition-a.php"],
    "table1_b" => ["label" => "Tables 1.C–1.D – Deworming & WIFA",           "page" => "report-table1-health-nutrition-b.php"],
    "box2_3"   => ["label" => "Boxes 2 & 3",                            "page" => "report-box2-box3.php"],
    "box4"     => ["label" => "Table 2 & Box 4 – Mental Health",        "page" => "report-table2-box4-mental-health.php"],
    "box5_6"   => ["label" => "Boxes 5 & 6 – ARH & Tobacco",            "page" => "report-box5-box6.php"],
    "box8_9"   => ["label" => "Boxes 7–9 – Drug Education, Food & Feeding", "page" => "report-box8-box9.php"],
    "box10_11" => ["label" => "Boxes 10 & 11",                          "page" => "report-box10-box11.php"],
];

// Find which keys already have a saved row for this school year.
$savedKeys = [];
$stmt = $conn->prepare("SELECT report_key, report_data, source_snapshot FROM report_saved_data WHERE school_year = ?");
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $savedKeys[$row["report_key"]] = $row;
}
$stmt->close();

$missing = [];
$saved   = [];
$stale = [];
foreach ($required as $key => $info) {
    $entry = ["key" => $key, "label" => $info["label"], "page" => $info["page"]];
    $reportData = isset($savedKeys[$key]) ? json_decode($savedKeys[$key]['report_data'], true) : null;
    $requiredFields = [
        'box1' => ['referralConcerns'],
        'box4' => ['mentalHealthCases'],
        'box5_6' => ['tobaccoIntervention'],
        'box8_9' => ['drugEducation', 'drugLifeSkills'],
    ][$key] ?? [];
    $currentFormat = is_array($reportData);
    $fields = $currentFormat ? $reportData : [];
    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $fields)) $currentFormat = false;
    }
    $requiredAnswers = [
        'box2_3' => ['hasSchoolClinic','visitedBySDO','waterForDrinking'],
        'box4' => ['hasGuidanceOffice','hasMentalHealthTraining'],
        'box5_6' => ['hasSupportCenter'],
        'box8_9' => ['drugEducation','hasCanteen','hasKitchen'],
    ][$key] ?? [];
    foreach ($requiredAnswers as $field) {
        if (!in_array($fields[$field] ?? null, ['Yes','No'], true)) $currentFormat = false;
    }
    if ($currentFormat && $key === 'box8_9' && trim((string)($reportData['drugEducation'] ?? '')) === '') {
        $currentFormat = false;
    }
    if ($currentFormat) {
        try { $currentSnapshot = reportSourceSnapshot($conn, $key, $schoolYear); }
        catch (Throwable $e) {
            error_log('ClinicDesk report completeness: ' . $e->getMessage());
            http_response_code(503);
            echo json_encode(['success'=>false,'message'=>'Could not check report changes. Please try again.']);
            exit;
        }
        if ($currentSnapshot !== []) {
            $baseline = $savedKeys[$key]['source_snapshot'] === null ? null : json_decode($savedKeys[$key]['source_snapshot'], true);
            $changes = reportSourceChanges($baseline, $currentSnapshot);
            if ($changes !== []) { $entry['changes'] = $changes; $stale[] = $entry; }
        }
        $saved[] = $entry;
    } else {
        if (is_array($reportData)) $entry['reason'] = 'Saved before the current report format; review and save again.';
        else $entry['reason'] = 'No saved answers for this school year.';
        $missing[] = $entry;
    }
}

echo json_encode([
    "success"      => true,
    "school_year"  => $schoolYear,
    "complete"     => count($missing) === 0 && count($stale) === 0,
    "total"        => count($required),
    "saved_count"  => count($saved),
    "missing"      => $missing,
    "saved"        => $saved,
    "stale"        => $stale
]);
?>
