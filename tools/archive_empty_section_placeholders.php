<?php
// CLI migration: preserve and remove only old, fully blank health-section rows.
// Run after the category-first schema migration. Archive tables retain recovery copies.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../api/db.php';

$blank = [
    'deworming_wifa_records' => "COALESCE(dewormed_sbfp,0)=0 AND COALESCE(dewormed_other,0)=0 AND COALESCE(wifa,0)=0 AND (wifa_date IS NULL OR wifa_date='' OR wifa_date='0000-00-00') AND (remarks IS NULL OR TRIM(remarks)='')",
    'okd_lhas_records' => "(screening_type IS NULL OR TRIM(screening_type)='') AND COALESCE(masterlisted,0)=0 AND COALESCE(screened,0)=0 AND COALESCE(findings,0)=0 AND COALESCE(referred_school,0)=0 AND COALESCE(referred_lgu,0)=0 AND COALESCE(referred_private,0)=0 AND COALESCE(referred_others,0)=0 AND (remarks IS NULL OR TRIM(remarks)='')",
    'immunization_records' => "(vaccine IS NULL OR TRIM(vaccine)='') AND (dose IS NULL OR TRIM(dose)='') AND COALESCE(immunized,0)=0 AND (remarks IS NULL OR TRIM(remarks)='')",
    'tobacco_control_records' => "(violation_type IS NULL OR TRIM(violation_type)='') AND COALESCE(referred_to_care,0)=0 AND (remarks IS NULL OR TRIM(remarks)='')",
    'arh_records' => "(pregnancy_status IS NULL OR TRIM(pregnancy_status)='') AND (delivery_mode IS NULL OR TRIM(delivery_mode)='') AND COALESCE(peer_educator,0)=0 AND (remarks IS NULL OR TRIM(remarks)='')",
];

try {
    foreach (array_keys($blank) as $table) {
        $archive = 'archive_20261003_' . $table;
        $conn->query("CREATE TABLE IF NOT EXISTS `$archive` LIKE `$table`");
    }
    $conn->begin_transaction();
    $summary = [];
    foreach ($blank as $table => $condition) {
        $archive = 'archive_20261003_' . $table;
        $where = "upload_id IS NULL AND student_record_id IS NOT NULL AND $condition";
        $count = (int)$conn->query("SELECT COUNT(*) AS n FROM `$table` WHERE $where")->fetch_assoc()['n'];
        if (!$count) { $summary[$table] = 0; continue; }
        $conn->query("INSERT INTO `$archive` SELECT * FROM `$table` WHERE $where");
        if ($conn->affected_rows !== $count) throw new RuntimeException("Archive count mismatch in $table.");
        $conn->query("DELETE FROM `$table` WHERE $where");
        if ($conn->affected_rows !== $count) throw new RuntimeException("Delete count mismatch in $table.");
        $summary[$table] = $count;
    }
    $conn->commit();
    foreach ($summary as $table => $count) echo "$table: archived $count empty rows\n";
} catch (Throwable $e) {
    try { $conn->rollback(); } catch (Throwable $ignored) {}
    fwrite(STDERR, 'Empty-section archive failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
} finally {
    $conn->close();
}
