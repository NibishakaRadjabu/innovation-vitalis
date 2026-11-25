-- Run once on an existing installation before deploying role-based registration.
ALTER TABLE staff_users
    ADD COLUMN full_name VARCHAR(120) NOT NULL DEFAULT '' AFTER id,
    MODIFY COLUMN role ENUM('admin', 'doctor', 'coordinator') NOT NULL DEFAULT 'coordinator';

UPDATE staff_users
SET full_name = SUBSTRING_INDEX(email, '@', 1)
WHERE full_name = '';
