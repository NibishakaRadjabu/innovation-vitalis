USE innovatio_votalis;

CREATE TABLE IF NOT EXISTS admin_registration_settings (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    code_hash VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_admin_registration_settings_singleton CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
