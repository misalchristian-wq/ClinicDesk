-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 29, 2026 at 02:11 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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
  `upload_id` int(11) NOT NULL,
  `student_record_id` int(11) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `pregnancy_status` varchar(50) DEFAULT NULL,
  `delivery_mode` varchar(100) DEFAULT NULL,
  `peer_educator` tinyint(4) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `date_saved` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `arh_records`
--

INSERT INTO `arh_records` (`arh_record_id`, `upload_id`, `student_record_id`, `learner_name`, `lrn`, `sex`, `birthdate`, `age`, `pregnancy_status`, `delivery_mode`, `peer_educator`, `remarks`, `date_saved`) VALUES
(1, 47, 37, 'Misal, Christian, J', '1286431000001', 'Male', '2004-12-12', '20', 'Not Pregnant', '', 1, '', '2026-05-28 01:58:18'),
(2, 47, 38, 'Rodriguez, Jojie G.', '1286431000002', 'Male', '2004-12-13', '20', 'Not Pregnant', '', 0, '', '2026-05-28 01:58:18'),
(3, 47, 39, 'Corpuz, Jerlyn P.', '1286431000003', 'Female', '2004-12-14', '20', 'Pregnant', '', 1, '', '2026-05-28 01:58:18'),
(4, 47, 40, 'Acedo, Jhon Patrick A.', '1286431000004', 'Male', '2004-12-15', '20', 'Pregnant', '', 0, '', '2026-05-28 01:58:18'),
(5, 47, 41, 'Marial, Aaliyah Angelica S.', '1286431000005', 'Female', '2004-12-16', '20', 'Not Pregnant', '', 1, '', '2026-05-28 01:58:18'),
(6, 47, 42, 'De Grace, Klarence A.', '1286431000006', 'Male', '2004-12-17', '20', 'Not Pregnant', '', 1, '', '2026-05-28 01:58:18'),
(7, 47, 43, 'Indig, Ruby Jean V.', '1286431000007', 'Female', '2004-12-18', '20', 'Not Pregnant', 'In School', 0, '', '2026-05-28 01:58:18'),
(8, 47, 44, 'Yamid, Kenneth James G.', '1286431000008', 'Male', '2004-12-19', '20', 'Not Pregnant', '', 1, '', '2026-05-28 01:58:18'),
(9, 47, 45, 'Gejapon, Karyl B.', '1286431000009', 'Female', '2004-12-20', '20', 'Pregnant', 'ADM', 0, '', '2026-05-28 01:58:18');

-- --------------------------------------------------------

--
-- Table structure for table `box1_okd_lhas_reports`
--

CREATE TABLE `box1_okd_lhas_reports` (
  `report_id` int(11) NOT NULL,
  `school_year` varchar(50) DEFAULT 'default',
  `report_data` longtext NOT NULL,
  `saved_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deworming_wifa_records`
--

CREATE TABLE `deworming_wifa_records` (
  `deworming_wifa_id` int(11) NOT NULL,
  `upload_id` int(11) NOT NULL,
  `student_record_id` int(11) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `dewormed_sbfp` tinyint(1) DEFAULT 0,
  `dewormed_other` tinyint(1) DEFAULT 0,
  `wifa` tinyint(1) DEFAULT 0,
  `wifa_date` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deworming_wifa_records`
--

INSERT INTO `deworming_wifa_records` (`deworming_wifa_id`, `upload_id`, `student_record_id`, `learner_name`, `lrn`, `sex`, `birthdate`, `age`, `dewormed_sbfp`, `dewormed_other`, `wifa`, `wifa_date`, `remarks`, `created_at`) VALUES
(28, 40, 37, 'Misal, Christian, J', '1286431000001', 'Male', '2004-12-12', '20', 1, 0, 0, '', '', '2026-05-28 02:05:36'),
(29, 40, 38, 'Rodriguez, Jojie G.', '1286431000002', 'Male', '2004-12-13', '20', 1, 0, 1, '2026-07-15', '', '2026-05-28 02:05:36'),
(30, 40, 39, 'Corpuz, Jerlyn P.', '1286431000003', 'Female', '2004-12-14', '20', 0, 1, 1, '', '', '2026-05-28 02:05:36'),
(31, 40, 40, 'Acedo, Jhon Patrick A.', '1286431000004', 'Male', '2004-12-15', '20', 1, 0, 1, '2026-07-18', '', '2026-05-28 02:05:36'),
(32, 40, 41, 'Marial, Aaliyah Angelica S.', '1286431000005', 'Female', '2004-12-16', '20', 1, 0, 0, '', '', '2026-05-28 02:05:36'),
(33, 40, 42, 'De Grace, Klarence A.', '1286431000006', 'Male', '2004-12-17', '20', 0, 1, 1, '2026-07-20', '', '2026-05-28 02:05:36'),
(34, 40, 43, 'Indig, Ruby Jean V.', '1286431000007', 'Female', '2004-12-18', '20', 1, 0, 0, '', '', '2026-05-28 02:05:36'),
(35, 40, 44, 'Yamid, Kenneth James G.', '1286431000008', 'Male', '2004-12-19', '20', 1, 0, 1, '2026-07-22', '', '2026-05-28 02:05:36'),
(36, 40, 45, 'Gejapon, Karyl B.', '1286431000009', 'Female', '2004-12-20', '20', 0, 1, 1, '', '', '2026-05-28 02:05:36');

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

-- --------------------------------------------------------

--
-- Table structure for table `immunization_records`
--

CREATE TABLE `immunization_records` (
  `immunization_id` int(11) NOT NULL,
  `upload_id` int(11) NOT NULL,
  `student_record_id` int(11) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `vaccine` varchar(100) DEFAULT NULL,
  `dose` varchar(20) DEFAULT NULL,
  `immunized` tinyint(1) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `immunization_records`
--

INSERT INTO `immunization_records` (`immunization_id`, `upload_id`, `student_record_id`, `learner_name`, `lrn`, `sex`, `birthdate`, `age`, `vaccine`, `dose`, `immunized`, `remarks`, `created_at`) VALUES
(10, 42, 37, 'Misal, Christian, J', '1286431000001', 'Male', '2004-12-12', '20', 'Tetanus Diphtheria', '1', 1, '', '2026-05-28 02:16:07'),
(11, 42, 38, 'Rodriguez, Jojie G.', '1286431000002', 'Male', '2004-12-13', '20', 'HPV', '1', 1, '', '2026-05-28 02:16:07'),
(12, 42, 39, 'Corpuz, Jerlyn P.', '1286431000003', 'Female', '2004-12-14', '20', 'Tetanus Diphtheria', '2', 1, '', '2026-05-28 02:16:07'),
(13, 42, 40, 'Acedo, Jhon Patrick A.', '1286431000004', 'Male', '2004-12-15', '20', 'HPV', '2', 0, '', '2026-05-28 02:16:07'),
(14, 42, 41, 'Marial, Aaliyah Angelica S.', '1286431000005', 'Female', '2004-12-16', '20', 'Tetanus Diphtheria', '1', 1, '', '2026-05-28 02:16:07'),
(15, 42, 42, 'De Grace, Klarence A.', '1286431000006', 'Male', '2004-12-17', '20', 'HPV', '2', 1, '', '2026-05-28 02:16:07'),
(16, 42, 43, 'Indig, Ruby Jean V.', '1286431000007', 'Female', '2004-12-18', '20', 'Tetanus Diphtheria', '1', 0, '', '2026-05-28 02:16:07'),
(17, 42, 44, 'Yamid, Kenneth James G.', '1286431000008', 'Male', '2004-12-19', '20', 'HPV', '2', 1, '', '2026-05-28 02:16:07'),
(18, 42, 45, 'Gejapon, Karyl B.', '1286431000009', 'Female', '2004-12-20', '20', 'Tetanus Diphtheria', '1', 1, '', '2026-05-28 02:16:07');

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
-- Dumping data for table `local_accounts`
--

INSERT INTO `local_accounts` (`account_id`, `full_name`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
(2, 'Christian Misal', 'itadmin@gmail.com', '$2y$10$CccNE.F.LV6j66JrJMH.T.DvhxWqt1ImjPOJp.vDMbcXFtOtf43Hi', 'IT Admin', 'Active', '2026-05-13 17:24:53'),
(3, 'Karyl B Gejapon', 'nurse@gmail.com', '$2y$10$B53Ulsltn2gG2l6LjdBKQulpUFYCDJ6QCKqyfJjKk/CspLqn2rEJG', 'Clinic Nurse', 'Active', '2026-05-13 17:25:44'),
(4, 'Aaliyah Angelica Marial', 'marial@gmail.com', '$2y$10$IQjKpfmpr7bjl7u9YLm/IuikSnDaPc4qFanaAQVi6ZsdhedDgdUIu', 'Clinic Nurse', 'Active', '2026-05-13 20:03:22'),
(5, 'Cyrel Morales', 'principal@gmail.com', '$2y$10$9LEbmsNj2jBkArbQnEJnqOOca53XGdl2IwMQCtIfkzBy9XkhcbKr.', 'School Admin', 'Active', '2026-05-17 00:00:35');

-- --------------------------------------------------------

--
-- Table structure for table `okd_lhas_records`
--

CREATE TABLE `okd_lhas_records` (
  `okd_lhas_id` int(11) NOT NULL,
  `upload_id` int(11) NOT NULL,
  `student_record_id` int(11) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `screening_type` varchar(150) DEFAULT NULL,
  `masterlisted` tinyint(1) DEFAULT 0,
  `screened` tinyint(1) DEFAULT 0,
  `findings` tinyint(1) DEFAULT 0,
  `referred_school` tinyint(1) DEFAULT 0,
  `referred_lgu` tinyint(1) DEFAULT 0,
  `referred_private` tinyint(1) DEFAULT 0,
  `referred_others` tinyint(1) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `okd_lhas_records`
--

INSERT INTO `okd_lhas_records` (`okd_lhas_id`, `upload_id`, `student_record_id`, `learner_name`, `lrn`, `sex`, `birthdate`, `age`, `screening_type`, `masterlisted`, `screened`, `findings`, `referred_school`, `referred_lgu`, `referred_private`, `referred_others`, `remarks`, `created_at`) VALUES
(19, 45, 37, 'Misal, Christian, J', '1286431000001', 'Male', '2004-12-12', '20', 'Nutritional Assessment', 1, 1, 0, 0, 0, 0, 0, 'No findings', '2026-05-28 02:11:23'),
(20, 45, 38, 'Rodriguez, Jojie G.', '1286431000002', 'Male', '2004-12-13', '20', 'Health History', 1, 1, 0, 1, 0, 0, 0, 'Health history concern', '2026-05-28 02:11:23'),
(21, 45, 39, 'Corpuz, Jerlyn P.', '1286431000003', 'Female', '2004-12-14', '20', 'Vision Screening', 1, 1, 0, 1, 1, 0, 0, 'Needs vision referral', '2026-05-28 02:11:23'),
(22, 45, 40, 'Acedo, Jhon Patrick A.', '1286431000004', 'Male', '2004-12-15', '20', 'Hearing Screening', 1, 1, 0, 0, 0, 0, 0, 'No hearing issue', '2026-05-28 02:11:23'),
(23, 45, 41, 'Marial, Aaliyah Angelica S.', '1286431000005', 'Female', '2004-12-16', '20', 'Oral Health', 1, 1, 0, 1, 0, 1, 0, 'Dental concern', '2026-05-28 02:11:23'),
(24, 45, 42, 'De Grace, Klarence A.', '1286431000006', 'Male', '2004-12-17', '20', 'CARS', 1, 1, 0, 0, 0, 0, 0, 'CARS completed', '2026-05-28 02:11:23'),
(25, 45, 43, 'Indig, Ruby Jean V.', '1286431000007', 'Female', '2004-12-18', '20', 'Rapid HEEADSSS', 1, 1, 0, 1, 0, 0, 0, 'Needs school counseling referral', '2026-05-28 02:11:23'),
(26, 45, 44, 'Yamid, Kenneth James G.', '1286431000008', 'Male', '2004-12-19', '20', 'Nutritional Assessment', 1, 1, 0, 0, 0, 0, 0, 'Normal nutritional screening', '2026-05-28 02:11:23'),
(27, 45, 45, 'Gejapon, Karyl B.', '1286431000009', 'Female', '2004-12-20', '20', 'Vision Screening', 1, 1, 0, 1, 1, 0, 0, 'Vision concern noted', '2026-05-28 02:11:23');

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

-- --------------------------------------------------------

--
-- Table structure for table `sf8_student_records`
--

CREATE TABLE `sf8_student_records` (
  `record_id` int(11) NOT NULL,
  `lrn` bigint(20) NOT NULL,
  `upload_id` int(11) NOT NULL,
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
  `sex` varchar(10) NOT NULL,
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
-- Dumping data for table `sf8_student_records`
--

INSERT INTO `sf8_student_records` (`record_id`, `lrn`, `upload_id`, `school_name`, `district`, `division`, `region`, `school_id`, `grade_level`, `section`, `track_strand`, `school_year`, `learner_name`, `birthdate`, `age`, `sex`, `weight_kg`, `height_m`, `height_squared`, `bmi`, `bmi_category`, `height_for_age`, `remarks`, `date_saved`) VALUES
(37, 1286431000001, 30, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Misal, Christian, J', '38333', '20', 'Male', 70.00, 1.524, 2.3226, 30.14, '0', '1.524', '0', '2026-05-28 01:20:50'),
(38, 1286431000002, 30, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Rodriguez, Jojie G.', '38334', '20', 'Male', 35.00, 1.524, 2.3226, 15.07, '0', '1.524', '0', '2026-05-28 01:20:50'),
(39, 1286431000003, 30, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Corpuz, Jerlyn P.', '38335', '20', 'Female', 60.00, 1.524, 2.3226, 25.83, '0', '1.524', '0', '2026-05-28 01:20:50'),
(40, 1286431000004, 30, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Acedo, Jhon Patrick A.', '38336', '20', 'Male', 86.00, 1.524, 2.3226, 37.03, '0', '1.524', '0', '2026-05-28 01:20:50'),
(41, 1286431000005, 30, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Marial, Aaliyah Angelica S.', '38337', '20', 'Female', 38.00, 1.524, 2.3226, 16.36, '0', '1.524', '0', '2026-05-28 01:20:50'),
(42, 1286431000006, 30, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'De Grace, Klarence A.', '38338', '20', 'Male', 77.00, 1.524, 2.3226, 33.15, '0', '1.524', '0', '2026-05-28 01:20:50'),
(43, 1286431000007, 30, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Indig, Ruby Jean V.', '38339', '20', 'Female', 76.00, 1.524, 2.3226, 32.72, '0', '1.524', '0', '2026-05-28 01:20:50'),
(44, 1286431000008, 30, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Yamid, Kenneth James G.', '38340', '20', 'Male', 40.00, 1.524, 2.3226, 17.22, '0', '1.524', '0', '2026-05-28 01:20:50'),
(45, 1286431000009, 30, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Gejapon, Karyl B.', '38341', '20', 'Female', 65.00, 1.524, 2.3226, 27.99, '0', '1.524', '0', '2026-05-28 01:20:50'),
(46, 1286431000010, 50, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Geraldo, Kent Diether A.', '2004-12-12', '20', 'Male', 70.00, 1.524, 2.3226, 30.14, 'Obese', '1.524', '', '2026-05-28 02:44:15'),
(47, 1286431000011, 50, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Duran, Christian Dave S.', '2005-12-13', '20', 'Male', 35.00, 1.524, 2.3226, 15.07, 'Severely Wasted', '1.524', '', '2026-05-28 02:44:15'),
(48, 1286431000012, 50, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Elevira, Herald Jay A.', '2004-12-14', '20', 'Male', 60.00, 1.524, 2.3226, 25.83, 'Overweight', '1.524', '', '2026-05-28 02:44:15'),
(49, 1286431000013, 50, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Paquinol, Joseph Claire L.', '2002-12-15', '20', 'Male', 86.00, 1.524, 2.3226, 37.03, 'Obese', '1.524', '', '2026-05-28 02:44:15'),
(50, 1286431000014, 50, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Lozada, John Lyold A.', '2004-12-16', '20', 'Male', 38.00, 1.524, 2.3226, 16.36, 'Wasted', '1.524', '', '2026-05-28 02:44:15'),
(51, 1286431000015, 50, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Villar, Romeo Sylwen A.', '2004-12-17', '20', 'Male', 77.00, 1.524, 2.3226, 33.15, 'Obese', '1.524', '', '2026-05-28 02:44:15'),
(52, 1286431000016, 50, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Jalique, Kyla S.', '2001-12-18', '20', 'Female', 76.00, 1.524, 2.3226, 32.72, 'Obese', '1.524', '', '2026-05-28 02:44:15'),
(53, 1286431000017, 50, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Morales, Cyrel Jave M.', '2004-12-19', '20', 'Male', 40.00, 1.524, 2.3226, 17.22, 'Wasted', '1.524', '', '2026-05-28 02:44:15'),
(54, 1286431000018, 50, 'Tubod', 'Carmen', 's', '11', '128643', '12', 'mabii', 'css', '2021', 'Lico, Kenneth S.', '2003-12-20', '20', 'Male', 55.00, 1.612, 2.5985, 21.17, 'Normal', '1.524', '', '2026-05-28 02:44:15');

-- --------------------------------------------------------

--
-- Table structure for table `sf8_uploads`
--

CREATE TABLE `sf8_uploads` (
  `upload_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `cloudinary_public_id` varchar(255) DEFAULT NULL,
  `cloudinary_url` text NOT NULL,
  `uploaded_by_email` varchar(150) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `status` varchar(30) DEFAULT 'Pending',
  `reviewed_by` varchar(150) DEFAULT NULL,
  `reviewed_date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `report_purpose` varchar(100) DEFAULT NULL,
  `report_code` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sf8_uploads`
--

INSERT INTO `sf8_uploads` (`upload_id`, `file_name`, `cloudinary_public_id`, `cloudinary_url`, `uploaded_by_email`, `upload_date`, `status`, `reviewed_by`, `reviewed_date`, `remarks`, `file_type`, `report_purpose`, `report_code`) VALUES
(29, 'SF8_StudentInformation.xlsx', 'fuo47thept7zxrem15bf.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779899415/fuo47thept7zxrem15bf.xlsx', 'teacher@gmail.com', '2026-05-28 00:30:15', 'Pending', NULL, NULL, NULL, 'xlsx', 'students_information', 'students_information'),
(30, 'SF8_StudentInformation.xlsx', 'np0abyzce0zgfdvmmle3.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779901345/np0abyzce0zgfdvmmle3.xlsx', 'teacher@gmail.com', '2026-05-28 01:02:26', 'Approved', 'Clinic Nurse', '2026-05-28 01:20:50', 'Student Information approved. Saved 9 records. Skipped 0 records.', 'xlsx', 'students_information', 'students_information'),
(31, 'SF8_StudentInformation.xlsx', 'yt5z4kbj88iibbw4pum8.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779902613/yt5z4kbj88iibbw4pum8.xlsx', 'teacher@gmail.com', '2026-05-28 01:23:34', 'Approved', 'Clinic Nurse', '2026-05-28 01:23:55', 'Student Information approved. Saved 0 records. Skipped 9 records. Reasons: LRN 1286431000001 already exists in database; LRN 1286431000002 already exists in database; LRN 1286431000003 already exists in database; LRN 1286431000004 already exists in database; LRN 1286431000005 already exists in database ... and more.', 'xlsx', 'students_information', 'students_information'),
(32, 'SF8_StudentInformation.xlsx', 'faynkscxslkgok0xnexg.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779902792/faynkscxslkgok0xnexg.xlsx', 'teacher@gmail.com', '2026-05-28 01:26:32', 'Approved', 'Clinic Nurse', '2026-05-28 01:27:18', 'Student Information approved. Saved 0 records. Skipped 9 records. Reasons: LRN 1286431000001 already exists in database; LRN 1286431000002 already exists in database; LRN 1286431000003 already exists in database; LRN 1286431000004 already exists in database; LRN 1286431000005 already exists in database ... and more.', 'xlsx', 'students_information', 'students_information'),
(33, 'SF8_StudentInformation.xlsx', 'us8hfnhgnulr2so6tdfq.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779902808/us8hfnhgnulr2so6tdfq.xlsx', 'teacher@gmail.com', '2026-05-28 01:26:48', 'Pending', NULL, NULL, NULL, 'xlsx', 'students_information', 'students_information'),
(34, 'adolescent_reproductive_health_arh.xlsx', 'keyuw4r6cspvtxfodqo6.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903061/keyuw4r6cspvtxfodqo6.xlsx', 'teacher@gmail.com', '2026-05-28 01:31:02', 'Approved', 'Clinic Nurse', '2026-05-28 01:37:25', NULL, 'xlsx', 'adolescent_reproductive_health_arh', 'adolescent_reproductive_health_arh'),
(35, 'adolescent_reproductive_health_arh.xlsx', 'fufcx1z8iznmo3uq8wd4.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903073/fufcx1z8iznmo3uq8wd4.xlsx', 'teacher@gmail.com', '2026-05-28 01:31:13', 'Approved', 'Clinic Nurse', '2026-05-28 01:50:39', 'Adolescent Reproductive Health approved. Saved 9 records. Skipped 0 records.', 'xlsx', 'adolescent_reproductive_health_arh', 'adolescent_reproductive_health_arh'),
(36, 'adolescent_reproductive_health_arh.xlsx', 'hcxyae15pw8t0u2mzn4t.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903266/hcxyae15pw8t0u2mzn4t.xlsx', 'teacher@gmail.com', '2026-05-28 01:34:27', 'Approved', 'Clinic Nurse', '2026-05-28 01:51:37', 'Adolescent Reproductive Health approved. Saved 9 records. Skipped 0 records.', 'xlsx', 'adolescent_reproductive_health_arh', 'adolescent_reproductive_health_arh'),
(37, 'adolescent_reproductive_health_arh.xlsx', 'aivc8jqqcnelvbmxhctq.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903282/aivc8jqqcnelvbmxhctq.xlsx', 'teacher@gmail.com', '2026-05-28 01:34:42', 'Approved', 'Clinic Nurse', '2026-05-28 01:52:08', 'Adolescent Reproductive Health approved. Saved 9 records. Skipped 0 records.', 'xlsx', 'adolescent_reproductive_health_arh', 'adolescent_reproductive_health_arh'),
(38, 'comprehensive_tobacco_control.xlsx', 'jtc4fora7we41crtsldw.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903289/jtc4fora7we41crtsldw.xlsx', 'teacher@gmail.com', '2026-05-28 01:34:49', 'Approved', 'Clinic Nurse', '2026-05-28 02:01:37', 'Comprehensive Tobacco Control approved. Saved 9 records. Skipped 0 records.', 'xlsx', 'comprehensive_tobacco_control', 'comprehensive_tobacco_control'),
(39, 'comprehensive_tobacco_control.xlsx', 'cgbkk12hyyrjnxrtgyvu.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903305/cgbkk12hyyrjnxrtgyvu.xlsx', 'teacher@gmail.com', '2026-05-28 01:35:06', 'Pending', NULL, NULL, NULL, 'xlsx', 'comprehensive_tobacco_control', 'comprehensive_tobacco_control'),
(40, 'SF8_Deworming & WIFA.xlsx', 'rc2a67ozm3gz3xssmj8n.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903319/rc2a67ozm3gz3xssmj8n.xlsx', 'teacher@gmail.com', '2026-05-28 01:35:20', 'Approved', 'Clinic Nurse', '2026-05-28 02:05:36', 'Deworming & WIFA approved. Saved 9 records. Skipped 0 records.', 'xlsx', 'deworming_wifa', 'deworming_wifa'),
(41, 'SF8_Deworming & WIFA.xlsx', 'pa3ikxdz1qiltmzabmqh.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903330/pa3ikxdz1qiltmzabmqh.xlsx', 'teacher@gmail.com', '2026-05-28 01:35:31', 'Pending', NULL, NULL, NULL, 'xlsx', 'deworming_wifa', 'deworming_wifa'),
(42, 'SF8_immunization_nutritional_status.xlsx', 'a7wn1h7fiasud2lg8sbn.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903340/a7wn1h7fiasud2lg8sbn.xlsx', 'teacher@gmail.com', '2026-05-28 01:35:40', 'Approved', 'Clinic Nurse', '2026-05-28 02:16:07', 'Immunization approved. Saved 9 records. Skipped 0 records.', 'xlsx', 'immunization_nutritional_status', 'immunization_nutritional_status'),
(43, 'SF8_immunization_nutritional_status.xlsx', 'zrovmapafe6gi9u4fbju.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903350/zrovmapafe6gi9u4fbju.xlsx', 'teacher@gmail.com', '2026-05-28 01:35:51', 'Pending', NULL, NULL, NULL, 'xlsx', 'immunization_nutritional_status', 'immunization_nutritional_status'),
(44, 'SF8_immunization_nutritional_status.xlsx', 'khwocvevmluzhasu4dnh.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903364/khwocvevmluzhasu4dnh.xlsx', 'teacher@gmail.com', '2026-05-28 01:36:04', 'Pending', NULL, NULL, NULL, 'xlsx', 'immunization_nutritional_status', 'immunization_nutritional_status'),
(45, 'SF8_OKD_LHAS.xlsx', 'qgcub7bgw56n9rhuez5g.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903374/qgcub7bgw56n9rhuez5g.xlsx', 'teacher@gmail.com', '2026-05-28 01:36:15', 'Approved', 'Clinic Nurse', '2026-05-28 02:11:23', 'OKD and LHAS approved. Saved 9 records. Skipped 0 records.', 'xlsx', 'okd_lhas', 'okd_lhas'),
(46, 'SF8_OKD_LHAS.xlsx', 'a0ctjbrxjiuo287wfkjo.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779903390/a0ctjbrxjiuo287wfkjo.xlsx', 'teacher@gmail.com', '2026-05-28 01:36:31', 'Pending', NULL, NULL, NULL, 'xlsx', 'okd_lhas', 'okd_lhas'),
(47, 'adolescent_reproductive_health_arh.xlsx', 'vyztmutnagrxk8imqsew.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779904667/vyztmutnagrxk8imqsew.xlsx', 'teacher@gmail.com', '2026-05-28 01:57:47', 'Approved', 'Clinic Nurse', '2026-05-28 01:58:18', 'Adolescent Reproductive Health approved. Saved 9 records. Skipped 0 records.', 'xlsx', 'adolescent_reproductive_health_arh', 'adolescent_reproductive_health_arh'),
(48, 'adolescent_reproductive_health_arh.xlsx', 'heasyqwy2qgarwbimzp3.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779904675/heasyqwy2qgarwbimzp3.xlsx', 'teacher@gmail.com', '2026-05-28 01:57:56', 'Pending', NULL, NULL, NULL, 'xlsx', 'adolescent_reproductive_health_arh', 'adolescent_reproductive_health_arh'),
(49, 'SF8_StudentInformation2.xlsx', 'ajxwcfcqzskxihsgrxur.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779906775/ajxwcfcqzskxihsgrxur.xlsx', 'teacher@gmail.com', '2026-05-28 02:32:56', 'Pending', NULL, NULL, NULL, 'xlsx', 'students_information', 'students_information'),
(50, 'SF8_StudentInformation2.xlsx', 'j764h3sjnwogx3bbz8rx.xlsx', 'https://res.cloudinary.com/du3qpurjj/raw/upload/v1779906822/j764h3sjnwogx3bbz8rx.xlsx', 'teacher@gmail.com', '2026-05-28 02:33:43', 'Approved', 'Clinic Nurse', '2026-05-28 02:44:15', 'Student Information approved. Saved 9 records. Skipped 0 records.', 'xlsx', 'students_information', 'students_information');

-- --------------------------------------------------------

--
-- Table structure for table `student_health_inputs`
--

CREATE TABLE `student_health_inputs` (
  `input_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `diet_type` varchar(100) DEFAULT NULL,
  `sun_exposure` varchar(100) DEFAULT NULL,
  `exercise_level` varchar(100) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `has_fatigue` varchar(10) DEFAULT 'No',
  `has_bone_pain` varchar(10) DEFAULT 'No',
  `has_bleeding_gums` varchar(10) DEFAULT 'No',
  `has_pale_skin` varchar(10) DEFAULT 'No',
  `has_night_blindness` varchar(10) DEFAULT 'No',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
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
  `needs_referral` varchar(10) DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tobacco_control_records`
--

CREATE TABLE `tobacco_control_records` (
  `tobacco_control_id` int(11) NOT NULL,
  `upload_id` int(11) NOT NULL,
  `student_record_id` int(11) DEFAULT NULL,
  `learner_name` varchar(150) NOT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birthdate` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `violation_type` varchar(100) DEFAULT NULL,
  `referred_to_care` tinyint(1) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tobacco_control_records`
--

INSERT INTO `tobacco_control_records` (`tobacco_control_id`, `upload_id`, `student_record_id`, `learner_name`, `lrn`, `sex`, `birthdate`, `age`, `violation_type`, `referred_to_care`, `remarks`, `created_at`) VALUES
(10, 38, 37, 'Misal, Christian, J', '1286431000001', 'Male', '2004-12-12', '20', 'None', 0, '', '2026-05-28 02:01:37'),
(11, 38, 38, 'Rodriguez, Jojie G.', '1286431000002', 'Male', '2004-12-13', '20', 'Brought vape', 1, '', '2026-05-28 02:01:37'),
(12, 38, 39, 'Corpuz, Jerlyn P.', '1286431000003', 'Female', '2004-12-14', '20', 'None', 0, '', '2026-05-28 02:01:37'),
(13, 38, 40, 'Acedo, Jhon Patrick A.', '1286431000004', 'Male', '2004-12-15', '20', 'Brought tobacco', 1, '', '2026-05-28 02:01:37'),
(14, 38, 41, 'Marial, Aaliyah Angelica S.', '1286431000005', 'Female', '2004-12-16', '20', 'None', 0, '', '2026-05-28 02:01:37'),
(15, 38, 42, 'De Grace, Klarence A.', '1286431000006', 'Male', '2004-12-17', '20', 'None', 0, '', '2026-05-28 02:01:37'),
(16, 38, 43, 'Indig, Ruby Jean V.', '1286431000007', 'Female', '2004-12-18', '20', 'Brought vape', 1, '', '2026-05-28 02:01:37'),
(17, 38, 44, 'Yamid, Kenneth James G.', '1286431000008', 'Male', '2004-12-19', '20', 'None', 0, '', '2026-05-28 02:01:37'),
(18, 38, 45, 'Gejapon, Karyl B.', '1286431000009', 'Female', '2004-12-20', '20', 'Brought tobacco', 1, '', '2026-05-28 02:01:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `arh_records`
--
ALTER TABLE `arh_records`
  ADD PRIMARY KEY (`arh_record_id`),
  ADD KEY `upload_id` (`upload_id`),
  ADD KEY `student_record_id` (`student_record_id`);

--
-- Indexes for table `box1_okd_lhas_reports`
--
ALTER TABLE `box1_okd_lhas_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `unique_box1_school_year` (`school_year`);

--
-- Indexes for table `deworming_wifa_records`
--
ALTER TABLE `deworming_wifa_records`
  ADD PRIMARY KEY (`deworming_wifa_id`),
  ADD UNIQUE KEY `unique_deworming_upload_student` (`upload_id`,`learner_name`,`birthdate`),
  ADD UNIQUE KEY `unique_deworming_upload_learner` (`upload_id`,`learner_name`,`birthdate`),
  ADD KEY `fk_deworming_student_record` (`student_record_id`);

--
-- Indexes for table `firebase_accounts`
--
ALTER TABLE `firebase_accounts`
  ADD PRIMARY KEY (`firebase_account_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `immunization_records`
--
ALTER TABLE `immunization_records`
  ADD PRIMARY KEY (`immunization_id`),
  ADD UNIQUE KEY `unique_immunization_upload_student` (`upload_id`,`learner_name`,`vaccine`,`dose`),
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
  ADD UNIQUE KEY `unique_okd_upload_student` (`upload_id`,`learner_name`,`screening_type`),
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
-- Indexes for table `sf8_student_records`
--
ALTER TABLE `sf8_student_records`
  ADD PRIMARY KEY (`record_id`),
  ADD UNIQUE KEY `unique_lrn` (`lrn`),
  ADD KEY `fk_sf8_student_upload` (`upload_id`);

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
-- Indexes for table `tobacco_control_records`
--
ALTER TABLE `tobacco_control_records`
  ADD PRIMARY KEY (`tobacco_control_id`),
  ADD UNIQUE KEY `unique_tobacco_upload_student` (`upload_id`,`learner_name`,`violation_type`),
  ADD KEY `student_record_id` (`student_record_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `arh_records`
--
ALTER TABLE `arh_records`
  MODIFY `arh_record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `box1_okd_lhas_reports`
--
ALTER TABLE `box1_okd_lhas_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `deworming_wifa_records`
--
ALTER TABLE `deworming_wifa_records`
  MODIFY `deworming_wifa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `firebase_accounts`
--
ALTER TABLE `firebase_accounts`
  MODIFY `firebase_account_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `immunization_records`
--
ALTER TABLE `immunization_records`
  MODIFY `immunization_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `local_accounts`
--
ALTER TABLE `local_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `okd_lhas_records`
--
ALTER TABLE `okd_lhas_records`
  MODIFY `okd_lhas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `prediction_results`
--
ALTER TABLE `prediction_results`
  MODIFY `prediction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `recommendations`
--
ALTER TABLE `recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sf8_student_records`
--
ALTER TABLE `sf8_student_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `sf8_uploads`
--
ALTER TABLE `sf8_uploads`
  MODIFY `upload_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `student_health_inputs`
--
ALTER TABLE `student_health_inputs`
  MODIFY `input_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tobacco_control_records`
--
ALTER TABLE `tobacco_control_records`
  MODIFY `tobacco_control_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
  ADD CONSTRAINT `fk_deworming_student_record` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_deworming_upload` FOREIGN KEY (`upload_id`) REFERENCES `sf8_uploads` (`upload_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `prediction_results_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`prediction_id`) REFERENCES `prediction_results` (`prediction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sf8_student_records`
--
ALTER TABLE `sf8_student_records`
  ADD CONSTRAINT `fk_sf8_student_upload` FOREIGN KEY (`upload_id`) REFERENCES `sf8_uploads` (`upload_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_health_inputs`
--
ALTER TABLE `student_health_inputs`
  ADD CONSTRAINT `student_health_inputs_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tobacco_control_records`
--
ALTER TABLE `tobacco_control_records`
  ADD CONSTRAINT `tobacco_control_records_ibfk_1` FOREIGN KEY (`upload_id`) REFERENCES `sf8_uploads` (`upload_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tobacco_control_records_ibfk_2` FOREIGN KEY (`student_record_id`) REFERENCES `sf8_student_records` (`record_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
CREATE TABLE IF NOT EXISTS report_saved_data (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    report_key VARCHAR(50) NOT NULL COMMENT 'e.g., box2_3, box4, box5_6, box8_9, box10_11, table1_a, table1_b',
    school_year VARCHAR(50) NOT NULL,
    report_data LONGTEXT NOT NULL COMMENT 'JSON data',
    saved_by VARCHAR(150) NOT NULL,
    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_report (report_key, school_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS generated_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    school_year VARCHAR(50) NOT NULL,
    report_type VARCHAR(50) DEFAULT 'consolidated',
    cloudinary_url TEXT NOT NULL,
    cloudinary_public_id VARCHAR(255),
    generated_by VARCHAR(150) NOT NULL,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);