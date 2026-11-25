<?php
declare(strict_types=1);
require_once __DIR__ . '/patient-auth.php';

$room = (string) ($_GET['room'] ?? '');
$patient = require_patient();
$validLink = false;
$doctorEmail = '';
if (preg_match('/^[a-f0-9-]{36}$/i', $room) === 1) {
    $callCheck = db()->prepare("SELECT room_id, doctor_email, status FROM telemedicine_calls WHERE room_id = ? AND patient_id = ? AND status IN ('waiting', 'connected') AND expires_at > CURRENT_TIMESTAMP LIMIT 1");
    $callCheck->execute([$room, $patient['id']]);
    $call = $callCheck->fetch();
    $validLink = (bool) $call;
    $doctorEmail = $validLink ? (string) $call['doctor_email'] : '';
}
http_response_code($validLink ? 200 : 400);
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="referrer" content="no-referrer"><title>Join video consultation | <?= htmlspecialchars(SITE_NAME) ?></title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/css/styles.css"></head>
<body class="dashboard-page">
<header class="site-header"><div class="container nav-wrap"><a class="brand" href="index.php"><span class="brand-mark">IV</span><span><strong>Innovatio</strong><small>Votalis Ltd</small></span></a><span class="role-badge">Private consultation</span></div></header>
<main class="dashboard-main container" data-call-ui="patient" data-room="<?= htmlspecialchars($validLink ? $room : '', ENT_QUOTES, 'UTF-8') ?>">
    <div class="dashboard-heading"><div><p class="eyebrow">Telemedicine</p><h1>Video <em>consultation.</em></h1><p data-patient-call-status><?= $validLink ? 'Join the call when you are ready.' : 'This invite link is invalid. Ask your care team for a new link.' ?></p></div><span class="role-badge">Encrypted peer to peer media</span></div>
    <?php if ($validLink): ?>
    <section class="dashboard-section telemedicine-section patient-call-workspace">
        <div class="dashboard-section-heading"><div><p class="eyebrow">Your clinician</p><h2>Doctor <em>information.</em></h2></div></div>
        <div class="doctor-call-summary"><article class="doctor-module-card"><h3>Doctor account</h3><p><?= htmlspecialchars($doctorEmail) ?></p><span class="module-status">Verified platform account</span></article><article class="doctor-module-card"><h3>Specialization &amp; credentials</h3><p>Specialty, registration credentials and profile details are not connected to this call.</p><span class="module-status">Not connected</span></article><article class="doctor-module-card"><h3>Availability</h3><p>The doctor created this call and is waiting for you to join.</p><span class="module-status">Call invitation active</span></article></div>
        <div class="telemedicine-layout">
            <div class="telemedicine-stage"><div class="telemedicine-stage-top"><div class="session-status-group"><span class="session-indicator" data-patient-indicator><i></i> Waiting to join</span><span class="connection-status" data-patient-connection><i></i> Not connected</span><span class="recording-status"><i></i> Not recording</span></div><span class="session-time" data-call-timer>00:00</span></div>
                <div class="video-placeholder" data-video-stage aria-label="Doctor video with your camera preview"><div class="video-placeholder-content"><span class="video-avatar">IV</span><strong data-main-video-label>Doctor video feed</strong><span data-call-hint>Your camera and microphone are off until you join.</span></div><video class="live-video live-remote-video" data-remote-video autoplay playsinline></video><button type="button" class="self-view-placeholder" data-swap-views aria-label="Swap doctor and patient video views"><video class="live-video" data-local-video autoplay muted playsinline></video><span data-thumb-avatar>YOU</span><small data-thumb-video-label>Your camera preview</small><em>Swap views</em></button></div>
                <div class="telemedicine-controls"><button type="button" class="button button-small" data-join-call>Join consultation</button><button type="button" class="call-control" data-toggle-mic aria-pressed="false" disabled><span aria-hidden="true">&#9834;</span><span data-mic-label>Mute mic</span></button><button type="button" class="call-control" data-toggle-camera aria-pressed="false" disabled><span aria-hidden="true">&#9635;</span><span data-camera-label>Turn camera off</span></button><button type="button" class="call-control call-control-end" data-end-session disabled><span aria-hidden="true">&#10005;</span><span>Leave call</span></button></div>
            </div>
            <aside class="telemedicine-sidepanel"><div class="telemedicine-tabs" role="tablist" aria-label="Consultation tools"><button type="button" class="telemedicine-tab is-active" role="tab" aria-selected="true" data-panel-target="patient-call-chat">Chat &amp; files</button><button type="button" class="telemedicine-tab" role="tab" aria-selected="false" data-panel-target="patient-call-notes">My notes</button></div><div class="telemedicine-panel" id="patient-call-chat" role="tabpanel" data-session-panel><div class="integration-notice">Messages and files transfer directly between browsers and are not stored. Files up to 8 MB can be shared while connected.</div><div class="chat-preview" data-chat-messages aria-live="polite"><p class="chat-empty">Join the consultation to use chat.</p></div><form class="chat-composer" data-chat-form><label class="sr-only" for="patient-message">Write a message</label><input id="patient-message" type="text" maxlength="240" placeholder="Write a message..." autocomplete="off" disabled><label class="file-picker" for="telemedicine-file" aria-label="Choose a file to share">&#128206;</label><input class="sr-only" id="telemedicine-file" type="file" accept="image/*,.pdf,.doc,.docx" disabled><button type="submit" aria-label="Send message" disabled>&#8594;</button></form><p class="file-selection" aria-live="polite" data-file-selection></p></div><div class="telemedicine-panel patient-notes-panel" id="patient-call-notes" role="tabpanel" data-session-panel hidden><div class="integration-notice">Notes are saved only in this browser on this device. They are not sent to your doctor or added to your medical record.</div><label for="patient-call-notes-input">Personal consultation notes</label><textarea id="patient-call-notes-input" rows="9" maxlength="5000" placeholder="Write notes for your own reference..."></textarea><p class="note-save-status" aria-live="polite" data-patient-note-status>Notes are saved automatically on this device.</p><button type="button" class="text-link" data-clear-patient-notes>Clear notes</button></div><div class="telemedicine-safety"><span aria-hidden="true">&#9673;</span><p><strong>Privacy reminder</strong><br>Use this private link only on a trusted device and do not share it with others.</p></div></aside>
        </div>
    </section>
    <?php endif; ?>
</main>
<?php if ($validLink): ?><script src="assets/js/telemedicine-ui.js" defer></script><script src="assets/js/patient-call-notes.js" defer></script><script src="assets/js/telemedicine-call.js" defer></script><?php endif; ?><script src="assets/js/language.js" defer></script>
</body></html>
