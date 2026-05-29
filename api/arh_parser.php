<?php
require __DIR__ . "/../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function arhGetCellValue($sheet, $cell) {
    return trim((string) $sheet->getCell($cell)->getCalculatedValue());
}

function arhDateValue($sheet, $cell) {
    $value = $sheet->getCell($cell)->getValue();
    if ($value === "" || $value === null) return "";
    if (is_numeric($value)) {
        return Date::excelToDateTimeObject($value)->format("Y-m-d");
    }
    return trim((string) $sheet->getCell($cell)->getFormattedValue());
}

function arhNormalizeYesNo($value) {
    $value = trim((string) $value);
    if ($value === "1") return 1;
    if ($value === "0") return 0;
    return 0;
}

function parseArhExcelFile($filePath) {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName("Nutritional Status");
    if (!$sheet) {
        $sheet = $spreadsheet->getActiveSheet();
    }

    $reportCode = arhGetCellValue($sheet, "A1");
    $highestRow = $sheet->getHighestRow();

    $header = [
        "school_name" => arhGetCellValue($sheet, "E5"),
        "district" => arhGetCellValue($sheet, "J5"),
        "division" => arhGetCellValue($sheet, "M5"),
        "region" => arhGetCellValue($sheet, "O5"),
        "school_id" => arhGetCellValue($sheet, "A7"),
        "grade_level" => arhGetCellValue($sheet, "F7"),
        "section" => arhGetCellValue($sheet, "I7"),
        "track_strand" => arhGetCellValue($sheet, "M7"),
        "school_year" => arhGetCellValue($sheet, "O7")
    ];

    $records = [];

    for ($row = 11; $row <= $highestRow; $row++) {
        // LRN is in column B (index 2)
        $lrn = arhGetCellValue($sheet, "B$row");
        $learnerName = arhGetCellValue($sheet, "C$row");

        if ($learnerName === "") {
            continue;
        }

        $records[] = [
            "row_no" => count($records) + 1,
            "lrn" => $lrn,   // <-- ADD LRN HERE
            "learner_name" => $learnerName,
            "gender" => arhGetCellValue($sheet, "G$row"),
            "birthdate" => arhDateValue($sheet, "H$row"),
            "age" => arhGetCellValue($sheet, "I$row"),
            "pregnancy_status" => arhGetCellValue($sheet, "J$row"),
            "delivery_mode" => arhGetCellValue($sheet, "K$row"),
            "peer_educator" => arhNormalizeYesNo(arhGetCellValue($sheet, "L$row")),
            "remarks" => arhGetCellValue($sheet, "M$row")
        ];
    }

    return [
        "report_code" => $reportCode,
        "header" => $header,
        "records" => $records
    ];
}
?>