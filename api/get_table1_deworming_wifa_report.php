<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

ini_set("display_errors", 0);
error_reporting(E_ALL);

include "db.php";

$sql = "
    SELECT
        COALESCE(s.grade_level, '') AS grade_level,
        d.sex,

        SUM(CASE WHEN d.dewormed_sbfp = 1 THEN 1 ELSE 0 END) AS sbfp_total,
        SUM(CASE WHEN d.dewormed_other = 1 THEN 1 ELSE 0 END) AS other_total,

        SUM(
            CASE 
                WHEN d.wifa = 1 
                 AND (
                    d.wifa_date IS NULL 
                    OR d.wifa_date = ''
                    OR MONTH(STR_TO_DATE(d.wifa_date, '%Y-%m-%d')) BETWEEN 7 AND 9
                 )
                THEN 1 ELSE 0 
            END
        ) AS wifa_jul_sep,

        SUM(
            CASE 
                WHEN d.wifa = 1 
                 AND MONTH(STR_TO_DATE(d.wifa_date, '%Y-%m-%d')) BETWEEN 1 AND 3
                THEN 1 ELSE 0 
            END
        ) AS wifa_jan_mar

    FROM deworming_wifa_records d
    LEFT JOIN sf8_student_records s
        ON d.student_record_id = s.record_id

    GROUP BY s.grade_level, d.sex
    ORDER BY s.grade_level, d.sex
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