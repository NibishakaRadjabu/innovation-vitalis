<?php
declare(strict_types=1);
require_once __DIR__ . '/patient-auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    http_response_code(403);
    echo json_encode(['error' => 'Session check failed.']);
    exit;
}
require_patient();
echo json_encode(['ok' => true]);
