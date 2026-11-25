ALTER TABLE patients
    ADD COLUMN date_of_birth DATE NULL AFTER phone,
    ADD COLUMN gender VARCHAR(40) NULL AFTER date_of_birth;
