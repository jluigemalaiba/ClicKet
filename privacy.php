<?php
// privacy.php — ClicKet Privacy Policy

$sections = [
  [
    'id'    => 'collection',
    'icon'  => 'collection',
    'label' => 'Collection',
    'title' => 'Data We Collect',
    'intro' => 'ClicKet collects only what is necessary to provide a secure and seamless ticketing experience.',
    'items' => [
      ['h' => 'Account Information', 'p' => 'When you sign up, we collect your name, email address, and a hashed password. This is the minimum required to identify you across sessions and associate tickets with your account.'],
      ['h' => 'Booking & Transaction Data', 'p' => 'Each ticket purchase generates a booking record tied to your account: event ID, seat selection, timestamp, and confirmation number. Payment credentials are never stored by ClicKet — they are processed through secure third-party payment gateways.'],
      ['h' => 'Usage & Navigation Data', 'p' => 'We log page visits, search queries on ClicKet, and button interactions to improve platform performance. This data is aggregated and does not identify individual browsing sessions.'],
      ['h' => 'Device & Browser Data', 'p' => 'Standard browser metadata (user-agent string, screen resolution, preferred language) may be collected to ensure compatibility and prevent fraudulent access.'],
    ],
  ],
  [
    'id'    => 'use',
    'icon'  => 'use',
    'label' => 'Use',
    'title' => 'How We Use It',
    'intro' => 'Your data drives the core experience — nothing more.',
    'items' => [
      ['h' => 'Delivering Your Tickets', 'p' => 'Booking data is used to issue, store, and validate your QR-coded tickets within My Tickets. Without this, we cannot confirm your seat.'],
      ['h' => 'Account & Security', 'p' => 'We use your email to authenticate login attempts, send password recovery links, and alert you to unusual activity on your account.'],
      ['h' => 'Event Notifications', 'p' => 'If you opt in, we may notify you about upcoming events, schedule changes for events you have booked, or cancellations affecting your tickets.'],
      ['h' => 'Platform Improvement', 'p' => 'Aggregated, anonymized usage data helps us identify slow pages, popular events, and common booking errors so we can improve ClicKet for everyone.'],
    ],
  ],
  [
    'id'    => 'sharing',
    'icon'  => 'sharing',
    'label' => 'Sharing',
    'title' => 'Data Sharing',
    'intro' => 'We do not sell your personal data. Sharing is limited to what keeps your booking working.',
    'items' => [
      ['h' => 'Event Organizers', 'p' => 'We share booking confirmation data (name, ticket count, seat details) with the relevant event organizer for attendance verification. No financial data is passed to organizers.'],
      ['h' => 'Payment Processors', 'p' => 'Your payment is handled by our third-party payment gateway. ClicKet passes the transaction amount and a session token. The gateway manages card data under its own PCI-DSS compliance.'],
      ['h' => 'Venue Partners', 'p' => 'Participating venues may receive aggregate attendance forecasts. Individual user data is not disclosed to venues beyond what is on your ticket.'],
      ['h' => 'Legal Disclosure', 'p' => 'We may disclose data if required by a valid court order, government request, or to protect the rights, property, or safety of ClicKet users.'],
    ],
  ],
  [
    'id'    => 'security',
    'icon'  => 'security',
    'label' => 'Security',
    'title' => 'How We Protect It',
    'intro' => 'Security is built into every layer of ClicKet, from your browser to our servers.',
    'items' => [
      ['h' => 'Encrypted Connections', 'p' => 'All data transferred between your device and ClicKet is encrypted via TLS. We enforce HTTPS across the entire platform.'],
      ['h' => 'Hashed Credentials', 'p' => 'Passwords are never stored in plain text. We apply industry-standard hashing algorithms before any credentials reach our database.'],
      ['h' => 'Access Controls', 'p' => 'Internal access to production data is restricted to essential personnel only. Logs are retained for security auditing purposes.'],
      ['h' => 'Incident Response', 'p' => 'In the unlikely event of a data breach affecting your account, ClicKet will notify affected users promptly and take immediate remediation steps.'],
    ],
  ],
  [
    'id'    => 'rights',
    'icon'  => 'rights',
    'label' => 'Your Rights',
    'title' => 'Your Rights',
    'intro' => 'You are in control of your data at all times.',
    'items' => [
      ['h' => 'Access & Portability', 'p' => 'You can request a copy of all personal data ClicKet holds about you. Submit a request through our Contact page and we will respond within 14 business days.'],
      ['h' => 'Correction', 'p' => 'If your name, email, or other account details are inaccurate, you may update them directly from your profile settings at any time.'],
      ['h' => 'Deletion', 'p' => 'You may request deletion of your ClicKet account and associated data. Note: active ticket records may be retained for a limited period to satisfy legal and organizer obligations.'],
      ['h' => 'Opt-Out of Marketing', 'p' => 'You can unsubscribe from promotional emails at any time using the link in any ClicKet email, or by visiting your notification preferences in account settings.'],
    ],
  ],
  [
    'id'    => 'cookies',
    'icon'  => 'cookies',
    'label' => 'Cookies',
    'title' => 'Cookies & Tracking',
    'intro' => 'ClicKet uses a minimal set of cookies to keep the platform functional.',
    'items' => [
      ['h' => 'Session Cookies', 'p' => 'Required to keep you logged in as you navigate between pages. These expire when you close your browser or sign out.'],
      ['h' => 'Preference Cookies', 'p' => 'Store non-sensitive settings such as your selected region or notification preferences so you do not need to reconfigure them on each visit.'],
      ['h' => 'Analytics Cookies', 'p' => 'Used to understand which features are most used. Data is aggregated and does not track individual identities. You may opt out via your browser settings.'],
      ['h' => 'No Third-Party Ad Tracking', 'p' => 'ClicKet does not place advertising trackers or share your browsing behavior with ad networks. Our cookie use is limited to operating and improving the platform.'],
    ],
  ],
];

$lastUpdated = 'June 2025';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ClicKet Privacy Policy — how we collect, use, and protect your personal data on our ticketing platform.">
  <title>Privacy Policy — ClicKet</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/partners-footer.css">

  <style>
    /* ── PAGE BASE ── */
    body.privacy-page {
      background: var(--light-bg);
      color: var(--text-primary);
    }

    /* ── HERO ── */
    .prv-hero {
      position: relative;
      padding: 120px 0 52px;
      overflow: hidden;
      background: #fff;
      border-bottom: 1px solid var(--gray-200);
    }

    .prv-hero-bg {
      position: absolute;
      inset: 0;
      pointer-events: none;
      overflow: hidden;
    }

    .prv-hero-bg-circle {
      position: absolute;
      border-radius: 50%;
      opacity: .055;
    }

    .prv-hero-bg-circle:nth-child(1) {
      width: 560px; height: 560px;
      top: -180px; right: -100px;
      background: var(--red-primary);
    }

    .prv-hero-bg-circle:nth-child(2) {
      width: 300px; height: 300px;
      bottom: -80px; left: 10%;
      background: var(--red-primary);
    }

    .prv-hero-inner {
      position: relative;
      z-index: 2;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 18px;
    }

    .prv-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 5px 14px;
      border-radius: 999px;
      background: rgba(232,22,43,.08);
      color: var(--red-primary);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .prv-eyebrow svg {
      width: 13px; height: 13px;
      stroke: currentColor;
    }

    .prv-title {
      margin: 0;
      font-family: var(--font-display);
      font-size: clamp(52px, 8vw, 96px);
      line-height: .9;
      letter-spacing: 1px;
    }

    .prv-title span { color: var(--red-primary); }

    .prv-sub {
      max-width: 640px;
      margin: 0;
      color: var(--gray-500);
      font-size: 15px;
      line-height: 1.75;
    }

    .prv-meta-row {
      display: flex;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
      margin-top: 4px;
    }

    .prv-meta-chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border: 1px solid var(--gray-200);
      border-radius: var(--card-radius);
      background: #fff;
      font-size: 12px;
      font-weight: 600;
      color: var(--gray-500);
    }

    .prv-meta-chip svg {
      width: 13px; height: 13px;
      stroke: currentColor;
      color: var(--red-primary);
    }

    /* ── SIDEBAR ACTIVE STATE ── */
    .prv-sidebar-link.is-active {
      background: rgba(232,22,43,.07);
      color: var(--red-primary);
      font-weight: 700;
    }

    .prv-sidebar-link.is-active svg {
      opacity: 1;
    }

    /* ── MAIN LAYOUT ── */
    .prv-body {
      padding: 64px 0 96px;
    }

    .prv-layout {
      display: grid;
      grid-template-columns: 260px minmax(0, 1fr);
      gap: 40px;
      align-items: start;
    }

    /* ── SIDEBAR ── */
    .prv-sidebar {
      position: sticky;
      top: 62px;
    }

    .prv-sidebar-card {
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }

    .prv-sidebar-header {
      padding: 16px 18px;
      background: var(--red-primary);
      color: #fff;
    }

    .prv-sidebar-header p {
      margin: 0;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      opacity: .75;
    }

    .prv-sidebar-header h3 {
      margin: 4px 0 0;
      font-size: 16px;
      font-weight: 900;
    }

    .prv-sidebar-nav {
      padding: 8px 0;
    }

    .prv-sidebar-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 18px;
      font-size: 13px;
      font-weight: 600;
      color: var(--gray-600);
      transition: background var(--dur-fast), color var(--dur-fast);
    }

    .prv-sidebar-link:hover {
      background: var(--gray-100);
      color: var(--red-primary);
    }

    .prv-sidebar-link svg {
      width: 15px; height: 15px;
      stroke: currentColor;
      flex-shrink: 0;
      opacity: .6;
    }

    .prv-sidebar-contact {
      margin-top: 16px;
      padding: 18px;
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: var(--gray-100);
    }

    .prv-sidebar-contact p {
      margin: 0 0 12px;
      font-size: 13px;
      color: var(--gray-600);
      line-height: 1.55;
    }

    .prv-sidebar-contact a {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      background: var(--red-primary);
      color: #fff;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .5px;
      border-radius: var(--btn-radius);
      transition: background var(--dur-fast), transform var(--dur-fast);
    }

    .prv-sidebar-contact a:hover {
      background: var(--red-light);
      transform: translateY(-2px);
    }

    /* ── SECTION BLOCKS ── */
    .prv-section {
      margin-bottom: 56px;
      scroll-margin-top: 72px;
    }

    .prv-section:last-child { margin-bottom: 0; }

    .prv-section-header {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 24px;
      padding-bottom: 18px;
      border-bottom: 1px solid var(--gray-200);
    }

    .prv-section-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 48px; height: 48px;
      border-radius: 14px;
      background: rgba(232,22,43,.08);
      color: var(--red-primary);
      flex-shrink: 0;
    }

    .prv-section-icon svg {
      width: 26px; height: 26px;
      stroke: currentColor;
    }

    .prv-section-title-wrap p {
      margin: 0 0 2px;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 1.8px;
      text-transform: uppercase;
      color: var(--red-primary);
    }

    .prv-section-title-wrap h2 {
      margin: 0;
      font-family: var(--font-display);
      font-size: 34px;
      letter-spacing: .5px;
      line-height: 1;
    }

    .prv-section-intro {
      margin: 0 0 22px;
      font-size: 15px;
      color: var(--gray-600);
      line-height: 1.7;
      padding: 14px 18px;
      border-left: 3px solid var(--red-primary);
      background: rgba(232,22,43,.03);
      border-radius: 0 8px 8px 0;
    }

    /* ── ITEM GRID ── */
    .prv-item-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
    }

    .prv-item {
      padding: 20px;
      border: 1px solid var(--light-border);
      border-radius: 10px;
      background: #fff;
      box-shadow: var(--shadow-sm);
      transition: border-color var(--dur-fast), transform var(--dur-fast), box-shadow var(--dur-fast);
    }

    .prv-item:hover {
      border-color: rgba(232,22,43,.25);
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .prv-item-num {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px; height: 28px;
      border-radius: 8px;
      background: rgba(232,22,43,.08);
      color: var(--red-primary);
      font-size: 11px;
      font-weight: 900;
      margin-bottom: 10px;
    }

    .prv-item h4 {
      margin: 0 0 6px;
      font-size: 14px;
      font-weight: 900;
    }

    .prv-item p {
      margin: 0;
      font-size: 13px;
      color: var(--gray-600);
      line-height: 1.65;
    }

    /* ── COMMITMENT BANNER ── */
    .prv-commitment {
      margin-top: 40px;
      padding: 32px 36px;
      border-radius: var(--card-radius);
      background: var(--gray-900);
      color: #fff;
      display: flex;
      align-items: center;
      gap: 28px;
    }

    .prv-commitment-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 60px; height: 60px;
      border-radius: 18px;
      background: rgba(232,22,43,.18);
      color: var(--red-light);
      flex-shrink: 0;
    }

    .prv-commitment-icon svg {
      width: 30px; height: 30px;
      stroke: currentColor;
    }

    .prv-commitment h3 {
      margin: 0 0 6px;
      font-size: 20px;
      font-weight: 900;
    }

    .prv-commitment p {
      margin: 0;
      color: var(--gray-400);
      font-size: 14px;
      line-height: 1.65;
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 991px) {
      .prv-layout {
        grid-template-columns: 1fr;
      }
      .prv-sidebar { position: static; }
      .prv-sidebar-nav { display: flex; flex-wrap: wrap; gap: 4px; padding: 10px; }
      .prv-sidebar-link { padding: 7px 12px; border-radius: 6px; background: var(--gray-100); }
    }

    @media (max-width: 640px) {
      .prv-hero { padding: 128px 0 52px; }
      .prv-item-grid { grid-template-columns: 1fr; }
      .prv-commitment { flex-direction: column; text-align: center; padding: 24px; }
      .prv-title { font-size: clamp(44px, 14vw, 72px); }
    }
  </style>
</head>
<body class="privacy-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>

  <!-- ===== HERO ===== -->
  <section class="prv-hero">
    <div class="prv-hero-bg">
      <div class="prv-hero-bg-circle"></div>
      <div class="prv-hero-bg-circle"></div>
    </div>
    <div class="container-xl px-4">
      <div class="prv-hero-inner">

        <h1 class="prv-title">Privacy<br><span>Policy</span></h1>

        <p class="prv-sub">
          We believe transparency builds trust. This page explains exactly what data ClicKet collects, why we collect it, and the controls you have over it.
        </p>

        <div class="prv-meta-row">
          <span class="prv-meta-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Last Updated: <?= $lastUpdated ?>
          </span>
        </div>
      </div>
    </div>

  </section>

  <!-- ===== BODY ===== -->
  <section class="prv-body">
    <div class="container-xl px-4">
      <div class="prv-layout">

        <!-- Sidebar -->
        <aside class="prv-sidebar" aria-label="Privacy navigation">
          <div class="prv-sidebar-card">
            <div class="prv-sidebar-header">
              <p>On this page</p>
              <h3>Privacy Policy</h3>
            </div>
            <nav class="prv-sidebar-nav">
              <?php
              $sideIcons = [
                'collection' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>',
                'use'        => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
                'sharing'    => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
                'security'   => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
                'rights'     => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                'cookies'    => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>',
              ];
              ?>
              <?php foreach ($sections as $s): ?>
                <a href="#<?= $s['id'] ?>" class="prv-sidebar-link">
                  <?= $sideIcons[$s['icon']] ?? '' ?>
                  <?= htmlspecialchars($s['title']) ?>
                </a>
              <?php endforeach; ?>
            </nav>
          </div>

          <div class="prv-sidebar-contact">
            <p>Privacy questions or data requests? Reach our support team directly.</p>
            <a href="contact.php">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Contact Support
            </a>
          </div>
        </aside>

        <!-- Content -->
        <div class="prv-content">
          <?php
          $sectionIcons = [
            'collection' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>',
            'use'        => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
            'sharing'    => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
            'security'   => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
            'rights'     => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            'cookies'    => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>',
          ];
          ?>
          <?php foreach ($sections as $idx => $s): ?>
            <section class="prv-section" id="<?= $s['id'] ?>">
              <div class="prv-section-header">
                <span class="prv-section-icon"><?= $sectionIcons[$s['icon']] ?></span>
                <div class="prv-section-title-wrap">
                  <p><?= sprintf('%02d', $idx + 1) ?> / <?= count($sections) ?></p>
                  <h2><?= htmlspecialchars($s['title']) ?></h2>
                </div>
              </div>

              <p class="prv-section-intro"><?= htmlspecialchars($s['intro']) ?></p>

              <div class="prv-item-grid">
                <?php foreach ($s['items'] as $i => $item): ?>
                  <div class="prv-item">
                    <span class="prv-item-num"><?= $i + 1 ?></span>
                    <h4><?= htmlspecialchars($item['h']) ?></h4>
                    <p><?= htmlspecialchars($item['p']) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endforeach; ?>

          <!-- Commitment Banner -->
          <div class="prv-commitment">
            <span class="prv-commitment-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </span>
            <div>
              <h3>Our Commitment to You</h3>
              <p>ClicKet will never sell your personal data to third parties, display advertising trackers on our platform, or use your information for purposes beyond operating and improving the ClicKet ticketing experience. If this policy changes, we will notify registered users before the new terms take effect.</p>
            </div>
          </div>
        </div>

      </div><!-- /prv-layout -->
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  const navbar = document.querySelector('.navbar-clicket');

  function handleScroll() {
    if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 60);
  }

  // Active sidebar link highlight based on scroll
  const sections   = document.querySelectorAll('.prv-section');
  const sideLinks  = document.querySelectorAll('.prv-sidebar-link');

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const id = entry.target.id;
        sideLinks.forEach(l => {
          const isActive = l.getAttribute('href') === '#' + id;
          l.classList.toggle('is-active', isActive);
        });
      }
    });
  }, { rootMargin: '-20% 0px -70% 0px' });

  sections.forEach(s => observer.observe(s));

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
})();
</script>
</body>
</html>
