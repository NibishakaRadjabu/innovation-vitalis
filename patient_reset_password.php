<?php
declare(strict_types=1);
require_once __DIR__ . '/patient-auth.php';

if (current_patient() !== null) {
    unset($_SESSION['patient'], $_SESSION['patient_last_activity']);
}

header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');

$token = strtolower(trim((string) ($_POST['token'] ?? $_GET['token'] ?? '')));
$error = '';
$validToken = preg_match('/^[a-f0-9]{64}$/', $token) === 1;
$tokenHash = $validToken ? hash('sha256', $token) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Your session expired. Please try again.';
    } elseif (strlen((string) ($_POST['password'] ?? '')) < 12 || strlen((string) ($_POST['password'] ?? '')) > 256) {
        $error = 'Choose a password between 12 and 256 characters.';
    } elseif (!hash_equals((string) ($_POST['password'] ?? ''), (string) ($_POST['password_confirm'] ?? ''))) {
        $error = 'The passwords do not match.';
    } elseif (!$validToken) {
        $error = 'This reset link is invalid or has expired. Request a new one.';
    } else {
        try {
            $pdo = db();
            $pdo->beginTransaction();
            $resetQuery = $pdo->prepare(
                'SELECT id, patient_id FROM patient_password_resets
                 WHERE token_hash = ? AND used_at IS NULL AND expires_at > CURRENT_TIMESTAMP
                 LIMIT 1 FOR UPDATE'
            );
            $resetQuery->execute([$tokenHash]);
            $reset = $resetQuery->fetch();

            if (!$reset) {
                $pdo->rollBack();
                $error = 'This reset link is invalid or has expired. Request a new one.';
            } else {
                $updatePatient = $pdo->prepare('UPDATE patients SET password_hash = ? WHERE id = ?');
                $updatePatient->execute([
                    password_hash((string) $_POST['password'], PASSWORD_DEFAULT),
                    $reset['patient_id'],
                ]);
                if ($updatePatient->rowCount() !== 1) {
                    throw new RuntimeException('Password reset token points to a missing patient account.');
                }

                $markUsed = $pdo->prepare('UPDATE patient_password_resets SET used_at = CURRENT_TIMESTAMP WHERE patient_id = ? AND used_at IS NULL');
                $markUsed->execute([$reset['patient_id']]);
                $pdo->commit();
                header('Location: login.php?account=patient&reset=1');
                exit;
            }
        } catch (PDOException | RuntimeException $exception) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($exception->getMessage());
            $error = 'Password reset is temporarily unavailable. Please try again later.';
        }
    }
}

if ($validToken && $error === '') {
    try {
        $pdo = db();
        $tokenQuery = $pdo->prepare(
            'SELECT 1 FROM patient_password_resets
             WHERE token_hash = ? AND used_at IS NULL AND expires_at > CURRENT_TIMESTAMP
             LIMIT 1'
        );
        $tokenQuery->execute([$tokenHash]);
        $validToken = (bool) $tokenQuery->fetchColumn();
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        $validToken = false;
        $error = 'Password reset is temporarily unavailable. Please try again later.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose a new password | <?= htmlspecialchars(SITE_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="auth-page">
    <?php require __DIR__ . '/matrix-loader.php'; ?>
    <main class="auth-shell">
        <a class="brand" href="index.php"><span class="brand-mark">IV</span><span><strong>Innovatio</strong><small>Vitalis Ltd</small></span></a>
        <section class="auth-panel">
            <p class="eyebrow">Account security</p>
            <h1>Choose a new<br><em>password.</em></h1>
            <?php if ($error !== ''): ?><p class="auth-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <?php if ($validToken): ?>
            <p class="auth-intro">Choose a new password for your patient account. Reset links expire after 30 minutes and can only be used once.</p>
            <form class="auth-form" method="post" action="patient_reset_password.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <label>New password<input type="password" name="password" required minlength="12" maxlength="256" autocomplete="new-password"></label>
                <label>Confirm new password<input type="password" name="password_confirm" required minlength="12" maxlength="256" autocomplete="new-password"></label>
                <button class="button" type="submit">Update password <span aria-hidden="true">&#8599;</span></button>
            </form>
            <?php elseif ($error === ''): ?>
            <p class="auth-intro">This reset link is invalid or has expired. Request a new link to continue.</p>
            <p class="auth-switch"><a href="patient_forgot_password.php">Request a new reset link</a></p>
            <?php endif; ?>
            <p class="auth-switch"><a href="login.php?account=patient">Return to sign in</a></p>
        </section>
    </main>
    <script src="assets/js/language.js" defer></script>
</body>
</html>
