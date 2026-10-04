<?php
// One nurse-only snapshot for an individual learner's profile and print view.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);
require_once __DIR__ . '/who_classifier.php';

function profileRows(mysqli $conn, string $sql, string $types, array $values): array {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

try {
    $id = filter_input(INPUT_GET, 'record_id', FILTER_VALIDATE_INT);
    if (!$id || $id <= 0) throw new InvalidArgumentException('Select a valid learner record.');
    require __DIR__ . '/db.php';
    $conn->set_charset('utf8mb4');

    $student = profileRows($conn, 'SELECT * FROM sf8_student_records WHERE record_id=? LIMIT 1', 'i', [$id])[0] ?? null;
    if (!$student) throw new OutOfBoundsException('Learner record was not found.');
    $student['height_for_age'] = is_numeric($student['age']) && is_numeric($student['height_m'])
        ? whoHeightForAge($student['height_m'], (float)$student['age'] * 12, $student['sex']) : '';
    $lrn = (string)($student['lrn'] ?? '');
    $year = (string)($student['school_year'] ?? '');

    $assessment = profileRows($conn, 'SELECT * FROM student_health_inputs WHERE record_id=? ORDER BY input_id DESC LIMIT 1', 'i', [$id])[0] ?? null;
    $predictions = profileRows($conn, 'SELECT p.prediction_id, p.predicted_deficiency, p.predicted_risk_level,
        p.confidence_score, p.algorithm_used, p.prediction_date,
        r.recommendation_text, r.recommended_foods, r.intervention_type
        FROM prediction_results p LEFT JOIN recommendations r ON r.prediction_id=p.prediction_id
        WHERE p.record_id=? ORDER BY p.prediction_id DESC', 'i', [$id]);
    $consultations = profileRows($conn, 'SELECT c.consultation_id, c.symptoms, c.care_given,
        c.follow_up_date, c.notes, c.recorded_at, a.full_name AS recorded_by
        FROM consultations c LEFT JOIN local_accounts a ON a.account_id=c.recorded_by_account_id
        WHERE c.record_id=? ORDER BY c.recorded_at DESC, c.consultation_id DESC', 'i', [$id]);

    // Historical SF8 rows have no student_record_id. Match those only by the
    // same LRN AND school year so another learner or year cannot leak in.
    $match = "student_record_id=? OR (student_record_id IS NULL AND lrn=? AND lrn<>'' AND school_year=? AND school_year<>'')";
    $identity = [$id, $lrn, $year];
    $arh = profileRows($conn, "SELECT arh_record_id, pregnancy_status, delivery_mode, peer_educator, remarks, date_saved
        FROM arh_records WHERE $match ORDER BY arh_record_id DESC", 'iss', $identity);
    $immunizations = profileRows($conn, "SELECT immunization_id, vaccine, dose, immunized, remarks
        FROM immunization_records WHERE $match ORDER BY immunization_id DESC", 'iss', $identity);
    $screenings = profileRows($conn, "SELECT okd_lhas_id, screening_type, masterlisted, screened, findings,
        referred_school, referred_lgu, referred_private, referred_others, remarks
        FROM okd_lhas_records WHERE $match ORDER BY okd_lhas_id DESC", 'iss', $identity);
    $tobacco = profileRows($conn, "SELECT tobacco_id, violation_type, referred_to_care, remarks
        FROM tobacco_control_records WHERE $match ORDER BY tobacco_id DESC", 'iss', $identity);
    $feeding = profileRows($conn, 'SELECT m.measurement_id, m.measured_on, m.weight_kg, m.bmi,
        m.progress_status, m.notes, a.full_name AS recorded_by
        FROM feeding_measurements m LEFT JOIN local_accounts a ON a.account_id=m.recorded_by_account_id
        WHERE m.student_record_id=? ORDER BY m.measured_on DESC, m.measurement_id DESC', 'i', [$id]);

    echo json_encode(['success' => true, 'student' => $student,
        'health_assessment' => $assessment, 'predictions' => $predictions, 'consultations' => $consultations,
        'arh_records' => $arh, 'immunization_records' => $immunizations,
        'screening_records' => $screenings, 'tobacco_records' => $tobacco,
        'feeding_measurements' => $feeding]);
} catch (Throwable $e) {
    http_response_code($e instanceof InvalidArgumentException ? 422 : ($e instanceof OutOfBoundsException ? 404 : 503));
    if (!($e instanceof InvalidArgumentException || $e instanceof OutOfBoundsException)) {
        error_log('ClinicDesk complete student profile: ' . $e->getMessage());
    }
    echo json_encode(['success' => false, 'message' => $e instanceof InvalidArgumentException || $e instanceof OutOfBoundsException
        ? $e->getMessage() : 'Could not load the complete learner profile. Please try again.']);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
