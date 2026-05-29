<?php
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"clinicdesk_training_data_" . date("Ymd") . ".csv\"");

include __DIR__ . "/../db.php";

// Open output stream
$output = fopen("php://output", "w");

// Define CSV header columns
$headers = [
    'record_id', 'age', 'sex', 'grade_level', 'bmi', 'bmi_category',
    'height_for_age', 'diet_type', 'sun_exposure', 'exercise_level',
    'has_fatigue', 'has_bone_pain', 'has_bleeding_gums', 'has_pale_skin', 'has_night_blindness',
    'screening_findings', 'immunized_td', 'immunized_hpv', 'dewormed', 'wifa',
    'pregnant', 'tobacco_violation'
];
fputcsv($output, $headers);

// Main query: left join all related tables
$sql = "
    SELECT 
        s.record_id,
        s.age,
        s.sex,
        s.grade_level,
        s.bmi,
        s.bmi_category,
        s.height_for_age,
        h.diet_type,
        h.sun_exposure,
        h.exercise_level,
        h.has_fatigue,
        h.has_bone_pain,
        h.has_bleeding_gums,
        h.has_pale_skin,
        h.has_night_blindness,
        -- OKD/LHAS: any finding (1 if at least one finding exists)
        MAX(CASE WHEN o.findings = 1 THEN 1 ELSE 0 END) AS screening_findings,
        -- Immunization: TD and HPV (simplified)
        MAX(CASE WHEN i.vaccine = 'Tetanus Diphtheria' AND i.immunized = 1 THEN 1 ELSE 0 END) AS immunized_td,
        MAX(CASE WHEN i.vaccine = 'HPV' AND i.immunized = 1 THEN 1 ELSE 0 END) AS immunized_hpv,
        -- Deworming & WIFA
        MAX(d.dewormed_sbfp) AS dewormed,
        MAX(d.wifa) AS wifa,
        -- ARH
        CASE WHEN a.pregnancy_status = 'Pregnant' THEN 1 ELSE 0 END AS pregnant,
        -- Tobacco
        CASE WHEN t.violation_type IS NOT NULL THEN 1 ELSE 0 END AS tobacco_violation
    FROM sf8_student_records s
    LEFT JOIN student_health_inputs h ON s.record_id = h.record_id
    LEFT JOIN okd_lhas_records o ON s.record_id = o.student_record_id
    LEFT JOIN immunization_records i ON s.record_id = i.student_record_id
    LEFT JOIN deworming_wifa_records d ON s.record_id = d.student_record_id
    LEFT JOIN arh_records a ON s.record_id = a.student_record_id
    LEFT JOIN tobacco_control_records t ON s.record_id = t.student_record_id
    GROUP BY s.record_id
";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    // Map sex to numeric (0 = Male, 1 = Female)
    $sex_num = ($row['sex'] == 'Female') ? 1 : 0;
    
    // Map BMI category to numeric target (0 = Normal, 1 = Underweight, 2 = Overweight, 3 = Obese, 4 = Severely Wasted)
    $bmi_cat = trim($row['bmi_category'] ?? '');
    if (stripos($bmi_cat, 'Severely Wasted') !== false) $target = 4;
    elseif (stripos($bmi_cat, 'Wasted') !== false) $target = 1;
    elseif (stripos($bmi_cat, 'Underweight') !== false) $target = 1;
    elseif (stripos($bmi_cat, 'Overweight') !== false) $target = 2;
    elseif (stripos($bmi_cat, 'Obese') !== false) $target = 3;
    else $target = 0;  // Normal
    
    $output_row = [
        $row['record_id'],
        $row['age'],
        $sex_num,
        $row['grade_level'],
        $row['bmi'],
        $target,   // target column (numeric)
        $row['height_for_age'],
        $row['diet_type'],
        $row['sun_exposure'],
        $row['exercise_level'],
        $row['has_fatigue'] == 'Yes' ? 1 : 0,
        $row['has_bone_pain'] == 'Yes' ? 1 : 0,
        $row['has_bleeding_gums'] == 'Yes' ? 1 : 0,
        $row['has_pale_skin'] == 'Yes' ? 1 : 0,
        $row['has_night_blindness'] == 'Yes' ? 1 : 0,
        $row['screening_findings'] ?? 0,
        $row['immunized_td'] ?? 0,
        $row['immunized_hpv'] ?? 0,
        $row['dewormed'] ?? 0,
        $row['wifa'] ?? 0,
        $row['pregnant'] ?? 0,
        $row['tobacco_violation'] ?? 0
    ];
    fputcsv($output, $output_row);
}

fclose($output);
$conn->close();
?>