-- Additive WIFA and deworming monitoring. Existing SF8 rows remain the
-- imported baseline. No dates or doses are inferred during migration.
CREATE TABLE IF NOT EXISTS wifa_events (
  wifa_event_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_record_id INT NOT NULL,
  event_date DATE NOT NULL,
  outcome ENUM('Given','Not given') NOT NULL,
  reason_code VARCHAR(60) DEFAULT NULL,
  remarks TEXT DEFAULT NULL,
  recorded_by_account_id INT DEFAULT NULL,
  recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_wifa_student_date (student_record_id, event_date),
  KEY idx_wifa_date (event_date),
  CONSTRAINT fk_wifa_student FOREIGN KEY (student_record_id) REFERENCES sf8_student_records(record_id) ON DELETE CASCADE,
  CONSTRAINT fk_wifa_actor FOREIGN KEY (recorded_by_account_id) REFERENCES local_accounts(account_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS wifa_reviews (
  wifa_review_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_record_id INT NOT NULL,
  decision ENUM('Needs review','Continue','Paused','Completed','Not applicable') NOT NULL,
  reason TEXT DEFAULT NULL,
  next_review_date DATE DEFAULT NULL,
  hemoglobin_g_dl DECIMAL(4,1) DEFAULT NULL,
  hemoglobin_date DATE DEFAULT NULL,
  reviewed_by_account_id INT DEFAULT NULL,
  reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_wifa_review_student (student_record_id, wifa_review_id),
  CONSTRAINT fk_wifa_review_student FOREIGN KEY (student_record_id) REFERENCES sf8_student_records(record_id) ON DELETE CASCADE,
  CONSTRAINT fk_wifa_review_actor FOREIGN KEY (reviewed_by_account_id) REFERENCES local_accounts(account_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS deworming_events (
  deworming_event_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_record_id INT NOT NULL,
  event_date DATE NOT NULL,
  channel ENUM('SBFP','Other') NOT NULL,
  outcome ENUM('Given','Not given') NOT NULL,
  remarks TEXT DEFAULT NULL,
  recorded_by_account_id INT DEFAULT NULL,
  recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_deworming_student_date (student_record_id, event_date),
  KEY idx_deworming_date (event_date),
  CONSTRAINT fk_deworming_event_student FOREIGN KEY (student_record_id) REFERENCES sf8_student_records(record_id) ON DELETE CASCADE,
  CONSTRAINT fk_deworming_event_actor FOREIGN KEY (recorded_by_account_id) REFERENCES local_accounts(account_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS health_program_event_audit (
  audit_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_record_id INT NOT NULL,
  program ENUM('WIFA','Deworming') NOT NULL,
  event_id INT NOT NULL,
  action ENUM('Created','Corrected') NOT NULL,
  before_json LONGTEXT DEFAULT NULL CHECK (before_json IS NULL OR JSON_VALID(before_json)),
  after_json LONGTEXT NOT NULL CHECK (JSON_VALID(after_json)),
  actor_account_id INT DEFAULT NULL,
  changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_program_audit_student (student_record_id, changed_at),
  CONSTRAINT fk_program_audit_student FOREIGN KEY (student_record_id) REFERENCES sf8_student_records(record_id) ON DELETE CASCADE,
  CONSTRAINT fk_program_audit_actor FOREIGN KEY (actor_account_id) REFERENCES local_accounts(account_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
