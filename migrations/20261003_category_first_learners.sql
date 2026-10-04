-- Apply after checking existing duplicate (lrn, school_year, screening_type)
-- groups in okd_lhas_records. This migration preserves existing health rows.
-- Existing upload_id-NULL section placeholders are not deleted because a
-- historical zero may represent an intentional recorded value.

ALTER TABLE sf8_student_records
  MODIFY sex varchar(10) NULL DEFAULT NULL,
  ADD COLUMN profile_status enum('Provisional','Complete') NOT NULL DEFAULT 'Complete' AFTER sex;

ALTER TABLE okd_lhas_records
  ADD UNIQUE KEY idx_unique_okd_lhas (lrn, school_year, screening_type);
