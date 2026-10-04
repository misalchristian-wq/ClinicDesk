<?php
header("Content-Type: application/json");
header("Cache-Control: no-store");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST");

ini_set("display_errors", 0);
error_reporting(E_ALL);

require_once __DIR__ . '/auth.php';
authenticate();
requireRole(['Clinic Nurse']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST to approve an SF8 upload.']);
    exit;
}

try {
    require_once __DIR__ . '/sf8_workbook.php';
    include __DIR__ . "/../db.php";
    require_once __DIR__ . '/student_sections.php';
    require_once __DIR__ . '/sf8_conflicts.php';
    require_once __DIR__ . '/sf8_approval_validation.php';
    require_once __DIR__ . '/who_classifier.php';

    // ------------------------------------------------------------------
    // Helper: read the "School Year" and "Grade" values from row 7 of the
    // SF8 sheet (the same fixed cells every SF8 file uses). Requires the
    // PhpSpreadsheet or a lightweight xlsx read. We use SimpleXLSX-free
    // approach via ZipArchive to avoid extra deps.
    // Returns ["school_year" => "...", "grade_level" => "..."].
    // ------------------------------------------------------------------
    function readSchoolYearAndGrade($xlsxPath) {
        $result = ["school_year" => "", "grade_level" => ""];
        if (!class_exists("ZipArchive")) return $result;

        $zip = new ZipArchive();
        if ($zip->open($xlsxPath) !== true) return $result;

        // Load shared strings (cell text is often stored here).
        $shared = [];
        $ssXml = $zip->getFromName("xl/sharedStrings.xml");
        if ($ssXml !== false) {
            $sx = @simplexml_load_string($ssXml);
            if ($sx !== false) {
                foreach ($sx->si as $si) {
                    // handle both plain <t> and rich text runs
                    $text = "";
                    if (isset($si->t)) {
                        $text = (string)$si->t;
                    } else {
                        foreach ($si->r as $r) { $text .= (string)$r->t; }
                    }
                    $shared[] = $text;
                }
            }
        }

        // Find the data sheet: prefer "Nutritional Status", else first sheet.
        $sheetPath = "xl/worksheets/sheet1.xml";
        $wbXml = $zip->getFromName("xl/workbook.xml");
        $relsXml = $zip->getFromName("xl/_rels/workbook.xml.rels");
        if ($wbXml !== false && $relsXml !== false) {
            $wb = @simplexml_load_string($wbXml);
            $rels = @simplexml_load_string($relsXml);
            if ($wb !== false && $rels !== false) {
                $relMap = [];
                foreach ($rels->Relationship as $rel) {
                    $relMap[(string)$rel['Id']] = (string)$rel['Target'];
                }
                $wb->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                foreach ($wb->sheets->sheet as $sh) {
                    $name = (string)$sh['name'];
                    $rid = "";
                    foreach ($sh->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships') as $k => $v) {
                        if ($k === 'id') $rid = (string)$v;
                    }
                    if (stripos($name, "Nutritional Status") !== false && isset($relMap[$rid])) {
                        $sheetPath = "xl/" . ltrim($relMap[$rid], "/");
                        break;
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName($sheetPath);
        $zip->close();
        if ($sheetXml === false) return $result;

        $sx = @simplexml_load_string($sheetXml);
        if ($sx === false) return $result;

        // Resolve a cell's text value (handles shared strings).
        $cellVal = function($c) use ($shared) {
            $t = (string)($c['t'] ?? "");
            $v = isset($c->v) ? (string)$c->v : "";
            if ($t === "s" && $v !== "" && isset($shared[(int)$v])) {
                return $shared[(int)$v];
            }
            if (isset($c->is->t)) return (string)$c->is->t;
            return $v;
        };

        // Walk row 7, collect label->value by scanning left-to-right.
        foreach ($sx->sheetData->row as $row) {
            if ((string)$row['r'] !== "7") continue;
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string)$c['r'];
                $col = preg_replace('/\d+/', '', $ref);
                $cells[] = ["col" => $col, "val" => $cellVal($c)];
            }
            // Find "School Year" and "Grade" labels, take the next non-empty cell.
            for ($i = 0; $i < count($cells); $i++) {
                $label = strtolower(trim($cells[$i]["val"]));
                if ($label === "") continue;
                if (strpos($label, "school year") !== false) {
                    for ($j = $i + 1; $j < count($cells); $j++) {
                        if (trim($cells[$j]["val"]) !== "") { $result["school_year"] = trim($cells[$j]["val"]); break; }
                    }
                } elseif (strpos($label, "grade") !== false) {
                    for ($j = $i + 1; $j < count($cells); $j++) {
                        if (trim($cells[$j]["val"]) !== "") { $result["grade_level"] = trim($cells[$j]["val"]); break; }
                    }
                }
            }
            break;
        }
        return $result;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $upload_id = intval($data["upload_id"] ?? 0);
    $reviewed_by = getCurrentUser()["full_name"];

    if ($upload_id <= 0) {
        echo json_encode(["success" => false, "message" => "Upload ID is required."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM sf8_uploads WHERE upload_id = ?");
    $stmt->bind_param("i", $upload_id);
    $stmt->execute();
    $upload = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$upload) {
        echo json_encode(["success" => false, "message" => "Upload record not found."]);
        exit;
    }

    if ($upload["status"] === "Approved") {
        echo json_encode(["success" => false, "message" => "This upload is already approved."]);
        exit;
    }

    $cloudinary_url = $upload["cloudinary_url"] ?? "";
    $report_code = $upload["report_code"] ?? "students_information";

    if ($cloudinary_url === "") {
        echo json_encode(["success" => false, "message" => "Cloudinary URL is empty."]);
        exit;
    }

    $tempFile = sf8DownloadForParsing($upload);

    $fileMeta = sf8ReadUploadMetadata($tempFile);
    if ($fileMeta['report_code'] !== $report_code) {
        throw new Sf8Exception('The stored SF8 file type does not match its detected columns. Re-upload the correct file.');
    }
    $fileSchoolYear = $fileMeta['school_year'];
    $parsedWorkbook = sf8ParseWorkbook($tempFile, $report_code);
    $fileGradeLevel = trim((string)($parsedWorkbook['header']['grade_level'] ?? ''));
    if ($fileGradeLevel === '') $fileGradeLevel = readSchoolYearAndGrade($tempFile)['grade_level'];
    $preflight = clinicSf8ApprovalPreflight($conn, $parsedWorkbook['records'], $report_code, $fileSchoolYear);
    if ($preflight['errors']) {
        @unlink($tempFile);
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'This SF8 file cannot be approved until its learner rows are corrected.',
            'details' => implode(' ', array_slice($preflight['errors'], 0, 10)),
            'error_count' => count($preflight['errors'])]);
        exit;
    }
    if ($preflight['identity_conflicts']) {
        @unlink($tempFile);
        http_response_code(409);
        echo json_encode(['success' => false, 'identity_conflicts' => $preflight['identity_conflicts'],
            'message' => 'A learner name in this school year is associated with a different LRN. Verify the workbook before approval.']);
        exit;
    }

    $overrideExisting = !empty($data['override_existing']);
    $conflicts = clinicSf8ReviewConflicts($conn, $tempFile, $report_code, $fileSchoolYear);
    $hasExistingDuplicates = (bool)array_filter($conflicts, static fn($item) => $item['existing_count'] > 1);
    $conflictFingerprint = hash('sha256', json_encode($conflicts));
    if ($conflicts && ($hasExistingDuplicates || !$overrideExisting || !hash_equals($conflictFingerprint, (string)($data['conflict_fingerprint'] ?? '')))) {
        if (file_exists($tempFile)) unlink($tempFile);
        http_response_code(409);
        echo json_encode(['success' => false, 'requires_override' => true,
            'message' => $hasExistingDuplicates
                ? 'Multiple existing rows were found for one learner. Resolve those rows before approving this file.'
                : 'There is existing data for this school year. Review the changes before approval.',
            'has_existing_duplicates' => $hasExistingDuplicates,
            'conflicts' => $conflicts, 'conflict_fingerprint' => $conflictFingerprint]);
        exit;
    }

    $conn->begin_transaction();

    $saved = 0;
    $skipped = 0;
    $provisionalCreated = 0;
    $provisionalCompleted = 0;

    // ------------------------------------------------------------------
    // 1. DEWORMING & WIFA
    // ------------------------------------------------------------------
    if ($report_code === "deworming_wifa") {
        $parsed = parseDewormingWifaExcelFile($tempFile);
        $records = $parsed["records"] ?? [];

        $lrnInFile = [];
        $duplicatesInFile = [];
        foreach ($records as $record) {
            $lrn = trim($record["lrn"] ?? "");
            if ($lrn === "") continue;
            if (in_array($lrn, $lrnInFile)) $duplicatesInFile[] = $lrn;
            else $lrnInFile[] = $lrn;
        }
        if (!empty($duplicatesInFile)) {
            $conn->rollback();
            if (file_exists($tempFile)) unlink($tempFile);
            echo json_encode([
                "success" => false,
                "message" => "Duplicate LRNs in Deworming file.",
                "details" => implode(", ", array_unique($duplicatesInFile))
            ]);
            exit;
        }

        $deleteStmt = $conn->prepare("DELETE FROM deworming_wifa_records WHERE upload_id = ?");
        $deleteStmt->bind_param("i", $upload_id);
        $deleteStmt->execute();
        $deleteStmt->close();

        foreach ($records as $record) {
            $lrn = trim($record["lrn"] ?? "");
            $learnerName = trim($record["learner_name"] ?? "");
            $sex = trim($record["gender"] ?? "");
            $birthdate = trim($record["birthdate"] ?? "");
            $age = trim((string)($record["age"] ?? ""));
            $dewormedSbfp = intval($record["dewormed_sbfp"] ?? 0);
            $dewormedOther = intval($record["dewormed_other"] ?? 0);
            $wifa = intval($record["wifa"] ?? 0);
            $wifaDate = trim($record["wifa_date"] ?? "");
            $remarks = trim($record["remarks"] ?? "");

            if ($learnerName === "" || $lrn === "") {
                $skipped++;
                continue;
            }

            $learner = clinicEnsureLearnerForCategory($conn, ['lrn' => $lrn, 'school_year' => $fileSchoolYear,
                'learner_name' => $learnerName, 'sex' => $sex, 'birthdate' => $birthdate,
                'age' => $age, 'grade_level' => $fileGradeLevel]);
            if ($learner['created']) $provisionalCreated++;
            $studentRecordId = $learner['record_id'];
            $recSchoolYear = $fileSchoolYear;
            $recGradeLevel = $learner['grade_level'] ?: $fileGradeLevel;

            if (clinicFillExistingSection($conn, 'deworming_wifa', [
                'student_record_id' => $studentRecordId, 'lrn' => $lrn, 'learner_name' => $learnerName,
                'sex' => $sex, 'birthdate' => $birthdate, 'age' => $age,
                'school_year' => $recSchoolYear, 'grade_level' => $recGradeLevel,
                'dewormed_sbfp' => $dewormedSbfp, 'dewormed_other' => $dewormedOther,
                'wifa' => $wifa, 'wifa_date' => $wifaDate, 'remarks' => $remarks,
            ], $upload_id, $overrideExisting)) { $saved++; continue; }

            $insertStmt = $conn->prepare("
                INSERT INTO deworming_wifa_records (
                    upload_id, student_record_id, lrn, learner_name, sex, birthdate, age,
                    school_year, grade_level,
                    dewormed_sbfp, dewormed_other, wifa, wifa_date, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertStmt->bind_param(
                "iisssssssiiiss",
                $upload_id, $studentRecordId, $lrn, $learnerName, $sex, $birthdate, $age,
                $recSchoolYear, $recGradeLevel,
                $dewormedSbfp, $dewormedOther, $wifa, $wifaDate, $remarks
            );
            if ($insertStmt->execute()) $saved++; else $skipped++;
            $insertStmt->close();
        }
        $approvalMessage = "Deworming & WIFA approved. Processed {$saved} records. Skipped {$skipped} records.";
    }
    // ------------------------------------------------------------------
    // 2. OKD & LHAS
    // ------------------------------------------------------------------
    elseif ($report_code === "okd_lhas") {
        $parsed = parseOkdLhasExcelFile($tempFile);
        $records = $parsed["records"] ?? [];

        $deleteStmt = $conn->prepare("DELETE FROM okd_lhas_records WHERE upload_id = ?");
        $deleteStmt->bind_param("i", $upload_id);
        $deleteStmt->execute();
        $deleteStmt->close();

        foreach ($records as $record) {
            $lrn = trim($record["lrn"] ?? "");
            $learnerName = trim($record["learner_name"] ?? "");
            $sex = trim($record["gender"] ?? "");
            $birthdate = trim($record["birthdate"] ?? "");
            $age = trim((string)($record["age"] ?? ""));
            $screeningType = trim($record["screening_type"] ?? "");
            $masterlisted = intval($record["masterlisted"] ?? 0);
            $screened = intval($record["screened"] ?? 0);
            $findings = intval($record["findings"] ?? 0);
            $referredSchool = intval($record["referred_school"] ?? 0);
            $referredLgu = intval($record["referred_lgu"] ?? 0);
            $referredPrivate = intval($record["referred_private"] ?? 0);
            $referredOthers = intval($record["referred_others"] ?? 0);
            $remarks = trim($record["remarks"] ?? "");

            if ($learnerName === "" || $screeningType === "" || $lrn === "") {
                $skipped++;
                continue;
            }

            $learner = clinicEnsureLearnerForCategory($conn, ['lrn' => $lrn, 'school_year' => $fileSchoolYear,
                'learner_name' => $learnerName, 'sex' => $sex, 'birthdate' => $birthdate,
                'age' => $age, 'grade_level' => $fileGradeLevel]);
            if ($learner['created']) $provisionalCreated++;
            $studentRecordId = $learner['record_id'];
            $recSchoolYear = $fileSchoolYear;
            $recGradeLevel = $learner['grade_level'] ?: $fileGradeLevel;

            if (clinicFillExistingSection($conn, 'okd_lhas', [
                'student_record_id' => $studentRecordId, 'lrn' => $lrn, 'learner_name' => $learnerName,
                'sex' => $sex, 'birthdate' => $birthdate, 'age' => $age,
                'school_year' => $recSchoolYear, 'grade_level' => $recGradeLevel,
                'screening_type' => $screeningType, 'masterlisted' => $masterlisted,
                'screened' => $screened, 'findings' => $findings,
                'referred_school' => $referredSchool, 'referred_lgu' => $referredLgu,
                'referred_private' => $referredPrivate, 'referred_others' => $referredOthers,
                'remarks' => $remarks,
            ], $upload_id, $overrideExisting)) { $saved++; continue; }

            $insertStmt = $conn->prepare("
                INSERT INTO okd_lhas_records (
                    upload_id, student_record_id, lrn, learner_name, sex, birthdate, age,
                    school_year, grade_level,
                    screening_type, masterlisted, screened, findings,
                    referred_school, referred_lgu, referred_private, referred_others, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertStmt->bind_param(
                "iissssssssiiiiiiis",
                $upload_id, $studentRecordId, $lrn, $learnerName, $sex, $birthdate, $age,
                $recSchoolYear, $recGradeLevel,
                $screeningType, $masterlisted, $screened, $findings,
                $referredSchool, $referredLgu, $referredPrivate, $referredOthers, $remarks
            );
            if ($insertStmt->execute()) $saved++; else $skipped++;
            $insertStmt->close();
        }
        $approvalMessage = "OKD and LHAS approved. Processed {$saved} records. Skipped {$skipped} records.";
    }
    // ------------------------------------------------------------------
    // 3. IMMUNIZATION
    // ------------------------------------------------------------------
    elseif ($report_code === "immunization_nutritional_status") {
        $parsed = parseImmunizationExcelFile($tempFile);
        $records = $parsed["records"] ?? [];

        $deleteStmt = $conn->prepare("DELETE FROM immunization_records WHERE upload_id = ?");
        $deleteStmt->bind_param("i", $upload_id);
        $deleteStmt->execute();
        $deleteStmt->close();

        foreach ($records as $record) {
            $lrn = trim($record["lrn"] ?? "");
            $learnerName = trim($record["learner_name"] ?? "");
            $sex = trim($record["gender"] ?? "");
            $birthdate = trim($record["birthdate"] ?? "");
            $age = trim((string)($record["age"] ?? ""));
            $vaccine = trim($record["vaccine"] ?? "");
            $dose = trim((string)($record["dose"] ?? ""));
            $immunized = intval($record["immunized"] ?? 0);
            $remarks = trim($record["remarks"] ?? "");

            if ($learnerName === "" || $vaccine === "" || $lrn === "") {
                $skipped++;
                continue;
            }

            $learner = clinicEnsureLearnerForCategory($conn, ['lrn' => $lrn, 'school_year' => $fileSchoolYear,
                'learner_name' => $learnerName, 'sex' => $sex, 'birthdate' => $birthdate,
                'age' => $age, 'grade_level' => $fileGradeLevel]);
            if ($learner['created']) $provisionalCreated++;
            $studentRecordId = $learner['record_id'];
            $recSchoolYear = $fileSchoolYear;
            $recGradeLevel = $learner['grade_level'] ?: $fileGradeLevel;

            if (clinicFillExistingSection($conn, 'immunization', [
                'student_record_id' => $studentRecordId, 'lrn' => $lrn, 'learner_name' => $learnerName,
                'sex' => $sex, 'birthdate' => $birthdate, 'age' => $age,
                'school_year' => $recSchoolYear, 'grade_level' => $recGradeLevel,
                'vaccine' => $vaccine, 'dose' => $dose, 'immunized' => $immunized,
                'remarks' => $remarks,
            ], $upload_id, $overrideExisting)) { $saved++; continue; }

            $insertStmt = $conn->prepare("
                INSERT INTO immunization_records (
                    upload_id, student_record_id, lrn, learner_name, sex, birthdate, age,
                    school_year, grade_level,
                    vaccine, dose, immunized, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertStmt->bind_param(
                "iisssssssssis",
                $upload_id, $studentRecordId, $lrn, $learnerName, $sex, $birthdate, $age,
                $recSchoolYear, $recGradeLevel,
                $vaccine, $dose, $immunized, $remarks
            );
            if ($insertStmt->execute()) $saved++; else $skipped++;
            $insertStmt->close();
        }
        $approvalMessage = "Immunization approved. Processed {$saved} records. Skipped {$skipped} records.";
    }
    // ------------------------------------------------------------------
    // 4. TOBACCO CONTROL
    // ------------------------------------------------------------------
    elseif ($report_code === "comprehensive_tobacco_control") {
        $parsed = parseTobaccoExcelFile($tempFile);
        $records = $parsed["records"] ?? [];

        $lrnInFile = [];
        $duplicatesInFile = [];
        foreach ($records as $record) {
            $lrn = trim($record["lrn"] ?? "");
            if ($lrn === "") continue;
            if (in_array($lrn, $lrnInFile)) $duplicatesInFile[] = $lrn;
            else $lrnInFile[] = $lrn;
        }
        if (!empty($duplicatesInFile)) {
            $conn->rollback();
            if (file_exists($tempFile)) unlink($tempFile);
            echo json_encode(["success" => false, "message" => "Duplicate LRNs in Tobacco file.", "details" => implode(", ", array_unique($duplicatesInFile))]);
            exit;
        }

        $deleteStmt = $conn->prepare("DELETE FROM tobacco_control_records WHERE upload_id = ?");
        $deleteStmt->bind_param("i", $upload_id);
        $deleteStmt->execute();
        $deleteStmt->close();

        foreach ($records as $record) {
            $lrn = trim($record["lrn"] ?? "");
            $learnerName = trim($record["learner_name"] ?? "");
            $sex = trim($record["gender"] ?? "");
            $birthdate = trim($record["birthdate"] ?? "");
            $age = trim((string)($record["age"] ?? ""));
            $violationType = trim($record["violation_type"] ?? "");
            $referredToCare = intval($record["referred_to_care"] ?? 0);
            $remarks = trim($record["remarks"] ?? "");

            if ($learnerName === "" || $violationType === "" || $lrn === "") {
                $skipped++;
                continue;
            }

            $learner = clinicEnsureLearnerForCategory($conn, ['lrn' => $lrn, 'school_year' => $fileSchoolYear,
                'learner_name' => $learnerName, 'sex' => $sex, 'birthdate' => $birthdate,
                'age' => $age, 'grade_level' => $fileGradeLevel]);
            if ($learner['created']) $provisionalCreated++;
            $studentRecordId = $learner['record_id'];
            $recSchoolYear = $fileSchoolYear;
            $recGradeLevel = $learner['grade_level'] ?: $fileGradeLevel;

            if (clinicFillExistingSection($conn, 'tobacco', [
                'student_record_id' => $studentRecordId, 'lrn' => $lrn, 'learner_name' => $learnerName,
                'sex' => $sex, 'birthdate' => $birthdate, 'age' => $age,
                'school_year' => $recSchoolYear, 'grade_level' => $recGradeLevel,
                'violation_type' => $violationType, 'referred_to_care' => $referredToCare,
                'remarks' => $remarks,
            ], $upload_id, $overrideExisting)) { $saved++; continue; }

            $insertStmt = $conn->prepare("
                INSERT INTO tobacco_control_records (
                    upload_id, student_record_id, lrn, learner_name, sex, birthdate, age,
                    school_year, grade_level,
                    violation_type, referred_to_care, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertStmt->bind_param(
                "iissssssssis",
                $upload_id, $studentRecordId, $lrn, $learnerName, $sex, $birthdate, $age,
                $recSchoolYear, $recGradeLevel,
                $violationType, $referredToCare, $remarks
            );
            if ($insertStmt->execute()) $saved++; else $skipped++;
            $insertStmt->close();
        }
        $approvalMessage = "Comprehensive Tobacco Control approved. Processed {$saved} records. Skipped {$skipped} records.";
    }
    // ------------------------------------------------------------------
    // 5. ADOLESCENT REPRODUCTIVE HEALTH (ARH)
    // ------------------------------------------------------------------
    elseif ($report_code === "adolescent_reproductive_health_arh") {
        $parsed = parseArhExcelFile($tempFile);
        $records = $parsed["records"] ?? [];

        $lrnInFile = [];
        $duplicatesInFile = [];
        foreach ($records as $record) {
            $lrn = trim($record["lrn"] ?? "");
            if ($lrn === "") continue;
            if (in_array($lrn, $lrnInFile)) $duplicatesInFile[] = $lrn;
            else $lrnInFile[] = $lrn;
        }
        if (!empty($duplicatesInFile)) {
            $conn->rollback();
            if (file_exists($tempFile)) unlink($tempFile);
            echo json_encode(["success" => false, "message" => "Duplicate LRNs in ARH file.", "details" => implode(", ", array_unique($duplicatesInFile))]);
            exit;
        }

        $deleteStmt = $conn->prepare("DELETE FROM arh_records WHERE upload_id = ?");
        $deleteStmt->bind_param("i", $upload_id);
        $deleteStmt->execute();
        $deleteStmt->close();

        foreach ($records as $record) {
            $lrn = trim($record["lrn"] ?? "");
            $learnerName = trim($record["learner_name"] ?? "");
            $sex = trim($record["gender"] ?? "");
            $birthdate = trim($record["birthdate"] ?? "");
            $age = trim((string)($record["age"] ?? ""));
            $pregnancyStatus = trim($record["pregnancy_status"] ?? "");
            $deliveryMode = trim($record["delivery_mode"] ?? "");
            $peerEducator = intval($record["peer_educator"] ?? 0);
            $remarks = trim($record["remarks"] ?? "");

            if ($learnerName === "" || $lrn === "") {
                $skipped++;
                continue;
            }

            $learner = clinicEnsureLearnerForCategory($conn, ['lrn' => $lrn, 'school_year' => $fileSchoolYear,
                'learner_name' => $learnerName, 'sex' => $sex, 'birthdate' => $birthdate,
                'age' => $age, 'grade_level' => $fileGradeLevel]);
            if ($learner['created']) $provisionalCreated++;
            $studentRecordId = $learner['record_id'];
            $recSchoolYear = $fileSchoolYear;
            $recGradeLevel = $learner['grade_level'] ?: $fileGradeLevel;

            if (clinicFillExistingSection($conn, 'arh', [
                'student_record_id' => $studentRecordId, 'lrn' => $lrn, 'learner_name' => $learnerName,
                'sex' => $sex, 'birthdate' => $birthdate, 'age' => $age,
                'school_year' => $recSchoolYear, 'grade_level' => $recGradeLevel,
                'pregnancy_status' => $pregnancyStatus, 'delivery_mode' => $deliveryMode,
                'peer_educator' => $peerEducator, 'remarks' => $remarks,
            ], $upload_id, $overrideExisting)) { $saved++; continue; }

            $insertStmt = $conn->prepare("
                INSERT INTO arh_records (
                    upload_id, student_record_id, lrn, learner_name, sex, birthdate, age,
                    school_year, grade_level,
                    pregnancy_status, delivery_mode, peer_educator, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertStmt->bind_param(
                "iisssssssssis",
                $upload_id, $studentRecordId, $lrn, $learnerName, $sex, $birthdate, $age,
                $recSchoolYear, $recGradeLevel,
                $pregnancyStatus, $deliveryMode, $peerEducator, $remarks
            );
            if ($insertStmt->execute()) $saved++; else $skipped++;
            $insertStmt->close();
        }
        $approvalMessage = "Adolescent Reproductive Health approved. Processed {$saved} records. Skipped {$skipped} records.";
    }
    // ------------------------------------------------------------------
    // 6. SF8 NUTRITIONAL STATUS (DEFAULT)
    // ------------------------------------------------------------------
    else {
        $parsed = parseSf8ExcelFile($tempFile);
        $students = $parsed["students"] ?? [];

        $lrnInFile = [];
        $duplicatesInFile = [];
        foreach ($students as $student) {
            $lrn = trim($student["lrn"] ?? "");
            if ($lrn === "") continue;
            if (in_array($lrn, $lrnInFile)) $duplicatesInFile[] = $lrn;
            else $lrnInFile[] = $lrn;
        }
        if (!empty($duplicatesInFile)) {
            $conn->rollback();
            if (file_exists($tempFile)) unlink($tempFile);
            echo json_encode(["success" => false, "message" => "Duplicate LRNs in SF8 file.", "details" => implode(", ", array_unique($duplicatesInFile))]);
            exit;
        }

        $deleteStmt = $conn->prepare("DELETE FROM sf8_student_records WHERE upload_id = ?");
        $deleteStmt->bind_param("i", $upload_id);
        $deleteStmt->execute();
        $deleteStmt->close();

        foreach ($students as $student) {
            $lrn           = trim($student["lrn"] ?? "");
            $schoolName    = $student["school_name"] ?? "";
            $district      = $student["district"] ?? "";
            $division      = $student["division"] ?? "";
            $region        = $student["region"] ?? "";
            $schoolId      = $student["school_id"] ?? "";
            $gradeLevel    = $student["grade_level"] ?? "";
            $section       = $student["section"] ?? "";
            $trackStrand   = $student["track_strand"] ?? "";
            $schoolYear    = $fileSchoolYear;
            $learnerName   = $student["learner_name"] ?? "";
            $birthdate     = $student["birthdate"] ?? "";
            $age           = $student["age"] ?? "";
            $sex           = $student["sex"] ?? "";

            $weightKg      = is_numeric($student["weight_kg"] ?? null) ? (float)$student["weight_kg"] : 0.0;
            $heightM       = is_numeric($student["height_m"] ?? null) ? (float)$student["height_m"] : 0.0;
            $heightSquared = is_numeric($student["height_squared"] ?? null) ? (float)$student["height_squared"] : 0.0;
            $bmi           = is_numeric($student["bmi"] ?? null) ? (float)$student["bmi"] : 0.0;

            $bmiCategory   = $student["bmi_category"] ?? "";
            $heightForAge  = is_numeric($age) && $heightM > 0
                ? whoHeightForAge($heightM, (float)$age * 12, $sex) : '';
            $remarks       = $student["remarks"] ?? "";

            if (trim($learnerName) === "" || $lrn === "") {
                $skipped++;
                continue;
            }

            $completedProvisional = false;
            $existingStudentId = clinicFillExistingStudent($conn, [
                'lrn' => $lrn, 'school_year' => $schoolYear,
                'school_name' => $schoolName, 'district' => $district,
                'division' => $division, 'region' => $region, 'school_id' => $schoolId,
                'grade_level' => $gradeLevel, 'section' => $section,
                'track_strand' => $trackStrand, 'learner_name' => $learnerName,
                'birthdate' => $birthdate, 'age' => $age, 'sex' => $sex,
                'weight_kg' => is_numeric($student['weight_kg'] ?? null) ? $weightKg : null,
                'height_m' => is_numeric($student['height_m'] ?? null) ? $heightM : null,
                'height_squared' => is_numeric($student['height_squared'] ?? null) ? $heightSquared : null,
                'bmi' => is_numeric($student['bmi'] ?? null) ? $bmi : null,
                'bmi_category' => $bmiCategory, 'height_for_age' => $heightForAge,
                'remarks' => $remarks,
            ], $overrideExisting, $completedProvisional);
            if ($existingStudentId !== null) {
                if ($completedProvisional) $provisionalCompleted++;
                clinicEnsureStudentSections($conn, $existingStudentId);
                $saved++;
                continue;
            }

            $insertStmt = $conn->prepare("
                INSERT INTO sf8_student_records (
                    upload_id, lrn, school_name, district, division, region, school_id,
                    grade_level, section, track_strand, school_year, learner_name,
                    birthdate, age, sex, weight_kg, height_m, height_squared,
                    bmi, bmi_category, height_for_age, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $typeString = "issssssssssssssddddsss";

            $insertStmt->bind_param(
                $typeString,
                $upload_id,
                $lrn,
                $schoolName,
                $district,
                $division,
                $region,
                $schoolId,
                $gradeLevel,
                $section,
                $trackStrand,
                $schoolYear,
                $learnerName,
                $birthdate,
                $age,
                $sex,
                $weightKg,
                $heightM,
                $heightSquared,
                $bmi,
                $bmiCategory,
                $heightForAge,
                $remarks
            );

            if ($insertStmt->execute()) {
                clinicEnsureStudentSections($conn, $insertStmt->insert_id);
                $saved++;
            } else {
                $skipped++;
            }
            $insertStmt->close();
        }

        $approvalMessage = "Student Information approved. Processed {$saved} records. Skipped {$skipped} records.";
    }

    if ($saved === 0) {
        $conn->rollback();
        if (file_exists($tempFile)) unlink($tempFile);
        http_response_code(422);
        echo json_encode(['success' => false,
            'message' => 'No valid learner records were found in this SF8 file. Nothing was approved.',
            'details' => "Skipped {$skipped} records. Check the LRN, learner name, and required columns."]);
        exit;
    }

    if ($provisionalCreated) $approvalMessage .= " Created {$provisionalCreated} provisional learner profiles.";
    if ($provisionalCompleted) $approvalMessage .= " Completed {$provisionalCompleted} provisional learner profiles.";

    $updateStmt = $conn->prepare("
        UPDATE sf8_uploads
        SET status = 'Approved',
            reviewed_by = ?,
            reviewed_date = NOW(),
            remarks = ?
        WHERE upload_id = ?
    ");
    $updateStmt->bind_param("ssi", $reviewed_by, $approvalMessage, $upload_id);
    $updateStmt->execute();
    $updateStmt->close();

    $conn->commit();

    if (file_exists($tempFile)) {
        unlink($tempFile);
    }

    echo json_encode([
        "success" => true,
        "message" => $approvalMessage,
        "saved" => $saved,
        "skipped" => $skipped,
        "provisional_created" => $provisionalCreated,
        "provisional_completed" => $provisionalCompleted
    ]);

    $conn->close();

} catch (Throwable $e) {
    if (isset($conn) && $conn) {
        try { $conn->rollback(); } catch (Throwable $rollbackError) {}
    }
    if (isset($tempFile) && file_exists($tempFile)) {
        unlink($tempFile);
    }
    $duplicate = $e instanceof mysqli_sql_exception && $e->getCode() === 1062;
    http_response_code($duplicate ? 409 : ($e instanceof Sf8Exception || $e instanceof InvalidArgumentException ? 422 : 503));
    if (!$duplicate && !($e instanceof Sf8Exception || $e instanceof InvalidArgumentException)) {
        error_log('ClinicDesk SF8 approval failed: ' . $e->getMessage());
    }
    echo json_encode([
        "success" => false,
        "message" => $duplicate ? 'Another record already exists for this learner and SF8 entry. Reload the preview before trying again.'
            : ($e instanceof Sf8Exception || $e instanceof InvalidArgumentException
                ? $e->getMessage() : "Approval failed. Check the server configuration or try again.")
    ]);
}
?>
