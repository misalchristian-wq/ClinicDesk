<?php
// api/get_deworming_wifa_raw.php
//
// Returns the imported SF8 baseline plus actual nurse-recorded events. The
// report counts distinct learners per period, not individual WIFA doses.
header("Content-Type: application/json");
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);
ini_set('display_errors', '0');
try {
include "../db.php";

$schoolYear = trim($_REQUEST["school_year"] ?? "");
if ($schoolYear === "" || !preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "A valid school_year (YYYY-YYYY) is required."]);
    exit;
}

$records = [];
$stmt = $conn->prepare(
    "SELECT student_record_id, lrn, school_year, grade_level, sex, dewormed_sbfp, dewormed_other, wifa, wifa_date
     FROM deworming_wifa_records
     WHERE school_year = ? AND upload_id IS NOT NULL"
);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $records[] = [
        "learner_key"    => (string)$row["lrn"] . '|' . (string)$row["school_year"],
        "grade_level"    => $row["grade_level"],
        "sex"            => $row["sex"],
        "dewormed_sbfp"  => (int)$row["dewormed_sbfp"],
        "dewormed_other" => (int)$row["dewormed_other"],
        "wifa"           => (int)$row["wifa"],
        "wifa_date"      => $row["wifa_date"] === '0000-00-00' ? null : $row["wifa_date"]
    ];
}
$stmt->close();

$wifaEvents = [];
$stmt = $conn->prepare("SELECT CONCAT(s.lrn, '|', s.school_year) AS learner_key,
    s.grade_level, s.sex, e.event_date
    FROM wifa_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id
    WHERE s.school_year=? AND e.outcome='Given'");
$stmt->bind_param('s', $schoolYear);
$stmt->execute();
$wifaEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$dewormingEvents = [];
$stmt = $conn->prepare("SELECT CONCAT(s.lrn, '|', s.school_year) AS learner_key,
    s.grade_level, s.sex, e.channel
    FROM deworming_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id
    WHERE s.school_year=? AND e.outcome='Given'");
$stmt->bind_param('s', $schoolYear);
$stmt->execute();
$dewormingEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(["success" => true, "school_year" => $schoolYear, "records" => $records,
    "wifa_events" => $wifaEvents, "deworming_events" => $dewormingEvents]);
} catch (Throwable $e) {
    error_log('ClinicDesk WIFA raw report: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Could not load WIFA records. Please try again.']);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
