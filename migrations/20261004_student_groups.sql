-- Nurse-managed student group membership for the selected school-year record.
ALTER TABLE sf8_student_records
  ADD COLUMN is_muslim TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN is_pwd TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN is_ip TINYINT(1) NOT NULL DEFAULT 0;
