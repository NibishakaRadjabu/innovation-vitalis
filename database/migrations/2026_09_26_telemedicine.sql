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

ALTER TABLE telemedicine_calls MODIFY expires_at DATETIME NULL DEFAULT NULL;

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

-- Calls and their signaling messages expire after three hours. Remove them regularly:
-- DELETE FROM telemedicine_calls WHERE expires_at < CURRENT_TIMESTAMP;
