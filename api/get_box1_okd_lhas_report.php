<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$sql = "
    SELECT
        screening_type,

        SUM(masterlisted) AS shs_masterlisted,
        SUM(screened) AS shs_screened,
        SUM(findings) AS shs_findings,
        SUM(referred_school) AS shs_referred_school,
        SUM(referred_lgu) AS shs_referred_lgu,
        SUM(referred_private) AS shs_referred_private,
        SUM(referred_others) AS shs_referred_others,

        0 AS jhs_masterlisted,
        0 AS jhs_screened,
        0 AS jhs_findings,
        0 AS jhs_referred_school,
        0 AS jhs_referred_lgu,
        0 AS jhs_referred_private,
        0 AS jhs_referred_others

    FROM okd_lhas_records
    GROUP BY screening_type
    ORDER BY screening_type
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $conn->error
    ]);
    exit;
}

$records = [];

while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

echo json_encode([
    "success" => true,
    "records" => $records
]);

$conn->close();
?>