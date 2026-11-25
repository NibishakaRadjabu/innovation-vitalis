<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    return $token !== '' && hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token);
}

function professional(): ?array
{
    return $_SESSION['professional'] ?? null;
}

function require_professional(): void
{
    if (professional() === null) {
        header('Location: login.php?account=staff');
        exit;
    }
}

function redirect_dashboard(): never
{
    $pages = ['admin' => 'adminpage.php', 'doctor' => 'doctorpage.php', 'coordinator' => 'coordinatorpage.php'];
    $role = professional()['role'] ?? '';
    header('Location: ' . ($pages[$role] ?? 'login.php'));
    exit;
}

function require_role(string $role): array
{
    require_professional();
    $account = professional();
    if (($account['role'] ?? '') !== $role) {
        redirect_dashboard();
    }
    return $account;
}

function redirect_auth_error(string $page, string $message): never
{
    $separator = str_contains($page, '?') ? '&' : '?';
    header('Location: ' . $page . $separator . 'error=' . rawurlencode($message));
    exit;
}
