<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-invite.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        redirect_auth_error('register.php', 'Your session expired. Please try again.');
    }
    $name = trim((string) ($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $role = (string) ($_POST['role'] ?? 'coordinator');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
        redirect_auth_error('register.php', 'Enter a full name between 2 and 120 characters.');
    }
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_auth_error('register.php', 'Enter a valid work email address.');
    }
    if (!in_array($role, ['doctor', 'coordinator', 'admin'], true)) {
        redirect_auth_error('register.php', 'Select a valid professional role.');
    }
    $pdo = db();
    if ($role === 'admin') {
        $inviteCode = (string) ($_POST['admin_code'] ?? '');
        $inviteHash = admin_invite_code_hash($pdo);
        if ($inviteHash === null) {
            redirect_auth_error('register.php', 'Administrator registration is not configured. Contact an administrator.');
        }
        if (!password_verify($inviteCode, $inviteHash)) {
            redirect_auth_error('register.php', 'The administrator invitation code is incorrect.');
        }
    }
    if (mb_strlen($password) < 12) {
        redirect_auth_error('register.php', 'Choose a password at least 12 characters long.');
    }
    if ($password !== $passwordConfirm) {
        redirect_auth_error('register.php', 'The passwords do not match.');
    }
    try {
        $statement = $pdo->prepare('INSERT INTO staff_users (id, full_name, email, password_hash, role) VALUES (UUID(), ?, ?, ?, ?)');
        $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        header('Location: login.php?account=staff&registered=1');
        exit;
    } catch (PDOException $exception) {
        if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
            redirect_auth_error('register.php', 'An account with that email already exists.');
        }
        error_log($exception->getMessage());
        redirect_auth_error('register.php', 'We could not create your account. Please try again.');
    }
}

if (professional() !== null) {
    redirect_dashboard();
}
$error = (string) ($_GET['error'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create professional account | <?= htmlspecialchars(SITE_NAME) ?></title>
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
            <p class="eyebrow">Professional access</p>
            <h1>Create your <em>staff account.</em></h1>
            <p class="auth-intro">Set up secure access for your clinical or care coordination work.</p>
            <?php if ($error !== ''): ?><p class="auth-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <form method="post" action="register.php" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <label>Full name<input name="full_name" type="text" maxlength="120" required autocomplete="name"></label>
                <label>Work email<input name="email" type="email" maxlength="254" required autocomplete="email"></label>
                <label>Professional role<select name="role" required><option value="coordinator">Care coordinator</option><option value="doctor">Doctor</option><option value="admin">Administrator (invitation code required)</option></select><small>Administrator registration requires a code provided by an administrator.</small></label>
                <label>Administrator invitation code<input name="admin_code" type="password" maxlength="128" autocomplete="off"><small>Required only when registering as an administrator.</small></label>
                <label>Password<input name="password" type="password" minlength="12" required autocomplete="new-password"><small>Use at least 12 characters.</small></label>
                <label>Confirm password<input name="password_confirm" type="password" minlength="12" required autocomplete="new-password"></label>
                <button class="button" type="submit">Create account <span aria-hidden="true">&#8599;</span></button>
            </form>
            <p class="auth-switch">Already registered? <a href="login.php?account=staff" data-auth-switch="login">Sign in</a></p>
            <span class="t-tilt-glare" aria-hidden="true"></span>
        </section>
        </div>
        <p class="auth-footnote">Protected professional access · Innovatio Votalis Ltd</p>
    </main>
    <script src="assets/js/matrix-loader.js" defer></script>
    <script src="assets/js/auth-tilt.js" defer></script>
</body>
</html>
