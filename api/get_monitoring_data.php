<?php
// api/get_monitoring_data.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

ini_set("display_errors", 0);
error_reporting(E_ALL);

try {
    include __DIR__ . "/../db.php";

    $school_year = trim($_GET["school_year"] ?? "");

    $syWhere  = $school_year ? "AND s.school_year = ?" : "";
    $syParams = $school_year ? [$school_year] : [];

    // Pull students + latest health inputs + latest prediction in one query
    $sql = "
        SELECT
            s.record_id,
            s.lrn,
            s.learner_name,
            s.grade_level,
            s.section,
            s.sex,
            s.age,
            s.weight_kg,
            s.height_m,
            s.bmi,
            s.bmi_category,
            s.height_for_age,
            s.school_year,
            s.date_saved,

            -- health assessment
            h.diet_type,
            h.exercise_level,
            h.has_fatigue,
            h.has_pale_skin,
            h.has_low_appetite,
            h.needs_followup,
            h.needs_referral,
            h.clinic_notes,
            h.updated_at   AS assessment_date,

            -- latest ML prediction
            pr.predicted_deficiency,
            pr.predicted_risk_level,
            pr.confidence_score,
            pr.algorithm_used,
            pr.prediction_date,
            r.recommendation_text,
            r.recommended_foods,
            r.intervention_type,

            -- feeding program flag
            CASE
                WHEN s.bmi_category IN ('Severely Wasted','Wasted') THEN 'Yes'
                WHEN pr.predicted_risk_level = 'High'               THEN 'Yes'
                ELSE 'No'
            END AS feeding_required

        FROM sf8_student_records s
        LEFT JOIN student_health_inputs h ON s.record_id = h.record_id
        LEFT JOIN (
            SELECT record_id, predicted_deficiency, predicted_risk_level,
                   confidence_score, algorithm_used, prediction_date,
                   prediction_id
            FROM prediction_results
            WHERE prediction_id IN (
                SELECT MAX(prediction_id) FROM prediction_results GROUP BY record_id
            )
        ) pr ON s.record_id = pr.record_id
        LEFT JOIN recommendations r ON pr.prediction_id = r.prediction_id
        WHERE 1=1 $syWhere
        ORDER BY
            FIELD(s.bmi_category,'Severely Wasted','Wasted','Obese','Overweight','Normal') ASC,
            s.learner_name ASC
    ";

    if ($school_year) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $school_year);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        $result = $conn->query($sql);
    }

    $records = [];
    while ($row = $result->fetch_assoc()) {
        // derive risk level if no ML prediction yet
        if (empty($row["predicted_risk_level"])) {
            $b = strtolower($row["bmi_category"] ?? "");
            $h = strtolower($row["height_for_age"] ?? "");
            if (str_contains($b, "severely") || str_contains($b, "obese") || str_contains($h, "severely")) {
                $row["predicted_risk_level"] = "High";
            } elseif (str_contains($b, "wasted") || str_contains($b, "overweight") || str_contains($h, "stunted")) {
                $row["predicted_risk_level"] = "Moderate";
            } elseif (str_contains($b, "normal")) {
                $row["predicted_risk_level"] = "Low";
            } else {
                $row["predicted_risk_level"] = "For Review";
            }
        }
        $records[] = $row;
    }

    // Summary counts
    $summary = [
        "total"           => count($records),
        "severely_wasted" => 0, "wasted" => 0, "normal" => 0,
        "overweight"      => 0, "obese"  => 0, "for_review" => 0,
        "feeding_required"=> 0, "assessed" => 0,
        "high_risk"       => 0, "moderate_risk" => 0, "low_risk" => 0,
    ];
    foreach ($records as $r) {
        $cat = strtolower($r["bmi_category"] ?? "");
        if (str_contains($cat, "severely"))        $summary["severely_wasted"]++;
        elseif (str_contains($cat, "wasted"))      $summary["wasted"]++;
        elseif (str_contains($cat, "normal"))      $summary["normal"]++;
        elseif (str_contains($cat, "overweight"))  $summary["overweight"]++;
        elseif (str_contains($cat, "obese"))       $summary["obese"]++;
        else                                       $summary["for_review"]++;

        if ($r["feeding_required"] === "Yes")      $summary["feeding_required"]++;
        if (!empty($r["assessment_date"]))         $summary["assessed"]++;

        $risk = strtolower($r["predicted_risk_level"] ?? "");
        if ($risk === "high")         $summary["high_risk"]++;
        elseif ($risk === "moderate") $summary["moderate_risk"]++;
        elseif ($risk === "low")      $summary["low_risk"]++;
    }

    echo json_encode([
        "success"  => true,
        "records"  => $records,
        "summary"  => $summary,
    ]);

    $conn->close();

} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage(),
    ]);
}
?>