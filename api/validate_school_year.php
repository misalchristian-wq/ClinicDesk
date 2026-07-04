<?php
// api/validate_school_year.php
//
// Server-side guard so the school-year rule can't be bypassed by editing
// the page in the browser. Include this from save_sf8_upload.php AFTER you
// have decoded the request body and BEFORE you insert the upload.
//
// Usage inside save_sf8_upload.php:
//
//   $data = json_decode(file_get_contents("php://input"), true);
//   $submittedYear = $data["school_year"] ?? "";
//   require __DIR__ . "/validate_school_year.php";  // needs $conn + $submittedYear
//   // ...if it passes, continue with your INSERT, using $submittedYear...
//
// On failure it echoes a JSON error and exits, so no upload is saved.

if (!isset($conn)) {
    // db.php defines $conn; include it if the caller hasn't.
    include __DIR__ . "/../db.php";
}

$submittedYear = isset($submittedYear) ? trim($submittedYear) : "";

// 1. Must be present.
if ($submittedYear === "") {
    echo json_encode([
        "success" => false,
        "message" => "Missing school year. It must be read from the file (row 7, next to \"School Year\")."
    ]);
    exit;
}

// 2. Must be exact YYYY-YYYY format (a bare 2021 is rejected).
if (!preg_match('/^\d{4}-\d{4}$/', $submittedYear)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid school year format in the file: \"$submittedYear\". It must be YYYY-YYYY (e.g. 2024-2025)."
    ]);
    exit;
}

// 3. Must match the nurse's active school year.
$activeYear = null;
$res = $conn->query("SELECT year_label FROM school_years WHERE is_active = 1 LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $activeYear = $row["year_label"];
}

if ($activeYear === null) {
    echo json_encode([
        "success" => false,
        "message" => "No active school year has been set by the clinic nurse. Upload is blocked."
    ]);
    exit;
}

if ($submittedYear !== $activeYear) {
    echo json_encode([
        "success" => false,
        "message" => "The file's school year ($submittedYear) does not match the active school year ($activeYear) set by the clinic nurse."
    ]);
    exit;
}

// Passed all checks. Control returns to save_sf8_upload.php.
?>