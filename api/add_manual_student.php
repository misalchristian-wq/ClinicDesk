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
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "../db.php";
require __DIR__ . "/who_classifier.php";

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

// --- Duplicate check: LRN unique per school year ---
$dup = $conn->prepare("SELECT record_id FROM sf8_student_records WHERE lrn = ? AND school_year = ? LIMIT 1");
$dup->bind_param("is", $lrn, $schoolYear);
$dup->execute();
$dup->store_result();
if ($dup->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "A student with LRN $lrn already exists for school year $schoolYear."]);
    exit;
}
$dup->close();

// --- Compute metrics ---
$ageMonths     = whoAgeToMonths($age);
$bmi           = whoComputeBMI($weightKg, $heightM);
$heightSquared = round((float)$heightM * (float)$heightM, 4);
$bmiCategory   = whoBmiCategory($bmi, $ageMonths, $sex);
$heightForAge  = whoHeightForAge($heightM, $ageMonths);

// upload_id 0 marks a manually-entered record (no CSV upload behind it).
$uploadId = 0;

$stmt = $conn->prepare(
    "INSERT INTO sf8_student_records
     (lrn, upload_id, school_name, district, division, region, school_id, grade_level,
      section, track_strand, school_year, learner_name, birthdate, age, sex,
      weight_kg, height_m, height_squared, bmi, bmi_category, height_for_age, remarks)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
);

$stmt->bind_param(
    "iissssssssssssssdddsss",
    $lrn, $uploadId, $schoolName, $district, $division, $region, $schoolId, $gradeLevel,
    $section, $trackStrand, $schoolYear, $learnerName, $birthdate, $age, $sex,
    $weightKg, $heightM, $heightSquared, $bmi, $bmiCategory, $heightForAge, $remarks
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Student added successfully.",
        "record" => [
            "record_id"      => $stmt->insert_id,
            "lrn"            => $lrn,
            "learner_name"   => $learnerName,
            "bmi"            => $bmi,
            "bmi_category"   => $bmiCategory,
            "height_for_age" => $heightForAge,
            "school_year"    => $schoolYear
        ]
    ]);
} else {
    if ($conn->errno === 1062) {
        echo json_encode(["success" => false, "message" => "Duplicate: LRN $lrn already exists for $schoolYear."]);
    } else {
        echo json_encode(["success" => false, "message" => "Could not save student: " . $conn->error]);
    }
}
?>