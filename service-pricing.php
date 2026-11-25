<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_professional();

$methods = [
    'mtn_momo' => 'MTN Mobile Money',
    'airtel_money' => 'Airtel Money',
    'bank_transfer' => 'Bank transfer',
    'cash' => 'Cash',
];
$destinationFields = [
    'bank_name' => ['Bank name', 120],
    'bank_account_name' => ['Account holder name', 120],
    'bank_account_number' => ['Bank account number', 80],
    'mtn_momo_name' => ['MTN account holder name', 120],
    'mtn_momo_number' => ['MTN Mobile Money number', 32],
    'mtn_momo_code' => ['MTN merchant / payment code', 80],
    'airtel_money_name' => ['Airtel Money account holder name', 120],
    'airtel_money_number' => ['Airtel Money number', 32],
    'airtel_money_code' => ['Airtel merchant / payment code', 80],
];
$destinationGroups = [
    'Bank transfer' => ['bank_name', 'bank_account_name', 'bank_account_number'],
    'MTN Mobile Money' => ['mtn_momo_name', 'mtn_momo_number', 'mtn_momo_code'],
    'Airtel Money' => ['airtel_money_name', 'airtel_money_number', 'airtel_money_code'],
];
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        header('Location: service-pricing.php?notice=invalid');
        exit;
    }

    if (($_POST['action'] ?? '') === 'save_destinations') {
        $values = [];
        foreach ($destinationFields as $field => [$label, $maxLength]) {
            $rawValue = $_POST[$field] ?? '';
            if (!is_string($rawValue)) {
                header('Location: service-pricing.php?notice=invalid');
                exit;
            }
            $value = trim($rawValue);
            if (mb_strlen($value) > $maxLength) {
                header('Location: service-pricing.php?notice=invalid');
                exit;
            }
            $values[$field] = $value !== '' ? $value : null;
        }

        try {
            $statement = $pdo->prepare(
                'INSERT INTO payment_destinations
                    (id, bank_name, bank_account_name, bank_account_number,
                     mtn_momo_name, mtn_momo_number, mtn_momo_code,
                     airtel_money_name, airtel_money_number, airtel_money_code)
                 VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    bank_name = VALUES(bank_name),
                    bank_account_name = VALUES(bank_account_name),
                    bank_account_number = VALUES(bank_account_number),
                    mtn_momo_name = VALUES(mtn_momo_name),
                    mtn_momo_number = VALUES(mtn_momo_number),
                    mtn_momo_code = VALUES(mtn_momo_code),
                    airtel_money_name = VALUES(airtel_money_name),
                    airtel_money_number = VALUES(airtel_money_number),
                    airtel_money_code = VALUES(airtel_money_code)'
            );
            $statement->execute(array_values($values));
            header('Location: service-pricing.php?notice=destinations_saved');
            exit;
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            header('Location: service-pricing.php?notice=unavailable');
            exit;
        }
    }

    $serviceIdInput = $_POST['service_id'] ?? '';
    $priceValue = $_POST['price_rwf'] ?? '';
    $serviceId = is_string($serviceIdInput) ? trim($serviceIdInput) : '';
    $priceInput = is_string($priceValue) ? trim($priceValue) : '';
    $selectedMethods = $_POST['methods'] ?? [];
    if (!is_array($selectedMethods)) {
        $selectedMethods = [];
    }
    $selectedMethods = array_values(array_unique(array_filter(
        $selectedMethods,
        static fn ($method): bool => is_string($method) && array_key_exists($method, $methods)
    )));

    $validPrice = $priceInput === ''
        || (ctype_digit($priceInput) && strlen($priceInput) <= 12 && (int) $priceInput <= 999999999999);
    if (!preg_match('/^[a-f0-9-]{36}$/i', $serviceId) || !$validPrice || ($priceInput !== '' && (int) $priceInput > 0 && !$selectedMethods)) {
        header('Location: service-pricing.php?notice=invalid');
        exit;
    }

    try {
        $pdo->beginTransaction();
        $service = $pdo->prepare('SELECT id FROM services WHERE id = ? FOR UPDATE');
        $service->execute([$serviceId]);
        if (!$service->fetchColumn()) {
            $pdo->rollBack();
            header('Location: service-pricing.php?notice=invalid');
            exit;
        }

        $price = $priceInput === '' ? null : (int) $priceInput;
        $update = $pdo->prepare('UPDATE services SET price_rwf = ? WHERE id = ?');
        $update->execute([$price, $serviceId]);
        $delete = $pdo->prepare('DELETE FROM service_payment_methods WHERE service_id = ?');
        $delete->execute([$serviceId]);
        if ($selectedMethods) {
            $insert = $pdo->prepare('INSERT INTO service_payment_methods (service_id, method) VALUES (?, ?)');
            foreach ($selectedMethods as $method) {
                $insert->execute([$serviceId, $method]);
            }
        }
        $pdo->commit();
        header('Location: service-pricing.php?notice=saved');
        exit;
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log($exception->getMessage());
        header('Location: service-pricing.php?notice=unavailable');
        exit;
    }
}

$services = $pdo->query(
    'SELECT s.id, s.name, s.summary, s.active, s.price_rwf, s.created_at, GROUP_CONCAT(pm.method SEPARATOR \',\') AS methods
     FROM services s
     LEFT JOIN service_payment_methods pm ON pm.service_id = s.id
     GROUP BY s.id, s.name, s.summary, s.active, s.price_rwf, s.created_at
     ORDER BY s.created_at, s.name'
)->fetchAll();
$destinationQuery = $pdo->query('SELECT * FROM payment_destinations WHERE id = 1');
$destinations = $destinationQuery->fetch() ?: [];
$notice = (string) ($_GET['notice'] ?? '');
$account = professional();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service pricing | <?= htmlspecialchars(SITE_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/service-pricing.css">
</head>
<body class="dashboard-page">
    <?php require __DIR__ . '/matrix-loader.php'; ?>
    <header class="site-header"><div class="container nav-wrap">
        <a class="brand" href="index.php"><span class="brand-mark">IV</span><span><strong>Innovatio</strong><small>Votalis Ltd</small></span></a>
        <div class="dashboard-user"><span><?= htmlspecialchars((string) ($account['name'] ?? $account['email'] ?? 'Staff')) ?></span><a class="button button-small" href="account-settings.php">Account settings</a><a class="button button-small" href="logout.php">Sign out</a></div>
    </div></header>
    <main class="dashboard-main container pricing-page">
        <div class="dashboard-heading pricing-heading">
            <div><p class="eyebrow">Service management</p><h1>Pricing &amp; <em>payments.</em></h1><p>Set clear prices and accepted payment options for every care service.</p></div>
            <span class="role-badge"><?= htmlspecialchars(ucfirst((string) ($account['role'] ?? 'staff'))) ?></span>
        </div>
        <div class="pricing-notice"><span aria-hidden="true">i</span><p><strong>Manual payments only.</strong> These details inform patients how to pay; the website does not process or verify payments.</p></div>
        <?php if ($notice === 'saved' || $notice === 'destinations_saved'): ?><p class="auth-success pricing-feedback" role="status"><?= $notice === 'saved' ? 'Service pricing and payment methods saved.' : 'Clinic payment destination details saved.' ?></p><?php elseif ($notice === 'invalid'): ?><p class="auth-error pricing-feedback" role="alert">Enter valid details and select at least one payment method for a paid service.</p><?php elseif ($notice === 'unavailable'): ?><p class="auth-error pricing-feedback" role="alert">The service settings could not be saved. Please try again.</p><?php endif; ?>

        <section class="pricing-card destination-card">
            <div class="pricing-card-heading">
                <div class="pricing-card-icon" aria-hidden="true">↗</div>
                <div><p class="eyebrow">Clinic-wide details</p><h2>Payment destinations</h2><p>Patients see the relevant details when a payment method is enabled for a service.</p></div>
            </div>
            <form class="destination-form" method="post" action="service-pricing.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="save_destinations">
                <div class="destination-groups">
                    <?php foreach ($destinationGroups as $groupLabel => $fields): ?>
                        <fieldset class="destination-group">
                            <legend><?= htmlspecialchars($groupLabel) ?></legend>
                            <?php foreach ($fields as $field): [$label, $maxLength] = $destinationFields[$field]; ?>
                                <label><?= htmlspecialchars($label) ?><input name="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>" type="text" maxlength="<?= $maxLength ?>" value="<?= htmlspecialchars((string) ($destinations[$field] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="off"></label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
                <div class="pricing-form-footer"><span>Only configured details are shown to patients.</span><button class="button" type="submit">Save payment details <span aria-hidden="true">→</span></button></div>
            </form>
        </section>

        <section class="service-pricing-section">
            <div class="pricing-section-heading"><div><p class="eyebrow">Service catalogue</p><h2>Prices &amp; accepted methods</h2></div><span><?= count($services) ?> services</span></div>
            <?php foreach ($services as $service): ?>
                <?php $selected = array_filter(explode(',', (string) ($service['methods'] ?? ''))); ?>
                <article class="pricing-card service-pricing-card">
                    <div class="service-pricing-title">
                        <div><span class="service-state <?= $service['active'] ? 'is-active' : '' ?>"><?= $service['active'] ? 'Active' : 'Inactive' ?></span><h3><?= htmlspecialchars($service['name']) ?></h3><p><?= htmlspecialchars($service['summary']) ?></p></div>
                        <span class="service-price-display"><?= $service['price_rwf'] === null ? '—' : number_format((int) $service['price_rwf']) ?><small>RWF</small></span>
                    </div>
                    <form class="service-pricing-form" method="post" action="service-pricing.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8') ?>">
                        <label class="price-input-label">Service price <span>RWF</span><input type="number" name="price_rwf" min="0" max="999999999999" step="1" value="<?= htmlspecialchars((string) ($service['price_rwf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Not set"></label>
                        <fieldset class="method-picker"><legend>Accepted payment methods</legend><div class="method-options">
                            <?php foreach ($methods as $key => $label): ?>
                                <label class="method-option"><input type="checkbox" name="methods[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($key, $selected, true) ? 'checked' : '' ?>><span><?= htmlspecialchars($label) ?></span></label>
                            <?php endforeach; ?>
                        </div></fieldset>
                        <div class="service-form-footer"><span>Patients see these options when booking.</span><button class="button button-small" type="submit">Save changes</button></div>
                    </form>
                </article>
            <?php endforeach; ?>
            <?php if (!$services): ?><p class="empty-state">No services are available to configure.</p><?php endif; ?>
        </section>
    </main>
    <script src="assets/js/language.js" defer></script>
    <script src="assets/js/matrix-loader.js" defer></script>
</body>
</html>
