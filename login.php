<?php
declare(strict_types=1);

require_once __DIR__ . '/patient-auth.php';

$accountType = (string) ($_POST['account_type'] ?? $_GET['account'] ?? 'patient');
if (!in_array($accountType, ['patient', 'staff'], true)) {
    $accountType = 'patient';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        redirect_auth_error('login.php?account=' . $accountType, 'Your session expired. Please try again.');
    }

    $identity = trim((string) ($_POST['identity'] ?? ''));
    $email = strtolower($identity);
    $password = (string) ($_POST['password'] ?? '');

    try {
        if ($accountType === 'patient') {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $statement = db()->prepare('SELECT id, full_name, email, phone, date_of_birth, gender, password_hash, created_at FROM patients WHERE email = ? LIMIT 10');
                $statement->execute([$email]);
            } else {
                $statement = db()->prepare('SELECT id, full_name, email, phone, date_of_birth, gender, password_hash, created_at FROM patients WHERE phone = ? LIMIT 10');
                $statement->execute([$identity]);
            }

            $account = null;
            foreach ($statement->fetchAll() as $candidate) {
                if (password_verify($password, (string) $candidate['password_hash'])) {
                    $account = $candidate;
                    break;
                }
            }
            if (!$account) {
                redirect_auth_error('login.php?account=patient', 'Email, phone number, or password is incorrect.');
            }

            session_regenerate_id(true);
            unset($_SESSION['professional']);
            $_SESSION['patient'] = [
                'id' => $account['id'],
                'name' => $account['full_name'],
                'email' => $account['email'],
                'phone' => $account['phone'],
                'date_of_birth' => $account['date_of_birth'],
                'gender' => $account['gender'],
                'created_at' => $account['created_at'],
            ];
            $_SESSION['patient_last_activity'] = time();
            header('Location: patientpage.php');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            redirect_auth_error('login.php?account=staff', 'Enter a valid work email address and password.');
        }

        $statement = db()->prepare('SELECT id, full_name, email, password_hash, role FROM staff_users WHERE email = ? LIMIT 1');
        $statement->execute([$email]);
        $account = $statement->fetch();
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        redirect_auth_error('login.php?account=' . $accountType, 'Sign in is temporarily unavailable.');
    }

    if (!$account || !password_verify($password, (string) $account['password_hash'])) {
        redirect_auth_error('login.php?account=staff', 'Email or password is incorrect.');
    }

    session_regenerate_id(true);
    unset($_SESSION['patient'], $_SESSION['patient_last_activity']);
    $_SESSION['professional'] = [
        'id' => $account['id'],
        'name' => $account['full_name'],
        'email' => $account['email'],
        'role' => $account['role'],
    ];
    redirect_dashboard();
}

if (current_patient() !== null) {
    header('Location: patientpage.php');
    exit;
}
if (professional() !== null) {
    redirect_dashboard();
}

$error = (string) ($_GET['error'] ?? '');
$registered = isset($_GET['registered']);
$passwordReset = isset($_GET['reset']);
$accountDeleted = isset($_GET['deleted']);
$expired = ($_GET['expired'] ?? '') === '1';
$identity = (string) ($_GET['identity'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | <?= htmlspecialchars(SITE_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="auth-page">
    <?php require __DIR__ . '/matrix-loader.php'; ?>
    <main class="auth-shell">
        <a class="brand" href="index.php" aria-label="Innovatio Votalis home"><span class="brand-mark">IV</span><span><strong>Innovatio</strong><small>Votalis Ltd</small></span></a>
        <div class="t-tilt" data-auth-panel>
        <section class="auth-panel t-tilt-card">
            <p class="eyebrow">Secure access</p>
            <h1>Welcome <em>back.</em></h1>
            <p class="auth-intro">One sign-in for patients and healthcare professionals.</p>
            <?php if ($registered): ?><p class="auth-success" role="status">Your professional account is ready. Sign in to continue.</p><?php endif; ?>
            <?php if ($passwordReset): ?><p class="auth-success" role="status">Your password has been updated. Sign in with your new password.</p><?php endif; ?>
            <?php if ($accountDeleted): ?><p class="auth-success" role="status">Your account has been deleted. You can create a new account if you wish.</p><?php endif; ?>
            <?php if ($expired): ?><p class="auth-error" role="status">You were signed out after 15 minutes without activity.</p><?php endif; ?>
            <?php if ($error !== ''): ?><p class="auth-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <form method="post" action="login.php" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <label>Account type<select name="account_type" required>
                    <option value="patient" <?= $accountType === 'patient' ? 'selected' : '' ?>>Patient</option>
                    <option value="staff" <?= $accountType === 'staff' ? 'selected' : '' ?>>Healthcare professional</option>
                </select></label>
                <label>Email or phone number<input name="identity" type="text" maxlength="254" required autocomplete="username" value="<?= htmlspecialchars($identity) ?>" placeholder="you@example.com or phone"></label>
                <label>Password<input name="password" type="password" required autocomplete="current-password"></label>
                <div class="login-options"><a href="patient_forgot_password.php">Forgot your password?</a></div>
                <button class="button" type="submit">Sign in <span aria-hidden="true">&#8599;</span></button>
            </form>
            <p class="auth-switch">New patient? <a href="patient_register.php">Create a patient account</a></p>
            <p class="auth-switch">Healthcare professional? <a href="register.php">Register for staff access</a></p>
            <span class="t-tilt-glare" aria-hidden="true"></span>
        </section>
        </div>
        <p class="auth-footnote">Protected access · Innovatio Votalis Ltd</p>
    </main>
    <script src="assets/js/matrix-loader.js" defer></script>
    <script src="assets/js/auth-tilt.js" defer></script>
</body>
</html>
