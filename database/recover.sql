-- Recovery script for Innovatio Vitalis database
-- This script rebuilds corrupted tables by dropping and recreating them

USE innovatio_votalis;

-- Drop all tables with foreign keys first (reverse dependency order)
DROP TABLE IF EXISTS telemedicine_signals;
DROP TABLE IF EXISTS telemedicine_calls;
DROP TABLE IF EXISTS appointment_requests;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS staff_users;
DROP TABLE IF EXISTS patients;

-- Recreate all tables
CREATE TABLE services (
    id CHAR(36) PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    summary TEXT NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE appointment_requests (
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

CREATE TABLE staff_users (
    id CHAR(36) PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'coordinator') NOT NULL DEFAULT 'coordinator',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE patients (
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

CREATE TABLE telemedicine_calls (
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

CREATE TABLE telemedicine_signals (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    room_id CHAR(36) NOT NULL,
    sender ENUM('doctor', 'patient') NOT NULL,
    signal_type ENUM('offer', 'answer', 'candidate', 'hangup') NOT NULL,
    payload MEDIUMTEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_telemedicine_signal_room FOREIGN KEY (room_id) REFERENCES telemedicine_calls(room_id) ON DELETE CASCADE,
    INDEX telemedicine_signal_poll_idx (room_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restore initial service data
INSERT IGNORE INTO services (id, slug, name, summary) VALUES
    (UUID(), 'virtual-care', 'Virtual care', 'Speak with a qualified clinician from the comfort of home.'),
    (UUID(), 'preventive-health', 'Preventive health', 'Practical screenings and care plans shaped around your goals.'),
    (UUID(), 'care-coordination', 'Care coordination', 'One clear path across providers, referrals, and next steps.');
