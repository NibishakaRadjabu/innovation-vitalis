<?php
declare(strict_types=1);
require_once __DIR__ . '/patient-auth.php';

if (current_patient() !== null) {
    header('Location: patientpage.php');
    exit;
}

$error = '';
$requested = ($_GET['requested'] ?? '') === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $error = 'Enter a valid email address.';
        } else {
            try {
                $pdo = db();
                $patientQuery = $pdo->prepare('SELECT id, full_name, email FROM patients WHERE email = ? LIMIT 1');
                $patientQuery->execute([$email]);
                $patient = $patientQuery->fetch();

                if ($patient) {
                    $recentQuery = $pdo->prepare(
                        'SELECT 1 FROM patient_password_resets
                         WHERE patient_id = ? AND created_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 MINUTE)
                         LIMIT 1'
                    );
                    $recentQuery->execute([$patient['id']]);
                    if (!$recentQuery->fetchColumn()) {
                        $baseUrl = rtrim((string) getenv('PASSWORD_RESET_BASE_URL'), '/');
                        if ($baseUrl === '') {
                            error_log('Password reset email was not sent: PASSWORD_RESET_BASE_URL is not configured.');
                        } else {
                            $token = bin2hex(random_bytes(32));
                            $tokenHash = hash('sha256', $token);

                            $pdo->beginTransaction();
                            $revoke = $pdo->prepare('UPDATE patient_password_resets SET used_at = CURRENT_TIMESTAMP WHERE patient_id = ? AND used_at IS NULL');
                            $revoke->execute([$patient['id']]);
                            $insert = $pdo->prepare(
                                'INSERT INTO patient_password_resets (patient_id, token_hash, expires_at)
                                 VALUES (?, ?, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE))'
                            );
                            $insert->execute([$patient['id'], $tokenHash]);
                            $pdo->commit();

                            $resetUrl = $baseUrl . '/patient_reset_password.php?token=' . rawurlencode($token);
                            $subject = SITE_NAME . ' password reset';
                            $message = "Hello {$patient['full_name']},\r\n\r\n"
                                . "A password reset was requested for your patient account. Use the link below within 30 minutes:\r\n\r\n"
                                . $resetUrl . "\r\n\r\n"
                                . "If you did not request this change, you can ignore this email. Your password will not change unless the link is used.\r\n";
                            $headers = [
                                'From: ' . SITE_NAME . ' <' . SITE_EMAIL . '>',
                                'Reply-To: ' . SITE_EMAIL,
                                'Content-Type: text/plain; charset=UTF-8',
                                'X-Content-Type-Options: nosniff',
                            ];
                            if (!mail((string) $patient['email'], $subject, $message, implode("\r\n", $headers))) {
                                $invalidate = $pdo->prepare('UPDATE patient_password_resets SET used_at = CURRENT_TIMESTAMP WHERE token_hash = ? AND used_at IS NULL');
                                $invalidate->execute([$tokenHash]);
                                error_log('Password reset email delivery failed; check the configured SMTP relay.');
                            }
                        }
                    }
                }

                header('Location: patient_forgot_password.php?requested=1');
                exit;
            } catch (PDOException $exception) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log($exception->getMessage());
                $error = 'Password reset is temporarily unavailable. Please try again later.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password recovery | <?= htmlspecialchars(SITE_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="auth-page">
    <?php require __DIR__ . '/matrix-loader.php'; ?>
    <main class="auth-shell">
        <a class="brand" href="index.php"><span class="brand-mark">IV</span><span><strong>Innovatio</strong><small>Vitalis Ltd</small></span></a>
        <section class="auth-panel">
            <p class="eyebrow">Account access</p>
            <h1>Reset your<br><em>password.</em></h1>
            <p class="auth-intro">Enter the email address on your patient account. If it matches an account, we’ll send a time-limited reset link when email delivery is available.</p>
            <?php if ($requested): ?><p class="auth-success" role="status">If an account matches that email, reset instructions will be sent when email delivery is configured. Check your inbox, or contact <?= htmlspecialchars(SITE_EMAIL) ?> for help.</p><?php endif; ?>
            <?php if ($error !== ''): ?><p class="auth-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <form class="auth-form" method="post" action="patient_forgot_password.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <label>Email address<input type="email" name="email" required maxlength="254" autocomplete="email" placeholder="you@example.com"></label>
                <button class="button" type="submit">Send reset instructions <span aria-hidden="true">&#8599;</span></button>
            </form>
            <p class="auth-switch"><a href="login.php?account=patient">Return to sign in</a></p>
        </section>
    </main>
    <script src="assets/js/language.js" defer></script>
</body>
</html>
