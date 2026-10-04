-- Nurse-recorded feeding follow-ups. SF8 anthropometry remains the baseline.
CREATE TABLE IF NOT EXISTS feeding_measurements (
  measurement_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_record_id INT NOT NULL,
  measured_on DATE NOT NULL,
  weight_kg DECIMAL(6,2) DEFAULT NULL,
  bmi DECIMAL(5,2) DEFAULT NULL,
  progress_status ENUM('Monitoring','Improving','Recovered','Needs follow-up') DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  recorded_by_account_id INT DEFAULT NULL,
  recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_feeding_student_date (student_record_id, measured_on),
  KEY idx_feeding_measurement_date (measured_on),
  CONSTRAINT fk_feeding_student FOREIGN KEY (student_record_id) REFERENCES sf8_student_records(record_id) ON DELETE CASCADE,
  CONSTRAINT fk_feeding_actor FOREIGN KEY (recorded_by_account_id) REFERENCES local_accounts(account_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS feeding_measurement_audit (
  audit_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  measurement_id INT NOT NULL,
  student_record_id INT NOT NULL,
  action ENUM('Created','Corrected') NOT NULL,
  before_json LONGTEXT DEFAULT NULL CHECK (before_json IS NULL OR JSON_VALID(before_json)),
  after_json LONGTEXT NOT NULL CHECK (JSON_VALID(after_json)),
  actor_account_id INT DEFAULT NULL,
  changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_feeding_audit_student (student_record_id, changed_at),
  CONSTRAINT fk_feeding_audit_student FOREIGN KEY (student_record_id) REFERENCES sf8_student_records(record_id) ON DELETE CASCADE,
  CONSTRAINT fk_feeding_audit_actor FOREIGN KEY (actor_account_id) REFERENCES local_accounts(account_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
