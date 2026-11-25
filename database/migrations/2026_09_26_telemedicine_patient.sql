SET @patient_id_column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'telemedicine_calls'
      AND COLUMN_NAME = 'patient_id'
);
SET @telemedicine_patient_ddl = IF(
    @patient_id_column_exists = 0,
    'ALTER TABLE telemedicine_calls ADD COLUMN patient_id CHAR(36) NULL AFTER doctor_email, ADD INDEX telemedicine_patient_idx (patient_id)',
    'SELECT 1'
);
PREPARE telemedicine_patient_stmt FROM @telemedicine_patient_ddl;
EXECUTE telemedicine_patient_stmt;
DEALLOCATE PREPARE telemedicine_patient_stmt;
