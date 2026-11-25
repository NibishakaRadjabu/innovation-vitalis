<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function current_patient(): ?array
{
    return $_SESSION['patient'] ?? null;
}

function require_patient(): array
{
    $patient = current_patient();
    if ($patient === null) {
        header('Location: login.php?account=patient');
        exit;
    }
    $now = time();
    if ($now - (int) ($_SESSION['patient_last_activity'] ?? $now) >= 900) {
        unset($_SESSION['patient'], $_SESSION['patient_last_activity']);
        header('Location: login.php?account=patient&expired=1');
        exit;
    }
    $_SESSION['patient_last_activity'] = $now;
    return $patient;
}
