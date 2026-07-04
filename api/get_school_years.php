<?php
// api/get_school_years.php
// Returns all school years (newest label first) and the active one.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "../db.php";

$years = [];
$active = null;

$result = $conn->query("SELECT id, year_label, is_active FROM school_years ORDER BY year_label DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row["is_active"] = (int)$row["is_active"];
        if ($row["is_active"] === 1) {
            $active = $row["year_label"];
        }
        $years[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "years"   => $years,
    "active"  => $active
]);
