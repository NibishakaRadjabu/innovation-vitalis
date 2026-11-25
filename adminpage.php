<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-invite.php';
$account = require_role('admin');
$pdo = db();
$inviteHash = admin_invite_code_hash($pdo);
$inviteNotice = (string) ($_GET['invite'] ?? '');
$stats = [
    'appointments' => (int) $pdo->query('SELECT COUNT(*) FROM appointment_requests')->fetchColumn(),
    'new' => (int) $pdo->query("SELECT COUNT(*) FROM appointment_requests WHERE status = 'new'")->fetchColumn(),
    'staff' => (int) $pdo->query('SELECT COUNT(*) FROM staff_users')->fetchColumn(),
    'doctors' => (int) $pdo->query("SELECT COUNT(*) FROM staff_users WHERE role = 'doctor'")->fetchColumn(),
    'coordinators' => (int) $pdo->query("SELECT COUNT(*) FROM staff_users WHERE role = 'coordinator'")->fetchColumn(),
];
$staff = $pdo->query('SELECT full_name, email, role, created_at FROM staff_users ORDER BY created_at DESC LIMIT 50')->fetchAll();
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Administration | <?= htmlspecialchars(SITE_NAME) ?></title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/css/styles.css"></head>
<body class="dashboard-page"><?php require __DIR__ . '/matrix-loader.php'; ?><header class="site-header"><div class="container nav-wrap"><a class="brand" href="index.php"><span class="brand-mark">IV</span><span><strong>Innovatio</strong><small>Vitalis Ltd</small></span></a><div class="dashboard-user"><span><?= htmlspecialchars($account['name'] ?? $account['email']) ?> · Administrator</span><a class="button button-small" href="service-pricing.php">Service pricing</a><a class="button button-small" href="account-settings.php">Account settings</a><a class="button button-small" href="logout.php">Sign out</a></div></div></header>
<main class="dashboard-main container"><div class="dashboard-heading"><div><p class="eyebrow">Administration</p><h1>Operations <em>overview.</em></h1><p>Monitor appointment activity and manage the professional directory.</p></div><span class="role-badge">Admin</span></div>
<section class="stat-grid" aria-label="Platform totals"><article class="stat-card"><span>All requests</span><strong><?= $stats['appointments'] ?></strong></article><article class="stat-card"><span>Awaiting review</span><strong><?= $stats['new'] ?></strong></article><article class="stat-card"><span>Team members</span><strong><?= $stats['staff'] ?></strong></article><article class="stat-card"><span>Doctors</span><strong><?= $stats['doctors'] ?></strong></article><article class="stat-card"><span>Coordinators</span><strong><?= $stats['coordinators'] ?></strong></article></section>
<section class="dashboard-section"><div class="dashboard-section-heading"><div><p class="eyebrow">Access directory</p><h2>Professional <em>team.</em></h2></div></div><div class="table-wrap"><table><thead><tr><th>Name</th><th>Work email</th><th>Role</th><th>Joined</th></tr></thead><tbody><?php foreach ($staff as $member): ?><tr><td><?= htmlspecialchars($member['full_name']) ?></td><td><?= htmlspecialchars($member['email']) ?></td><td><span class="role-badge role-badge-inline"><?= htmlspecialchars(ucfirst($member['role'])) ?></span></td><td><?= htmlspecialchars(date('j M Y', strtotime($member['created_at']))) ?></td></tr><?php endforeach; ?><?php if (!$staff): ?><tr><td colspan="4">No professional accounts found.</td></tr><?php endif; ?></tbody></table></div></section>
<section class="dashboard-section"><div class="dashboard-section-heading"><div><p class="eyebrow">Administrator access</p><h2>Invitation <em>code.</em></h2></div></div>
<?php if ($inviteNotice !== ''): ?><p class="<?= $inviteNotice === 'updated' ? 'auth-success' : 'auth-error' ?>" role="status"><?php echo match ($inviteNotice) { 'updated' => 'Administrator invitation code updated.', 'invalid_length' => 'Use an invitation code between 12 and 128 characters.', 'mismatch' => 'The new invitation codes do not match.', 'password' => 'Your current password is incorrect.', 'unavailable' => 'The invitation code could not be updated. Please try again.', default => 'The request could not be verified. Please try again.' }; ?></p><?php endif; ?>
<p>Only someone with the current code can register an administrator account. Codes are stored as password hashes.</p>
<?php if ($inviteHash === null): ?><p class="auth-error" role="alert">No administrator invitation code is configured. Set ADMIN_REGISTRATION_CODE in the server environment or create a code below.</p><?php endif; ?>
<form class="auth-form" method="post" action="admin-invite-settings.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><label>Current administrator password<input type="password" name="current_password" required autocomplete="current-password"></label><label>New invitation code<input type="password" name="new_code" minlength="12" maxlength="128" required autocomplete="new-password"><small>Use 12 to 128 characters. Share it only with trusted administrators.</small></label><label>Confirm new invitation code<input type="password" name="new_code_confirm" minlength="12" maxlength="128" required autocomplete="new-password"></label><button class="button" type="submit">Change invitation code</button></form>
</section>
</main><script src="assets/js/matrix-loader.js" defer></script></body></html>
