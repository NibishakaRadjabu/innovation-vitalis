<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#contact');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || $email === false || $message === '') {
    header('Location: index.php?status=error#contact');
    exit;
}

try {
    $statement = db()->prepare('INSERT INTO inquiries (full_name, email, message) VALUES (?, ?, ?)');
    $statement->execute([$name, $email, $message]);
    header('Location: index.php?status=contact-success#contact');
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    header('Location: index.php?status=error#contact');
}
exit;