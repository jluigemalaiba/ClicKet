<?php
// accessibility.php — ClicKet Accessibility Statement

$features = [
  [
    'id'    => 'keyboard',
    'icon'  => 'keyboard',
    'title' => 'Keyboard Navigation',
    'desc'  => 'Everything on ClicKet is reachable and operable using a keyboard alone. Tab moves focus forward; Shift+Tab moves it back. Enter or Space activates buttons, links, and controls. Focus outlines are always visible so you know exactly where you are.',
    'details' => [
      'All interactive elements are in a logical tab order',
      'No keyboard traps — focus always moves freely',
      'Skip-to-content link available at the top of every page',
      'Dropdown menus and modals are fully keyboard-dismissible',
    ],
  ],
  [
    'id'    => 'screen-reader',
    'icon'  => 'screenreader',
    'title' => 'Screen Reader Support',
    'desc'  => 'ClicKet is built with semantic HTML so screen readers can interpret page structure accurately. Every image carries a meaningful alt attribute or is marked decorative when appropriate.',
    'details' => [
      'Landmark regions (header, main, nav, footer) are correctly marked',
      'Dynamic content updates are announced via ARIA live regions',
      'Ticket confirmations and error messages are announced immediately',
      'Icon buttons carry descriptive aria-label text',
    ],
  ],
  [
    'id'    => 'colour',
    'icon'  => 'colour',
    'title' => 'Colour & Contrast',
    'desc'  => 'Text and interactive controls meet WCAG 2.1 AA contrast ratios. Status and error states are never communicated by colour alone — they always include an icon or text label.',
    'details' => [
      'Body text meets a 4.5 : 1 contrast ratio against its background',
      'Large display text meets a 3 : 1 ratio',
      'Seat status (available, taken, selected) uses shape and label alongside colour',
      'Form error states include an error icon and descriptive message',
    ],
  ],
  [
    'id'    => 'resize',
    'icon'  => 'resize',
    'title' => 'Text Resize & Zoom',
    'desc'  => 'Pages remain readable and fully functional when text is enlarged up to 200% or when the browser zoom level is increased. No content clips or overflows at larger text sizes.',
    'details' => [
      'Layouts use relative units (rem, em, %) so they scale with browser settings',
      'Zoom to 200% without horizontal scrolling on desktop',
      'No fixed-height containers that truncate enlarged text',
      'Touch targets on mobile are at least 44×44 px',
    ],
  ],
  [
    'id'    => 'motion',
    'icon'  => 'motion',
    'title' => 'Reduced Motion',
    'desc'  => 'If your operating system or browser is set to reduce motion, ClicKet respects that preference. Hover transitions, scroll animations, and loading effects are all suppressed.',
    'details' => [
      'Respects prefers-reduced-motion media query',
      'No autoplaying videos or looping animations',
      'Page transitions are fade-only when motion is reduced',
      'Countdown timers update silently without flashing',
    ],
  ],
  [
    'id'    => 'forms',
    'icon'  => 'forms',
    'title' => 'Forms & Errors',
    'desc'  => 'Every form input has a visible label that is programmatically associated. Error messages appear adjacent to the relevant field and are announced to assistive technology automatically.',
    'details' => [
      'Labels are always visible — no placeholder-only inputs',
      'Required fields are marked with aria-required',
      'Inline validation does not shift focus unexpectedly',
      'Session timeouts during checkout warn users 60 seconds in advance',
    ],
  ],
];

$standards = [
  ['abbr' => 'WCAG 2.1 AA', 'desc' => 'Web Content Accessibility Guidelines, the international standard we target.'],
  ['abbr' => 'ARIA 1.2',    'desc' => 'Accessible Rich Internet Applications spec for dynamic UI components.'],
  ['abbr' => 'NVDA / JAWS', 'desc' => 'Tested against common screen readers on Windows.'],
  ['abbr' => 'VoiceOver',   'desc' => 'Tested with VoiceOver on macOS and iOS Safari.'],
  ['abbr' => 'TalkBack',    'desc' => 'Tested with TalkBack on Android Chrome.'],
];

$lastUpdated = 'June 2025';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ClicKet Accessibility Statement — our commitment to keyboard navigation, screen reader support, colour contrast, and inclusive design.">
  <title>Accessibility — ClicKet</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/partners-footer.css">

  <style>
    /* ── BASE ── */
    body.access-page {
      background: var(--light-bg);
      color: var(--text-primary);
    }

    .acc-hero-left-wrap {
      display: contents;
    }

    /* ── HERO ── */
    .acc-hero {
      position: relative;
      min-height: 460px;
      padding: 0;
      overflow: hidden;
      background: #fff;
      color: var(--text-primary);
      display: grid;
      grid-template-columns: 1fr 1fr;
      border-bottom: 3px solid var(--red-primary);
    }

    /* Left panel — text content */
    .acc-hero-left {
      position: relative;
      z-index: 3;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 160px max(24px, calc((100vw - 1320px) / 2 + 24px)) 72px max(24px, calc((100vw - 1320px) / 2 + 24px));
    }

    /* Right panel — tag cloud */
    .acc-hero-right {
      position: relative;
      overflow: hidden;
    }

    /* Subtle grid lines across whole hero */
    .acc-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(232,22,43,.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(232,22,43,.05) 1px, transparent 1px);
      background-size: 52px 52px;
      pointer-events: none;
      z-index: 1;
    }

    /* Red glow behind tag cloud */
    .acc-hero-glow {
      position: absolute;
      width: 520px; height: 520px;
      top: 53%; left: 40%;
      transform: translate(-50%, -50%);
      border-radius: 50%;
      background: radial-gradient(circle, rgba(232,22,43,.12) 0%, transparent 68%);
      pointer-events: none;
      z-index: 1;
    }

    /* ── EYEBROW ── */
    .acc-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 5px 14px;
      border-radius: 999px;
      border: 1px solid rgba(232,22,43,.3);
      background: rgba(232,22,43,.07);
      color: var(--red-primary);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
      margin-bottom: 20px;
      width: fit-content;
    }
    .acc-eyebrow svg { width: 13px; height: 13px; stroke: currentColor; }

    /* ── TITLE ── */
    .acc-title {
      margin: 0 0 20px;
      font-family: var(--font-display);
      font-size: clamp(52px, 5.5vw, 88px);
      line-height: .9;
      letter-spacing: 1px;
    }
    .acc-title span { color: var(--red-primary); }

    /* ── SUBTEXT ── */
    .acc-sub {
      max-width: 460px;
      margin: 0;
      color: var(--gray-500, #6b7280);
      font-size: 15px;
      line-height: 1.75;
    }

    /* ── TAG CLOUD ── */
    .acc-tag-cloud {
      position: absolute;
      inset: 0;
      z-index: 2;
    }

    .acc-tag {
      position: absolute;
      display: inline-block;
      padding: 8px 18px;
      border-radius: 6px;
      font-size: 15px;
      font-weight: 700;
      white-space: nowrap;
      line-height: 1;
      user-select: none;
      transition: transform .25s ease, box-shadow .25s ease;
    }

    .acc-tag:hover {
      transform: scale(1.07) rotate(0deg) !important;
      box-shadow: 0 8px 28px rgba(0,0,0,.35);
      z-index: 10;
    }

    /* Tag variants */
    .acc-tag-white {
      background: #fff;
      color: var(--gray-900, #111);
      border: 1.5px solid rgba(0,0,0,.08);
      box-shadow: 0 2px 8px rgba(0,0,0,.07);
    }
    .acc-tag-red {
      background: var(--red-primary);
      color: #fff;
    }
    .acc-tag-outline {
      background: #fff;
      color: var(--text-primary);
      border: 1.5px solid var(--gray-200, #e5e5e5);
      box-shadow: 0 1px 6px rgba(0,0,0,.06);
    }
    .acc-tag-dark {
      background: rgba(232,22,43,.07);
      color: var(--red-primary);
      border: 1px solid rgba(232,22,43,.18);
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 991px) {
      .acc-hero {
        grid-template-columns: 1fr;
        min-height: auto;
      }
      .acc-hero-left {
        padding: 120px 24px 48px;
      }
      .acc-hero-right {
        display: none;
      }
    }

    @media (max-width: 576px) {
      .acc-hero-left {
        padding: 100px 20px 40px;
      }
      .acc-title {
        font-size: clamp(38px, 10vw, 64px);
      }
    }

    /* ── COMMITMENT STRIP ── */
    .acc-commitment-strip {
      background: var(--red-primary);
      color: #fff;
      padding: 16px 0;
    }

    .acc-commitment-inner {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .acc-commitment-inner svg {
      width: 18px; height: 18px;
      stroke: #fff;
      flex-shrink: 0;
    }

    .acc-commitment-inner p {
      margin: 0;
      font-size: 14px;
      font-weight: 600;
      line-height: 1.5;
    }

    .acc-commitment-inner strong { font-weight: 900; }

    /* ── FEATURES BODY ── */
    .acc-body {
      padding: 72px 0 96px;
    }

    .acc-section-header {
      margin-bottom: 48px;
    }

    .acc-kicker {
      margin: 0 0 10px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--red-primary);
    }

    .acc-section-title {
      margin: 0 0 10px;
      font-family: var(--font-display);
      font-size: clamp(32px, 4vw, 52px);
      letter-spacing: 1px;
      line-height: 1;
    }

    .acc-section-title span { color: var(--red-primary); }

    .acc-section-desc {
      max-width: 600px;
      margin: 0;
      color: var(--gray-500);
      font-size: 15px;
      line-height: 1.7;
    }

    /* ── FEATURE CARDS ── */
    .acc-feature-list {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .acc-feature-card {
      display: grid;
      grid-template-columns: 72px minmax(0, 1fr);
      gap: 0;
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-sm);
      overflow: hidden;
      transition: border-color var(--dur-fast), box-shadow var(--dur-fast), transform var(--dur-fast);
    }

    .acc-feature-card:hover {
      border-color: rgba(232,22,43,.25);
      box-shadow: var(--shadow-md);
      transform: translateY(-3px);
    }

    .acc-feature-aside {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      padding: 24px 0;
      background: rgba(232,22,43,.05);
      border-right: 1px solid rgba(232,22,43,.08);
      gap: 10px;
    }

    .acc-feature-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 42px; height: 42px;
      border-radius: 12px;
      background: rgba(232,22,43,.12);
      color: var(--red-primary);
    }

    .acc-feature-icon svg {
      width: 22px; height: 22px;
      stroke: currentColor;
    }

    .acc-feature-num {
      font-size: 10px;
      font-weight: 900;
      letter-spacing: 1px;
      color: var(--gray-400);
    }

    .acc-feature-body {
      padding: 24px 28px;
    }

    .acc-feature-body h3 {
      margin: 0 0 8px;
      font-size: 18px;
      font-weight: 900;
    }

    .acc-feature-body > p {
      margin: 0 0 16px;
      color: var(--gray-600);
      font-size: 14px;
      line-height: 1.7;
    }

    .acc-detail-list {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 8px;
    }

    .acc-detail-item {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      font-size: 13px;
      color: var(--gray-600);
      line-height: 1.5;
    }

    .acc-detail-item::before {
      content: '';
      display: inline-block;
      width: 7px; height: 7px;
      border-radius: 50%;
      background: var(--red-primary);
      flex-shrink: 0;
      margin-top: 5px;
    }

    /* ── STANDARDS GRID ── */
    .acc-standards {
      margin-top: 64px;
      padding-top: 48px;
      border-top: 1px solid var(--gray-200);
    }

    .acc-standards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
      gap: 14px;
      margin-top: 28px;
    }

    .acc-std-card {
      padding: 18px 20px;
      border: 1px solid var(--light-border);
      border-radius: 10px;
      background: #fff;
      box-shadow: var(--shadow-sm);
    }

    .acc-std-card abbr {
      display: block;
      font-size: 14px;
      font-weight: 900;
      text-decoration: none;
      color: var(--text-primary);
      margin-bottom: 5px;
    }

    .acc-std-card p {
      margin: 0;
      font-size: 12px;
      color: var(--gray-500);
      line-height: 1.55;
    }

    .acc-std-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      margin-bottom: 10px;
      padding: 3px 8px;
      border-radius: 4px;
      background: rgba(232,22,43,.08);
      color: var(--red-primary);
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .8px;
      text-transform: uppercase;
    }

    /* ── CONTACT PANEL ── */
    .acc-contact {
      margin-top: 64px;
      padding: 40px;
      border-radius: var(--card-radius);
      background: var(--gray-900);
      color: #fff;
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 28px;
      align-items: center;
    }

    .acc-contact h3 {
      margin: 0 0 8px;
      font-size: 22px;
      font-weight: 900;
    }

    .acc-contact p {
      margin: 0;
      color: var(--gray-400);
      font-size: 14px;
      line-height: 1.65;
      max-width: 520px;
    }

    .acc-contact-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 13px 26px;
      border-radius: var(--btn-radius);
      background: var(--red-primary);
      color: #fff;
      font-size: 13px;
      font-weight: 800;
      white-space: nowrap;
      transition: background var(--dur-fast), transform var(--dur-fast), box-shadow var(--dur-fast);
    }

    .acc-contact-btn:hover {
      background: var(--red-light);
      color: #fff;
      transform: translateY(-2px);
      box-shadow: var(--glow-red);
    }

    .acc-contact-btn svg {
      width: 15px; height: 15px;
      stroke: currentColor;
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      .acc-feature-card {
        grid-template-columns: 1fr;
      }
      .acc-feature-aside {
        flex-direction: row;
        padding: 16px 20px;
        border-right: 0;
        border-bottom: 1px solid rgba(232,22,43,.08);
      }
      .acc-detail-list {
        grid-template-columns: 1fr;
      }
      .acc-contact {
        grid-template-columns: 1fr;
        padding: 28px;
      }
    }
  </style>
</head>
<body class="access-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>

  <!-- ===== HERO ===== -->
  <section class="acc-hero">

    <!-- Left: text -->
    <div class="acc-hero-left">
      <h1 class="acc-title">Accessib&shy;ility<br><span>Statement</span></h1>
      <p class="acc-sub">
        ClicKet is designed so every fan — regardless of ability, device, or assistive technology — can discover, book, and manage tickets without friction.
      </p>
    </div>

    <!-- Right: tag cloud -->
    <div class="acc-hero-right" aria-hidden="true">
      <div class="acc-hero-glow"></div>
      <div class="acc-tag-cloud">
        <span class="acc-tag acc-tag-white"  style="top:29%; left:29%; transform:rotate(-3deg); font-size:22px;">inclusive</span>
        <span class="acc-tag acc-tag-red"    style="top:22%; left:48%; transform:rotate(2deg); font-size:18px;">keyboard</span>
        <span class="acc-tag acc-tag-outline" style="top:14%; left:29%; transform:rotate(-1.5deg); font-size:13px;">WCAG 2.1 AA</span>
        <span class="acc-tag acc-tag-white"  style="top:36%; left:5%;  transform:rotate(1.5deg); font-size:16px;">screen reader</span>
        <span class="acc-tag acc-tag-dark"   style="top:33%; left:52%; transform:rotate(-2deg); font-size:13px;">ARIA 1.2</span>
        <span class="acc-tag acc-tag-red"    style="top:50%; left:22%; transform:rotate(-2.5deg); font-size:26px;">contrast</span>
        <span class="acc-tag acc-tag-outline" style="top:52%; left:60%; transform:rotate(1deg); font-size:15px;">focus visible</span>
        <span class="acc-tag acc-tag-white"  style="top:64%; left:8%;  transform:rotate(2deg); font-size:14px;">VoiceOver</span>
        <span class="acc-tag acc-tag-dark"   style="top:65%; left:42%; transform:rotate(-1deg); font-size:20px;">reduced motion</span>
        <span class="acc-tag acc-tag-red"    style="top:78%; left:18%; transform:rotate(1.5deg); font-size:13px;">TalkBack</span>
        <span class="acc-tag acc-tag-outline" style="top:76%; left:52%; transform:rotate(-2deg); font-size:17px;">tab order</span>
        <span class="acc-tag acc-tag-white"  style="top:88%; left:30%; transform:rotate(1deg); font-size:15px;">NVDA / JAWS</span>
        <span class="acc-tag acc-tag-dark"   style="top:20%; left:8%;  transform:rotate(-1deg); font-size:12px;">semantic HTML</span>
        <span class="acc-tag acc-tag-outline" style="top:44%; left:41%; transform:rotate(2.5deg); font-size:12px;">aria-label</span>
      </div>
    </div>

  </section>

  <!-- ===== FEATURES ===== -->
  <section class="acc-body">
    <div class="container-xl px-4">

      <div class="acc-section-header">
        <p class="acc-kicker">What we support</p>
        <h2 class="acc-section-title">Built for <span>Everyone</span></h2>
        <p class="acc-section-desc">
          ClicKet is developed against WCAG 2.1 Level AA. Here is a plain-language breakdown of the accessibility features you can count on across the platform.
        </p>
      </div>

      <?php
      $icons = [
        'keyboard'    => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M8 14h8"/></svg>',
        'screenreader'=> '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>',
        'colour'      => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20"/><path d="M12 6a6 6 0 0 1 0 12"/></svg>',
        'resize'      => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>',
        'motion'      => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3l14 9-14 9V3z"/><line x1="19" y1="3" x2="19" y2="21"/></svg>',
        'forms'       => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
      ];
      ?>

      <div class="acc-feature-list">
        <?php foreach ($features as $idx => $feat): ?>
          <article class="acc-feature-card" id="<?= $feat['id'] ?>">
            <div class="acc-feature-aside" aria-hidden="true">
              <span class="acc-feature-icon"><?= $icons[$feat['icon']] ?></span>
              <span class="acc-feature-num"><?= sprintf('%02d', $idx + 1) ?></span>
            </div>
            <div class="acc-feature-body">
              <h3><?= htmlspecialchars($feat['title']) ?></h3>
              <p><?= htmlspecialchars($feat['desc']) ?></p>
              <ul class="acc-detail-list" role="list">
                <?php foreach ($feat['details'] as $detail): ?>
                  <li class="acc-detail-item"><?= htmlspecialchars($detail) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- Standards Grid -->
      <div class="acc-standards">
        <p class="acc-kicker">Compliance & Testing</p>
        <h2 class="acc-section-title" style="font-size:clamp(26px,3vw,38px);">Standards &amp; <span>Tools</span></h2>
        <div class="acc-standards-grid">
          <?php foreach ($standards as $std): ?>
            <div class="acc-std-card">
              <span class="acc-std-badge">Standard</span>
              <abbr title="<?= htmlspecialchars($std['desc']) ?>"><?= htmlspecialchars($std['abbr']) ?></abbr>
              <p><?= htmlspecialchars($std['desc']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Contact -->
      <div class="acc-contact" role="complementary" aria-label="Accessibility feedback">
        <div>
          <h3>Found a barrier? Tell us.</h3>
          <p>
            If you encounter any part of ClicKet that is difficult or impossible to use with your assistive technology, we want to know. Our team will investigate and prioritise a fix. No request is too small.
          </p>
        </div>
        <a href="contact.php" class="acc-contact-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Send Feedback
        </a>
      </div>

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

  // Animate feature cards on scroll
  const cards = document.querySelectorAll('.acc-feature-card');

  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    cards.forEach((card, i) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = `opacity .4s ease ${i * 0.07}s, transform .4s ease ${i * 0.07}s, border-color var(--dur-fast), box-shadow var(--dur-fast)`;
      io.observe(card);
    });
  }

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
})();
</script>
</body>
</html>
