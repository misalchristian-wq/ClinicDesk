<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

ini_set("display_errors", 0);
error_reporting(E_ALL);

try {
    include __DIR__ . "/../db.php";
    include __DIR__ . "/sf8_parser.php";
    include __DIR__ . "/deworming_wifa_parser.php";
    include __DIR__ . "/okd_lhas_parser.php";
    include __DIR__ . "/immunization_parser.php";
    include __DIR__ . "/tobacco_parser.php";
    include __DIR__ . "/arh_parser.php";

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
    $reviewed_by = trim($data["reviewed_by"] ?? "Clinic Nurse");

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

    $tempDir = __DIR__ . "/../temp_uploads";
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    $tempFile = $tempDir . "/approve_sf8_" . $upload_id . "_" . time() . ".xlsx";

    $ch = curl_init($cloudinary_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $fileData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($fileData === false || $httpCode < 200 || $httpCode >= 300) {
        echo json_encode([
            "success" => false,
            "message" => "Unable to download Excel file from Cloudinary.",
            "http_code" => $httpCode,
            "curl_error" => $curlError
        ]);
        exit;
    }

    file_put_contents($tempFile, $fileData);

    if (!file_exists($tempFile) || filesize($tempFile) === 0) {
        echo json_encode(["success" => false, "message" => "Downloaded Excel file is missing or empty."]);
        exit;
    }

    // Read the file-level school year + grade from row 7 (used by ARH/tobacco).
    $fileMeta = readSchoolYearAndGrade($tempFile);
    $fileSchoolYear = $fileMeta["school_year"];
    $fileGradeLevel = $fileMeta["grade_level"];

    $conn->begin_transaction();

    $saved = 0;
    $skipped = 0;

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

        $existingLrns = [];
        if (!empty($lrnInFile)) {
            $placeholders = implode(',', array_fill(0, count($lrnInFile), '?'));
            $checkStmt = $conn->prepare("SELECT lrn FROM deworming_wifa_records WHERE lrn IN ($placeholders)");
            $types = str_repeat("s", count($lrnInFile));
            $checkStmt->bind_param($types, ...$lrnInFile);
            $checkStmt->execute();
            $res = $checkStmt->get_result();
            while ($row = $res->fetch_assoc()) $existingLrns[] = $row["lrn"];
            $checkStmt->close();
        }
        if (!empty($existingLrns)) {
            $conn->rollback();
            if (file_exists($tempFile)) unlink($tempFile);
            echo json_encode([
                "success" => false,
                "message" => "LRNs already exist in Deworming records.",
                "details" => implode(", ", $existingLrns)
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

            $studentRecordId = null;
            $findStmt = $conn->prepare("SELECT record_id, grade_level, school_year FROM sf8_student_records WHERE lrn = ? LIMIT 1");
            $findStmt->bind_param("s", $lrn);
            $findStmt->execute();
            $match = $findStmt->get_result()->fetch_assoc();
            $findStmt->close();
            if ($match) $studentRecordId = intval($match["record_id"]);

            $recSchoolYear = ($match && !empty($match["school_year"])) ? $match["school_year"] : $fileSchoolYear;
            $recGradeLevel = ($match && !empty($match["grade_level"])) ? $match["grade_level"] : $fileGradeLevel;

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
        $approvalMessage = "Deworming & WIFA approved. Saved {$saved} records. Skipped {$skipped} records.";
    }
    // ------------------------------------------------------------------
    // 2. OKD & LHAS
    // ------------------------------------------------------------------
    elseif ($report_code === "okd_lhas") {
        $parsed = parseOkdLhasExcelFile($tempFile);
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
            echo json_encode(["success" => false, "message" => "Duplicate LRNs in OKD file.", "details" => implode(", ", array_unique($duplicatesInFile))]);
            exit;
        }

        $existingLrns = [];
        if (!empty($lrnInFile)) {
            $placeholders = implode(',', array_fill(0, count($lrnInFile), '?'));
            $checkStmt = $conn->prepare("SELECT lrn FROM okd_lhas_records WHERE lrn IN ($placeholders)");
            $types = str_repeat("s", count($lrnInFile));
            $checkStmt->bind_param($types, ...$lrnInFile);
            $checkStmt->execute();
            $res = $checkStmt->get_result();
            while ($row = $res->fetch_assoc()) $existingLrns[] = $row["lrn"];
            $checkStmt->close();
        }
        if (!empty($existingLrns)) {
            $conn->rollback();
            if (file_exists($tempFile)) unlink($tempFile);
            echo json_encode(["success" => false, "message" => "LRNs already exist in OKD records.", "details" => implode(", ", $existingLrns)]);
            exit;
        }

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

            $studentRecordId = null;
            $findStmt = $conn->prepare("SELECT record_id, grade_level, school_year FROM sf8_student_records WHERE lrn = ? LIMIT 1");
            $findStmt->bind_param("s", $lrn);
            $findStmt->execute();
            $match = $findStmt->get_result()->fetch_assoc();
            $findStmt->close();
            if ($match) $studentRecordId = intval($match["record_id"]);

            $recSchoolYear = ($match && !empty($match["school_year"])) ? $match["school_year"] : $fileSchoolYear;
            $recGradeLevel = ($match && !empty($match["grade_level"])) ? $match["grade_level"] : $fileGradeLevel;

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
        $approvalMessage = "OKD and LHAS approved. Saved {$saved} records. Skipped {$skipped} records.";
    }
    // ------------------------------------------------------------------
    // 3. IMMUNIZATION
    // ------------------------------------------------------------------
    elseif ($report_code === "immunization_nutritional_status") {
        $parsed = parseImmunizationExcelFile($tempFile);
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
            echo json_encode(["success" => false, "message" => "Duplicate LRNs in Immunization file.", "details" => implode(", ", array_unique($duplicatesInFile))]);
            exit;
        }

        $existingLrns = [];
        if (!empty($lrnInFile)) {
            $placeholders = implode(',', array_fill(0, count($lrnInFile), '?'));
            $checkStmt = $conn->prepare("SELECT lrn FROM immunization_records WHERE lrn IN ($placeholders)");
            $types = str_repeat("s", count($lrnInFile));
            $checkStmt->bind_param($types, ...$lrnInFile);
            $checkStmt->execute();
            $res = $checkStmt->get_result();
            while ($row = $res->fetch_assoc()) $existingLrns[] = $row["lrn"];
            $checkStmt->close();
        }
        if (!empty($existingLrns)) {
            $conn->rollback();
            if (file_exists($tempFile)) unlink($tempFile);
            echo json_encode(["success" => false, "message" => "LRNs already exist in Immunization records.", "details" => implode(", ", $existingLrns)]);
            exit;
        }

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

            $studentRecordId = null;
            $findStmt = $conn->prepare("SELECT record_id, grade_level, school_year FROM sf8_student_records WHERE lrn = ? LIMIT 1");
            $findStmt->bind_param("s", $lrn);
            $findStmt->execute();
            $match = $findStmt->get_result()->fetch_assoc();
            $findStmt->close();
            if ($match) $studentRecordId = intval($match["record_id"]);

            $recSchoolYear = ($match && !empty($match["school_year"])) ? $match["school_year"] : $fileSchoolYear;
            $recGradeLevel = ($match && !empty($match["grade_level"])) ? $match["grade_level"] : $fileGradeLevel;

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
        $approvalMessage = "Immunization approved. Saved {$saved} records. Skipped {$skipped} records.";
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

        $existingLrns = [];
        if (!empty($lrnInFile)) {
            $placeholders = implode(',', array_fill(0, count($lrnInFile), '?'));
            $checkStmt = $conn->prepare("SELECT lrn FROM tobacco_control_records WHERE lrn IN ($placeholders)");
            $types = str_repeat("s", count($lrnInFile));
            $checkStmt->bind_param($types, ...$lrnInFile);
            $checkStmt->execute();
            $res = $checkStmt->get_result();
            while ($row = $res->fetch_assoc()) $existingLrns[] = $row["lrn"];
            $checkStmt->close();
        }
        if (!empty($existingLrns)) {
            $conn->rollback();
            if (file_exists($tempFile)) unlink($tempFile);
            echo json_encode(["success" => false, "message" => "LRNs already exist in Tobacco records.", "details" => implode(", ", $existingLrns)]);
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

            $studentRecordId = null;
            $findStmt = $conn->prepare("SELECT record_id, grade_level, school_year FROM sf8_student_records WHERE lrn = ? LIMIT 1");
            $findStmt->bind_param("s", $lrn);
            $findStmt->execute();
            $match = $findStmt->get_result()->fetch_assoc();
            $findStmt->close();
            if ($match) $studentRecordId = intval($match["record_id"]);

            // School year + grade: prefer the matched student's, else the file's row 7 values.
            $recSchoolYear = ($match && !empty($match["school_year"])) ? $match["school_year"] : $fileSchoolYear;
            $recGradeLevel = ($match && !empty($match["grade_level"])) ? $match["grade_level"] : $fileGradeLevel;

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
        $approvalMessage = "Comprehensive Tobacco Control approved. Saved {$saved} records. Skipped {$skipped} records.";
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

        $existingLrns = [];
        if (!empty($lrnInFile)) {
            $placeholders = implode(',', array_fill(0, count($lrnInFile), '?'));
            $checkStmt = $conn->prepare("SELECT lrn FROM arh_records WHERE lrn IN ($placeholders)");
            $types = str_repeat("s", count($lrnInFile));
            $checkStmt->bind_param($types, ...$lrnInFile);
            $checkStmt->execute();
            $res = $checkStmt->get_result();
            while ($row = $res->fetch_assoc()) $existingLrns[] = $row["lrn"];
            $checkStmt->close();
        }
        if (!empty($existingLrns)) {
            $conn->rollback();
            if (file_exists($tempFile)) unlink($tempFile);
            echo json_encode(["success" => false, "message" => "LRNs already exist in ARH records.", "details" => implode(", ", $existingLrns)]);
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

            $studentRecordId = null;
            $findStmt = $conn->prepare("SELECT record_id, grade_level, school_year FROM sf8_student_records WHERE lrn = ? LIMIT 1");
            $findStmt->bind_param("s", $lrn);
            $findStmt->execute();
            $match = $findStmt->get_result()->fetch_assoc();
            $findStmt->close();
            if ($match) $studentRecordId = intval($match["record_id"]);

            // School year + grade: prefer the matched student's, else the file's row 7 values.
            $recSchoolYear = ($match && !empty($match["school_year"])) ? $match["school_year"] : $fileSchoolYear;
            $recGradeLevel = ($match && !empty($match["grade_level"])) ? $match["grade_level"] : $fileGradeLevel;

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
        $approvalMessage = "Adolescent Reproductive Health approved. Saved {$saved} records. Skipped {$skipped} records.";
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

        $existingLrns = [];
        if (!empty($lrnInFile)) {
            $placeholders = implode(',', array_fill(0, count($lrnInFile), '?'));
            $checkStmt = $conn->prepare("SELECT lrn FROM sf8_student_records WHERE lrn IN ($placeholders)");
            $types = str_repeat("s", count($lrnInFile));
            $checkStmt->bind_param($types, ...$lrnInFile);
            $checkStmt->execute();
            $res = $checkStmt->get_result();
            while ($row = $res->fetch_assoc()) $existingLrns[] = $row["lrn"];
            $checkStmt->close();
        }
        if (!empty($existingLrns)) {
            $conn->rollback();
            if (file_exists($tempFile)) unlink($tempFile);
            echo json_encode(["success" => false, "message" => "LRNs already exist in Student Records.", "details" => implode(", ", $existingLrns)]);
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
            $schoolYear    = $student["school_year"] ?? "";
            $learnerName   = $student["learner_name"] ?? "";
            $birthdate     = $student["birthdate"] ?? "";
            $age           = $student["age"] ?? "";
            $sex           = $student["sex"] ?? "";

            $weightKg      = is_numeric($student["weight_kg"] ?? null) ? (float)$student["weight_kg"] : 0.0;
            $heightM       = is_numeric($student["height_m"] ?? null) ? (float)$student["height_m"] : 0.0;
            $heightSquared = is_numeric($student["height_squared"] ?? null) ? (float)$student["height_squared"] : 0.0;
            $bmi           = is_numeric($student["bmi"] ?? null) ? (float)$student["bmi"] : 0.0;

            $bmiCategory   = $student["bmi_category"] ?? "";
            $heightForAge  = $student["height_for_age"] ?? "";
            $remarks       = $student["remarks"] ?? "";

            if (trim($learnerName) === "" || $lrn === "") {
                $skipped++;
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
                $saved++;
            } else {
                $skipped++;
            }
            $insertStmt->close();
        }

        $approvalMessage = "Student Information approved. Saved {$saved} records. Skipped {$skipped} records.";
    }

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
        "skipped" => $skipped
    ]);

    $conn->close();

} catch (Throwable $e) {
    if (isset($conn) && $conn) {
        try { $conn->rollback(); } catch (Throwable $rollbackError) {}
    }
    if (isset($tempFile) && file_exists($tempFile)) {
        unlink($tempFile);
    }
    echo json_encode([
        "success" => false,
        "message" => "Approval failed: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}
?>