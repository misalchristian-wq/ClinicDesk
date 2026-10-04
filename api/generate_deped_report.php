<?php
// api/generate_deped_report.php
//
// Generates the official DepEd Part IX report as a downloadable .xlsx, filled
// with ClinicDesk data for the selected school year. The DepEd template's
// design and formulas are preserved; only blank data cells are written.
//
// Request (GET or POST): school_year=YYYY-YYYY
// Response: the .xlsx file as a download (or JSON error).
//
// Requirements on the server:
//   - Python 3 with openpyxl installed  (pip install openpyxl)
//   - The template file and the generator script (paths below)

require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);
ini_set('display_errors', '0');
$recordsJson = null;
$outputXlsx = null;
set_exception_handler(function (Throwable $e) use (&$recordsJson, &$outputXlsx): void {
    error_log('ClinicDesk Excel report: ' . $e->getMessage());
    if (is_string($recordsJson)) @unlink($recordsJson);
    if (is_string($outputXlsx)) @unlink($outputXlsx);
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Could not generate the report. Please try again.']);
});
include __DIR__ . '/../db.php';
require_once __DIR__ . '/report_sources.php';

$schoolYear = trim($_REQUEST["school_year"] ?? "");

if (!preg_match('/^(\d{4})-(\d{4})$/', $schoolYear, $yearParts) || (int)$yearParts[2] !== (int)$yearParts[1] + 1) {
    http_response_code(422);
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "A valid school_year (YYYY-YYYY) is required."]);
    exit;
}

// A saved source-derived count must be reviewed before it is copied into the
// official workbook. Unsaved sections retain the existing template behavior.
$staleSections = [];
$statusStmt = $conn->prepare('SELECT report_key,source_snapshot FROM report_saved_data WHERE school_year=?');
$statusStmt->bind_param('s', $schoolYear);
$statusStmt->execute();
foreach ($statusStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $savedStatus) {
    $current = reportSourceSnapshot($conn, $savedStatus['report_key'], $schoolYear);
    if ($current === []) continue;
    $baseline = $savedStatus['source_snapshot'] === null ? null : json_decode($savedStatus['source_snapshot'], true);
    if (reportSourceChanges($baseline, $current) !== []) $staleSections[] = $savedStatus['report_key'];
}
$statusStmt->close();
if ($staleSections) {
    http_response_code(409);
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'message'=>'Review and save updated student counts in: ' . implode(', ', $staleSections) . '.']);
    exit;
}

// --- Paths (adjust if your folder layout differs) ---
$baseDir   = realpath(__DIR__ . "/..");                 // ClinicDesk root
$genDir    = $baseDir . DIRECTORY_SEPARATOR . "report_generator";
$script    = $genDir . DIRECTORY_SEPARATOR . "generate_deped_report.py";
$template  = $genDir . DIRECTORY_SEPARATOR . "SCHOOL_YEAR_REPORT.xlsx";
$tmpDir    = sys_get_temp_dir();

// Fail early with a clear message if the generator files are missing.
if (!file_exists($script)) {
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "Generator script not found at: " . $script]);
    exit;
}
if (!file_exists($template)) {
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "DepEd template not found at: " . $template . " — copy SCHOOL_YEAR_REPORT.xlsx into the report_generator folder."]);
    exit;
}
$recordsJson = tempnam($tmpDir, "cd_rec_");
$outputXlsx = tempnam($tmpDir, "cd_out_");
if ($recordsJson === false || $outputXlsx === false) {
    if ($recordsJson) @unlink($recordsJson);
    if ($outputXlsx) @unlink($outputXlsx);
    http_response_code(503);
    echo json_encode(["success" => false, "message" => "Could not prepare report files. Please try again."]);
    exit;
}

// --- Fetch the data for this school year ---
$data = ["students" => [], "immunization" => [], "deworming" => []];
$data['saved_reports'] = [];
$stmt = $conn->prepare('SELECT report_key, report_data FROM report_saved_data WHERE school_year = ?');
$stmt->bind_param('s', $schoolYear);
$stmt->execute();
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $savedRow) {
    $decoded = json_decode($savedRow['report_data'], true);
    if (is_array($decoded)) $data['saved_reports'][$savedRow['report_key']] = $decoded;
}
$stmt->close();

// Nutritional status: directly from sf8_student_records.
$stmt = $conn->prepare(
    "SELECT grade_level, sex, bmi_category, school_year, school_name, school_id
     FROM sf8_student_records
     WHERE school_year = ?"
);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $data["students"][] = $row;
}
$stmt->close();
$schools = [];
foreach ($data['students'] as $studentRow) {
    $name = trim((string)($studentRow['school_name'] ?? ''));
    $id = trim((string)($studentRow['school_id'] ?? ''));
    if ($name !== '' && $id !== '') $schools[$name . '|' . $id] = ['name' => $name, 'id' => $id];
}
if (count($schools) === 1) $data['school'] = reset($schools);

// Immunization: uses its own school_year + grade_level columns.
$stmt = $conn->prepare(
    "SELECT i.lrn, COALESCE(NULLIF(i.grade_level, ''), s.grade_level) AS grade_level,
            i.sex, i.vaccine, i.immunized
     FROM immunization_records i
     LEFT JOIN sf8_student_records s ON s.school_year = i.school_year AND s.lrn = i.lrn
     WHERE i.school_year = ?"
);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $data["immunization"][] = $row;
}
$stmt->close();

// SF8 WIFA date is the imported start date; the latest nurse-given dose is
// added below. The workbook counts each learner once per reporting window.
$stmt = $conn->prepare(
    "SELECT lrn, school_year, grade_level, sex, dewormed_sbfp, dewormed_other, wifa,
        NULLIF(wifa_date, '0000-00-00') AS wifa_date
     FROM deworming_wifa_records
     WHERE school_year = ?"
);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $data["deworming"][] = $row;
}
$stmt->close();

$stmt = $conn->prepare("SELECT s.lrn, s.school_year, s.grade_level, s.sex,
    0 AS dewormed_sbfp, 0 AS dewormed_other, 1 AS wifa, e.last_given_date AS wifa_date
    FROM (SELECT student_record_id, MAX(event_date) AS last_given_date
          FROM wifa_events WHERE outcome='Given' GROUP BY student_record_id) e
    JOIN sf8_student_records s ON s.record_id=e.student_record_id
    WHERE s.school_year=?");
$stmt->bind_param('s', $schoolYear);
$stmt->execute();
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) $data['deworming'][] = $row;
$stmt->close();

$stmt = $conn->prepare("SELECT s.lrn, s.school_year, s.grade_level, s.sex,
    CASE WHEN e.channel='SBFP' THEN 1 ELSE 0 END AS dewormed_sbfp,
    CASE WHEN e.channel='Other' THEN 1 ELSE 0 END AS dewormed_other,
    0 AS wifa, NULL AS wifa_date
    FROM deworming_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id
    WHERE s.school_year=? AND e.outcome='Given'");
$stmt->bind_param('s', $schoolYear);
$stmt->execute();
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) $data['deworming'][] = $row;
$stmt->close();

// ARH (Box 5): pregnant learners by grade + delivery mode. Uses arh_records'
// own school_year + grade_level columns (no fragile join).
$data["arh"] = [];
$stmt = $conn->prepare(
    "SELECT grade_level, delivery_mode, COUNT(*) AS total
     FROM arh_records
     WHERE school_year = ? AND LOWER(pregnancy_status) = 'pregnant'
     GROUP BY grade_level, delivery_mode"
);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $data["arh"][] = [
        "grade_level"   => $row["grade_level"],
        "delivery_mode" => $row["delivery_mode"],
        "total"         => (int)$row["total"]
    ];
}
$stmt->close();

// Peer educators count.
$data["peer_educators"] = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM arh_records WHERE school_year = ? AND peer_educator = 1");
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$stmt->bind_result($peerCount);
$stmt->fetch();
$data["peer_educators"] = (int)$peerCount;
$stmt->close();

// Tobacco (Box 6): brought / referred by level group.
$data["tobacco"] = [];
$stmt = $conn->prepare(
    "SELECT grade_level, referred_to_care
     FROM tobacco_control_records
     WHERE school_year = ?"
);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
$tobTally = ["jhs" => ["brought" => 0, "referred" => 0], "shs" => ["brought" => 0, "referred" => 0]];
while ($row = $res->fetch_assoc()) {
    $g = (int)preg_replace('/\D/', '', (string)$row["grade_level"]);
    $grp = ($g >= 11) ? "shs" : "jhs";
    $tobTally[$grp]["brought"] += 1;
    if ((int)$row["referred_to_care"] === 1) {
        $tobTally[$grp]["referred"] += 1;
    }
}
$stmt->close();
foreach ($tobTally as $grp => $vals) {
    $data["tobacco"][] = ["level_group" => $grp, "brought" => $vals["brought"], "referred" => $vals["referred"]];
}

// LHAS (Box 1): uses its own school_year + grade_level columns.
$data["lhas"] = [];
$stmt = $conn->prepare(
    "SELECT grade_level, screening_type, masterlisted, screened, findings,
            referred_school, referred_lgu, referred_private, referred_others
     FROM okd_lhas_records
     WHERE school_year = ?"
);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $data["lhas"][] = $row;
}
$stmt->close();

if (file_put_contents($recordsJson, json_encode($data)) === false) {
    @unlink($recordsJson);
    @unlink($outputXlsx);
    http_response_code(503);
    echo json_encode(["success" => false, "message" => "Could not prepare report data. Please try again."]);
    exit;
}

// --- Run the Python generator ---
// Use "python3"; change to full path if needed (e.g. C:\\Python\\python.exe on XAMPP Windows).
$python = getenv('CLINICDESK_PYTHON');
if (!$python && stripos(PHP_OS, 'WIN') === 0) {
    $bundled = getenv('USERPROFILE') . '\\.cache\\codex-runtimes\\codex-primary-runtime\\dependencies\\python\\python.exe';
    $python = is_file($bundled) ? $bundled : 'python';
}
if (!$python) $python = 'python3';
$cmd = escapeshellarg($python) . " " .
       escapeshellarg($script) . " " .
       escapeshellarg($template) . " " .
       escapeshellarg($outputXlsx) . " " .
       escapeshellarg($schoolYear) . " " .
       escapeshellarg($recordsJson) . " 2>&1";

$out = shell_exec($cmd);
$result = json_decode($out, true);

// Clean up the records temp file.
@unlink($recordsJson);

if (!is_array($result) || empty($result["success"]) || !is_file($outputXlsx) || filesize($outputXlsx) === 0) {
    error_log('ClinicDesk Excel report generation failed: ' . substr((string)$out, 0, 1000));
    http_response_code(503);
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "message" => "Report generation failed.",
        "detail"  => "Check the server's Python and openpyxl configuration."
    ]);
    @unlink($outputXlsx);
    exit;
}

// --- Stream the .xlsx back as a download ---
$downloadName = "DepEd_School_Health_Report_" . $schoolYear . ".xlsx";
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header("Content-Length: " . filesize($outputXlsx));
header("Cache-Control: no-store");
readfile($outputXlsx);

@unlink($outputXlsx);
exit;
?>
