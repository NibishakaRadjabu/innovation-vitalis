<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$account = require_role('doctor');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    http_response_code($_SERVER['REQUEST_METHOD'] === 'POST' ? 403 : 405);
    exit('Invalid appointment request. Refresh the page and try again.');
}

$appointmentId = trim((string) ($_POST['appointment_id'] ?? ''));
$requestedTime = trim((string) ($_POST['preferred_at'] ?? ''));
$date = DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i', $requestedTime);
$dateErrors = DateTimeImmutable::getLastErrors();
$validDate = $date !== false
    && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
    && $date->format('Y-m-d\\TH:i') === $requestedTime
    && $date > new DateTimeImmutable('+1 hour')
    && $date < new DateTimeImmutable('+1 year');

if (!preg_match('/^[a-f0-9-]{36}$/i', $appointmentId) || !$validDate) {
    header('Location: doctorpage.php?appointment=invalid#doctor-appointments');
    exit;
}

$pdo = db();
try {
    $update = $pdo->prepare(
        "UPDATE appointment_requests
         SET status = 'scheduled', preferred_at = ?, doctor_email = ?
         WHERE id = ? AND (doctor_email = ? OR doctor_email IS NULL)
           AND status IN ('new', 'contacted', 'scheduled')"
    );
    $formattedTime = $date->format('Y-m-d H:i:s');
    $update->execute([$formattedTime, $account['email'], $appointmentId, $account['email']]);

    if ($update->rowCount() === 0) {
        $unchanged = $pdo->prepare(
            "SELECT 1 FROM appointment_requests
             WHERE id = ? AND doctor_email = ? AND status = 'scheduled' AND preferred_at = ?
             LIMIT 1"
        );
        $unchanged->execute([$appointmentId, $account['email'], $formattedTime]);
        if (!$unchanged->fetchColumn()) {
            header('Location: doctorpage.php?appointment=unavailable#doctor-appointments');
            exit;
        }
    }
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('The appointment could not be scheduled. Please try again.');
}

header('Location: doctorpage.php?appointment=scheduled#doctor-appointments');
exit;
