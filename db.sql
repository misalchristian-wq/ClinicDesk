CREATE DATABASE IF NOT EXISTS clinicdesk;
USE clinicdesk;

-- =========================
-- LOCAL XAMPP ACCOUNTS
-- For Clinic Nurse, School Admin, and IT Admin
-- =========================
CREATE TABLE IF NOT EXISTS local_accounts (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- FIREBASE ACCOUNT REFERENCES
-- Optional/local reference only.
-- Real teacher accounts are stored in Firebase Authentication.
-- =========================
CREATE TABLE IF NOT EXISTS firebase_accounts (
    firebase_account_id INT AUTO_INCREMENT PRIMARY KEY,
    firebase_uid VARCHAR(150) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role VARCHAR(50) DEFAULT 'Teacher',
    status VARCHAR(20) DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- SF8 UPLOADS
-- Stores metadata of uploaded SF8 Excel files from Cloudinary
-- =========================
CREATE TABLE IF NOT EXISTS sf8_uploads (
    upload_id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    cloudinary_public_id VARCHAR(255) NULL,
    cloudinary_url TEXT NOT NULL,
    uploaded_by_email VARCHAR(150) NOT NULL,
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(30) DEFAULT 'Pending',
    reviewed_by VARCHAR(150) NULL,
    reviewed_date DATETIME NULL,
    remarks TEXT NULL
);

-- =========================
-- SF8 STUDENT RECORDS
-- Stores approved/extracted learner health records
-- LRN is intentionally not stored for privacy/security.
-- =========================
CREATE TABLE IF NOT EXISTS sf8_student_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    upload_id INT NOT NULL,

    school_name VARCHAR(150) NULL,
    district VARCHAR(100) NULL,
    division VARCHAR(100) NULL,
    region VARCHAR(100) NULL,
    school_id VARCHAR(50) NULL,
    grade_level VARCHAR(50) NULL,
    section VARCHAR(100) NULL,
    track_strand VARCHAR(100) NULL,
    school_year VARCHAR(50) NULL,

    learner_name VARCHAR(150) NOT NULL,
    birthdate VARCHAR(50) NULL,
    age VARCHAR(20) NULL,
    sex VARCHAR(10) NOT NULL,
    weight_kg DECIMAL(6,2) NULL,
    height_m DECIMAL(6,3) NULL,
    height_squared DECIMAL(8,4) NULL,
    bmi DECIMAL(6,2) NULL,
    bmi_category VARCHAR(50) NULL,
    height_for_age VARCHAR(50) NULL,
    remarks TEXT NULL,

    date_saved DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sf8_student_upload
        FOREIGN KEY (upload_id)
        REFERENCES sf8_uploads(upload_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

USE clinicdesk;

CREATE TABLE IF NOT EXISTS student_health_inputs (
    input_id INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT NOT NULL,
    diet_type VARCHAR(100) NULL,
    sun_exposure VARCHAR(100) NULL,
    exercise_level VARCHAR(100) NULL,
    symptoms TEXT NULL,
    has_fatigue VARCHAR(10) DEFAULT 'No',
    has_bone_pain VARCHAR(10) DEFAULT 'No',
    has_bleeding_gums VARCHAR(10) DEFAULT 'No',
    has_pale_skin VARCHAR(10) DEFAULT 'No',
    has_night_blindness VARCHAR(10) DEFAULT 'No',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (record_id) REFERENCES sf8_student_records(record_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS prediction_results (
    prediction_id INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT NOT NULL,
    predicted_deficiency VARCHAR(150) NULL,
    predicted_risk_level VARCHAR(50) NULL,
    confidence_score DECIMAL(6,4) NULL,
    algorithm_used VARCHAR(100) NULL,
    prediction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (record_id) REFERENCES sf8_student_records(record_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS recommendations (
    recommendation_id INT AUTO_INCREMENT PRIMARY KEY,
    prediction_id INT NOT NULL,
    recommendation_text TEXT NOT NULL,
    recommended_foods TEXT NULL,
    intervention_type VARCHAR(100) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prediction_id) REFERENCES prediction_results(prediction_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);