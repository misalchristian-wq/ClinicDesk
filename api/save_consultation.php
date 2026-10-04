<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to save a consultation.']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) throw new InvalidArgumentException('Enter valid consultation details.');
    $recordId = filter_var($data['record_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$recordId || $recordId < 1) throw new InvalidArgumentException('Select a student first.');

    $symptoms = trim((string)($data['symptoms'] ?? ''));
    $careGiven = trim((string)($data['care_given'] ?? ''));
    $notes = trim((string)($data['notes'] ?? ''));
    if ($symptoms === '') throw new InvalidArgumentException('Record the learner\'s reported symptoms or reason for visit.');
    if (mb_strlen($symptoms) > 4000 || mb_strlen($careGiven) > 4000 || mb_strlen($notes) > 4000) {
        throw new InvalidArgumentException('Consultation text is too long.');
    }
    $followUp = trim((string)($data['follow_up_date'] ?? ''));
    if ($followUp !== '') {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $followUp);
        if (!$date || $date->format('Y-m-d') !== $followUp) {
            throw new InvalidArgumentException('Enter a valid follow-up date.');
        }
    } else {
        $followUp = null;
    }

    require __DIR__ . '/db.php';
    $conn->set_charset('utf8mb4');
    $student = $conn->prepare('SELECT record_id FROM sf8_student_records WHERE record_id=? LIMIT 1');
    $student->bind_param('i', $recordId);
    $student->execute();
    $exists = (bool)$student->get_result()->fetch_assoc();
    $student->close();
    if (!$exists) throw new OutOfBoundsException('Student record was not found.');

    $nurseId = (int)(getCurrentUser()['account_id'] ?? 0);
    $stmt = $conn->prepare('INSERT INTO consultations
        (record_id, symptoms, care_given, follow_up_date, notes, recorded_by_account_id)
        VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('issssi', $recordId, $symptoms, $careGiven, $followUp, $notes, $nurseId);
    $stmt->execute();
    $consultationId = $stmt->insert_id;
    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Consultation saved.', 'consultation_id' => $consultationId]);
} catch (Throwable $e) {
    $expected = $e instanceof InvalidArgumentException || $e instanceof OutOfBoundsException;
    if (!$expected) error_log('ClinicDesk consultation save: ' . $e->getMessage());
    http_response_code($e instanceof InvalidArgumentException ? 422 : ($e instanceof OutOfBoundsException ? 404 : 503));
    echo json_encode(['success' => false, 'message' => $expected
        ? $e->getMessage() : 'Consultation could not be saved. Please try again.']);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
