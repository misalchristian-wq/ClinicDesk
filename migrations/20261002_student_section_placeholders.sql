-- Run once before enabling automatic student-section placeholders.
-- NULL upload_id means the row is an empty student section or a manually added master student.
ALTER TABLE sf8_student_records MODIFY upload_id int(11) NULL DEFAULT NULL;
ALTER TABLE deworming_wifa_records MODIFY upload_id int(11) NULL DEFAULT NULL;
ALTER TABLE okd_lhas_records MODIFY upload_id int(11) NULL DEFAULT NULL;
ALTER TABLE immunization_records MODIFY upload_id int(11) NULL DEFAULT NULL;
ALTER TABLE tobacco_control_records MODIFY upload_id int(11) NULL DEFAULT NULL;
ALTER TABLE arh_records MODIFY upload_id int(11) NULL DEFAULT NULL;

-- The composite unique key remains the authority for a student in one school year.
-- Check for repeated (lrn, school_year) values before applying this migration.
ALTER TABLE sf8_student_records DROP INDEX idx_unique_lrn;

-- A learner may have the same health entry in a later school year.
ALTER TABLE immunization_records DROP INDEX idx_unique_immunization,
    ADD UNIQUE KEY idx_unique_immunization (lrn, school_year, vaccine, dose);
ALTER TABLE arh_records DROP INDEX idx_unique_arh,
    ADD UNIQUE KEY idx_unique_arh (lrn, school_year, pregnancy_status);
ALTER TABLE tobacco_control_records DROP INDEX idx_unique_tobacco,
    ADD UNIQUE KEY idx_unique_tobacco (lrn, school_year, violation_type);
