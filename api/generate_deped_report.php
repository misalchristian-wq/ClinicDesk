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

include "../db.php";

$schoolYear = trim($_REQUEST["school_year"] ?? "");

if ($schoolYear === "" || !preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "A valid school_year (YYYY-YYYY) is required."]);
    exit;
}

// --- Paths (adjust if your folder layout differs) ---
$baseDir   = realpath(__DIR__ . "/..");                 // ClinicDesk root
$genDir    = $baseDir . DIRECTORY_SEPARATOR . "report_generator";
$script    = $genDir . DIRECTORY_SEPARATOR . "generate_deped_report.py";
$template  = $genDir . DIRECTORY_SEPARATOR . "SCHOOL_YEAR_REPORT.xlsx";
$tmpDir    = sys_get_temp_dir();
$recordsJson = tempnam($tmpDir, "cd_rec_") . ".json";
$outputXlsx  = tempnam($tmpDir, "cd_out_") . ".xlsx";

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

// --- Fetch the data for this school year ---
$data = ["students" => [], "immunization" => [], "deworming" => []];

// Nutritional status: directly from sf8_student_records.
$stmt = $conn->prepare(
    "SELECT grade_level, sex, bmi_category, school_year
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

// Immunization: uses its own school_year + grade_level columns.
$stmt = $conn->prepare(
    "SELECT grade_level, sex, vaccine, immunized
     FROM immunization_records
     WHERE school_year = ?"
);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $data["immunization"][] = $row;
}
$stmt->close();

// Deworming: uses its own school_year + grade_level columns.
$stmt = $conn->prepare(
    "SELECT grade_level, sex, dewormed_sbfp, dewormed_other
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

file_put_contents($recordsJson, json_encode($data));

// --- Run the Python generator ---
// Use "python3"; change to full path if needed (e.g. C:\\Python\\python.exe on XAMPP Windows).
$python = stripos(PHP_OS, "WIN") === 0 ? "python" : "python3";
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

if (!is_array($result) || empty($result["success"]) || !file_exists($outputXlsx)) {
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "message" => "Report generation failed.",
        "detail"  => $out
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