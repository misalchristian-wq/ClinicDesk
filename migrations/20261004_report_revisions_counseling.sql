ALTER TABLE report_saved_data
  ADD COLUMN source_snapshot LONGTEXT NULL AFTER report_data;

CREATE TABLE IF NOT EXISTS report_saved_revisions (
  revision_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  report_id INT NOT NULL,
  report_key VARCHAR(50) NOT NULL,
  school_year VARCHAR(50) NOT NULL,
  report_data LONGTEXT NOT NULL,
  source_snapshot LONGTEXT NULL,
  saved_by VARCHAR(150) NOT NULL,
  saved_at DATETIME NULL,
  action ENUM('replaced','deleted') NOT NULL,
  acted_by VARCHAR(150) NOT NULL,
  acted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_report_revision (report_key, school_year, acted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS guidance_counseling_visits (
  visit_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_record_id INT NOT NULL,
  visit_date DATE NOT NULL,
  recorded_by VARCHAR(150) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_counseling_student_date (student_record_id, visit_date),
  KEY idx_counseling_date (visit_date),
  CONSTRAINT fk_counseling_student FOREIGN KEY (student_record_id)
    REFERENCES sf8_student_records(record_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
