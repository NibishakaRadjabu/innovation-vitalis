<?php
declare(strict_types=1);

require_once __DIR__ . '/patient-auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function api_response(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function request_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 262144) {
        api_response(['error' => 'Request is too large.'], 413);
    }
    $body = json_decode($raw, true);
    if (!is_array($body)) {
        api_response(['error' => 'Invalid request body.'], 400);
    }
    return $body;
}

function is_doctor(): bool
{
    return (professional()['role'] ?? '') === 'doctor';
}

function valid_room(string $room): bool
{
    return preg_match('/^[a-f0-9-]{36}$/i', $room) === 1;
}

function call_participant(PDO $pdo, string $room, string $key): array
{
    if (!valid_room($room)) {
        api_response(['error' => 'This call link is invalid or expired.'], 404);
    }
    $query = $pdo->prepare('SELECT room_id, access_hash, doctor_email, patient_id, status, expires_at FROM telemedicine_calls WHERE room_id = ? AND expires_at > CURRENT_TIMESTAMP LIMIT 1');
    $query->execute([$room]);
    $call = $query->fetch();
    if (!$call || $call['status'] === 'ended') {
        api_response(['error' => 'This call has ended or expired.'], 410);
    }
    $loggedInDoctor = is_doctor()
        && hash_equals(strtolower((string) $call['doctor_email']), strtolower((string) (professional()['email'] ?? '')));
    $sessionPatient = current_patient();
    $loggedInPatient = $sessionPatient !== null && $call['patient_id'] !== null
        && hash_equals((string) $call['patient_id'], (string) ($sessionPatient['id'] ?? ''));
    $validInvite = preg_match('/^[a-f0-9]{64}$/i', $key) === 1
        && hash_equals((string) $call['access_hash'], hash('sha256', strtolower($key)));
    if (!$loggedInDoctor && !$loggedInPatient && !$validInvite) {
        api_response(['error' => 'This call link is invalid or expired.'], 404);
    }
    return [$call, $loggedInDoctor ? 'doctor' : 'patient'];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response(['error' => 'Method not allowed.'], 405);
}

$body = request_body();
$action = (string) ($body['action'] ?? '');
try {
    $pdo = db();
} catch (PDOException) {
    api_response(['error' => 'Call service unavailable. Start MySQL and install the telemedicine database migration.'], 503);
}

if ($action === 'create') {
    if (!is_doctor()) {
        api_response(['error' => 'Doctor sign in is required to start a call.'], 403);
    }
    if (!hash_equals(csrf_token(), (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) {
        api_response(['error' => 'Refresh the page and try again.'], 403);
    }
    $room = sprintf('%04x%04x-%04x-4%03x-%04x-%04x%04x%04x', random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0x0fff), random_int(0, 0x3fff) | 0x8000, random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff));
    $key = bin2hex(random_bytes(32));
    $email = (string) (professional()['email'] ?? '');
    if ($email === '') {
        api_response(['error' => 'Doctor account details are incomplete.'], 403);
    }
    $patientEmail = strtolower(trim((string) ($body['patient_email'] ?? '')));
    if (!filter_var($patientEmail, FILTER_VALIDATE_EMAIL)) {
        api_response(['error' => 'Enter the email address on the patient account.'], 400);
    }
    $patientQuery = $pdo->prepare('SELECT id, full_name FROM patients WHERE email = ? LIMIT 1');
    $patientQuery->execute([$patientEmail]);
    $patient = $patientQuery->fetch();
    if (!$patient) {
        api_response(['error' => 'No patient account uses that email. Ask the patient to register first.'], 404);
    }
    $pdo->exec('DELETE FROM telemedicine_calls WHERE expires_at < CURRENT_TIMESTAMP');
    $insert = $pdo->prepare("INSERT INTO telemedicine_calls (room_id, access_hash, doctor_email, patient_id, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 3 HOUR))");
    $insert->execute([$room, hash('sha256', strtolower($key)), $email, $patient['id']]);
    api_response(['room' => $room, 'patient_name' => $patient['full_name'], 'join_path' => 'telemedicine-join.php?room=' . rawurlencode($room)]);
}

$room = (string) ($body['room'] ?? '');
$key = strtolower((string) ($body['key'] ?? ''));
[$call, $sender] = call_participant($pdo, $room, $key);

if ($sender === 'doctor' && !hash_equals(csrf_token(), (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) {
    api_response(['error' => 'Refresh the page and try again.'], 403);
}

if ($action === 'patient-info') {
    if ($sender !== 'doctor') {
        api_response(['error' => 'Only the assigned doctor can view patient information.'], 403);
    }
    if ($call['patient_id'] === null) {
        api_response(['error' => 'This call is not linked to a patient account.'], 404);
    }

    try {
        $patientQuery = $pdo->prepare('SELECT full_name, email, phone, date_of_birth, gender FROM patients WHERE id = ? LIMIT 1');
        $patientQuery->execute([$call['patient_id']]);
        $patient = $patientQuery->fetch();
        if (!$patient) {
            api_response(['error' => 'The linked patient account could not be found.'], 404);
        }

        $appointmentQuery = $pdo->prepare(
            'SELECT a.appointment_type, a.status, a.preferred_at, s.name AS service_name
             FROM appointment_requests a
             LEFT JOIN services s ON s.id = a.service_id
             WHERE a.patient_id = ?
             ORDER BY COALESCE(a.preferred_at, a.created_at) DESC
             LIMIT 1'
        );
        $appointmentQuery->execute([$call['patient_id']]);
        $appointment = $appointmentQuery->fetch() ?: null;
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        api_response(['error' => 'Patient information is temporarily unavailable.'], 503);
    }

    api_response([
        'patient' => [
            'name' => (string) $patient['full_name'],
            'email' => (string) $patient['email'],
            'phone' => $patient['phone'] !== null ? (string) $patient['phone'] : null,
            'date_of_birth' => $patient['date_of_birth'] !== null ? (string) $patient['date_of_birth'] : null,
            'gender' => $patient['gender'] !== null ? (string) $patient['gender'] : null,
        ],
        'latest_appointment' => $appointment ? [
            'service' => $appointment['service_name'] !== null ? (string) $appointment['service_name'] : null,
            'type' => $appointment['appointment_type'] !== null ? (string) $appointment['appointment_type'] : null,
            'status' => (string) $appointment['status'],
            'preferred_at' => $appointment['preferred_at'] !== null ? (string) $appointment['preferred_at'] : null,
        ] : null,
    ]);
}

if ($action === 'send') {
    $type = (string) ($body['type'] ?? '');
    $allowed = ['offer', 'answer', 'candidate', 'hangup'];
    if (!in_array($type, $allowed, true)) {
        api_response(['error' => 'Unsupported call signal.'], 400);
    }
    $payload = $body['payload'] ?? null;
    $encoded = $payload === null ? null : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($encoded !== null && strlen($encoded) > 200000) {
        api_response(['error' => 'Call signal is too large.'], 413);
    }
    $insert = $pdo->prepare('INSERT INTO telemedicine_signals (room_id, sender, signal_type, payload) VALUES (?, ?, ?, ?)');
    $insert->execute([$room, $sender, $type, $encoded]);
    if ($type === 'answer') {
        $update = $pdo->prepare("UPDATE telemedicine_calls SET status = 'connected' WHERE room_id = ? AND status = 'waiting'");
        $update->execute([$room]);
    }
    if ($type === 'hangup') {
        $update = $pdo->prepare("UPDATE telemedicine_calls SET status = 'ended' WHERE room_id = ?");
        $update->execute([$room]);
    }
    api_response(['ok' => true]);
}

if ($action === 'poll') {
    $after = max(0, (int) ($body['after'] ?? 0));
    $query = $pdo->prepare('SELECT id, sender, signal_type, payload FROM telemedicine_signals WHERE room_id = ? AND id > ? AND sender <> ? ORDER BY id ASC LIMIT 100');
    $query->execute([$room, $after, $sender]);
    $signals = [];
    foreach ($query->fetchAll() as $row) {
        $signals[] = ['id' => (int) $row['id'], 'sender' => $row['sender'], 'type' => $row['signal_type'], 'payload' => $row['payload'] === null ? null : json_decode((string) $row['payload'], true)];
    }
    api_response(['signals' => $signals, 'status' => $call['status']]);
}

if ($action === 'end') {
    $update = $pdo->prepare("UPDATE telemedicine_calls SET status = 'ended' WHERE room_id = ?");
    $update->execute([$room]);
    api_response(['ok' => true]);
}

api_response(['error' => 'Unknown call action.'], 400);
