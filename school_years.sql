-- ============================================================
-- ClinicDesk : school_years reference table
-- Nurse-managed list of valid school years. Exactly one row
-- is flagged is_active = 1 (the "active" year everyone defaults to).
-- Run this once in phpMyAdmin (clinicdesk database).
-- ============================================================

CREATE TABLE IF NOT EXISTS `school_years` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `year_label` VARCHAR(20) NOT NULL,       -- e.g. "2024-2025"
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_year_label` (`year_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Optional seed rows (safe to keep or edit). Remove if you prefer
-- the nurse to add every year manually.
INSERT IGNORE INTO `school_years` (`year_label`, `is_active`) VALUES
('2023-2024', 0),
('2024-2025', 1),
('2025-2026', 0);
