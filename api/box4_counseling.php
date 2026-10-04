<?php
header('Content-Type: application/json');
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse', 'School Admin']);
include __DIR__ . '/../db.php';
$method = $_SERVER['REQUEST_METHOD'];
$input = $method === 'GET' ? $_GET : (json_decode(file_get_contents('php://input'), true) ?: []);
$year = trim((string)($input['school_year'] ?? ''));
if (!preg_match('/^(\d{4})-(\d{4})$/', $year, $parts) || (int)$parts[2] !== (int)$parts[1]+1) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Select a valid school year.']);
    exit;
}
try {
    if ($method === 'POST') {
        $studentId = filter_var($input['student_record_id'] ?? null, FILTER_VALIDATE_INT);
        $date = trim((string)($input['visit_date'] ?? ''));
        $dateValue = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $start = $parts[1] . '-06-01'; $end = $parts[2] . '-05-31';
        if (!$studentId || !$dateValue || $dateValue->format('Y-m-d') !== $date || $date < $start || $date > $end || $date > date('Y-m-d')) {
            http_response_code(422);
            echo json_encode(['success'=>false,'message'=>'Choose a student and a visit date within the school year.']);
            exit;
        }
        $check = $conn->prepare('SELECT record_id FROM sf8_student_records WHERE record_id=? AND school_year=?');
        $check->bind_param('is', $studentId, $year); $check->execute();
        $exists = $check->get_result()->num_rows > 0; $check->close();
        if (!$exists) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Student is not in this school year.']); exit; }
        $actor = (string)(getCurrentUser()['full_name'] ?? 'Clinic Nurse');
        $stmt = $conn->prepare('INSERT INTO guidance_counseling_visits(student_record_id,visit_date,recorded_by) VALUES(?,?,?)');
        $stmt->bind_param('iss', $studentId, $date, $actor); $stmt->execute(); $stmt->close();
        echo json_encode(['success'=>true,'message'=>'Counseling visit recorded.']);
    } elseif ($method === 'DELETE') {
        $visitId = filter_var($input['visit_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$visitId) { http_response_code(422); echo json_encode(['success'=>false,'message'=>'Select a visit.']); exit; }
        $stmt = $conn->prepare('DELETE v FROM guidance_counseling_visits v JOIN sf8_student_records s ON s.record_id=v.student_record_id WHERE v.visit_id=? AND s.school_year=?');
        $stmt->bind_param('is', $visitId, $year); $stmt->execute();
        $deleted = $stmt->affected_rows; $stmt->close();
        echo json_encode(['success'=>$deleted>0,'message'=>$deleted>0?'Visit removed.':'Visit not found.']);
    } else {
        $q = trim((string)($input['q'] ?? ''));
        $like = '%' . $q . '%';
        $students = [];
        $stmt = $conn->prepare("SELECT record_id,lrn,learner_name,grade_level,sex FROM sf8_student_records WHERE school_year=? AND (learner_name LIKE ? OR lrn LIKE ?) ORDER BY learner_name LIMIT 40");
        $stmt->bind_param('sss',$year,$like,$like); $stmt->execute();
        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        $stmt = $conn->prepare('SELECT v.visit_id,v.visit_date,s.record_id,s.learner_name,s.grade_level FROM guidance_counseling_visits v JOIN sf8_student_records s ON s.record_id=v.student_record_id WHERE s.school_year=? ORDER BY v.visit_date DESC,v.visit_id DESC LIMIT 100');
        $stmt->bind_param('s',$year); $stmt->execute(); $visits=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        $stmt = $conn->prepare("SELECT IF(CAST(REGEXP_REPLACE(s.grade_level,'[^0-9]','') AS UNSIGNED) BETWEEN 7 AND 10,'JHS','SHS') level_group, s.sex,s.is_muslim,s.is_ip,s.is_pwd,s.record_id FROM guidance_counseling_visits v JOIN sf8_student_records s ON s.record_id=v.student_record_id WHERE s.school_year=? AND v.visit_date BETWEEN ? AND ? AND CAST(REGEXP_REPLACE(s.grade_level,'[^0-9]','') AS UNSIGNED) BETWEEN 7 AND 12 GROUP BY s.record_id");
        $start=$parts[1].'-06-01'; $end=$parts[2].'-05-31';
        $stmt->bind_param('sss',$year,$start,$end); $stmt->execute(); $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        $counts=['counselingJHS'=>['male'=>0,'female'=>0],'counselingSHS'=>['male'=>0,'female'=>0],'vulnerableJHS'=>['muslim'=>0,'ip'=>0,'lwd'=>0],'vulnerableSHS'=>['muslim'=>0,'ip'=>0,'lwd'=>0]];
        foreach ($rows as $row) {
            $level=$row['level_group']; $sex=strtolower((string)$row['sex']);
            if (isset($counts['counseling'.$level][$sex])) $counts['counseling'.$level][$sex]++;
            foreach (['is_muslim'=>'muslim','is_ip'=>'ip','is_pwd'=>'lwd'] as $field=>$target) if ((int)$row[$field]===1) $counts['vulnerable'.$level][$target]++;
        }
        echo json_encode(['success'=>true,'students'=>$students,'visits'=>$visits,'counts'=>$counts]);
    }
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) { http_response_code(409); echo json_encode(['success'=>false,'message'=>'This student already has a counseling visit on that date.']); }
    else { error_log('ClinicDesk counseling: '.$e->getMessage()); http_response_code(503); echo json_encode(['success'=>false,'message'=>'Could not update counseling visits.']); }
} catch (Throwable $e) {
    error_log('ClinicDesk counseling: '.$e->getMessage()); http_response_code(503); echo json_encode(['success'=>false,'message'=>'Could not load counseling visits.']);
}
