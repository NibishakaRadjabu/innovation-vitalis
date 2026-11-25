-- Run once on existing installations to link patient booking requests to
-- preferred times, appointment type, and (optional) doctor selection.
ALTER TABLE appointment_requests
    ADD COLUMN patient_id CHAR(36) NULL AFTER status,
    ADD COLUMN doctor_email VARCHAR(254) NULL AFTER patient_id,
    ADD COLUMN appointment_type ENUM('consultation', 'follow_up', 'lab_test') NULL AFTER doctor_email,
    ADD COLUMN preferred_at DATETIME NULL AFTER appointment_type,
    ADD INDEX appointment_patient_idx (patient_id, preferred_at);
