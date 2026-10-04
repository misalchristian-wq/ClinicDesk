-- Record the inputs required by the currently deployed 20-feature model.
-- Apply once to an existing ClinicDesk database. Blank laboratory values remain unknown.
ALTER TABLE `student_health_inputs`
  ADD COLUMN `has_muscle_weakness` varchar(10) DEFAULT NULL AFTER `has_night_blindness`,
  ADD COLUMN `has_numbness_tingling` varchar(10) DEFAULT NULL AFTER `has_muscle_weakness`,
  ADD COLUMN `has_memory_problems` varchar(10) DEFAULT NULL AFTER `has_numbness_tingling`,
  ADD COLUMN `has_multiple_deficiencies` varchar(10) DEFAULT NULL AFTER `has_memory_problems`,
  ADD COLUMN `hemoglobin_g_dl` decimal(4,1) DEFAULT NULL AFTER `clinic_notes`,
  ADD COLUMN `serum_vitamin_d_ng_ml` decimal(6,2) DEFAULT NULL AFTER `hemoglobin_g_dl`,
  ADD COLUMN `serum_vitamin_b12_pg_ml` decimal(8,2) DEFAULT NULL AFTER `serum_vitamin_d_ng_ml`,
  ADD COLUMN `serum_folate_ng_ml` decimal(6,2) DEFAULT NULL AFTER `serum_vitamin_b12_pg_ml`,
  ADD COLUMN `lab_result_date` date DEFAULT NULL AFTER `serum_folate_ng_ml`;
