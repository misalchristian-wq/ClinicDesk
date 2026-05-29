<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$modelDir = __DIR__ . "/../ml_model";
// Find the latest version directory (sorted by name, which contains timestamp)
$versions = glob($modelDir . "/version_*", GLOB_ONLYDIR);
if (empty($versions)) {
    echo json_encode(["success" => false, "message" => "No model version found."]);
    exit;
}
// Get the most recent version (by name, since timestamp is YYYYMMDD_HHMMSS)
rsort($versions);
$latestVersion = $versions[0];
$metricsFile = $latestVersion . "/model_comparison.json";

if (!file_exists($metricsFile)) {
    echo json_encode(["success" => false, "message" => "Comparison metrics file not found."]);
    exit;
}

$metrics = json_decode(file_get_contents($metricsFile), true);
echo json_encode(["success" => true, "metrics" => $metrics]);
?>