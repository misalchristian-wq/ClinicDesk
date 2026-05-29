<?php
require __DIR__ . "/../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function dwGetCellValue($sheet, $cell) {
    return trim((string) $sheet->getCell($cell)->getCalculatedValue());
}

function dwDateValue($sheet, $cell) {
    $value = $sheet->getCell($cell)->getValue();
    if ($value === "" || $value === null) {
        return "";
    }
    if (is_numeric($value)) {
        return Date::excelToDateTimeObject($value)->format("Y-m-d");
    }
    return trim((string) $sheet->getCell($cell)->getFormattedValue());
}

function normalizeYesNo($value) {
    $value = trim((string) $value);
    if ($value === "1") return 1;
    if ($value === "0") return 0;
    $upper = strtoupper($value);
    if ($upper === "YES" || $upper === "Y") return 1;
    if ($upper === "NO" || $upper === "N") return 0;
    return "";
}

function parseDewormingWifaExcelFile($filePath) {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName("Nutritional Status");
    if (!$sheet) {
        $sheet = $spreadsheet->getActiveSheet();
    }

    $reportCode = dwGetCellValue($sheet, "A1");
    $highestRow = $sheet->getHighestRow();

    $header = [
        "school_name" => dwGetCellValue($sheet, "E5"),
        "district" => dwGetCellValue($sheet, "J5"),
        "division" => dwGetCellValue($sheet, "M5"),
        "region" => dwGetCellValue($sheet, "O5"),
        "grade_level" => dwGetCellValue($sheet, "F7"),
        "section" => dwGetCellValue($sheet, "I7"),
        "school_year" => dwGetCellValue($sheet, "O7")
    ];

    $records = [];

    for ($row = 11; $row <= $highestRow; $row++) {
        // LRN is in column B
        $lrn = dwGetCellValue($sheet, "B$row");
        $learnerName = dwGetCellValue($sheet, "C$row");

        if ($learnerName === "") {
            continue;
        }

        $records[] = [
            "row_no" => count($records) + 1,
            "lrn" => $lrn,   // <-- ADD LRN
            "learner_name" => $learnerName,
            "gender" => dwGetCellValue($sheet, "G$row"),
            "birthdate" => dwDateValue($sheet, "H$row"),
            "age" => dwGetCellValue($sheet, "I$row"),
            "dewormed_sbfp" => normalizeYesNo(dwGetCellValue($sheet, "J$row")),
            "dewormed_other" => normalizeYesNo(dwGetCellValue($sheet, "K$row")),
            "wifa" => normalizeYesNo(dwGetCellValue($sheet, "L$row")),
            "wifa_date" => dwDateValue($sheet, "M$row"),
            "remarks" => dwGetCellValue($sheet, "N$row")
        ];
    }

    return [
        "report_code" => $reportCode,
        "header" => $header,
        "records" => $records
    ];
}
?>