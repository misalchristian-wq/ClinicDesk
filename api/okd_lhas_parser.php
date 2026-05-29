<?php
require __DIR__ . "/../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function okdGetCellValue($sheet, $cell) {
    return trim((string) $sheet->getCell($cell)->getCalculatedValue());
}

function okdDateValue($sheet, $cell) {
    $value = $sheet->getCell($cell)->getValue();
    if ($value === "" || $value === null) return "";
    if (is_numeric($value)) {
        return Date::excelToDateTimeObject($value)->format("Y-m-d");
    }
    return trim((string) $sheet->getCell($cell)->getFormattedValue());
}

function okdNormalizeYesNo($value) {
    $value = trim((string) $value);
    if ($value === "1") return 1;
    if ($value === "0") return 0;
    $upper = strtoupper($value);
    if ($upper === "YES" || $upper === "Y") return 1;
    if ($upper === "NO" || $upper === "N") return 0;
    return "";
}

function parseOkdLhasExcelFile($filePath) {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName("Nutritional Status");
    if (!$sheet) {
        $sheet = $spreadsheet->getActiveSheet();
    }

    $reportCode = okdGetCellValue($sheet, "A1");
    $highestRow = $sheet->getHighestRow();

    $header = [
        "school_name" => okdGetCellValue($sheet, "E5"),
        "district" => okdGetCellValue($sheet, "J5"),
        "division" => okdGetCellValue($sheet, "M5"),
        "region" => okdGetCellValue($sheet, "O5"),
        "school_id" => okdGetCellValue($sheet, "C7"),
        "grade_level" => okdGetCellValue($sheet, "F7"),
        "section" => okdGetCellValue($sheet, "I7"),
        "track_strand" => okdGetCellValue($sheet, "M7"),
        "school_year" => okdGetCellValue($sheet, "O7")
    ];

    $records = [];

    for ($row = 11; $row <= $highestRow; $row++) {
        // LRN is in column B
        $lrn = okdGetCellValue($sheet, "B$row");
        $learnerName = okdGetCellValue($sheet, "C$row");

        if ($learnerName === "") {
            continue;
        }

        $records[] = [
            "row_no" => count($records) + 1,
            "lrn" => $lrn,   // <-- ADD LRN
            "learner_name" => $learnerName,
            "gender" => okdGetCellValue($sheet, "G$row"),
            "birthdate" => okdDateValue($sheet, "H$row"),
            "age" => okdGetCellValue($sheet, "I$row"),
            "screening_type" => okdGetCellValue($sheet, "J$row"),
            "masterlisted" => okdNormalizeYesNo(okdGetCellValue($sheet, "K$row")),
            "screened" => okdNormalizeYesNo(okdGetCellValue($sheet, "L$row")),
            "findings" => okdNormalizeYesNo(okdGetCellValue($sheet, "M$row")),
            "referred_school" => okdNormalizeYesNo(okdGetCellValue($sheet, "N$row")),
            "referred_lgu" => okdNormalizeYesNo(okdGetCellValue($sheet, "O$row")),
            "referred_private" => okdNormalizeYesNo(okdGetCellValue($sheet, "P$row")),
            "referred_others" => okdNormalizeYesNo(okdGetCellValue($sheet, "Q$row")),
            "remarks" => okdGetCellValue($sheet, "R$row")
        ];
    }

    return [
        "report_code" => $reportCode,
        "header" => $header,
        "records" => $records
    ];
}
?>