<?php
declare(strict_types=1);
require_once __DIR__ . '/patient-auth.php';

$patient = require_patient();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    http_response_code($_SERVER['REQUEST_METHOD'] === 'POST' ? 403 : 405);
    exit('Invalid appointment request. Refresh the page and try again.');
}

$action = (string) ($_POST['action'] ?? '');
$appointmentId = trim((string) ($_POST['appointment_id'] ?? ''));
$pdo = db();

if ($action === 'book') {
    $serviceId = trim((string) ($_POST['service_id'] ?? ''));
    $doctorEmail = strtolower(trim((string) ($_POST['doctor_email'] ?? '')));
    $type = (string) ($_POST['appointment_type'] ?? 'consultation');
    $requestedTime = trim((string) ($_POST['preferred_at'] ?? ''));
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i', $requestedTime);
    $errors = DateTimeImmutable::getLastErrors();
    $validDate = $date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
        && $date->format('Y-m-d\\TH:i') === $requestedTime
        && $date > new DateTimeImmutable('+1 hour')
        && $date < new DateTimeImmutable('+1 year');
    $validType = in_array($type, ['consultation', 'follow_up', 'lab_test'], true);
    $service = $pdo->prepare('SELECT id FROM services WHERE id = ? AND active = TRUE');
    $service->execute([$serviceId]);
    $validDoctor = $doctorEmail === '';
    if (!$validDoctor && filter_var($doctorEmail, FILTER_VALIDATE_EMAIL)) {
        $doctor = $pdo->prepare("SELECT email FROM staff_users WHERE email = ? AND role = 'doctor' LIMIT 1");
        $doctor->execute([$doctorEmail]);
        $validDoctor = (bool) $doctor->fetchColumn();
    }
    if (!preg_match('/^[a-f0-9-]{36}$/i', $serviceId) || !$service->fetchColumn() || !$validDate || !$validType || !$validDoctor) {
        header('Location: patientpage.php?appointment=invalid#patient-appointments');
        exit;
    }
    $insert = $pdo->prepare("INSERT INTO appointment_requests (id, full_name, email, service_id, phone, message, consent_given, patient_id, doctor_email, appointment_type, preferred_at) VALUES (UUID(), ?, ?, ?, ?, ?, TRUE, ?, ?, ?, ?)");
    $message = trim((string) ($_POST['message'] ?? ''));
    $insert->execute([$patient['name'], $patient['email'], $serviceId, $patient['phone'] ?? null, $message !== '' ? mb_substr($message, 0, 1000) : null, $patient['id'], $doctorEmail !== '' ? $doctorEmail : null, $type, $date->format('Y-m-d H:i:s')]);
    header('Location: patientpage.php?appointment=sent#patient-appointments');
    exit;
}

if (!preg_match('/^[a-f0-9-]{36}$/i', $appointmentId)) {
    header('Location: patientpage.php?appointment=invalid#patient-appointments');
    exit;
}

if ($action === 'cancel') {
    $update = $pdo->prepare("UPDATE appointment_requests SET status = 'cancelled' WHERE id = ? AND patient_id = ? AND status IN ('new', 'contacted', 'scheduled')");
    $update->execute([$appointmentId, $patient['id']]);
    header('Location: patientpage.php?appointment=' . ($update->rowCount() ? 'cancelled' : 'unchanged') . '#patient-appointments');
    exit;
}

if ($action === 'reschedule') {
    $requestedTime = trim((string) ($_POST['preferred_at'] ?? ''));
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i', $requestedTime);
    $errors = DateTimeImmutable::getLastErrors();
    if (!$date || ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) || $date->format('Y-m-d\\TH:i') !== $requestedTime || $date <= new DateTimeImmutable('+1 hour') || $date >= new DateTimeImmutable('+1 year')) {
        header('Location: patientpage.php?appointment=invalid#patient-appointments');
        exit;
    }
    $update = $pdo->prepare("UPDATE appointment_requests SET preferred_at = ?, status = 'new' WHERE id = ? AND patient_id = ? AND status IN ('new', 'contacted', 'scheduled')");
    $update->execute([$date->format('Y-m-d H:i:s'), $appointmentId, $patient['id']]);
    header('Location: patientpage.php?appointment=' . ($update->rowCount() ? 'rescheduled' : 'unchanged') . '#patient-appointments');
    exit;
}

header('Location: patientpage.php?appointment=invalid#patient-appointments');
