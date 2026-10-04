-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 17, 2026 at 08:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clinicdesk`
--

-- --------------------------------------------------------

--
-- Table structure for table `arh_records`
--

CREATE TABLE `arh_records` (
  `arh_record_id` int(11) NOT NULL,
  `upload_id` int(11) DEFAULT NULL,
  `student_record_id` int(11) DEFAULT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `grade_level` varchar(30) DEFAULT NULL,
  `pregnancy_status` varchar(50) DEFAULT NULL,
  `delivery_mode` varchar(100) DEFAULT NULL,
  `peer_educator` tinyint(4) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `date_saved` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `box1_okd_lhas_reports`
--

CREATE TABLE `box1_okd_lhas_reports` (
  `report_id` int(11) NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  `saved_by` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `box5_box6_reports`
--

CREATE TABLE `box5_box6_reports` (
  `report_id` int(11) NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  `saved_by` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deworming_wifa_records`
--

CREATE TABLE `deworming_wifa_records` (
  `deworming_wifa_id` int(11) NOT NULL,
  `upload_id` int(11) DEFAULT NULL,
  `student_record_id` int(11) DEFAULT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `grade_level` varchar(30) DEFAULT NULL,
  `dewormed_sbfp` tinyint(4) DEFAULT 0,
  `dewormed_other` tinyint(4) DEFAULT 0,
  `wifa` tinyint(4) DEFAULT 0,
  `wifa_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `firebase_accounts`
--

CREATE TABLE `firebase_accounts` (
  `firebase_account_id` int(11) NOT NULL,
  `firebase_uid` varchar(150) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'Teacher',
  `status` varchar(20) DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `generated_reports`
--

CREATE TABLE `generated_reports` (
  `report_id` int(11) NOT NULL,
  `school_year` varchar(50) NOT NULL,
  `report_type` varchar(50) DEFAULT 'consolidated',
  `cloudinary_url` text NOT NULL,
  `cloudinary_public_id` varchar(255) DEFAULT NULL,
  `generated_by` varchar(150) NOT NULL,
  `generated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `immunization_records`
--

CREATE TABLE `immunization_records` (
  `immunization_id` int(11) NOT NULL,
  `upload_id` int(11) DEFAULT NULL,
  `student_record_id` int(11) DEFAULT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `grade_level` varchar(30) DEFAULT NULL,
  `vaccine` varchar(100) DEFAULT NULL,
  `dose` varchar(20) DEFAULT NULL,
  `immunized` tinyint(4) DEFAULT 0,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `local_accounts`
--

CREATE TABLE `local_accounts` (
  `account_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `okd_lhas_records`
--

CREATE TABLE `okd_lhas_records` (
  `okd_lhas_id` int(11) NOT NULL,
  `upload_id` int(11) DEFAULT NULL,
  `student_record_id` int(11) DEFAULT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `grade_level` varchar(30) DEFAULT NULL,
  `screening_type` varchar(100) DEFAULT NULL,
  `masterlisted` tinyint(4) DEFAULT 0,
  `screened` tinyint(4) DEFAULT 0,
  `findings` tinyint(4) DEFAULT 0,
  `referred_school` tinyint(4) DEFAULT 0,
  `referred_lgu` tinyint(4) DEFAULT 0,
  `referred_private` tinyint(4) DEFAULT 0,
  `referred_others` tinyint(4) DEFAULT 0,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `prediction_results`
--

CREATE TABLE `prediction_results` (
  `prediction_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `predicted_deficiency` varchar(150) DEFAULT NULL,
  `predicted_risk_level` varchar(50) DEFAULT NULL,
  `confidence_score` decimal(6,4) DEFAULT NULL,
  `algorithm_used` varchar(100) DEFAULT NULL,
  `prediction_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `recommendation_id` int(11) NOT NULL,
  `prediction_id` int(11) NOT NULL,
  `recommendation_text` text NOT NULL,
  `recommended_foods` text DEFAULT NULL,
  `intervention_type` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `report_saved_data`
--

CREATE TABLE `report_saved_data` (
  `report_id` int(11) NOT NULL,
  `report_key` varchar(50) NOT NULL COMMENT 'e.g., box2_3, box4, box5_6, box8_9, box10_11, table1_a, table1_b',
  `school_year` varchar(50) NOT NULL,
  `report_data` longtext NOT NULL COMMENT 'JSON data',
  `source_snapshot` longtext DEFAULT NULL COMMENT 'Source counts when this section was saved',
  `saved_by` varchar(150) NOT NULL,
  `saved_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `id` int(11) NOT NULL,
  `year_label` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `sf8_student_records`
--

CREATE TABLE `sf8_student_records` (
  `record_id` int(11) NOT NULL,
  `upload_id` int(11) DEFAULT NULL,
  `lrn` varchar(50) NOT NULL DEFAULT '',
  `school_name` varchar(150) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `division` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `school_id` varchar(50) DEFAULT NULL,
  `grade_level` varchar(50) DEFAULT NULL,
  `section` varchar(100) DEFAULT NULL,
  `track_strand` varchar(100) DEFAULT NULL,
  `school_year` varchar(50) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `profile_status` enum('Provisional','Complete') NOT NULL DEFAULT 'Complete',
  `is_muslim` tinyint(1) NOT NULL DEFAULT 0,
  `is_pwd` tinyint(1) NOT NULL DEFAULT 0,
  `is_ip` tinyint(1) NOT NULL DEFAULT 0,
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `height_m` decimal(6,3) DEFAULT NULL,
  `height_squared` decimal(8,4) DEFAULT NULL,
  `bmi` decimal(6,2) DEFAULT NULL,
  `bmi_category` varchar(50) DEFAULT NULL,
  `height_for_age` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `date_saved` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `sf8_uploads`
--

CREATE TABLE `sf8_uploads` (
  `upload_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `report_purpose` varchar(150) DEFAULT NULL,
  `cloudinary_public_id` varchar(255) DEFAULT NULL,
  `cloudinary_url` text NOT NULL,
  `uploaded_by_email` varchar(150) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `status` varchar(30) DEFAULT 'Pending',
  `reviewed_by` varchar(150) DEFAULT NULL,
  `reviewed_date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `report_code` varchar(100) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `student_health_inputs`
--

CREATE TABLE `student_health_inputs` (
  `input_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `diet_type` varchar(100) DEFAULT NULL,
  `living_environment` varchar(10) DEFAULT NULL,
  `skin_condition` varchar(30) DEFAULT NULL,
  `sun_exposure` varchar(100) DEFAULT NULL,
  `exercise_level` varchar(100) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `has_fatigue` varchar(10) DEFAULT 'No',
  `has_bone_pain` varchar(10) DEFAULT 'No',
  `has_bleeding_gums` varchar(10) DEFAULT 'No',
  `has_pale_skin` varchar(10) DEFAULT 'No',
  `has_night_blindness` varchar(10) DEFAULT 'No',
  `has_dry_eyes` varchar(10) DEFAULT NULL,
  `has_shortness_of_breath` varchar(10) DEFAULT NULL,
  `has_fast_heart_rate` varchar(10) DEFAULT NULL,
  `has_brittle_nails` varchar(10) DEFAULT NULL,
  `has_weight_loss` varchar(10) DEFAULT NULL,
  `has_reduced_wound_healing` varchar(10) DEFAULT NULL,
  `has_muscle_weakness` varchar(10) DEFAULT NULL,
  `has_numbness_tingling` varchar(10) DEFAULT NULL,
  `has_memory_problems` varchar(10) DEFAULT NULL,
  `has_multiple_deficiencies` varchar(10) DEFAULT NULL,
  `has_low_appetite` varchar(10) DEFAULT 'No',
  `has_irregular_meals` varchar(10) DEFAULT 'No',
  `has_weight_changes` varchar(10) DEFAULT 'No',
  `has_headache` varchar(10) DEFAULT 'No',
  `has_poor_concentration` varchar(10) DEFAULT 'No',
  `has_vision_problem` varchar(10) DEFAULT 'No',
  `has_hearing_problem` varchar(10) DEFAULT 'No',
  `has_dental_problem` varchar(10) DEFAULT 'No',
  `has_skin_problem` varchar(10) DEFAULT 'No',
  `has_breathing_problem` varchar(10) DEFAULT 'No',
  `has_recent_illness` varchar(10) DEFAULT 'No',
  `has_current_medication` varchar(10) DEFAULT 'No',
  `immunization_updated` varchar(20) DEFAULT 'Unknown',
  `has_known_allergy` varchar(10) DEFAULT 'No',
  `allergy_details` text DEFAULT NULL,
  `family_history_diabetes` varchar(10) DEFAULT 'No',
  `family_history_heart_disease` varchar(10) DEFAULT 'No',
  `family_history_anemia` varchar(10) DEFAULT 'No',
  `existing_medical_condition` text DEFAULT NULL,
  `needs_followup` varchar(10) DEFAULT 'No',
  `needs_referral` varchar(10) DEFAULT 'No',
  `clinic_notes` text DEFAULT NULL,
  `hemoglobin_g_dl` decimal(4,1) DEFAULT NULL,
  `serum_vitamin_d_ng_ml` decimal(6,2) DEFAULT NULL,
  `serum_vitamin_b12_pg_ml` decimal(8,2) DEFAULT NULL,
  `serum_folate_ng_ml` decimal(6,2) DEFAULT NULL,
  `lab_result_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- --------------------------------------------------------

--
-- Table structure for table `table1_health_nutrition_reports`
--

CREATE TABLE `table1_health_nutrition_reports` (
  `report_id` int(11) NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  `saved_by` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tobacco_control_records`
--

CREATE TABLE `tobacco_control_records` (
  `tobacco_id` int(11) NOT NULL,
  `upload_id` int(11) DEFAULT NULL,
  `student_record_id` int(11) DEFAULT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `grade_level` varchar(30) DEFAULT NULL,
  `violation_type` varchar(100) DEFAULT NULL,
  `referred_to_care` tinyint(4) DEFAULT 0,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


--
-- Indexes for dumped tables
--

--
-- Indexes for table `arh_records`
--
ALTER TABLE `arh_records`
  ADD PRIMARY KEY (`arh_record_id`),
  ADD UNIQUE KEY `idx_unique_arh` (`lrn`,`school_year`),
  ADD KEY `upload_id` (`upload_id`),
  ADD KEY `student_record_id` (`student_record_id`);

--
-- Indexes for table `box1_okd_lhas_reports`
--
ALTER TABLE `box1_okd_lhas_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `school_year` (`school_year`);

--
-- Indexes for table `box5_box6_reports`
--
ALTER TABLE `box5_box6_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `school_year` (`school_year`);

--
-- Indexes for table `deworming_wifa_records`
--
ALTER TABLE `deworming_wifa_records`
  ADD PRIMARY KEY (`deworming_wifa_id`),
  ADD UNIQUE KEY `idx_unique_deworming_wifa` (`lrn`,`school_year`),
  ADD KEY `upload_id` (`upload_id`),
  ADD KEY `student_record_id` (`student_record_id`);

--
-- Indexes for table `firebase_accounts`
--
ALTER TABLE `firebase_accounts`
  ADD PRIMARY KEY (`firebase_account_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `generated_reports`
--
ALTER TABLE `generated_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `immunization_records`
--
ALTER TABLE `immunization_records`
  ADD PRIMARY KEY (`immunization_id`),
  ADD UNIQUE KEY `idx_unique_immunization` (`lrn`,`school_year`,`vaccine`,`dose`),
  ADD KEY `upload_id` (`upload_id`),
  ADD KEY `student_record_id` (`student_record_id`);

--
-- Indexes for table `local_accounts`
--
ALTER TABLE `local_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `okd_lhas_records`
--
ALTER TABLE `okd_lhas_records`
  ADD PRIMARY KEY (`okd_lhas_id`),
  ADD UNIQUE KEY `idx_unique_okd_lhas` (`lrn`,`school_year`,`screening_type`),
  ADD KEY `upload_id` (`upload_id`),
  ADD KEY `student_record_id` (`student_record_id`);

--
-- Indexes for table `prediction_results`
--
ALTER TABLE `prediction_results`
  ADD PRIMARY KEY (`prediction_id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `prediction_id` (`prediction_id`);

--
-- Indexes for table `report_saved_data`
--
ALTER TABLE `report_saved_data`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `unique_report` (`report_key`,`school_year`);

--
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_year_label` (`year_label`);

--
-- Indexes for table `sf8_student_records`
--
ALTER TABLE `sf8_student_records`
  ADD PRIMARY KEY (`record_id`),
  ADD UNIQUE KEY `unique_lrn_school_year` (`lrn`,`school_year`),
  ADD KEY `upload_id` (`upload_id`);

--
-- Indexes for table `sf8_uploads`
--
ALTER TABLE `sf8_uploads`
  ADD PRIMARY KEY (`upload_id`);

--
-- Indexes for table `student_health_inputs`
--
ALTER TABLE `student_health_inputs`
  ADD PRIMARY KEY (`input_id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `table1_health_nutrition_reports`
--
ALTER TABLE `table1_health_nutrition_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `school_year` (`school_year`);

--
-- Indexes for table `tobacco_control_records`
--
ALTER TABLE `tobacco_control_records`
  ADD PRIMARY KEY (`tobacco_id`),
  ADD UNIQUE KEY `idx_unique_tobacco` (`lrn`,`school_year`),
  ADD KEY `upload_id` (`upload_id`),
  ADD KEY `student_record_id` (`student_record_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `arh_records`
--
ALTER TABLE `arh_records`
  MODIFY `arh_record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `box1_okd_lhas_reports`
--
ALTER TABLE `box1_okd_lhas_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `box5_box6_reports`
--
ALTER TABLE `box5_box6_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deworming_wifa_records`
--
ALTER TABLE `deworming_wifa_records`
  MODIFY `deworming_wifa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `firebase_accounts`
--
ALTER TABLE `firebase_accounts`
  MODIFY `firebase_account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `generated_reports`
--
ALTER TABLE `generated_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `immunization_records`
--
ALTER TABLE `immunization_records`
  MODIFY `immunization_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `local_accounts`
--
ALTER TABLE `local_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `okd_lhas_records`
--
ALTER TABLE `okd_lhas_records`
  MODIFY `okd_lhas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `prediction_results`
--
ALTER TABLE `prediction_results`
  MODIFY `prediction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `recommendations`
--
ALTER TABLE `recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `report_saved_data`
--
ALTER TABLE `report_saved_data`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sf8_student_records`
--
ALTER TABLE `sf8_student_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `sf8_uploads`
--
ALTER TABLE `sf8_uploads`
  MODIFY `upload_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `student_health_inputs`
--
ALTER TABLE `student_health_inputs`
  MODIFY `input_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `table1_health_nutrition_reports`
--
ALTER TABLE `table1_health_nutrition_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tobacco_control_records`
--
ALTER TABLE `tobacco_control_records`
  MODIFY `tobacco_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `arh_records`
--
ALTER TABLE `arh_records`
  ADD CONSTRAINT `arh_records_ibfk_1` FOREIGN KEY (`upload_id`) REFERENCES `sf8_uploads` (`upload_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `arh_records_ibfk_2` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE SET NULL;

--
-- Constraints for table `deworming_wifa_records`
--
ALTER TABLE `deworming_wifa_records`
  ADD CONSTRAINT `deworming_wifa_records_ibfk_1` FOREIGN KEY (`upload_id`) REFERENCES `sf8_uploads` (`upload_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deworming_wifa_records_ibfk_2` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE SET NULL;

--
-- Constraints for table `immunization_records`
--
ALTER TABLE `immunization_records`
  ADD CONSTRAINT `immunization_records_ibfk_1` FOREIGN KEY (`upload_id`) REFERENCES `sf8_uploads` (`upload_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `immunization_records_ibfk_2` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE SET NULL;

--
-- Constraints for table `okd_lhas_records`
--
ALTER TABLE `okd_lhas_records`
  ADD CONSTRAINT `okd_lhas_records_ibfk_1` FOREIGN KEY (`upload_id`) REFERENCES `sf8_uploads` (`upload_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `okd_lhas_records_ibfk_2` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE SET NULL;

--
-- Constraints for table `prediction_results`
--
ALTER TABLE `prediction_results`
  ADD CONSTRAINT `prediction_results_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE;

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`prediction_id`) REFERENCES `prediction_results` (`prediction_id`) ON DELETE CASCADE;

--
-- Constraints for table `sf8_student_records`
--
ALTER TABLE `sf8_student_records`
  ADD CONSTRAINT `sf8_student_records_ibfk_1` FOREIGN KEY (`upload_id`) REFERENCES `sf8_uploads` (`upload_id`);

--
-- Constraints for table `student_health_inputs`
--
ALTER TABLE `student_health_inputs`
  ADD CONSTRAINT `student_health_inputs_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE;

--
-- Constraints for table `tobacco_control_records`
--
ALTER TABLE `tobacco_control_records`
  ADD CONSTRAINT `tobacco_control_records_ibfk_1` FOREIGN KEY (`upload_id`) REFERENCES `sf8_uploads` (`upload_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tobacco_control_records_ibfk_2` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE SET NULL;

-- Nurse consultation observations and care given.
CREATE TABLE IF NOT EXISTS `consultations` (
  `consultation_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `record_id` int NOT NULL,
  `symptoms` text NOT NULL,
  `care_given` text NOT NULL,
  `follow_up_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by_account_id` int DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  KEY `idx_consultations_student_date` (`record_id`,`recorded_at`),
  CONSTRAINT `fk_consultation_student` FOREIGN KEY (`record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_consultation_nurse` FOREIGN KEY (`recorded_by_account_id`) REFERENCES `local_accounts` (`account_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- WIFA and deworming event histories. The original SF8 row remains the import baseline.
CREATE TABLE IF NOT EXISTS `wifa_events` (
  `wifa_event_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_record_id` int NOT NULL,
  `event_date` date NOT NULL,
  `outcome` enum('Given','Not given') NOT NULL,
  `reason_code` varchar(60) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by_account_id` int DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `uq_wifa_student_date` (`student_record_id`,`event_date`),
  KEY `idx_wifa_date` (`event_date`),
  CONSTRAINT `fk_wifa_student` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wifa_actor` FOREIGN KEY (`recorded_by_account_id`) REFERENCES `local_accounts` (`account_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `wifa_reviews` (
  `wifa_review_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_record_id` int NOT NULL,
  `decision` enum('Needs review','Continue','Paused','Completed','Not applicable') NOT NULL,
  `reason` text DEFAULT NULL,
  `next_review_date` date DEFAULT NULL,
  `hemoglobin_g_dl` decimal(4,1) DEFAULT NULL,
  `hemoglobin_date` date DEFAULT NULL,
  `reviewed_by_account_id` int DEFAULT NULL,
  `reviewed_at` datetime NOT NULL DEFAULT current_timestamp(),
  KEY `idx_wifa_review_student` (`student_record_id`,`wifa_review_id`),
  CONSTRAINT `fk_wifa_review_student` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wifa_review_actor` FOREIGN KEY (`reviewed_by_account_id`) REFERENCES `local_accounts` (`account_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `deworming_events` (
  `deworming_event_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_record_id` int NOT NULL,
  `event_date` date NOT NULL,
  `channel` enum('SBFP','Other') NOT NULL,
  `outcome` enum('Given','Not given') NOT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by_account_id` int DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `uq_deworming_student_date` (`student_record_id`,`event_date`),
  KEY `idx_deworming_date` (`event_date`),
  CONSTRAINT `fk_deworming_event_student` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_deworming_event_actor` FOREIGN KEY (`recorded_by_account_id`) REFERENCES `local_accounts` (`account_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `health_program_event_audit` (
  `audit_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_record_id` int NOT NULL,
  `program` enum('WIFA','Deworming') NOT NULL,
  `event_id` int NOT NULL,
  `action` enum('Created','Corrected') NOT NULL,
  `before_json` longtext DEFAULT NULL CHECK (`before_json` IS NULL OR JSON_VALID(`before_json`)),
  `after_json` longtext NOT NULL CHECK (JSON_VALID(`after_json`)),
  `actor_account_id` int DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp(),
  KEY `idx_program_audit_student` (`student_record_id`,`changed_at`),
  CONSTRAINT `fk_program_audit_student` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_program_audit_actor` FOREIGN KEY (`actor_account_id`) REFERENCES `local_accounts` (`account_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `feeding_measurements` (
  `measurement_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_record_id` int NOT NULL,
  `measured_on` date NOT NULL,
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `progress_status` enum('Monitoring','Improving','Recovered','Needs follow-up') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by_account_id` int DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `uq_feeding_student_date` (`student_record_id`,`measured_on`),
  KEY `idx_feeding_measurement_date` (`measured_on`),
  CONSTRAINT `fk_feeding_student` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feeding_actor` FOREIGN KEY (`recorded_by_account_id`) REFERENCES `local_accounts` (`account_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `feeding_measurement_audit` (
  `audit_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `measurement_id` int NOT NULL,
  `student_record_id` int NOT NULL,
  `action` enum('Created','Corrected') NOT NULL,
  `before_json` longtext DEFAULT NULL CHECK (`before_json` IS NULL OR JSON_VALID(`before_json`)),
  `after_json` longtext NOT NULL CHECK (JSON_VALID(`after_json`)),
  `actor_account_id` int DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp(),
  KEY `idx_feeding_audit_student` (`student_record_id`,`changed_at`),
  CONSTRAINT `fk_feeding_audit_student` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feeding_audit_actor` FOREIGN KEY (`actor_account_id`) REFERENCES `local_accounts` (`account_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS `report_saved_revisions` (
  `revision_id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `report_id` int NOT NULL,
  `report_key` varchar(50) NOT NULL,
  `school_year` varchar(50) NOT NULL,
  `report_data` longtext NOT NULL,
  `source_snapshot` longtext DEFAULT NULL,
  `saved_by` varchar(150) NOT NULL,
  `saved_at` datetime DEFAULT NULL,
  `action` enum('replaced','deleted') NOT NULL,
  `acted_by` varchar(150) NOT NULL,
  `acted_at` datetime NOT NULL DEFAULT current_timestamp(),
  KEY `idx_report_revision` (`report_key`,`school_year`,`acted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `guidance_counseling_visits` (
  `visit_id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_record_id` int NOT NULL,
  `visit_date` date NOT NULL,
  `recorded_by` varchar(150) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `uq_counseling_student_date` (`student_record_id`,`visit_date`),
  KEY `idx_counseling_date` (`visit_date`),
  CONSTRAINT `fk_counseling_student` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
