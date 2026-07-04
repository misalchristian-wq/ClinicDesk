<?php
// api/who_classifier.php
//
// Computes BMI, BMI-for-age category, and Height-for-Age category using the
// DepEd/WHO reference thresholds extracted from the official SF8 helper table
// (who_reference.json). Ages are matched by age-in-months (5-19 yrs = 60-228 mo).
//
// Provides:
//   whoComputeBMI($weightKg, $heightM)             -> float|null
//   whoBmiCategory($bmi, $ageMonths, $sex)         -> string
//   whoHeightForAge($heightM, $ageMonths)          -> string
//   whoAgeToMonths($ageYears)                       -> int
//
// Categories match the values stored by the CSV path:
//   BMI:  "Severely Wasted", "Wasted", "Normal", "Overweight", "Obese"
//   HFA:  "Severely Stunted", "Stunted", "Normal", "Tall"

function whoLoadReference() {
    static $ref = null;
    if ($ref === null) {
        $path = __DIR__ . "/who_reference.json";
        $ref = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
    }
    return $ref;
}

// Clamp age-in-months into the reference range (60..228).
function whoClampMonths($m) {
    $m = (int)round($m);
    if ($m < 60) $m = 60;
    if ($m > 228) $m = 228;
    return $m;
}

function whoAgeToMonths($ageYears) {
    $y = (float)$ageYears;
    return whoClampMonths($y * 12);
}

function whoComputeBMI($weightKg, $heightM) {
    $w = (float)$weightKg;
    $h = (float)$heightM;
    if ($w <= 0 || $h <= 0) return null;
    return round($w / ($h * $h), 2);
}

// $sex: "Male"/"Female" (case-insensitive). Row format in JSON:
//   [sw_max, normal_min, normal_max, overweight_max, obese_min]
function whoBmiCategory($bmi, $ageMonths, $sex) {
    if ($bmi === null) return "";
    $ref = whoLoadReference();
    $isFemale = (strtolower(substr(trim((string)$sex), 0, 1)) === "f");
    $table = $isFemale ? ($ref["bmi_girls"] ?? []) : ($ref["bmi_boys"] ?? []);
    $m = (string)whoClampMonths($ageMonths);
    if (!isset($table[$m])) return "";

    list($swMax, $normalMin, $normalMax, $overweightMax, $obeseMin) = $table[$m];

    if ($swMax !== null && $bmi <= $swMax) return "Severely Wasted";
    if ($normalMin !== null && $bmi < $normalMin) return "Wasted";
    if ($normalMax !== null && $bmi <= $normalMax) return "Normal";
    if ($overweightMax !== null && $bmi <= $overweightMax) return "Overweight";
    return "Obese";
}

// Row format: [ss_max, normal_min, normal_max, tall_min] (heights in meters)
function whoHeightForAge($heightM, $ageMonths) {
    $ref = whoLoadReference();
    $table = $ref["hfa"] ?? [];
    $m = (string)whoClampMonths($ageMonths);
    if (!isset($table[$m])) return "";
    $h = (float)$heightM;
    if ($h <= 0) return "";

    list($ssMax, $normalMin, $normalMax, $tallMin) = $table[$m];

    if ($ssMax !== null && $h <= $ssMax) return "Severely Stunted";
    if ($normalMin !== null && $h < $normalMin) return "Stunted";
    if ($normalMax !== null && $h <= $normalMax) return "Normal";
    return "Tall";
}
?>