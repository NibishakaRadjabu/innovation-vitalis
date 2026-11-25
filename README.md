# Innovatio Votalis Ltd

Professional healthcare website starter built with plain PHP, MySQL, HTML and CSS.

## Local setup

1. Create the database by importing `database/setup.sql` into MySQL or phpMyAdmin.
2. Confirm the credentials in `config.php` match the local XAMPP MySQL setup.
3. Place the project under `C:\xampp\htdocs` and open `http://localhost/innovatio%20votalis%20ltd/`.

## Professional accounts

Staff can register from `register.php` as a doctor, care coordinator, or administrator. Admin registration requires the invitation code. Set `ADMIN_REGISTRATION_CODE` in the server environment to a strong initial value (at least 12 characters); after signing in, an administrator can replace it in the **Administrator access** section of `adminpage.php`. The database stores only a password hash of the current code. Do not put the code in source control or share it publicly.

For an existing database, take a backup and run `database/migrations/2026_10_07_admin_invitation_codes.sql` once. Fresh installations include the settings table in `database/setup.sql`. On Windows/XAMPP, set the environment variable for the Apache service/process and restart Apache; the application has no default or hard-coded invitation code.

Professional staff can open **Service pricing** from their dashboard to set a price in RWF and choose accepted methods (MTN Mobile Money, Airtel Money, bank transfer, or cash) for each service. They can also configure the clinic-wide bank account and mobile-money numbers/payment codes. Patients see the payment destinations that apply to each service on the public site and in the patient portal. This is informational/manual payment only; the site does not process online payments. For an existing database that already has the service pricing tables, run `database/migrations/2026_10_08_payment_destinations.sql` once. If service pricing has not yet been installed, back up the database and run `database/migrations/2026_10_08_service_pricing.sql` first. Fresh installations include the schema in `database/setup.sql`.

If the initial environment variable is not configured, an already-signed-in administrator can set the first code in the dashboard after confirming their password. For existing installations, admins provisioned earlier through SQL continue to sign in normally.

Admins use `adminpage.php`, doctors use `doctorpage.php`, and coordinators use `coordinatorpage.php`. Sign-in sends each role to its own page, and the pages enforce the role on the server.

For an existing database, take a backup and run `database/migrations/2026_09_25_staff_roles.sql` once before deploying the new PHP files. Fresh installations should import `database/setup.sql` instead.

To enable doctor to patient video calls on an existing database, run `database/migrations/2026_09_26_telemedicine.sql` once. Doctors start a call from their workspace and share the three-hour join link with the intended registered patient. Calls use browser WebRTC for encrypted peer-to-peer audio/video, with PHP polling for signaling and a peer-to-peer data channel for chat and file transfer. No recording is performed. The file transfer limit is 8 MB.

To associate telemedicine calls with registered patient accounts, run `database/migrations/2026_09_26_telemedicine_patient.sql` once on an existing database. Doctors enter the patient account email when starting a call; the patient signs in and joins from the patient portal. Fresh databases include this column in `database/setup.sql`.

The public site includes responsive navigation, service information, clinician profiles, appointment requests and a contact form. Appointment and contact submissions use PDO prepared statements and are stored in MySQL.

The site includes an English, Kinyarwanda and French language selector. A visitor's choice is saved in that browser and applied across the public site, account pages and patient/staff workspaces. English is used by default.

Patients can create an account at `patient_register.php`, sign in at `patient_login.php`, and view their profile at `patientpage.php`. Existing appointment submissions and medical records are not yet linked to patient accounts. For an existing database, run `database/migrations/2026_09_26_patients.sql` and `database/migrations/2026_09_26_patient_profile_fields.sql` once; fresh setups include these columns in `database/setup.sql`.

Signed-in patients and professionals can delete their account from **Account settings**. Deletion requires the current password and typing `DELETE`; it removes the sign-in profile while retaining appointment/history records. Active patient video calls are ended. The sole remaining administrator cannot delete their account until another administrator exists.

Patient password recovery sends a single-use reset link valid for 30 minutes. For an existing database, run `database/migrations/2026_10_07_patient_password_resets.sql` once; fresh setups include this table in `database/setup.sql`. Configure a working SMTP relay for PHP `mail()` and set the `PASSWORD_RESET_BASE_URL` environment variable to the public application URL before enabling email delivery. Until both are configured, the reset form will not issue a link. Patient sessions sign out after 15 minutes without activity. Persistent “remember me” sessions, OTP/authenticator two-factor authentication, and revoking other devices remain unavailable because no provider or session registry is configured.

The identity verification wizard at `patient_verify.php` is a UI preview only. Selected ID images/PDFs are previewed locally and never uploaded; OTP delivery, document review, biometric checks and trusted-device registration require verification providers before they can be enabled.

## Production notes

Before handling real patient information, add authenticated staff access, CSRF protection, rate limiting, audit logging, consent management, encrypted backups, a privacy policy and a healthcare-specific compliance review. The supplied form flow is a development foundation, not a production medical-records system.

Video calls require HTTPS (localhost is allowed for development), browser camera/microphone permission, and a running MySQL database. The demo uses a public STUN server; networks behind restrictive firewalls may also require your own TURN service. Call invite links are bearer credentials: share them only with the intended patient. Configure and maintain an appropriate call signaling retention and cleanup policy before production use.
