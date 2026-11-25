CREATE DATABASE IF NOT EXISTS innovatio_votalis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE innovatio_votalis;

CREATE TABLE IF NOT EXISTS services (
    id CHAR(36) PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    summary TEXT NOT NULL,
    price_rwf BIGINT UNSIGNED NULL DEFAULT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS service_payment_methods (
    service_id CHAR(36) NOT NULL,
    method ENUM('mtn_momo', 'airtel_money', 'bank_transfer', 'cash') NOT NULL,
    PRIMARY KEY (service_id, method),
    CONSTRAINT fk_service_payment_method_service
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

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
);

CREATE TABLE IF NOT EXISTS appointment_requests (
    id CHAR(36) PRIMARY KEY,
    service_id CHAR(36) NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(32) NULL,
    message VARCHAR(1000) NULL,
    consent_given BOOLEAN NOT NULL DEFAULT FALSE,
    status ENUM('new', 'contacted', 'scheduled', 'cancelled') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_service FOREIGN KEY (service_id) REFERENCES services(id),
    INDEX appointment_status_created_idx (status, created_at)
);

CREATE TABLE IF NOT EXISTS staff_users (
    id CHAR(36) PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'coordinator') NOT NULL DEFAULT 'coordinator',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO services (id, slug, name, summary) VALUES
    (UUID(), 'virtual-care', 'Virtual care', 'Speak with a qualified clinician from the comfort of home.'),
    (UUID(), 'preventive-health', 'Preventive health', 'Practical screenings and care plans shaped around your goals.'),
    (UUID(), 'care-coordination', 'Care coordination', 'One clear path across providers, referrals, and next steps.');

-- Provision the first administrator directly in the database; admin is not publicly self-assignable.
