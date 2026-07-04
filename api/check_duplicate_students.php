<?php
// api/check_duplicate_students.php
//
// Prevents duplicate student records on approval. A student (by LRN) may
// exist once PER school year. Include this from approve_sf8_upload.php
// BEFORE inserting the extracted records.
//
// Expects, from the caller:
//   $conn         -> mysqli connection (from db.php)
//   $records      -> array of rows to be saved, each with at least "lrn"
//   $schoolYear   -> the school year for this upload (string, "YYYY-YYYY")
//
// Behaviour:
//   - Skips already-existing (lrn, school_year) rows rather than erroring,
//     so re-uploading the same file won't create duplicates. It reports how
//     many were skipped vs. how many are new.
//   - After running, $recordsToInsert holds only the new, non-duplicate rows,
//     and $duplicateInfo describes what was skipped.
//
// If you'd rather HARD-REJECT any upload that contains duplicates instead of
// silently skipping, set $rejectOnDuplicate = true before including.

if (!isset($conn)) {
    include __DIR__ . "/../db.php";
}

$schoolYear = isset($schoolYear) ? trim($schoolYear) : "";
$records = isset($records) && is_array($records) ? $records : [];
$rejectOnDuplicate = isset($rejectOnDuplicate) ? (bool)$rejectOnDuplicate : false;

if ($schoolYear === "") {
    echo json_encode([
        "success" => false,
        "message" => "Cannot check duplicates: the upload has no school year."
    ]);
    exit;
}

// Collect the LRNs from the incoming records.
$incomingLrns = [];
foreach ($records as $r) {
    $lrn = trim((string)($r["lrn"] ?? ""));
    if ($lrn !== "") {
        $incomingLrns[] = $lrn;
    }
}

// 1. Check for duplicates WITHIN the file itself (same LRN twice).
$seen = [];
$dupInFile = [];
foreach ($incomingLrns as $lrn) {
    if (isset($seen[$lrn])) {
        $dupInFile[$lrn] = true;
    }
    $seen[$lrn] = true;
}

// 2. Check which LRNs already exist in the DB for this same school year.
$existing = [];
if (!empty($incomingLrns)) {
    // Build a safe IN-list of integers (lrn is bigint).
    $safe = array_map(function ($v) { return (int)$v; }, $incomingLrns);
    $inList = implode(",", array_unique($safe));

    $stmt = $conn->prepare(
        "SELECT lrn FROM sf8_student_records WHERE school_year = ? AND lrn IN ($inList)"
    );
    $stmt->bind_param("s", $schoolYear);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $existing[(string)$row["lrn"]] = true;
    }
}

// Build the clean insert list and a summary of what was skipped.
$recordsToInsert = [];
$seenForInsert = [];
$skippedExisting = 0;
$skippedInFile = 0;

foreach ($records as $r) {
    $lrn = trim((string)($r["lrn"] ?? ""));
    if ($lrn === "") { continue; }

    if (isset($existing[$lrn])) {
        $skippedExisting++;
        continue; // already in DB for this school year
    }
    if (isset($seenForInsert[$lrn])) {
        $skippedInFile++;
        continue; // duplicate within this same file
    }

    $seenForInsert[$lrn] = true;
    $recordsToInsert[] = $r;
}

$duplicateInfo = [
    "total_in_file"    => count($incomingLrns),
    "new_records"      => count($recordsToInsert),
    "skipped_existing" => $skippedExisting,
    "skipped_in_file"  => $skippedInFile,
    "school_year"      => $schoolYear
];

// Optional hard-reject mode.
if ($rejectOnDuplicate && ($skippedExisting > 0 || $skippedInFile > 0)) {
    echo json_encode([
        "success" => false,
        "message" => "Upload rejected: it contains $skippedExisting record(s) already saved for $schoolYear "
                   . "and $skippedInFile duplicate LRN(s) within the file.",
        "details" => $duplicateInfo
    ]);
    exit;
}

// If nothing new is left to insert, tell the caller so it can stop.
if (empty($recordsToInsert)) {
    echo json_encode([
        "success" => false,
        "message" => "No new students to save. All " . count($incomingLrns)
                   . " record(s) already exist for school year $schoolYear.",
        "details" => $duplicateInfo
    ]);
    exit;
}

// Passed. $recordsToInsert and $duplicateInfo are now available to the caller,
// which should INSERT only $recordsToInsert.
?>