<?php
declare(strict_types=1);
require_once __DIR__ . '/patient-auth.php';

if (current_patient() !== null) {
    header('Location: patientpage.php');
    exit;
}

$error = '';
$name = '';
$email = '';
$phone = '';
$dateOfBirth = '';
$gender = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $dateOfBirth = trim((string) ($_POST['date_of_birth'] ?? ''));
    $gender = trim((string) ($_POST['gender'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!verify_csrf()) {
        $error = 'Your session expired. Please submit the form again.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $error = 'Enter a valid name and email address.';
    } elseif (strlen($phone) > 32) {
        $error = 'The phone number is too long.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth) || !checkdate((int) substr($dateOfBirth, 5, 2), (int) substr($dateOfBirth, 8, 2), (int) substr($dateOfBirth, 0, 4)) || $dateOfBirth >= date('Y-m-d')) {
        $error = 'Enter a valid date of birth in the past.';
    } elseif (!in_array($gender, ['', 'female', 'male', 'nonbinary', 'prefer_not_to_say'], true)) {
        $error = 'Choose a valid gender option.';
    } elseif (strlen($password) < 12 || strlen($password) > 256) {
        $error = 'Choose a password between 12 and 256 characters.';
    } elseif (!hash_equals($password, $confirmation)) {
        $error = 'The passwords do not match.';
    } elseif (($_POST['privacy_consent'] ?? '') !== '1') {
        $error = 'Please confirm that you have read the privacy notice.';
    } else {
        $pdo = db();
        $existing = $pdo->prepare('SELECT id FROM patients WHERE email = ? LIMIT 1');
        $existing->execute([$email]);
        if ($existing->fetch()) {
            $error = 'An account with this email already exists. Sign in or use a different email.';
        } else {
            $id = sprintf('%04x%04x-%04x-4%03x-%04x-%04x%04x%04x', random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0x0fff), random_int(0, 0x3fff) | 0x8000, random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff));
            $insert = $pdo->prepare('INSERT INTO patients (id, full_name, email, phone, date_of_birth, gender, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $insert->execute([$id, $name, $email, $phone !== '' ? $phone : null, $dateOfBirth, $gender !== '' ? $gender : null, password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            unset($_SESSION['professional']);
            $_SESSION['patient'] = ['id' => $id, 'name' => $name, 'email' => $email, 'phone' => $phone, 'date_of_birth' => $dateOfBirth, 'gender' => $gender, 'created_at' => date('Y-m-d H:i:s')];
            $_SESSION['patient_last_activity'] = time();
            header('Location: patientpage.php');
            exit;
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Create patient account | <?= htmlspecialchars(SITE_NAME) ?></title><link rel="stylesheet" href="assets/css/styles.css"></head><body class="auth-page"><main class="auth-shell"><a class="brand" href="index.php"><span class="brand-mark">IV</span><span><strong>Innovatio</strong><small>Votalis Ltd</small></span></a><section class="auth-panel"><p class="eyebrow">Patient account</p><h1>Create your<br><em>account.</em></h1><p class="auth-intro">Register to create your patient profile and access the patient portal.</p><p class="auth-role-note">Optional ID document preview, contact verification and profile setup are available after registration. Documents are not uploaded by this form.</p><?php if ($error !== ''): ?><p class="auth-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?><form class="auth-form" method="post" action="patient_register.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><label>Full name<input name="full_name" type="text" required maxlength="120" autocomplete="name" value="<?= htmlspecialchars($name) ?>"></label><label>Email address<input name="email" type="email" required maxlength="254" autocomplete="email" value="<?= htmlspecialchars($email) ?>"></label><label>Phone number <small>Optional</small><input name="phone" type="tel" maxlength="32" autocomplete="tel" value="<?= htmlspecialchars($phone) ?>"></label><label>Date of birth<input name="date_of_birth" type="date" required max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($dateOfBirth) ?>"></label><label>Gender<select name="gender"><option value="">Choose an option</option><option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option><option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option><option value="nonbinary" <?= $gender === 'nonbinary' ? 'selected' : '' ?>>Non-binary</option><option value="prefer_not_to_say" <?= $gender === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option></select></label><label>Password <small>12 to 256 characters</small><input name="password" type="password" required minlength="12" maxlength="256" autocomplete="new-password" data-password-input></label><div class="password-strength" aria-live="polite"><span data-password-meter></span><small data-password-strength-text>Use at least 12 characters.</small></div><label>Confirm password<input name="password_confirmation" type="password" required minlength="12" maxlength="256" autocomplete="new-password"></label><label class="auth-consent"><input type="checkbox" name="privacy_consent" value="1" required> I agree to the terms and privacy notice and consent to the use of my details to create and manage this account.</label><button class="button" type="submit">Create patient account <span aria-hidden="true">&#8599;</span></button></form><p class="auth-switch">Already registered? <a href="login.php?account=patient">Sign in</a></p></section></main><script src="assets/js/patient-registration.js" defer></script><script src="assets/js/language.js" defer></script></body></html>
