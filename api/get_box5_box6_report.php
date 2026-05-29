<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$response = [
    "success" => true,
    "arh" => [],
    "peer_educators" => 0,
    "tobacco" => []
];

$arhSql = "
    SELECT
        s.grade_level,
        a.delivery_mode,
        COUNT(*) AS total
    FROM arh_records a
    LEFT JOIN sf8_student_records s
        ON a.student_record_id = s.record_id
    WHERE a.pregnancy_status = 'Pregnant'
    GROUP BY s.grade_level, a.delivery_mode
";

$arhResult = $conn->query($arhSql);

if (!$arhResult) {
    echo json_encode([
        "success" => false,
        "message" => "ARH query failed: " . $conn->error
    ]);
    exit;
}

while ($row = $arhResult->fetch_assoc()) {
    $response["arh"][] = $row;
}

$peerSql = "
    SELECT SUM(peer_educator) AS total_peer
    FROM arh_records
";

$peerResult = $conn->query($peerSql);

if ($peerResult) {
    $peerRow = $peerResult->fetch_assoc();
    $response["peer_educators"] = intval($peerRow["total_peer"] ?? 0);
}

$tobaccoSql = "
    SELECT
        CASE
            WHEN s.grade_level IN ('7','8','9','10') THEN 'jhs'
            WHEN s.grade_level IN ('11','12') THEN 'shs'
            ELSE 'unknown'
        END AS level_group,

        SUM(CASE WHEN t.violation_type IN ('Brought tobacco', 'Brought vape') THEN 1 ELSE 0 END) AS brought,
        SUM(CASE WHEN t.referred_to_care = 1 THEN 1 ELSE 0 END) AS referred

    FROM tobacco_control_records t
    LEFT JOIN sf8_student_records s
        ON t.student_record_id = s.record_id
    GROUP BY level_group
";

$tobaccoResult = $conn->query($tobaccoSql);

if (!$tobaccoResult) {
    echo json_encode([
        "success" => false,
        "message" => "Tobacco query failed: " . $conn->error
    ]);
    exit;
}

while ($row = $tobaccoResult->fetch_assoc()) {
    $response["tobacco"][] = $row;
}

echo json_encode($response);

$conn->close();
?>