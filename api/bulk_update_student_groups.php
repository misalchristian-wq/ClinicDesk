<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to update learner groups.']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    $rawIds = $data['record_ids'] ?? null;
    $changes = $data['changes'] ?? null;
    if (!is_array($rawIds) || !$rawIds || count($rawIds) > 100 || !is_array($changes)) {
        throw new InvalidArgumentException('Select 1 to 100 learners and at least one group change.');
    }
    $ids = [];
    foreach ($rawIds as $rawId) {
        if (filter_var($rawId, FILTER_VALIDATE_INT) === false || (int)$rawId <= 0) {
            throw new InvalidArgumentException('The learner selection contains an invalid record.');
        }
        $ids[(int)$rawId] = (int)$rawId;
    }
    $ids = array_values($ids);
    $allowed = ['is_muslim', 'is_pwd', 'is_ip'];
    $updates = [];
    foreach ($changes as $field => $value) {
        if (!in_array($field, $allowed, true) || !is_bool($value)) {
            throw new InvalidArgumentException('Choose Yes or No for valid learner groups only.');
        }
        $updates[$field] = (int)$value;
    }
    if (!$updates) throw new InvalidArgumentException('Choose at least one group to update.');

    require __DIR__ . '/db.php';
    $conn->begin_transaction();
    $find = $conn->prepare('SELECT record_id FROM sf8_student_records WHERE record_id=? FOR UPDATE');
    $set = implode(', ', array_map(static fn($field) => "$field=?", array_keys($updates)));
    $save = $conn->prepare("UPDATE sf8_student_records SET $set WHERE record_id=?");
    foreach ($ids as $id) {
        $find->bind_param('i', $id);
        $find->execute();
        if (!$find->get_result()->fetch_assoc()) {
            throw new OutOfBoundsException('One of the selected learners no longer exists. Refresh the list and try again.');
        }
        $params = array_values($updates);
        $params[] = $id;
        $save->bind_param(str_repeat('i', count($params)), ...$params);
        $save->execute();
    }
    $find->close();
    $save->close();
    $conn->commit();
    echo json_encode(['success' => true, 'updated' => count($ids), 'message' => count($ids) . ' learner record(s) updated.']);
} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof mysqli) $conn->rollback();
    $inputError = $e instanceof InvalidArgumentException || $e instanceof OutOfBoundsException;
    http_response_code($inputError ? 422 : 500);
    if (!$inputError) error_log('ClinicDesk bulk student groups: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $inputError ? $e->getMessage() : 'Could not update learner groups. Please try again.']);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
