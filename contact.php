<?php
// contact.php - ClicKet Contact Page
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/log.php';

$messageSent = false;
$errors = [];

$subjects = [
    'ticket-inquiry' => 'Ticket inquiry',
    'refund-exchange' => 'Refund or exchange',
    'event-info' => 'Event information',
    'venue-support' => 'Venue support',
    'organizer' => 'Organizer or partnership',
    'technical' => 'Technical issue',
    'other' => 'Other concern',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($name === '') {
        $errors[] = 'Full name is required.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if ($subject === '' || !array_key_exists($subject, $subjects)) {
        $errors[] = 'Please choose a support topic.';
    }

    if ($message === '') {
        $errors[] = 'Message cannot be empty.';
    }

    if (!$errors) {
        $messageSent = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Contact ClicKet support for ticket booking, refunds, event, venue, and account concerns.">
  <title>Contact ClicKet</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/partners-footer.css">

  <style>
    body.contact-page {
      background: var(--light-bg);
      color: var(--text-primary);
    }

    .contact-hero {
      position: relative;
      padding: 160px 0 82px;
      overflow: hidden;
      background: #111;
      color: #fff;
      isolation: isolate;
    }

    .contact-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      z-index: -2;
      background:
        linear-gradient(90deg, rgba(17,17,17,.92) 0%, rgba(17,17,17,.74) 48%, rgba(232,22,43,.52) 100%),
        url('<?= htmlspecialchars(landscapeUrl('featured', 71)) ?>') center / cover;
    }

    .contact-hero::after {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      height: 8px;
      background: var(--red-primary);
    }

    .contact-eyebrow,
    .contact-kicker {
      margin: 0 0 10px;
      color: var(--red-primary);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .contact-title {
      margin: 0;
      max-width: 760px;
      font-family: var(--font-display);
      font-size: clamp(50px, 7vw, 86px);
      line-height: .92;
      letter-spacing: 1px;
    }

    .contact-title span {
      color: var(--red-primary);
    }

    .contact-copy {
      max-width: 650px;
      margin: 22px 0 0;
      color: rgba(255,255,255,.78);
      font-size: 16px;
      line-height: 1.75;
    }

    .contact-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: rgba(232,22,43,.1);
      color: var(--red-primary);
    }

    .contact-icon svg {
      width: 19px;
      height: 19px;
      stroke: currentColor;
    }

    .contact-main {
      padding: 54px 0 82px;
    }

    .contact-layout {
      display: grid;
      grid-template-columns: minmax(280px, .78fr) minmax(0, 1.22fr);
      gap: 28px;
      align-items: start;
    }

    .contact-panel,
    .contact-form-panel {
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-card);
    }

    .contact-panel {
      padding: 24px;
    }

    .contact-info-section {
      padding-top: 20px;
      margin-top: 20px;
      border-top: 1px solid var(--gray-200);
    }

    .contact-info-section:first-of-type {
      padding-top: 0;
      margin-top: 24px;
      border-top: 0;
    }

    .contact-panel h2,
    .contact-form-panel h2 {
      margin: 0 0 8px;
      font-size: 22px;
      font-weight: 900;
    }

    .contact-panel p,
    .contact-form-panel > p {
      margin: 0;
      color: var(--gray-600);
      font-size: 14px;
      line-height: 1.65;
    }

    .contact-info-list {
      display: grid;
      gap: 12px;
      margin: 0;
      padding: 0;
      list-style: none;
    }

    .contact-info-item {
      display: grid;
      grid-template-columns: 62px minmax(0, 1fr);
      gap: 12px;
      align-items: center;
      min-height: 38px;
    }

    .contact-info-item strong {
      display: block;
      margin-bottom: 3px;
      font-size: 14px;
      font-weight: 900;
    }

    .contact-info-item a,
    .contact-info-item span {
      color: var(--gray-600);
      font-size: 14px;
      line-height: 1.55;
    }

    .contact-carrier {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 24px;
      padding: 0 8px;
      border-radius: 999px;
      background: rgba(232,22,43,.1);
      color: var(--red-primary);
      font-size: 10px;
      font-weight: 900;
      letter-spacing: .5px;
      text-transform: uppercase;
    }

    .contact-info-item a:hover {
      color: var(--red-primary);
    }

    .contact-form-panel {
      padding: 28px;
    }

    .contact-alert {
      display: grid;
      grid-template-columns: 38px minmax(0, 1fr);
      gap: 12px;
      margin: 22px 0;
      padding: 16px;
      border-radius: 12px;
      font-size: 14px;
      line-height: 1.55;
    }

    .contact-alert--success {
      border: 1px solid rgba(22,163,74,.22);
      background: rgba(22,163,74,.08);
      color: #166534;
    }

    .contact-alert--error {
      border: 1px solid rgba(232,22,43,.2);
      background: rgba(232,22,43,.08);
      color: var(--red-dark);
    }

    .contact-alert ul {
      margin: 6px 0 0;
      padding-left: 18px;
    }

    .contact-form {
      display: grid;
      gap: 18px;
      margin-top: 24px;
    }

    .contact-form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .contact-field {
      display: grid;
      gap: 8px;
    }

    .contact-field label {
      color: var(--gray-600);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 1.4px;
      text-transform: uppercase;
    }

    .contact-field input,
    .contact-field select,
    .contact-field textarea {
      width: 100%;
      min-height: 48px;
      padding: 0 14px;
      border: 1.5px solid var(--gray-200);
      border-radius: 10px;
      background: var(--gray-100);
      color: var(--text-primary);
      font-family: var(--font-body);
      font-size: 14px;
      font-weight: 600;
      outline: none;
      transition: border-color var(--dur-fast), background var(--dur-fast), box-shadow var(--dur-fast);
    }

    .contact-field textarea {
      min-height: 150px;
      padding-top: 13px;
      resize: vertical;
      line-height: 1.6;
    }

    .contact-field input:focus,
    .contact-field select:focus,
    .contact-field textarea:focus {
      border-color: var(--red-primary);
      background: #fff;
      box-shadow: 0 0 0 4px rgba(232,22,43,.1);
    }

    .contact-char-count {
      margin: -8px 0 0;
      color: var(--gray-500);
      font-size: 12px;
      text-align: right;
    }

    .contact-submit {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 48px;
      padding: 0 24px;
      border: 0;
      border-radius: var(--btn-radius);
      background: var(--red-primary);
      color: #fff;
      font-size: 13px;
      font-weight: 900;
      justify-self: start;
      transition: background var(--dur-fast), transform var(--dur-fast), box-shadow var(--dur-fast);
    }

    .contact-submit:hover {
      background: var(--red-light);
      transform: translateY(-2px);
      box-shadow: var(--glow-red);
    }

    @media (max-width: 991px) {
      .contact-layout {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 640px) {
      .contact-hero {
        padding: 132px 0 48px;
      }

      .contact-form-row {
        grid-template-columns: 1fr;
      }

      .contact-submit {
        width: 100%;
      }
    }
  </style>
</head>
<body class="contact-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>
  <section class="contact-hero">
    <div class="container-xl px-4">
      <p class="contact-eyebrow">ClicKet Support</p>
      <h1 class="contact-title">Contact <span>Us</span></h1>
      <p class="contact-copy">
        Reach our support team for ticket purchases, refunds, venue questions, organizer inquiries, and account concerns.
      </p>
    </div>
  </section>

  <section class="contact-main">
    <div class="container-xl px-4">
      <div class="contact-layout">
        <aside class="contact-panel">
          <p class="contact-kicker">Direct Channels</p>
          <h2>Get in Touch</h2>
          <p>Use the form for tracked support, or reach us through these channels.</p>

          <div class="contact-info-section">
            <p class="contact-kicker">Mobile Hotlines</p>
            <ul class="contact-info-list">
              <li class="contact-info-item"><span class="contact-carrier">Globe</span><span>+63 917 550 6997</span></li>
              <li class="contact-info-item"><span class="contact-carrier">Smart</span><span>+63 999 954 5922</span></li>
              <li class="contact-info-item"><span class="contact-carrier">TM</span><span>+63 935 276 8300</span></li>
              <li class="contact-info-item"><span class="contact-carrier">Globe</span><span>+63 917 103 1149</span></li>
            </ul>
          </div>

          <div class="contact-info-section">
            <p class="contact-kicker">Mailing Address</p>
            <div class="contact-info-item">
              <span class="contact-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              </span>
              <span>
                <strong>ClicKet Support Office</strong>
                <span>Metro Manila, Philippines</span>
              </span>
            </div>
          </div>

          <div class="contact-info-section">
            <p class="contact-kicker">Email Support</p>
            <div class="contact-info-item">
              <span class="contact-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/></svg>
              </span>
              <span>
                <strong>Information and Customer Service</strong>
                <a href="mailto:support@clicket.ph">support@clicket.ph</a>
              </span>
            </div>
          </div>
        </aside>

        <section class="contact-form-panel">
          <p class="contact-kicker">Send a Message</p>
          <h2>Tell us how we can help</h2>
          <p>Share the key details and our team will route your concern to the right support lane.</p>

          <?php if ($messageSent): ?>
            <div class="contact-alert contact-alert--success">
              <span class="contact-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </span>
              <div>
                <strong>Message received.</strong><br>
                Thanks for contacting ClicKet. Our team will review your message and respond as soon as possible.
              </div>
            </div>
          <?php elseif ($errors): ?>
            <div class="contact-alert contact-alert--error">
              <span class="contact-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5"/><path d="M12 16h.01"/></svg>
              </span>
              <div>
                <strong>Please review the form.</strong>
                <ul>
                  <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!$messageSent): ?>
            <form class="contact-form" method="post" action="contact.php" novalidate>
              <div class="contact-form-row">
                <div class="contact-field">
                  <label for="name">Full Name</label>
                  <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Juan dela Cruz" autocomplete="name">
                </div>
                <div class="contact-field">
                  <label for="email">Email Address</label>
                  <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="you@example.com" autocomplete="email">
                </div>
              </div>

              <div class="contact-field">
                <label for="subject">Support Topic</label>
                <select id="subject" name="subject">
                  <option value="" disabled <?= empty($_POST['subject']) ? 'selected' : '' ?>>Select a topic</option>
                  <?php foreach ($subjects as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value) ?>" <?= ($_POST['subject'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="contact-field">
                <label for="message">Message</label>
                <textarea id="message" name="message" maxlength="1000" placeholder="Include your event name, ticket reference, or account email when relevant."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
              </div>
              <p class="contact-char-count"><span id="contactCharCount"><?= strlen((string) ($_POST['message'] ?? '')) ?></span> / 1000</p>

              <button type="submit" class="contact-submit">Send Message</button>
            </form>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  const navbar = document.querySelector('.navbar-clicket');
  const message = document.getElementById('message');
  const count = document.getElementById('contactCharCount');

  function handleScroll() {
    if (navbar) {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
    }
  }

  if (message && count) {
    message.addEventListener('input', () => {
      count.textContent = message.value.length;
    });
  }

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
})();
</script>
</body>
</html>
