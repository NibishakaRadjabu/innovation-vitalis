<?php
declare(strict_types=1);

function admin_invite_code_hash(PDO $pdo): ?string
{
    $query = $pdo->query('SELECT code_hash FROM admin_registration_settings WHERE id = 1');
    $storedHash = $query->fetchColumn();
    if (is_string($storedHash) && $storedHash !== '') {
        return $storedHash;
    }

    $initialCode = getenv('ADMIN_REGISTRATION_CODE');
    if (!is_string($initialCode) || strlen($initialCode) < 12 || strlen($initialCode) > 128) {
        if (is_string($initialCode) && $initialCode !== '') {
            error_log('ADMIN_REGISTRATION_CODE must be between 12 and 128 characters.');
        }
        return null;
    }

    $insert = $pdo->prepare(
        'INSERT IGNORE INTO admin_registration_settings (id, code_hash) VALUES (1, ?)'
    );
    $insert->execute([password_hash($initialCode, PASSWORD_DEFAULT)]);

    $query = $pdo->query('SELECT code_hash FROM admin_registration_settings WHERE id = 1');
    $storedHash = $query->fetchColumn();
    return is_string($storedHash) && $storedHash !== '' ? $storedHash : null;
}
