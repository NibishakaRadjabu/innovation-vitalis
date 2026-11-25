<?php
declare(strict_types=1);

$query = ['account' => 'patient'];
if (($_GET['expired'] ?? '') === '1') {
    $query['expired'] = '1';
}
header('Location: login.php?' . http_build_query($query));
exit;
