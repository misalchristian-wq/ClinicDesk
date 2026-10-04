-- Nurse observations and care given without creating SF8 screening records.
CREATE TABLE IF NOT EXISTS consultations (
  consultation_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  record_id INT NOT NULL,
  symptoms TEXT NOT NULL,
  care_given TEXT NOT NULL,
  follow_up_date DATE DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  recorded_by_account_id INT DEFAULT NULL,
  recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_consultations_student_date (record_id, recorded_at),
  CONSTRAINT fk_consultation_student FOREIGN KEY (record_id) REFERENCES sf8_student_records(record_id) ON DELETE CASCADE,
  CONSTRAINT fk_consultation_nurse FOREIGN KEY (recorded_by_account_id) REFERENCES local_accounts(account_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
