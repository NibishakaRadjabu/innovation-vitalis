-- Innovatio Votalis Ltd: fresh database setup
-- Run this file once in phpMyAdmin or the MySQL command line.
-- This creates the database, core tables, and initial service catalogue.

CREATE DATABASE IF NOT EXISTS innovatio_votalis
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE innovatio_votalis;

CREATE TABLE IF NOT EXISTS services (
    id CHAR(36) PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    summary TEXT NOT NULL,
    price_rwf BIGINT UNSIGNED NULL DEFAULT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_payment_methods (
    service_id CHAR(36) NOT NULL,
    method ENUM('mtn_momo', 'airtel_money', 'bank_transfer', 'cash') NOT NULL,
    PRIMARY KEY (service_id, method),
    CONSTRAINT fk_service_payment_method_service
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_destinations (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    bank_name VARCHAR(120) NULL,
    bank_account_name VARCHAR(120) NULL,
    bank_account_number VARCHAR(80) NULL,
    mtn_momo_name VARCHAR(120) NULL,
    mtn_momo_number VARCHAR(32) NULL,
    mtn_momo_code VARCHAR(80) NULL,
    airtel_money_name VARCHAR(120) NULL,
    airtel_money_number VARCHAR(32) NULL,
    airtel_money_code VARCHAR(80) NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_payment_destinations_singleton CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS appointment_requests (
    id CHAR(36) PRIMARY KEY,
    service_id CHAR(36) NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(32) NULL,
    message VARCHAR(1000) NULL,
    consent_given BOOLEAN NOT NULL DEFAULT FALSE,
    status ENUM('new', 'contacted', 'scheduled', 'cancelled') NOT NULL DEFAULT 'new',
    patient_id CHAR(36) NULL,
    doctor_email VARCHAR(254) NULL,
    appointment_type ENUM('consultation', 'follow_up', 'lab_test') NULL,
    preferred_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_service FOREIGN KEY (service_id) REFERENCES services(id),
    INDEX appointment_status_created_idx (status, created_at),
    INDEX appointment_patient_idx (patient_id, preferred_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS staff_users (
    id CHAR(36) PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'coordinator') NOT NULL DEFAULT 'coordinator',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_registration_settings (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    code_hash VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_admin_registration_settings_singleton CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS patients (
    id CHAR(36) PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    phone VARCHAR(32) NULL,
    date_of_birth DATE NULL,
    gender VARCHAR(40) NULL,
    password_hash VARCHAR(255) NOT NULL,
    privacy_consent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS patient_password_resets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    patient_id CHAR(36) NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_patient_password_reset_patient
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    INDEX patient_password_reset_lookup_idx (patient_id, used_at, expires_at),
    INDEX patient_password_reset_expiry_idx (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS telemedicine_calls (
    room_id CHAR(36) PRIMARY KEY,
    access_hash CHAR(64) NOT NULL,
    doctor_email VARCHAR(254) NOT NULL,
    patient_id CHAR(36) NULL,
    status ENUM('waiting', 'connected', 'ended') NOT NULL DEFAULT 'waiting',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL DEFAULT NULL,
    INDEX telemedicine_expiry_idx (expires_at),
    INDEX telemedicine_patient_idx (patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS telemedicine_signals (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    room_id CHAR(36) NOT NULL,
    sender ENUM('doctor', 'patient') NOT NULL,
    signal_type ENUM('offer', 'answer', 'candidate', 'hangup') NOT NULL,
    payload MEDIUMTEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_telemedicine_signal_room FOREIGN KEY (room_id) REFERENCES telemedicine_calls(room_id) ON DELETE CASCADE,
    INDEX telemedicine_signal_poll_idx (room_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO services (id, slug, name, summary) VALUES
    (UUID(), 'virtual-care', 'Virtual care', 'Speak with a qualified clinician from the comfort of home.'),
    (UUID(), 'preventive-health', 'Preventive health', 'Practical screenings and care plans shaped around your goals.'),
    (UUID(), 'care-coordination', 'Care coordination', 'One clear path across providers, referrals, and next steps.');

-- No administrator is created by this script. Register an account, then assign
-- the admin role to its verified work email from a trusted MySQL session.
