<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../db.php';
require __DIR__ . '/../api/report_sources.php';
$year = '2025-2026';
foreach (['box1','table1_a','table1_b','box4','box5_6'] as $key) {
    $snapshot = reportSourceSnapshot($conn, $key, $year);
    if (!is_array($snapshot) || $snapshot === [] || reportSourceChanges($snapshot, $snapshot) !== []) {
        throw new RuntimeException("Unstable source snapshot for $key");
    }
    if (reportSourceChanges(null, $snapshot) === []) throw new RuntimeException("Missing review status for $key");
}
foreach (['box2_3','box8_9','box10_11'] as $key) {
    if (reportSourceSnapshot($conn, $key, $year) !== []) throw new RuntimeException("Manual section $key should not track student changes");
}
if (reportSourceChanges(['Example'=>[['total'=>1]]], ['Example'=>[['total'=>2]]]) === []) {
    throw new RuntimeException('Source difference was not detected');
}
$conn->begin_transaction();
try {
    $testYear = '2099-2100';
    $empty = reportSourceSnapshot($conn, 'box4', $testYear);
    $stmt = $conn->prepare("INSERT INTO sf8_student_records(lrn,learner_name,school_year,grade_level,sex,is_muslim) VALUES('SYNTHETIC-REPORT-TEST','Synthetic Report Test',?,'7','Female',0)");
    $stmt->bind_param('s', $testYear); $stmt->execute();
    $studentId = $conn->insert_id; $stmt->close();
    $visitDate = '2099-07-01'; $actor = 'Report test';
    $stmt = $conn->prepare('INSERT INTO guidance_counseling_visits(student_record_id,visit_date,recorded_by) VALUES(?,?,?)');
    $stmt->bind_param('iss', $studentId, $visitDate, $actor); $stmt->execute(); $stmt->close();
    $withVisit = reportSourceSnapshot($conn, 'box4', $testYear);
    if (reportSourceChanges($empty, $withVisit) === []) throw new RuntimeException('Counseling visit did not mark Box 4 stale');
    $stmt = $conn->prepare('UPDATE sf8_student_records SET is_muslim=1 WHERE record_id=?');
    $stmt->bind_param('i', $studentId); $stmt->execute(); $stmt->close();
    $withGroup = reportSourceSnapshot($conn, 'box4', $testYear);
    if (reportSourceChanges($withVisit, $withGroup) === []) throw new RuntimeException('Counseled learner group edit did not mark Box 4 stale');
} finally {
    $conn->rollback();
}
echo "Report source snapshots passed\n";
