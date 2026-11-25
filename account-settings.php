<?php
declare(strict_types=1);

require_once __DIR__ . '/patient-auth.php';

$patient = current_patient();
$staff = professional();

if ($patient !== null) {
    $patient = require_patient();
    $accountType = 'patient';
    $accountId = (string) $patient['id'];
} elseif ($staff !== null) {
    require_professional();
    $accountType = 'staff';
    $accountId = (string) $staff['id'];
} else {
    header('Location: login.php');
    exit;
}

$error = '';
$deleted = ($_GET['deleted'] ?? '') === '1';
header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Your session expired. Please try again.';
    } elseif ((string) ($_POST['confirmation'] ?? '') !== 'DELETE') {
        $error = 'Type DELETE exactly to confirm account deletion.';
    } else {
        $pdo = null;
        try {
            $pdo = db();
            $pdo->beginTransaction();

            if ($accountType === 'staff' && ($staff['role'] ?? '') === 'admin') {
                $adminLock = $pdo->query("SELECT id FROM staff_users WHERE role = 'admin' FOR UPDATE");
                if (count($adminLock->fetchAll()) <= 1) {
                    $pdo->rollBack();
                    $error = 'The only administrator account cannot be deleted. Assign another administrator first.';
                }
            }

            if ($error === '') {
                $table = $accountType === 'patient' ? 'patients' : 'staff_users';
                $accountQuery = $pdo->prepare("SELECT password_hash FROM {$table} WHERE id = ? FOR UPDATE");
                $accountQuery->execute([$accountId]);
                $storedAccount = $accountQuery->fetch();

                if (!$storedAccount || !password_verify((string) ($_POST['password'] ?? ''), (string) $storedAccount['password_hash'])) {
                    $pdo->rollBack();
                    $error = 'The current password is incorrect.';
                } else {
                    if ($accountType === 'patient') {
                        $endCalls = $pdo->prepare(
                            "UPDATE telemedicine_calls SET status = 'ended'
                             WHERE patient_id = ? AND status != 'ended'"
                        );
                        $endCalls->execute([$accountId]);
                    } elseif (($staff['role'] ?? '') === 'doctor') {
                        $endCalls = $pdo->prepare(
                            "UPDATE telemedicine_calls SET status = 'ended'
                             WHERE doctor_email = ? AND status != 'ended'"
                        );
                        $endCalls->execute([$staff['email']]);
                    }

                    $deleteAccount = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
                    $deleteAccount->execute([$accountId]);
                    if ($deleteAccount->rowCount() !== 1) {
                        throw new RuntimeException('The authenticated account could not be deleted.');
                    }

                    $pdo->commit();
                    $_SESSION = [];
                    if (ini_get('session.use_cookies')) {
                        $cookie = session_get_cookie_params();
                        setcookie(session_name(), '', [
                            'expires' => time() - 42000,
                            'path' => $cookie['path'],
                            'domain' => $cookie['domain'],
                            'secure' => $cookie['secure'],
                            'httponly' => $cookie['httponly'],
                            'samesite' => $cookie['samesite'] ?? 'Lax',
                        ]);
                    }
                    session_destroy();
                    header('Location: login.php?deleted=1');
                    exit;
                }
            }
        } catch (PDOException | RuntimeException $exception) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($exception->getMessage());
            $error = 'We could not delete your account. Please try again later.';
        }
    }
}

$displayName = $patient['name'] ?? $staff['name'] ?? 'Account';
$email = $patient['email'] ?? $staff['email'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account settings | <?= htmlspecialchars(SITE_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="auth-page">
    <?php require __DIR__ . '/matrix-loader.php'; ?>
    <main class="auth-shell">
        <a class="brand" href="index.php"><span class="brand-mark">IV</span><span><strong>Innovatio</strong><small>Vitalis Ltd</small></span></a>
        <section class="auth-panel">
            <p class="eyebrow">Account settings</p>
            <h1>Manage your<br><em>account.</em></h1>
            <p class="auth-intro">Signed in as <?= htmlspecialchars($displayName) ?> (<?= htmlspecialchars($email) ?>).</p>
            <?php if ($deleted): ?><p class="auth-success" role="status">Your account has been deleted.</p><?php endif; ?>
            <?php if ($error !== ''): ?><p class="auth-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <div class="auth-error" role="note">
                Deleting your account permanently removes your sign-in profile. Appointment and clinical history records are retained.<?php if ($accountType === 'patient' || ($staff['role'] ?? '') === 'doctor'): ?> Active video calls linked to your account will be ended.<?php endif; ?>
            </div>
            <form class="auth-form" method="post" action="account-settings.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <label>Current password<input type="password" name="password" required autocomplete="current-password"></label>
                <label>Type DELETE to confirm<input type="text" name="confirmation" required autocomplete="off" pattern="DELETE" title="Type DELETE exactly"></label>
                <button class="button" type="submit">Delete my account</button>
            </form>
            <p class="auth-switch"><a href="<?= $accountType === 'patient' ? 'patientpage.php' : ($staff['role'] === 'admin' ? 'adminpage.php' : ($staff['role'] === 'doctor' ? 'doctorpage.php' : 'coordinatorpage.php')) ?>">Cancel and return to my account</a></p>
        </section>
    </main>
    <script src="assets/js/language.js" defer></script>
</body>
</html>
