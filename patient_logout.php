<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
unset($_SESSION['patient']);
session_regenerate_id(true);
$destination = ($_GET['reason'] ?? '') === 'inactive' ? 'login.php?account=patient&expired=1' : 'login.php?account=patient';
header('Location: ' . $destination);
exit;
