<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$result = $conn->query("SELECT record_id, learner_name, grade_level, section, bmi_category FROM sf8_student_records ORDER BY learner_name ASC");
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
echo json_encode(["success" => true, "students" => $students]);
?>