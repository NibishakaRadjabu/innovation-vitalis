<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-invite.php';

$account = require_role('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    header('Location: adminpage.php?invite=invalid');
    exit;
}

$newCode = (string) ($_POST['new_code'] ?? '');
$confirmation = (string) ($_POST['new_code_confirm'] ?? '');
if (strlen($newCode) < 12 || strlen($newCode) > 128) {
    header('Location: adminpage.php?invite=invalid_length');
    exit;
}
if (!hash_equals($newCode, $confirmation)) {
    header('Location: adminpage.php?invite=mismatch');
    exit;
}

try {
    $pdo = db();
    $adminQuery = $pdo->prepare("SELECT password_hash FROM staff_users WHERE id = ? AND role = 'admin' LIMIT 1");
    $adminQuery->execute([$account['id']]);
    $admin = $adminQuery->fetch();
    if (!$admin || !password_verify((string) ($_POST['current_password'] ?? ''), (string) $admin['password_hash'])) {
        header('Location: adminpage.php?invite=password');
        exit;
    }

    $update = $pdo->prepare(
        'INSERT INTO admin_registration_settings (id, code_hash)
         VALUES (1, ?)
         ON DUPLICATE KEY UPDATE code_hash = VALUES(code_hash), updated_at = CURRENT_TIMESTAMP'
    );
    $update->execute([password_hash($newCode, PASSWORD_DEFAULT)]);
    header('Location: adminpage.php?invite=updated');
    exit;
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    header('Location: adminpage.php?invite=unavailable');
    exit;
}
