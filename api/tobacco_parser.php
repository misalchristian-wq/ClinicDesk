<?php
require __DIR__ . "/../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function tobGetCellValue($sheet, $cell) {
    return trim((string) $sheet->getCell($cell)->getCalculatedValue());
}

function tobDateValue($sheet, $cell) {
    $value = $sheet->getCell($cell)->getValue();
    if ($value === "" || $value === null) return "";
    if (is_numeric($value)) {
        return Date::excelToDateTimeObject($value)->format("Y-m-d");
    }
    return trim((string) $sheet->getCell($cell)->getFormattedValue());
}

function tobNormalizeYesNo($value) {
    $value = trim((string) $value);
    if ($value === "1") return 1;
    if ($value === "0") return 0;
    return 0;
}

function parseTobaccoExcelFile($filePath) {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName("Nutritional Status");
    if (!$sheet) {
        $sheet = $spreadsheet->getActiveSheet();
    }

    $reportCode = tobGetCellValue($sheet, "A1");
    $highestRow = $sheet->getHighestRow();

    $header = [
        "school_name" => tobGetCellValue($sheet, "E5"),
        "district" => tobGetCellValue($sheet, "J5"),
        "division" => tobGetCellValue($sheet, "M5"),
        "region" => tobGetCellValue($sheet, "O5"),
        "school_id" => tobGetCellValue($sheet, "A7"),
        "grade_level" => tobGetCellValue($sheet, "F7"),
        "section" => tobGetCellValue($sheet, "I7"),
        "track_strand" => tobGetCellValue($sheet, "M7"),
        "school_year" => tobGetCellValue($sheet, "O7")
    ];

    $records = [];

    for ($row = 11; $row <= $highestRow; $row++) {
        // LRN is in column B
        $lrn = tobGetCellValue($sheet, "B$row");
        $learnerName = tobGetCellValue($sheet, "C$row");

        if ($learnerName === "") {
            continue;
        }

        $records[] = [
            "row_no" => count($records) + 1,
            "lrn" => $lrn,   // <-- ADD LRN
            "learner_name" => $learnerName,
            "gender" => tobGetCellValue($sheet, "G$row"),
            "birthdate" => tobDateValue($sheet, "H$row"),
            "age" => tobGetCellValue($sheet, "I$row"),
            "violation_type" => tobGetCellValue($sheet, "J$row"),
            "referred_to_care" => tobNormalizeYesNo(tobGetCellValue($sheet, "K$row")),
            "remarks" => tobGetCellValue($sheet, "L$row")
        ];
    }

    return [
        "report_code" => $reportCode,
        "header" => $header,
        "records" => $records
    ];
}
?>