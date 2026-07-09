<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    include __DIR__ . '/../db.php';
    include __DIR__ . '/who_classifier.php'; // for BMI calc if needed

    $input = json_decode(file_get_contents('php://input'), true);
    $header = $input['header'] ?? [];
    $records = $input['records'] ?? [];
    $reportCode = $input['report_code'] ?? 'students_information';
    $uploadedBy = $input['uploaded_by'] ?? 'Clinic Nurse';

    if (empty($records)) {
        throw new Exception('No records to save.');
    }

    // Create a placeholder upload record (for logging purposes)
    $stmt = $conn->prepare("INSERT INTO sf8_uploads (file_name, file_type, report_code, uploaded_by_email, status, cloudinary_url) VALUES (?, ?, ?, ?, ?, ?)");
    $fileName = 'offline_upload_' . date('Ymd_His') . '.xlsx';
    $fileType = 'local';
    $status = 'Approved';
    $cloudUrl = ''; // no cloudinary
    $stmt->bind_param("ssssss", $fileName, $fileType, $reportCode, $uploadedBy, $status, $cloudUrl);
    $stmt->execute();
    $uploadId = $stmt->insert_id;
    $stmt->close();

    $saved = 0;
    $skipped = 0;

    // Helper to link student_record_id by LRN
    function findStudentRecordId($conn, $lrn, $schoolYear) {
        $stmt = $conn->prepare("SELECT record_id FROM sf8_student_records WHERE lrn = ? AND school_year = ?");
        $stmt->bind_param("ss", $lrn, $schoolYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['record_id'] : null;
    }

    // Determine school year and grade from header or fallback
    $schoolYear = $header['school_year'] ?? date('Y') . '-' . (date('Y') + 1);
    $gradeLevel = $header['grade_level'] ?? '';

    // Start transaction
    $conn->begin_transaction();

    if ($reportCode === 'students_information') {
        // Insert into sf8_student_records
        $stmt = $conn->prepare("
            INSERT INTO sf8_student_records (
                upload_id, lrn, school_name, district, division, region, school_id,
                grade_level, section, track_strand, school_year, learner_name,
                birthdate, age, sex, weight_kg, height_m, height_squared, bmi, bmi_category, height_for_age, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($records as $rec) {
            $lrn = trim($rec['lrn'] ?? '');
            if (empty($lrn)) { $skipped++; continue; }
            // Check duplicate
            $dup = $conn->prepare("SELECT record_id FROM sf8_student_records WHERE lrn = ? AND school_year = ?");
            $dup->bind_param("ss", $lrn, $schoolYear);
            $dup->execute();
            if ($dup->get_result()->num_rows > 0) { $skipped++; $dup->close(); continue; }
            $dup->close();

            // Compute BMI etc. if not provided
            $weight = $rec['weight_kg'] ?? null;
            $height = $rec['height_m'] ?? null;
            $bmi = $rec['bmi'] ?? null;
            $bmiCat = $rec['bmi_category'] ?? '';
            $hfa = $rec['height_for_age'] ?? '';
            if ($weight && $height && !$bmi) {
                $bmi = round($weight / ($height * $height), 2);
            }
            if ($bmi && !$bmiCat) {
                // Simple adult classification (or use WHO if needed)
                if ($bmi < 16) $bmiCat = 'Severely Wasted';
                elseif ($bmi < 18.5) $bmiCat = 'Wasted';
                elseif ($bmi < 25) $bmiCat = 'Normal';
                elseif ($bmi < 30) $bmiCat = 'Overweight';
                else $bmiCat = 'Obese';
            }
            $heightSquared = $height ? round($height * $height, 4) : null;
            $stmt->bind_param(
                "issssssssssssssddddsss",
                $uploadId, $lrn,
                $header['school_name'] ?? '', $header['district'] ?? '', $header['division'] ?? '', $header['region'] ?? '',
                $header['school_id'] ?? '',
                $rec['grade_level'] ?? $gradeLevel,
                $rec['section'] ?? '',
                $rec['track_strand'] ?? '',
                $schoolYear,
                $rec['learner_name'] ?? '',
                $rec['birthdate'] ?? '',
                $rec['age'] ?? '',
                $rec['sex'] ?? '',
                $weight ?? 0,
                $height ?? 0,
                $heightSquared ?? 0,
                $bmi ?? 0,
                $bmiCat,
                $hfa,
                $rec['remarks'] ?? ''
            );
            if ($stmt->execute()) $saved++; else $skipped++;
        }
        $stmt->close();
    }
    // TODO: Add similar blocks for other report codes (okd_lhas, deworming_wifa, etc.)
    // You can copy the logic from approve_sf8_upload.php for each type.

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Saved $saved records. Skipped $skipped records.",
        'saved' => $saved,
        'skipped' => $skipped
    ]);

} catch (Throwable $e) {
    if (isset($conn) && $conn) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Approval failed: ' . $e->getMessage()
    ]);
}