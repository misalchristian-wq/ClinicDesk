<?php
// Header signatures shared by the upload preview and the server-side upload check.
function sf8ReportSignatures(): array {
    return [
        'students_information' => [
            'label' => 'Students Information',
            'columns' => ['J9' => 'weight', 'K9' => 'height', 'N10' => 'bmicategory', 'O9' => 'heightforage'],
        ],
        'okd_lhas' => [
            'label' => 'OKD and LHAS',
            'columns' => ['J9' => 'screeningtype', 'K9' => 'masterlisted', 'L9' => 'screened', 'M9' => 'findings'],
        ],
        'immunization_nutritional_status' => [
            'label' => 'Immunization & Nutritional Status',
            'columns' => ['J9' => 'vaccine', 'K9' => 'dose', 'L9' => 'immunized'],
        ],
        'deworming_wifa' => [
            'label' => 'Deworming & WIFA',
            'columns' => ['J9' => 'dewormedsbfp', 'K9' => 'dewormedother', 'L9' => 'wifa', 'M9' => 'wifadate'],
        ],
        'adolescent_reproductive_health_arh' => [
            'label' => 'Adolescent Reproductive Health / ARH',
            'columns' => ['J9' => 'pregnancystatus', 'K9' => 'deliverymode', 'L9' => 'peereducator'],
        ],
        'comprehensive_tobacco_control' => [
            'label' => 'Comprehensive Tobacco Control',
            'columns' => ['J9' => 'violationtype', 'K9' => 'referredtocare'],
        ],
    ];
}

function sf8NormalizeHeader(string $value): string {
    return preg_replace('/[^a-z0-9]/', '', strtolower($value));
}

function sf8DetectReportType(callable $cellValue): ?array {
    $common = ['B9' => 'lrn', 'C9' => 'learnersname', 'G9' => 'sex', 'H9' => 'birthdate', 'I9' => 'age'];
    foreach ($common as $cell => $expected) {
        if (!str_contains(sf8NormalizeHeader((string)$cellValue($cell)), $expected)) return null;
    }
    $matches = [];
    foreach (sf8ReportSignatures() as $code => $signature) {
        foreach ($signature['columns'] as $cell => $expected) {
            if (!str_contains(sf8NormalizeHeader((string)$cellValue($cell)), $expected)) continue 2;
        }
        $matches[] = ['report_code' => $code, 'report_purpose' => $signature['label']];
    }
    return count($matches) === 1 ? $matches[0] : null;
}
