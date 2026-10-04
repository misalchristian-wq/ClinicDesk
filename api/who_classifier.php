<?php
// api/who_classifier.php
//
// Computes BMI, BMI-for-age category, and Height-for-Age category using the
// WHO age- and sex-specific references. BMI uses who_reference.json; height-for-age
// uses WHO child standards at 24-60 months and the 5-19-year reference at
// 61-228 months, separately for boys and girls, in who_hfa_reference.json.
//
// Provides:
//   whoComputeBMI($weightKg, $heightM)             -> float|null
//   whoBmiCategory($bmi, $ageMonths, $sex)         -> string
//   whoHeightForAge($heightM, $ageMonths, $sex)    -> string
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

// Height cutoffs are the WHO -3, -2 and +2 SD values in centimetres, by completed
// month and sex. Missing or out-of-range inputs remain unclassified; never clamp
// an older/younger learner to the nearest reference month.
function whoHeightForAge($heightM, $ageMonths, $sex) {
    static $reference = null;
    if ($reference === null) {
        $path = __DIR__ . '/who_hfa_reference.json';
        $reference = is_file($path) ? json_decode(file_get_contents($path), true) : [];
    }
    $sex = strtolower(trim((string)$sex));
    if ($sex === 'male' || $sex === 'm') $sexKey = 'boys';
    elseif ($sex === 'female' || $sex === 'f') $sexKey = 'girls';
    else return '';
    if (!is_numeric($ageMonths) || !is_numeric($heightM)) return '';
    $month = (int)floor((float)$ageMonths + 1e-8);
    if ($month < 24 || $month > 228) return '';
    $table = $reference[$month <= 60 ? 'child_' . $sexKey : $sexKey] ?? [];
    if (!isset($table[$month])) return '';
    $heightCm = round((float)$heightM * 100, 1);
    if ($heightCm <= 0) return '';
    [$minus3, $minus2, $plus2] = $table[$month];
    if ($heightCm < $minus3) return 'Severely Stunted';
    if ($heightCm < $minus2) return 'Stunted';
    if ($heightCm <= $plus2) return 'Normal';
    return 'Tall';
}
?>
