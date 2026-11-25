<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#appointment');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$serviceId = trim((string) ($_POST['service_id'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$consentGiven = isset($_POST['consent_given']) && $_POST['consent_given'] === '1';

if ($name === '' || $email === false || !preg_match('/^[a-f0-9-]{36}$/i', $serviceId) || !$consentGiven) {
    header('Location: index.php?status=error#appointment');
    exit;
}

try {
    $serviceStatement = db()->prepare('SELECT id FROM services WHERE id = ? AND active = TRUE');
    $serviceStatement->execute([$serviceId]);

    if (!$serviceStatement->fetch()) {
        header('Location: index.php?status=error#appointment');
        exit;
    }

    $statement = db()->prepare('INSERT INTO appointment_requests (id, full_name, email, service_id, phone, message, consent_given) VALUES (UUID(), ?, ?, ?, ?, ?, ?)');
    $statement->execute([$name, $email, $serviceId, $phone !== '' ? $phone : null, $message !== '' ? $message : null, $consentGiven]);
    header('Location: index.php?status=appointment-success#appointment');
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    header('Location: index.php?status=error#appointment');
}
exit;