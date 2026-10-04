<?php
// Only values that feed a report are included. This avoids marking unrelated
// sections stale when a learner's name or another program record is edited.
function reportSourceSnapshot(mysqli $conn, string $key, string $year): array {
    $queries = [
        'box1' => [
            'Screening' => "SELECT screening_type, grade_level, SUM(masterlisted) masterlisted, SUM(screened) screened, SUM(findings) findings, SUM(referred_school) referred_school, SUM(referred_lgu) referred_lgu, SUM(referred_private) referred_private, SUM(referred_others) referred_others FROM okd_lhas_records WHERE school_year=? GROUP BY screening_type, grade_level ORDER BY screening_type, grade_level"
        ],
        'table1_a' => [
            'Immunization' => "SELECT vaccine, sex, COUNT(DISTINCT COALESCE(NULLIF(lrn,''), CONCAT('record:',immunization_id))) total FROM immunization_records WHERE school_year=? AND immunized=1 GROUP BY vaccine, sex ORDER BY vaccine, sex",
            'Nutrition' => "SELECT grade_level, sex, bmi_category, COUNT(DISTINCT COALESCE(NULLIF(lrn,''), CONCAT('record:',record_id))) total FROM sf8_student_records WHERE school_year=? AND bmi_category IS NOT NULL AND bmi_category<>'' GROUP BY grade_level, sex, bmi_category ORDER BY grade_level, sex, bmi_category"
        ],
        'table1_b' => [
            'Deworming and WIFA uploads' => "SELECT grade_level, sex, dewormed_sbfp, dewormed_other, wifa, wifa_date, COUNT(*) total FROM deworming_wifa_records WHERE school_year=? AND upload_id IS NOT NULL GROUP BY grade_level, sex, dewormed_sbfp, dewormed_other, wifa, wifa_date ORDER BY grade_level, sex, dewormed_sbfp, dewormed_other, wifa, wifa_date",
            'WIFA doses' => "SELECT s.grade_level, s.sex, e.event_date, COUNT(*) total FROM wifa_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id WHERE s.school_year=? AND e.outcome='Given' GROUP BY s.grade_level,s.sex,e.event_date ORDER BY s.grade_level,s.sex,e.event_date",
            'Deworming doses' => "SELECT s.grade_level, s.sex, e.channel, COUNT(*) total FROM deworming_events e JOIN sf8_student_records s ON s.record_id=e.student_record_id WHERE s.school_year=? AND e.outcome='Given' GROUP BY s.grade_level,s.sex,e.channel ORDER BY s.grade_level,s.sex,e.channel"
        ],
        'box4' => [
            'Counseling learners' => "SELECT IF(CAST(REGEXP_REPLACE(s.grade_level,'[^0-9]','') AS UNSIGNED) BETWEEN 7 AND 10,'JHS','SHS') level_group, s.sex, s.is_muslim, s.is_ip, s.is_pwd, COUNT(DISTINCT s.record_id) total FROM guidance_counseling_visits v JOIN sf8_student_records s ON s.record_id=v.student_record_id WHERE s.school_year=? AND v.visit_date BETWEEN STR_TO_DATE(CONCAT(LEFT(?,4),'-06-01'),'%Y-%m-%d') AND STR_TO_DATE(CONCAT(RIGHT(?,4),'-05-31'),'%Y-%m-%d') AND CAST(REGEXP_REPLACE(s.grade_level,'[^0-9]','') AS UNSIGNED) BETWEEN 7 AND 12 GROUP BY level_group,s.sex,s.is_muslim,s.is_ip,s.is_pwd ORDER BY level_group,s.sex,s.is_muslim,s.is_ip,s.is_pwd"
        ],
        'box5_6' => [
            'ARH' => "SELECT grade_level, delivery_mode, pregnancy_status, peer_educator, COUNT(*) total FROM arh_records WHERE school_year=? AND upload_id IS NOT NULL GROUP BY grade_level,delivery_mode,pregnancy_status,peer_educator ORDER BY grade_level,delivery_mode,pregnancy_status,peer_educator",
            'Tobacco' => "SELECT grade_level,referred_to_care,COUNT(*) total FROM tobacco_control_records WHERE school_year=? AND upload_id IS NOT NULL GROUP BY grade_level,referred_to_care ORDER BY grade_level,referred_to_care"
        ]
    ];
    $snapshot = [];
    foreach ($queries[$key] ?? [] as $label => $sql) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new RuntimeException('Could not prepare report source query.');
        $parameters = array_fill(0, substr_count($sql, '?'), $year);
        $stmt->bind_param(str_repeat('s', count($parameters)), ...$parameters);
        $stmt->execute();
        $snapshot[$label] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    return $snapshot;
}

function reportSourceChanges(?array $saved, array $current): array {
    if ($saved === null) return [['source' => 'Student records', 'before' => 'Not checked', 'after' => 'Review current records']];
    $changes = [];
    $measureNames = ['total','masterlisted','screened','findings','referred_school','referred_lgu','referred_private','referred_others'];
    foreach ($current as $label => $rows) {
        $old = $saved[$label] ?? [];
        if (json_encode($old) === json_encode($rows)) continue;
        $flatten = static function (array $items) use ($measureNames): array {
            $result = [];
            foreach ($items as $row) {
                $groups = array_diff_key($row, array_flip($measureNames));
                $groupName = implode(' · ', array_map(static fn($value) => (string)$value, $groups));
                foreach ($measureNames as $field) {
                    if (array_key_exists($field, $row)) $result[trim($groupName . ' · ' . str_replace('_', ' ', $field), ' ·')] = (int)$row[$field];
                }
            }
            return $result;
        };
        $before = $flatten($old); $after = $flatten($rows);
        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $metric) {
            $oldValue = $before[$metric] ?? 0; $newValue = $after[$metric] ?? 0;
            if ($oldValue === $newValue) continue;
            $changes[] = ['source' => $label . ' · ' . $metric, 'before' => $oldValue, 'after' => $newValue];
            if (count($changes) >= 12) break 2;
        }
    }
    return $changes;
}
