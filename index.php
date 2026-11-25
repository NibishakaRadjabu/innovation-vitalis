<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/payment-display.php';

$services = db()->query(
    'SELECT s.id, s.name, s.summary, s.price_rwf, s.created_at, GROUP_CONCAT(pm.method SEPARATOR \',\') AS methods
     FROM services s
     LEFT JOIN service_payment_methods pm ON pm.service_id = s.id
     WHERE s.active = TRUE
     GROUP BY s.id, s.name, s.summary, s.price_rwf, s.created_at
     ORDER BY s.created_at, s.name'
)->fetchAll();
$paymentDestinationQuery = db()->query('SELECT * FROM payment_destinations WHERE id = 1');
$paymentDestinations = $paymentDestinationQuery->fetch() ?: [];
$paymentMethodLabels = [
    'mtn_momo' => 'MTN Mobile Money',
    'airtel_money' => 'Airtel Money',
    'bank_transfer' => 'Bank transfer',
    'cash' => 'Cash',
];

$doctors = [
    ['name' => 'Dr Amara Okafor', 'role' => 'Clinical Director', 'initials' => 'AO'],
    ['name' => 'Dr Elliot Mercer', 'role' => 'Consultant Physician', 'initials' => 'EM'],
    ['name' => 'Dr Sofia Rahman', 'role' => 'Family Medicine', 'initials' => 'SR'],
];

$status = $_GET['status'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Innovatio Vitalis provides considered, connected healthcare for every stage of life.">
    <title><?= SITE_NAME ?> | Care, made human</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php require __DIR__ . '/matrix-loader.php'; ?>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="index.php" aria-label="Innovatio Vitalis home">
                <span class="brand-mark">IV</span>
                <span><strong>Innovatio</strong><small>Vitalis Ltd</small></span>
            </a>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">Menu</button>
            <nav id="site-nav" class="site-nav" aria-label="Main navigation">
                <a href="#care">Our care</a>
                <a href="#patients">For patients</a>
                <a href="login.php">Sign in</a>
                <a href="#clinicians">For clinicians</a>
                <a href="#administrators">For administrators</a>
                <a href="#approach">Our approach</a>
                <a href="#team">Our clinicians</a>
                <a href="#contact">Contact</a>
                <a class="button button-small" href="#appointment">Book an appointment <span aria-hidden="true">&#8599;</span></a>
            </nav>
        </div>
    </header>

    <?php if ($status === 'appointment-success' || $status === 'contact-success'): ?>
        <div class="notice success" role="status">Thank you. Your message has been received and our team will be in touch shortly.</div>
    <?php elseif ($status === 'error'): ?>
        <div class="notice error" role="alert">Please check the form and try again, or contact our team directly.</div>
    <?php endif; ?>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div class="hero-copy reveal">
                    <p class="eyebrow">Independent healthcare, thoughtfully delivered</p>
                    <h1>Care that sees the <em>whole</em> you.</h1>
                    <p class="hero-text">A calmer, more connected approach to healthcare. Expert clinicians, clear answers and time to listen.</p>
                    <div class="hero-actions">
                        <a class="button" href="#appointment">Find your care <span aria-hidden="true">&#8599;</span></a>
                        <a class="text-link" href="#care">Explore our services <span aria-hidden="true">&#8594;</span></a>
                    </div>
                    <div class="trust-row"><span class="trust-icon">+</span><span>Trusted by our community<br><strong>4.9 / 5 patient rating</strong></span></div>
                </div>
                <div class="hero-art reveal reveal-delay" aria-label="A clinician and patient in conversation" role="img">
                    <div class="art-panel art-panel-main"></div>
                    <div class="art-panel art-panel-small"></div>
                    <div class="art-note"><span>01</span><strong>Listen first.<br>Care deeply.</strong></div>
                    <div class="art-stamp">EST.<br><strong>2012</strong></div>
                </div>
            </div>
        </section>

        <section class="signal-bar"><div class="container signal-grid"><span>Same-week appointments</span><span>Clinician-led care</span><span>Digital and in-person</span><span>Accessible to all</span></div></section>

        <section id="care" class="section section-light">
            <div class="container">
                <div class="section-heading"><div><p class="eyebrow">Your health, in focus</p><h2>Care designed around<br><em>real life.</em></h2></div><p>From the first conversation to every next step, our services work together to make your healthcare feel simpler, clearer and more personal.</p></div>
                <div class="service-grid">
                    <?php foreach ($services as $index => $service): ?>
                        <?php $serviceMethods = array_filter(explode(',', (string) ($service['methods'] ?? '')), static fn (string $method): bool => isset($paymentMethodLabels[$method])); ?>
                        <article class="service-card"><span class="card-number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= htmlspecialchars($service['name']) ?></h3><p><?= htmlspecialchars($service['summary']) ?></p><p><strong>Price:</strong> <?= $service['price_rwf'] === null ? 'To be confirmed' : number_format((int) $service['price_rwf']) . ' RWF' ?><br><strong>Payment methods:</strong> <?= $serviceMethods ? htmlspecialchars(implode(', ', array_map(static fn (string $method): string => $paymentMethodLabels[$method], $serviceMethods))) : 'To be confirmed' ?></p><?php foreach (payment_destination_lines($serviceMethods, $paymentDestinations) as $paymentLine): ?><p><?= htmlspecialchars($paymentLine) ?></p><?php endforeach; ?><a href="#appointment" class="circle-link" aria-label="Book <?= htmlspecialchars($service['name']) ?>">&#8599;</a></article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="patients" class="section patient-section">
            <div class="container">
                <div class="section-heading">
                    <div><p class="eyebrow">For every kind of patient</p><h2>Care that fits<br><em>everyday life.</em></h2></div>
                    <p>Whether you are booking a routine check-up, coordinating care for your family or fitting an appointment around work, getting help should feel straightforward.</p>
                </div>
                <div class="patient-audience-grid">
                    <article class="patient-audience-card"><span class="card-number">01 / ROUTINE CARE</span><h3>Adults seeking care</h3><p>Clear service information and easy appointment requests for check-ups and everyday health concerns.</p></article>
                    <article class="patient-audience-card"><span class="card-number">02 / FAMILY HEALTH</span><h3>Families</h3><p>Support for parents arranging care for themselves and their children, with room for ongoing health needs.</p></article>
                    <article class="patient-audience-card"><span class="card-number">03 / BUSY SCHEDULES</span><h3>Working professionals</h3><p>Flexible ways to connect with a clinician and plan care around a full working day.</p></article>
                    <article class="patient-audience-card"><span class="card-number">04 / DIGITAL CONVENIENCE</span><h3>Urban residents</h3><p>Convenient access to in-person care, with digital services that can make the next steps easier.</p></article>
                </div>
                <div class="patient-needs">
                    <div class="patient-needs-intro"><p class="eyebrow">What makes care easier</p><h3>Simple steps.<br><em>Clear information.</em></h3><p>Patients value a connected experience from the first appointment request through follow-up care.</p></div>
                    <ul class="patient-needs-list">
                        <li><span>01</span><div><strong>Easy registration</strong><p>Secure sign-in using email or phone, with one-time passcode support.</p></div></li>
                        <li><span>02</span><div><strong>Appointments that work for you</strong><p>Book, reschedule or cancel with clear options and timely updates.</p></div></li>
                        <li><span>03</span><div><strong>Records close at hand</strong><p>Access prescriptions, refill information, test results and health history.</p></div></li>
                        <li><span>04</span><div><strong>Connected conversations</strong><p>Communicate securely with clinicians, including chat or video where available.</p></div></li>
                        <li><span>05</span><div><strong>Care in your language</strong><p>English, French and Kinyarwanda language support.</p></div></li>
                    </ul>
                </div>
                <div class="patient-note"><span aria-hidden="true">+</span><p><strong>Why this matters</strong> General patients make up the heart of a healthcare community. Meeting everyday needs well helps build confidence, trust and lasting engagement.</p></div>
            </div>
        </section>

        <section id="clinicians" class="section clinician-section">
            <div class="container">
                <div class="section-heading">
                    <div><p class="eyebrow">For the people providing care</p><h2>Better workflows.<br><em>More human care.</em></h2></div>
                    <p>From a routine consultation to a specialist follow-up, clinicians need clear information and practical tools that support focused, connected care.</p>
                </div>
                <div class="clinician-audience-grid">
                    <article class="clinician-audience-card"><span class="card-number">01 / PRIMARY CARE</span><h3>General practitioners</h3><p>Quick access to patient records, appointment schedules and prescription workflows for routine consultations.</p></article>
                    <article class="clinician-audience-card"><span class="card-number">02 / SPECIALIST CARE</span><h3>Specialists</h3><p>Detailed patient histories, lab information and telemedicine support for focused cases.</p></article>
                    <article class="clinician-audience-card"><span class="card-number">03 / TEAM BASED CARE</span><h3>Hospital doctors</h3><p>Clear dashboards for managing patients, tracking compliance and coordinating with labs and pharmacies.</p></article>
                    <article class="clinician-audience-card"><span class="card-number">04 / REMOTE CONSULTS</span><h3>Telemedicine doctors</h3><p>Secure video, chat and digital prescription tools to support care at a distance.</p></article>
                    <article class="clinician-audience-card"><span class="card-number">05 / DIGITAL FIRST</span><h3>Tech savvy clinicians</h3><p>Mobile friendly dashboards, useful analytics and an intuitive experience for clinicians at every career stage.</p></article>
                </div>
                <div class="clinician-needs">
                    <div class="clinician-needs-intro"><p class="eyebrow">What clinicians need</p><h3>Tools that support<br><em>good decisions.</em></h3><p>Well designed clinical workflows help providers spend less time navigating systems and more time with patients.</p></div>
                    <ul class="clinician-needs-list">
                        <li><span>01</span><div><strong>Trusted access</strong><p>Secure sign in and verified clinician identities to support confidence in every interaction.</p></div></li>
                        <li><span>02</span><div><strong>Useful patient context</strong><p>Access to relevant history, lab results and prescriptions in one clear view.</p></div></li>
                        <li><span>03</span><div><strong>Connected prescribing</strong><p>Digital prescription workflows with pharmacy coordination.</p></div></li>
                        <li><span>04</span><div><strong>Remote consultation tools</strong><p>Secure video, chat and file sharing for virtual care.</p></div></li>
                        <li><span>05</span><div><strong>Insight and accountability</strong><p>Consultation and outcome analytics, audit trails and data protection controls.</p></div></li>
                    </ul>
                </div>
                <div class="clinician-note"><span aria-hidden="true">+</span><p><strong>Why this matters</strong> Clinicians are the primary care providers on the platform. Their workflows shape the core experience and support efficient care, confidence and adoption.</p></div>
            </div>
        </section>

        <section id="administrators" class="section admin-section">
            <div class="container">
                <div class="section-heading">
                    <div><p class="eyebrow">For the people behind the system</p><h2>Run care with<br><em>confidence.</em></h2></div>
                    <p>Administrators keep healthcare operations organized, accountable and secure. Their tools need to support clear oversight without losing sight of the people behind every record.</p>
                </div>
                <div class="admin-audience-grid">
                    <article class="admin-audience-card"><span class="card-number">01 / PLATFORM OPERATIONS</span><h3>System administrators</h3><p>Secure tools for managing infrastructure, databases, accounts and role based access.</p></article>
                    <article class="admin-audience-card"><span class="card-number">02 / CARE OPERATIONS</span><h3>Healthcare administrators</h3><p>Operational views for managing clinicians, patients, services and appointment activity.</p></article>
                    <article class="admin-audience-card"><span class="card-number">03 / GOVERNANCE</span><h3>Compliance officers</h3><p>Audit trails, data protection visibility and reporting to support regulatory oversight.</p></article>
                    <article class="admin-audience-card"><span class="card-number">04 / FINANCE</span><h3>Finance and billing staff</h3><p>Transaction records, revenue insights and clear processes for handling billing issues.</p></article>
                    <article class="admin-audience-card"><span class="card-number">05 / PATIENT SUPPORT</span><h3>Support teams</h3><p>Tools to respond to account questions, coordinate communication and resolve technical issues.</p></article>
                </div>
                <div class="admin-needs">
                    <div class="admin-needs-intro"><p class="eyebrow">What administrators need</p><h3>Clear oversight.<br><em>Responsible control.</em></h3><p>Practical administration tools help teams manage daily operations while keeping access and activity accountable.</p></div>
                    <ul class="admin-needs-list">
                        <li><span>01</span><div><strong>User management</strong><p>Manage patient, clinician and staff accounts with appropriate access controls.</p></div></li>
                        <li><span>02</span><div><strong>Service management</strong><p>Create, update and organize the healthcare services offered on the platform.</p></div></li>
                        <li><span>03</span><div><strong>Appointment oversight</strong><p>Monitor activity and support appointment changes or cancellations.</p></div></li>
                        <li><span>04</span><div><strong>Billing operations</strong><p>Review transactions, subscriptions, refunds and payment disputes.</p></div></li>
                        <li><span>05</span><div><strong>Reporting and security</strong><p>Understand platform usage and revenue, with audit logs, role controls and backup monitoring.</p></div></li>
                    </ul>
                </div>
                <div class="admin-note"><span aria-hidden="true">+</span><p><strong>Why this matters</strong> Administrators protect the systems and processes that care depends on. Strong governance and clear oversight build trust, transparency and room to grow.</p></div>
            </div>
        </section>

        <section id="approach" class="section approach-section"><div class="container approach-grid"><div class="approach-image"><div class="image-caption">A different kind<br>of healthcare</div></div><div class="approach-copy"><p class="eyebrow">Our approach</p><h2>Good medicine is<br><em>deeply human.</em></h2><p>Healthcare can feel rushed and fragmented. We built Innovatio Vitalis to give people the time, continuity and confidence they deserve.</p><p>Our clinicians look beyond symptoms to understand the context of your life, then work with you on a plan that feels achievable.</p><a class="text-link" href="#appointment">Meet your care team <span aria-hidden="true">&#8594;</span></a></div></div></section>

        <section id="team" class="section section-light team-section"><div class="container"><div class="section-heading"><div><p class="eyebrow">The people behind the care</p><h2>Meet your<br><em>care team.</em></h2></div><a class="text-link" href="#appointment">View all clinicians <span aria-hidden="true">&#8594;</span></a></div><div class="doctor-grid"><?php foreach ($doctors as $doctor): ?><article class="doctor-card"><div class="doctor-photo"><span><?= $doctor['initials'] ?></span></div><h3><?= htmlspecialchars($doctor['name']) ?></h3><p><?= htmlspecialchars($doctor['role']) ?></p></article><?php endforeach; ?></div></div></section>

        <section id="appointment" class="booking-section"><div class="container booking-grid"><div><p class="eyebrow">Take the next step</p><h2>Let's make a plan<br><em>for your health.</em></h2><p>Tell us a little about what you need and our patient team will find the right appointment for you.</p><div class="contact-mini"><span>Prefer to talk?</span><a href="tel:+4402079460182"><?= SITE_PHONE ?></a></div></div><form class="form-panel" action="submit_appointment.php" method="post"><div class="form-row"><label>Full name<input name="name" type="text" required maxlength="120" placeholder="Your name"></label><label>Email address<input name="email" type="email" required maxlength="254" placeholder="you@example.com"></label></div><div class="form-row"><label>Phone number<input name="phone" type="tel" maxlength="32" placeholder="Optional"></label><label>Preferred service<select name="service_id" required><option value="">Choose a service</option><?php foreach ($services as $service): ?><?php $serviceMethods = array_filter(explode(',', (string) ($service['methods'] ?? '')), static fn (string $method): bool => isset($paymentMethodLabels[$method])); ?><option value="<?= htmlspecialchars($service['id']) ?>"><?= htmlspecialchars($service['name']) ?> — <?= $service['price_rwf'] === null ? 'Price to be confirmed' : number_format((int) $service['price_rwf']) . ' RWF' ?><?= $serviceMethods ? ' · ' . htmlspecialchars(implode(', ', array_map(static fn (string $method): string => $paymentMethodLabels[$method], $serviceMethods))) : '' ?></option><?php endforeach; ?></select></label></div><label>How can we help?<textarea name="message" rows="4" maxlength="1000" placeholder="A few details help us prepare..."></textarea></label><label class="checkbox-label"><input name="consent_given" type="checkbox" value="1" required> I agree that Innovatio Vitalis may use these details to respond to my appointment request.</label><button class="button" type="submit">Request appointment <span aria-hidden="true">&#8599;</span></button><small>We will only use your details to respond to this request. Payment is handled by the care team; online payment is not available.</small></form></div></section>

        <section id="contact" class="contact-section"><div class="container contact-grid"><div><p class="eyebrow">We are here to help</p><h2>Have a question?<br><em>Talk to us.</em></h2></div><div class="contact-details"><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a><a href="tel:+4402079460182"><?= SITE_PHONE ?></a><p>18 Waverley Square<br>London, W1G 8QH</p></div><form class="contact-form" action="submit_contact.php" method="post"><input name="name" type="text" required placeholder="Your name"><input name="email" type="email" required placeholder="Email address"><textarea name="message" rows="3" required placeholder="Your message"></textarea><button class="text-link" type="submit">Send message <span aria-hidden="true">&#8594;</span></button></form></div></section>
    </main>
    <footer class="site-footer"><div class="container footer-grid"><a class="brand" href="index.php"><span class="brand-mark">IV</span><span><strong>Innovatio</strong><small>Vitalis Ltd</small></span></a><p>Considered care for every stage of life.</p><p>developed by nibishaka radjab</p><div><a href="#">Privacy</a><a href="#">Accessibility</a><a href="#">Patient information</a></div><small>&copy; <?= date('Y') ?> Innovatio Vitalis Ltd</small></div></footer>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/matrix-loader.js" defer></script>
</body>
</html>
