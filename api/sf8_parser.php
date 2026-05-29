<?php
require __DIR__ . "/../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

function classifyBmi($bmi) {
    if (!is_numeric($bmi)) {
        return "";
    }
    $bmi = (float) $bmi;
    if ($bmi < 16) {
        return "Severely Wasted";
    } elseif ($bmi < 18.5) {
        return "Wasted";
    } elseif ($bmi < 25) {
        return "Normal";
    } elseif ($bmi < 30) {
        return "Overweight";
    } else {
        return "Obese";
    }
}

function getCellValue($sheet, $cell) {
    return trim((string) $sheet->getCell($cell)->getCalculatedValue());
}

function getCellFormatted($sheet, $cell) {
    return trim((string) $sheet->getCell($cell)->getFormattedValue());
}

// Convert Excel serial date to Y-m-d
function excelDateToYMD($excelDate) {
    if (!is_numeric($excelDate) || $excelDate < 1) return "";
    $unix = ($excelDate - 25569) * 86400;
    return date("Y-m-d", $unix);
}

// Clean numeric values: remove spaces, replace comma with dot, return as string
function cleanNumeric($value) {
    $value = trim((string) $value);
    if ($value === "") return "";
    $cleaned = str_replace(' ', '', $value);
    $cleaned = str_replace(',', '.', $cleaned);
    if (is_numeric($cleaned)) {
        return (string) (float) $cleaned;
    }
    return "";
}

function parseSf8ExcelFile($filePath) {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName("Nutritional Status");
    if (!$sheet) {
        $sheet = $spreadsheet->getActiveSheet();
    }

    $highestRow = $sheet->getHighestRow();

    // School info (rows based on SF8 template)
    $schoolName = getCellValue($sheet, "E5");
    $district   = getCellValue($sheet, "J5");
    $division   = getCellValue($sheet, "M5");
    $region     = getCellValue($sheet, "P5");
    $schoolId   = getCellValue($sheet, "C7");
    $grade      = getCellValue($sheet, "F7");
    $section    = getCellValue($sheet, "I7");
    $trackStrand = getCellValue($sheet, "M7");
    $schoolYear = getCellValue($sheet, "P7");

    $header = [
        'school_name'   => $schoolName,
        'district'      => $district,
        'division'      => $division,
        'region'        => $region,
        'school_id'     => $schoolId,
        'grade_level'   => $grade,
        'section'       => $section,
        'track_strand'  => $trackStrand,
        'school_year'   => $schoolYear
    ];

    $students = [];

    for ($row = 10; $row <= $highestRow; $row++) {
        $lrn           = getCellValue($sheet, "B$row");
        $learnerName   = getCellValue($sheet, "C$row");
        $sex           = getCellValue($sheet, "G$row");
        $birthdateRaw  = getCellValue($sheet, "H$row");
        $birthdate     = is_numeric($birthdateRaw) ? excelDateToYMD((float)$birthdateRaw) : $birthdateRaw;
        $age           = getCellValue($sheet, "I$row");
        $weight        = cleanNumeric(getCellValue($sheet, "J$row"));
        $height        = cleanNumeric(getCellValue($sheet, "K$row"));
        $heightSquared = cleanNumeric(getCellValue($sheet, "L$row"));
        $bmiRaw        = cleanNumeric(getCellValue($sheet, "M$row"));
        $bmiCategoryRaw= getCellValue($sheet, "N$row");
        $hfa           = cleanNumeric(getCellValue($sheet, "O$row"));
        $remarks       = getCellValue($sheet, "P$row");

        if (empty($learnerName)) continue;

        // ----- BMI and BMI Category Computation -----
        $bmiComputed = null;
        $bmiCategoryComputed = null;

        // Use Excel BMI only if numeric AND > 0
        if (is_numeric($bmiRaw) && $bmiRaw !== "" && (float) $bmiRaw > 0) {
            $bmiComputed = (float) $bmiRaw;
            $bmiCategoryComputed = $bmiCategoryRaw; // use Excel's category if available
        } 
        // Otherwise compute from weight and height (both must be numeric and >0)
        elseif (is_numeric($weight) && is_numeric($height) && $weight > 0 && $height > 0) {
            $heightM = (float) $height;
            $weightKg = (float) $weight;
            $bmiComputed = round($weightKg / ($heightM * $heightM), 2);
            $bmiCategoryComputed = classifyBmi($bmiComputed);
        }

        // If Excel category is still empty but we have a valid BMI, set category
        if (($bmiCategoryComputed === null || $bmiCategoryComputed === "") && $bmiComputed !== null && $bmiComputed > 0) {
            $bmiCategoryComputed = classifyBmi($bmiComputed);
        }

        // Format output (keep as string for display)
        $bmiFinal = ($bmiComputed !== null && $bmiComputed > 0) ? (string) $bmiComputed : "";
        $bmiCategoryFinal = ($bmiCategoryComputed !== null && $bmiCategoryComputed !== "") ? $bmiCategoryComputed : "";

        $students[] = [
            'lrn'            => $lrn,
            'learner_name'   => $learnerName,
            'sex'            => $sex,
            'birthdate'      => $birthdate,
            'age'            => $age,
            'weight_kg'      => $weight,
            'height_m'       => $height,
            'height_squared' => $heightSquared,
            'bmi'            => $bmiFinal,
            'bmi_category'   => $bmiCategoryFinal,
            'height_for_age' => $hfa,
            'remarks'        => $remarks,
            'school_name'    => $schoolName,
            'district'       => $district,
            'division'       => $division,
            'region'         => $region,
            'school_id'      => $schoolId,
            'grade_level'    => $grade,
            'section'        => $section,
            'track_strand'   => $trackStrand,
            'school_year'    => $schoolYear
        ];
    }

    return [
        'header'  => $header,
        'students' => $students
    ];
}