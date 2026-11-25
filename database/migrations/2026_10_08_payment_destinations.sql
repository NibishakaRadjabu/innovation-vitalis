USE innovatio_votalis;

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
