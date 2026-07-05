<?php
// api/get_box5_box6_report.php
//
// Returns aggregated ARH (Box 5) and Tobacco (Box 6) data for a school year.
// ARH/tobacco records now carry their own school_year + grade_level
// (see arh_add_year_grade.sql), so no fragile join is needed.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "../db.php";

$schoolYear = trim($_REQUEST["school_year"] ?? "");
if ($schoolYear === "" || !preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
    echo json_encode(["success" => false, "message" => "A valid school_year (YYYY-YYYY) is required."]);
    exit;
}

$response = [
    "success"        => true,
    "school_year"    => $schoolYear,
    "arh"            => [],
    "peer_educators" => 0,
    "tobacco"        => []
];

// ---- ARH (Box 5): pregnant learners by grade + delivery mode ----
$sql = "SELECT grade_level, delivery_mode, COUNT(*) AS total
        FROM arh_records
        WHERE school_year = ?
          AND LOWER(pregnancy_status) = 'pregnant'
        GROUP BY grade_level, delivery_mode";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    // Pregnant learners with no delivery mode default to "In School" so they
    // still appear in Box 5 rather than being silently dropped.
    $mode = trim((string)$row["delivery_mode"]);
    if ($mode === "") {
        $mode = "In School";
    }
    $response["arh"][] = [
        "grade_level"   => $row["grade_level"],
        "delivery_mode" => $mode,
        "total"         => (int)$row["total"]
    ];
}
$stmt->close();

// Peer educators
$sql = "SELECT COUNT(*) AS c FROM arh_records WHERE school_year = ? AND peer_educator = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$stmt->bind_result($peerCount);
$stmt->fetch();
$response["peer_educators"] = (int)$peerCount;
$stmt->close();

// ---- Tobacco (Box 6): by level group (JHS 7-10, SHS 11-12) ----
$sql = "SELECT grade_level, referred_to_care
        FROM tobacco_control_records
        WHERE school_year = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$res = $stmt->get_result();

$tally = ["jhs" => ["brought" => 0, "referred" => 0], "shs" => ["brought" => 0, "referred" => 0]];
while ($row = $res->fetch_assoc()) {
    $grade = (int)preg_replace('/\D/', '', (string)$row["grade_level"]);
    $group = ($grade >= 11) ? "shs" : "jhs";
    $tally[$group]["brought"] += 1;
    if ((int)$row["referred_to_care"] === 1) {
        $tally[$group]["referred"] += 1;
    }
}
$stmt->close();

foreach ($tally as $group => $vals) {
    $response["tobacco"][] = [
        "level_group" => $group,
        "brought"     => $vals["brought"],
        "referred"    => $vals["referred"]
    ];
}

echo json_encode($response);
?>