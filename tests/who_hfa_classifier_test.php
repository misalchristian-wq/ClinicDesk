<?php
require_once __DIR__ . '/../api/who_classifier.php';

$cases = [
    [0.964, 61, 'Male', 'Severely Stunted'],
    [0.965, 61, 'Male', 'Stunted'],
    [1.010, 61, 'Male', 'Stunted'],
    [1.011, 61, 'Male', 'Normal'],
    [1.194, 61, 'Male', 'Normal'],
    [1.195, 61, 'Male', 'Tall'],
    [1.005, 61, 'Female', 'Normal'],
    [1.005, 61, 'Male', 'Stunted'],
    [1.501, 228, 'Female', 'Normal'],
    [1.500, 228, 'Female', 'Stunted'],
    [1.762, 228, 'Female', 'Normal'],
    [1.763, 228, 'Female', 'Tall'],
    [0.951, 60, 'Female', 'Severely Stunted'],
    [0.952, 60, 'Female', 'Stunted'],
    [0.999, 60, 'Female', 'Normal'],
    [1.190, 60, 'Female', 'Tall'],
    [1.007, 60, 'Male', 'Normal'],
    [1.007, 60, 'Female', 'Normal'],
    [0.800, 24, 'Male', 'Stunted'],
    [1.50, 23, 'Female', ''],
    [1.50, 229, 'Female', ''],
    [1.50, 120, 'Unknown', ''],
];

foreach ($cases as [$height, $months, $sex, $expected]) {
    $actual = whoHeightForAge($height, $months, $sex);
    if ($actual !== $expected) {
        fwrite(STDERR, "HFA mismatch for {$height}m, {$months}mo, {$sex}: expected '{$expected}', got '{$actual}'\n");
        exit(1);
    }
}
echo 'WHO height-for-age boundaries and sex-specific lookup passed' . PHP_EOL;
