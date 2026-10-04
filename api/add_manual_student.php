<?php
// api/add_manual_student.php
//
// Adds ONE student record manually (nurse-entered), computing BMI, BMI category,
// height-for-age, and height² using the WHO reference. Enforces the same rule as
// CSV approval: an LRN is unique PER school year.
//
// Expects JSON:
//   lrn, learner_name, sex, birthdate, age, weight_kg, height_m,
//   school_year (required, must match active year),
//   school_name, district, division, region, school_id, grade_level, section, track_strand, remarks
header('Content-Type: application/json');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to add a student.']);
    exit;
}

include __DIR__ . '/db.php';
require __DIR__ . "/who_classifier.php";
require_once __DIR__ . '/student_sections.php';

$data = json_decode(file_get_contents("php://input"), true);

$lrn          = trim((string)($data["lrn"] ?? ""));
$learnerName  = trim((string)($data["learner_name"] ?? ""));
$sex          = trim((string)($data["sex"] ?? ""));
$birthdate    = trim((string)($data["birthdate"] ?? ""));
$age          = trim((string)($data["age"] ?? ""));
$weightKg     = $data["weight_kg"] ?? null;
$heightM      = $data["height_m"] ?? null;
$schoolYear   = trim((string)($data["school_year"] ?? ""));

$schoolName   = trim((string)($data["school_name"] ?? ""));
$district     = trim((string)($data["district"] ?? ""));
$division     = trim((string)($data["division"] ?? ""));
$region       = trim((string)($data["region"] ?? ""));
$schoolId     = trim((string)($data["school_id"] ?? ""));
$gradeLevel   = trim((string)($data["grade_level"] ?? ""));
$section      = trim((string)($data["section"] ?? ""));
$trackStrand  = trim((string)($data["track_strand"] ?? ""));
$remarks      = trim((string)($data["remarks"] ?? ""));

// --- Required-field validation ---
$missing = [];
if ($lrn === "")         $missing[] = "LRN";
if ($learnerName === "") $missing[] = "Learner's Name";
if ($sex === "")         $missing[] = "Sex";
if ($age === "")         $missing[] = "Age";
if ($weightKg === null || $weightKg === "") $missing[] = "Weight";
if ($heightM === null || $heightM === "")   $missing[] = "Height";
if ($schoolYear === "")  $missing[] = "School Year";

if (!empty($missing)) {
    echo json_encode(["success" => false, "message" => "Missing required field(s): " . implode(", ", $missing) . "."]);
    exit;
}
if (!is_numeric($age) || (float)$age <= 0 || !is_numeric($weightKg) || (float)$weightKg <= 0
    || !is_numeric($heightM) || (float)$heightM <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Age, weight, and height must be greater than zero.']);
    exit;
}

// LRN must be numeric (column is bigint).
if (!ctype_digit($lrn)) {
    echo json_encode(["success" => false, "message" => "LRN must be numeric."]);
    exit;
}

// --- School year: valid format + must be the nurse's active year ---
if (!preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
    echo json_encode(["success" => false, "message" => "School year must be in YYYY-YYYY format (e.g. 2024-2025)."]);
    exit;
}
$activeYear = null;
$res = $conn->query("SELECT year_label FROM school_years WHERE is_active = 1 LIMIT 1");
if ($res && $row = $res->fetch_assoc()) $activeYear = $row["year_label"];

if ($activeYear === null) {
    echo json_encode(["success" => false, "message" => "No active school year has been set by the clinic nurse."]);
    exit;
}
if ($schoolYear !== $activeYear) {
    echo json_encode(["success" => false, "message" => "School year ($schoolYear) must match the active year ($activeYear)."]);
    exit;
}

// --- Compute metrics ---
$ageMonths     = whoAgeToMonths($age);
$bmi           = whoComputeBMI($weightKg, $heightM);
$heightSquared = round((float)$heightM * (float)$heightM, 4);
$bmiCategory   = whoBmiCategory($bmi, $ageMonths, $sex);
$heightForAge  = whoHeightForAge($heightM, (float)$age * 12, $sex);

$conn->begin_transaction();
try {
    $find = $conn->prepare('SELECT record_id, profile_status FROM sf8_student_records WHERE lrn = ? AND school_year = ? LIMIT 1 FOR UPDATE');
    $find->bind_param('ss', $lrn, $schoolYear);
    $find->execute();
    $existing = $find->get_result()->fetch_assoc();
    $find->close();
    require_once __DIR__ . '/sf8_approval_validation.php';
    $candidates = clinicSf8IdentityCandidates($conn, $schoolYear, $learnerName, $lrn);
    if ($candidates) throw new DomainException('A learner with this name and school year has a different LRN. Review the existing record before saving.');
    $values = [$schoolName, $district, $division, $region, $schoolId, $gradeLevel,
        $section, $trackStrand, $learnerName, $birthdate, $age, $sex,
        $weightKg, $heightM, $heightSquared, $bmi, $bmiCategory, $heightForAge, $remarks];
    if ($existing) {
        if ($existing['profile_status'] !== 'Provisional') {
            throw new DomainException("A complete student record with this LRN already exists for school year $schoolYear.");
        }
        $recordId = (int)$existing['record_id'];
        $update = $conn->prepare("UPDATE sf8_student_records SET school_name=?, district=?, division=?, region=?, school_id=?,
            grade_level=?, section=?, track_strand=?, learner_name=?, birthdate=?, age=?, sex=?, weight_kg=?,
            height_m=?, height_squared=?, bmi=?, bmi_category=?, height_for_age=?, remarks=?, profile_status='Complete'
            WHERE record_id=?");
        clinicSectionBind($update, array_merge($values, [$recordId]));
        $update->execute();
        $update->close();
        $message = 'Provisional learner completed from manual Student Information.';
    } else {
        $insert = $conn->prepare("INSERT INTO sf8_student_records
            (lrn, upload_id, school_name, district, division, region, school_id, grade_level,
             section, track_strand, school_year, learner_name, birthdate, age, sex,
             weight_kg, height_m, height_squared, bmi, bmi_category, height_for_age, remarks, profile_status)
            VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Complete')");
        clinicSectionBind($insert, array_merge([$lrn], array_slice($values, 0, 8),
            [$schoolYear], array_slice($values, 8)));
        $insert->execute();
        $recordId = $insert->insert_id;
        $insert->close();
        $message = 'Student added successfully.';
    }
    clinicEnsureStudentSections($conn, $recordId);
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => $message,
        'record' => [
            'record_id' => $recordId, 'lrn' => $lrn, 'learner_name' => $learnerName,
            'bmi' => $bmi, 'bmi_category' => $bmiCategory,
            'height_for_age' => $heightForAge, 'school_year' => $schoolYear,
        ],
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    $duplicate = $e instanceof mysqli_sql_exception && $e->getCode() === 1062;
    http_response_code($e instanceof DomainException || $duplicate ? 409 : 503);
    echo json_encode(['success' => false, 'message' => $e instanceof DomainException ? $e->getMessage()
        : ($duplicate ? 'A student with this LRN already exists for the school year.'
            : 'Student could not be saved. Check the database migration and try again.')]);
}
$conn->close();
?>
