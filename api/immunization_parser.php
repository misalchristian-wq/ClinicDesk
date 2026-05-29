<?php
require __DIR__ . "/../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function immGetCellValue($sheet, $cell) {
    return trim((string) $sheet->getCell($cell)->getCalculatedValue());
}

function immDateValue($sheet, $cell) {
    $value = $sheet->getCell($cell)->getValue();
    if ($value === "" || $value === null) return "";
    if (is_numeric($value)) {
        return Date::excelToDateTimeObject($value)->format("Y-m-d");
    }
    return trim((string) $sheet->getCell($cell)->getFormattedValue());
}

function immNormalizeYesNo($value) {
    $value = trim((string) $value);
    if ($value === "1") return 1;
    if ($value === "0") return 0;
    $upper = strtoupper($value);
    if ($upper === "YES" || $upper === "Y") return 1;
    if ($upper === "NO" || $upper === "N") return 0;
    return "";
}

function parseImmunizationExcelFile($filePath) {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName("Nutritional Status");
    if (!$sheet) {
        $sheet = $spreadsheet->getActiveSheet();
    }

    $reportCode = immGetCellValue($sheet, "A1");
    $highestRow = $sheet->getHighestRow();

    $header = [
        "school_name" => immGetCellValue($sheet, "E5"),
        "district" => immGetCellValue($sheet, "J5"),
        "division" => immGetCellValue($sheet, "M5"),
        "region" => immGetCellValue($sheet, "O5"),
        "school_id" => immGetCellValue($sheet, "A7"),
        "grade_level" => immGetCellValue($sheet, "F7"),
        "section" => immGetCellValue($sheet, "I7"),
        "track_strand" => immGetCellValue($sheet, "M7"),
        "school_year" => immGetCellValue($sheet, "O7")
    ];

    $records = [];

    for ($row = 11; $row <= $highestRow; $row++) {
        // LRN is in column B
        $lrn = immGetCellValue($sheet, "B$row");
        $learnerName = immGetCellValue($sheet, "C$row");

        if ($learnerName === "") {
            continue;
        }

        $records[] = [
            "row_no" => count($records) + 1,
            "lrn" => $lrn,   // ADD LRN
            "learner_name" => $learnerName,
            "gender" => immGetCellValue($sheet, "G$row"),
            "birthdate" => immDateValue($sheet, "H$row"),
            "age" => immGetCellValue($sheet, "I$row"),
            "vaccine" => immGetCellValue($sheet, "J$row"),
            "dose" => immGetCellValue($sheet, "K$row"),
            "immunized" => immNormalizeYesNo(immGetCellValue($sheet, "L$row")),
            "remarks" => immGetCellValue($sheet, "M$row")
        ];
    }

    return [
        "report_code" => $reportCode,
        "header" => $header,
        "records" => $records
    ];
}
?>