USE innovatio_votalis;

ALTER TABLE services
    ADD COLUMN price_rwf BIGINT UNSIGNED NULL DEFAULT NULL;

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
