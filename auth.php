<?php
require_once __DIR__ . '/includes/log.php';

$mode = $_GET['mode'] ?? 'login';
$errors = [];
$notif = pullFlashMessage();

if (isset($_GET['logout'])) {
    logoutUser();

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    setFlashMessage('success', 'You have been signed out successfully.');
    header('Location: auth.php?mode=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'login';

    if ($mode === 'signup') {
        $result = registerUser(
            $_POST['name'] ?? '',
            $_POST['email'] ?? '',
            $_POST['password'] ?? '',
            $_POST['confirm_password'] ?? ''
        );
    } else {
        $result = loginWithEmail($_POST['email'] ?? '', $_POST['password'] ?? '');
    }

    if ($result['success']) {
        $authUser = currentUser();
        $firstName = userDisplayName($authUser);
        $message = $mode === 'signup'
            ? 'Welcome to ClicKet' . ($firstName !== '' ? ', ' . $firstName : '') . '!'
            : 'Welcome back' . ($firstName !== '' ? ', ' . $firstName : '') . '!';

        setFlashMessage('success', $message);
        header('Location: index.php');
        exit;
    }

    $errors = $result['errors'];
}

$user = currentUser();

if ($user && $mode !== 'account') {
    $mode = 'account';
}

if (!$user && $mode === 'account') {
    $mode = 'login';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ClicKet</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-body auth-mode-<?= $mode === 'signup' ? 'signup' : ($mode === 'account' ? 'account' : 'login') ?>">

<!-- ============================================================
     FLASH NOTIFICATION BAR
     ============================================================ -->
<?php if ($notif): ?>
<div class="ck-notif-bar ck-notif-bar--<?= htmlspecialchars($notif['type']) ?>" id="ckNotifBar" role="status" aria-live="polite">
  <span class="ck-notif-bar__dot"></span>
  <?= htmlspecialchars($notif['message']) ?>
</div>
<?php endif; ?>

<!-- ============================================================
     FULL-SCREEN ANIMATED BACKGROUND CARD GRID
     All columns animate continuously behind the glass form panel.
     Replace the `background-image` URLs below with your own photos.
     Each card slot is labelled with a comment for easy swapping.
     ============================================================ -->
<div class="bg-stage" aria-hidden="true">

  <!-- Tinted global overlay — dims the cards so glass panel pops -->
  <div class="bg-stage__scrim"></div>

  <div class="bg-grid">

    <!-- ── COLUMN 1 — drifts upward slowly ────────────────── -->
    <div class="bg-col bg-col--1">

      <!-- [BG IMAGE SLOT 1 — e.g. Concert crowd wide shot] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=600&q=80')">
        <div class="bg-card__badge">Live</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Rock Arena</span>
          <span class="bg-card__meta">Manila · Tonight</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 2 — e.g. Theater stage with dramatic lighting] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1507676184212-d03ab07a01bf?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">West Side Story</span>
          <span class="bg-card__meta">CCP · Jun 14</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 3 — e.g. Basketball arena overhead] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1546519638-68e109498ffc?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">PBA Finals</span>
          <span class="bg-card__meta">Araneta · Jun 20</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 4 — e.g. DJ / electronic music festival] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1571266028243-e4733b0f0bb0?w=600&q=80')">
        <div class="bg-card__badge bg-card__badge--red">Hot</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Electric Fest</span>
          <span class="bg-card__meta">BGC · Jul 5</span>
        </div>
      </div>

      <!-- Duplicates below for seamless infinite scroll loop -->
      <!-- [BG IMAGE SLOT 1 — duplicate] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=600&q=80')">
        <div class="bg-card__badge">Live</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Rock Arena</span>
          <span class="bg-card__meta">Manila · Tonight</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 2 — duplicate] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1507676184212-d03ab07a01bf?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">West Side Story</span>
          <span class="bg-card__meta">CCP · Jun 14</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 3 — duplicate] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1546519638-68e109498ffc?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">PBA Finals</span>
          <span class="bg-card__meta">Araneta · Jun 20</span>
        </div>
      </div>

    </div><!-- /bg-col--1 -->

    <!-- ── COLUMN 2 — drifts downward (opposite direction) ─ -->
    <div class="bg-col bg-col--2">

      <!-- [BG IMAGE SLOT 5 — e.g. Ballet or classical performance] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1518834107812-67b0b7c58434?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Swan Lake</span>
          <span class="bg-card__meta">RCBC · Jun 28</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 6 — e.g. Boxing / MMA fight night] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1555597673-b21d5c935865?w=600&q=80')">
        <div class="bg-card__badge bg-card__badge--red">Sold Out</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Fight Night</span>
          <span class="bg-card__meta">SM Mall · Jul 12</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 7 — e.g. Acoustic / indie artist performing] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Indie Night</span>
          <span class="bg-card__meta">Saguijo · Jun 30</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 8 — e.g. Outdoor stadium / fireworks] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Grand Finale</span>
          <span class="bg-card__meta">MOA Arena · Aug 1</span>
        </div>
      </div>

      <!-- Duplicates for seamless loop -->
      <!-- [BG IMAGE SLOT 5 — duplicate] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1518834107812-67b0b7c58434?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Swan Lake</span>
          <span class="bg-card__meta">RCBC · Jun 28</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 6 — duplicate] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1555597673-b21d5c935865?w=600&q=80')">
        <div class="bg-card__badge bg-card__badge--red">Sold Out</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Fight Night</span>
          <span class="bg-card__meta">SM Mall · Jul 12</span>
        </div>
      </div>

    </div><!-- /bg-col--2 -->

    <!-- ── COLUMN 3 — drifts upward, slightly faster ───────── -->
    <div class="bg-col bg-col--3">

      <!-- [BG IMAGE SLOT 9 — e.g. K-pop / pop concert with stage lights] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=600&q=80')">
        <div class="bg-card__badge">New</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Pop Spectacular</span>
          <span class="bg-card__meta">Araneta · Jul 19</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 10 — e.g. Comedy stand-up performer] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1527224538127-2104bb71c51b?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Laugh Factory</span>
          <span class="bg-card__meta">Meralco · Jul 3</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 11 — e.g. Orchestra / symphony hall interior] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1465847899084-d164df4dedc6?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">PhilOrchestra</span>
          <span class="bg-card__meta">CCP · Jun 22</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 12 — e.g. Volleyball / football match] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=600&q=80')">
        <div class="bg-card__badge bg-card__badge--red">Hot</div>
        <div class="bg-card__info">
          <span class="bg-card__title">V-League Finals</span>
          <span class="bg-card__meta">FilOil · Aug 8</span>
        </div>
      </div>

      <!-- Duplicates for seamless loop -->
      <!-- [BG IMAGE SLOT 9 — duplicate] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=600&q=80')">
        <div class="bg-card__badge">New</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Pop Spectacular</span>
          <span class="bg-card__meta">Araneta · Jul 19</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 10 — duplicate] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1527224538127-2104bb71c51b?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Laugh Factory</span>
          <span class="bg-card__meta">Meralco · Jul 3</span>
        </div>
      </div>

    </div><!-- /bg-col--3 -->

    <!-- ── COLUMN 4 — drifts downward, slowest ─────────────── -->
    <div class="bg-col bg-col--4">

      <!-- [BG IMAGE SLOT 13 — e.g. Broadway / musical theater cast bow] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1503095396549-807759245b35?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Hamilton PH</span>
          <span class="bg-card__meta">RCBC · Jul 26</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 14 — e.g. Surfing / extreme sports event] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1502680390469-be75c86b636f?w=600&q=80')">
        <div class="bg-card__badge">New</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Surf Open</span>
          <span class="bg-card__meta">Siargao · Aug 15</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 15 — e.g. Jazz or blues bar performer] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1415201364774-f6f0bb35f28f?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Jazz at Midnight</span>
          <span class="bg-card__meta">B-Side · Jun 27</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 16 — e.g. Marathon / running race start line] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=600&q=80')">
        <div class="bg-card__badge bg-card__badge--red">Soon</div>
        <div class="bg-card__info">
          <span class="bg-card__title">BGC Run Fest</span>
          <span class="bg-card__meta">BGC · Sep 7</span>
        </div>
      </div>

      <!-- Duplicates for seamless loop -->
      <!-- [BG IMAGE SLOT 13 — duplicate] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1503095396549-807759245b35?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Hamilton PH</span>
          <span class="bg-card__meta">RCBC · Jul 26</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 14 — duplicate] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1502680390469-be75c86b636f?w=600&q=80')">
        <div class="bg-card__badge">New</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Surf Open</span>
          <span class="bg-card__meta">Siargao · Aug 15</span>
        </div>
      </div>

    </div><!-- /bg-col--4 -->

    <!-- ── COLUMN 5 — drifts upward, medium speed ──────────── -->
    <div class="bg-col bg-col--5">

      <!-- [BG IMAGE SLOT 17 — e.g. Hip-hop / rap concert crowd] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&q=80')">
        <div class="bg-card__badge">Live</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Rap Summit</span>
          <span class="bg-card__meta">Araneta · Jul 8</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 18 — e.g. Circus or acrobatics show] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Cirque Manila</span>
          <span class="bg-card__meta">MOA · Jul 22</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 19 — e.g. Swimming / aquatics competition] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1530549387789-4c1017266635?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Aquatics Open</span>
          <span class="bg-card__meta">PhilSports · Aug 3</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 20 — e.g. Art fair or gallery opening] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1531058020387-3be344556be6?w=600&q=80')">
        <div class="bg-card__badge bg-card__badge--red">Hot</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Art Fair PH</span>
          <span class="bg-card__meta">The Link · Mar 2027</span>
        </div>
      </div>

      <!-- Duplicates for seamless loop -->
      <!-- [BG IMAGE SLOT 17 — duplicate] -->
      <div class="bg-card bg-card--tall" style="background-image:url('https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&q=80')">
        <div class="bg-card__badge">Live</div>
        <div class="bg-card__info">
          <span class="bg-card__title">Rap Summit</span>
          <span class="bg-card__meta">Araneta · Jul 8</span>
        </div>
      </div>

      <!-- [BG IMAGE SLOT 18 — duplicate] -->
      <div class="bg-card" style="background-image:url('https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&q=80')">
        <div class="bg-card__info">
          <span class="bg-card__title">Cirque Manila</span>
          <span class="bg-card__meta">MOA · Jul 22</span>
        </div>
      </div>

    </div><!-- /bg-col--5 -->

  </div><!-- /bg-grid -->
</div><!-- /bg-stage -->

<!-- ============================================================
     CENTERED PAGE WRAPPER
     ============================================================ -->
<div class="auth-page">

  <!-- Top bar: logo left, back button right — sits above glass card -->
  <header class="auth-topbar">
    <a href="index.php" class="auth-brand" aria-label="ClicKet — home">
      <div class="auth-brand__mark">
        <img src="assets/Icon_Logo.png" alt="" aria-hidden="true">
      </div>
      <span class="auth-brand__name">
        <img src="assets/Name_Logo.png" alt="ClicKet">
      </span>
    </a>

    <a href="index.php" class="auth-back-btn">
      <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Back to Home
    </a>
  </header>

  <!-- ============================================================
       GLASSMORPHIC FORM PANEL — centered on screen
       ============================================================ -->
  <main class="glass-wrap">
    <div class="glass-card auth-panel" data-auth-mode="<?= $mode === 'signup' ? 'signup' : ($mode === 'account' ? 'account' : 'login') ?>">

      <?php if ($mode === 'account' && $user): ?>
        <!-- ====================================================
             ACCOUNT VIEW
             ==================================================== -->
        <div class="gc-header">
          <p class="gc-eyebrow">My Account</p>
          <h2 class="gc-title">Hello, <?= htmlspecialchars($user['name']) ?></h2>
          <p class="gc-copy">You are signed in and ready to continue booking. Your ticket activity is available below.</p>
        </div>

        <div class="account-summary">
          <div class="account-summary-item">
            <span class="account-summary-label">Account Status</span>
            <strong class="account-summary-value account-summary-value--active">Active</strong>
          </div>
          <div class="account-summary-item">
            <span class="account-summary-label">Email</span>
            <strong class="account-summary-value"><?= htmlspecialchars($user['email']) ?></strong>
          </div>
        </div>

        <div class="gc-actions">
          <a href="index.php#concerts" class="btn-primary">Browse Events</a>
          <a href="auth.php?logout=1" class="btn-ghost">Log Out</a>
        </div>

      <?php else: ?>
        <!-- ====================================================
             LOGIN / SIGNUP FORM
             ==================================================== -->
        <div class="gc-header">
          <p class="gc-eyebrow" data-auth-eyebrow><?= $mode === 'signup' ? 'Create Account' : 'Welcome Back' ?></p>
          <h2 class="gc-title" data-auth-title><?= $mode === 'signup' ? 'Join ClicKet' : 'Sign in to continue' ?></h2>
          <p class="gc-copy" data-auth-copy>
            <?= $mode === 'signup'
              ? 'Set up your account to book events faster and keep all your tickets in one place.'
              : 'Access your tickets, event history, and booking details.' ?>
          </p>
        </div>

        <!-- Mode toggle tabs -->
        <div class="mode-tabs" role="tablist">
          <a href="auth.php?mode=login"
             class="mode-tab <?= $mode !== 'signup' ? 'mode-tab--active' : '' ?>"
             data-auth-switch="login"
             role="tab"
             aria-selected="<?= $mode !== 'signup' ? 'true' : 'false' ?>">
            Log In
          </a>
          <a href="auth.php?mode=signup"
             class="mode-tab <?= $mode === 'signup' ? 'mode-tab--active' : '' ?>"
             data-auth-switch="signup"
             role="tab"
             aria-selected="<?= $mode === 'signup' ? 'true' : 'false' ?>">
            Sign Up
          </a>
        </div>

        <?php if ($errors): ?>
          <div class="auth-alert" role="alert">
            <svg class="auth-alert__icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.4"/>
              <line x1="8" y1="5" x2="8" y2="8.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
              <circle cx="8" cy="11" r=".7" fill="currentColor"/>
            </svg>
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form class="auth-form" method="post" action="auth.php" id="authForm" novalidate>
          <input type="hidden" name="mode" id="authModeInput" value="<?= $mode === 'signup' ? 'signup' : 'login' ?>">

          <div class="field-group auth-signup-field <?= $mode === 'signup' ? '' : 'auth-field--hidden' ?>">
            <label class="field-label" for="nameField">Full Name</label>
            <input
              class="field-input"
              type="text"
              id="nameField"
              name="name"
              value="<?= oldInput('name') ?>"
              placeholder="e.g. Maria Santos"
              autocomplete="name"
              <?= $mode === 'signup' ? 'required' : 'disabled' ?>
            >
          </div>

          <div class="field-group">
            <label class="field-label" for="emailField">Email Address</label>
            <input
              class="field-input"
              type="email"
              id="emailField"
              name="email"
              value="<?= oldInput('email') ?>"
              placeholder="you@email.com"
              autocomplete="email"
              required
            >
          </div>

          <div class="field-group">
            <label class="field-label" for="pwField">Password</label>
            <div class="field-wrap">
              <input
                class="field-input"
                type="password"
                name="password"
                id="pwField"
                placeholder="<?= $mode === 'signup' ? 'Minimum 8 characters' : 'Enter your password' ?>"
                autocomplete="<?= $mode === 'signup' ? 'new-password' : 'current-password' ?>"
                required
              >
              <button type="button" class="pw-toggle" id="pwToggle" aria-label="Show password">
                <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                  <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="field-group auth-signup-field <?= $mode === 'signup' ? '' : 'auth-field--hidden' ?>">
            <label class="field-label" for="pwConfirmField">Confirm Password</label>
            <div class="field-wrap">
              <input
                class="field-input"
                type="password"
                name="confirm_password"
                id="pwConfirmField"
                placeholder="Repeat your password"
                autocomplete="new-password"
                <?= $mode === 'signup' ? 'required' : 'disabled' ?>
              >
              <button type="button" class="pw-toggle" id="pwConfirmToggle" aria-label="Show confirm password">
                <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                  <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="auth-submit" id="authSubmit">
            <span class="btn-label" data-auth-submit-label><?= $mode === 'signup' ? 'Create Account' : 'Log In' ?></span>
            <span class="spinner" aria-hidden="true"></span>
          </button>
        </form>

        <p class="gc-footer-note">
          By continuing, you agree to ClicKet's <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.
        </p>

      <?php endif; ?>

    </div><!-- /glass-card -->
  </main>

</div><!-- /auth-page -->

<script>
(function () {
  const bgColumns = document.querySelectorAll('.bg-col');

  bgColumns.forEach((column) => {
    const cards = Array.from(column.children).filter((child) => child.classList.contains('bg-card'));
    if (!cards.length) return;

    const fragment = document.createDocumentFragment();
    cards.forEach((card) => {
      const clone = card.cloneNode(true);
      clone.setAttribute('aria-hidden', 'true');
      fragment.appendChild(clone);
    });

    column.appendChild(fragment);
  });

  /* ── Password visibility toggles ──────────────────── */
  /* Smooth mode switching between login and signup panels */
  const panel = document.querySelector('.auth-panel');
  const modeLinks = document.querySelectorAll('[data-auth-switch]');
  const motionAllowed = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canAnimateAuthMode = panel && panel.dataset.authMode !== 'account';
  const modeInput = document.getElementById('authModeInput');
  const signupFields = document.querySelectorAll('.auth-signup-field');
  const authAlert = document.querySelector('.auth-alert');
  const authText = {
    login: {
      eyebrow: 'Welcome Back',
      title: 'Sign in to continue',
      copy: 'Access your tickets, event history, and booking details.',
      submit: 'Log In',
      passwordPlaceholder: 'Enter your password',
      passwordAutocomplete: 'current-password'
    },
    signup: {
      eyebrow: 'Create Account',
      title: 'Join ClicKet',
      copy: 'Set up your account to book events faster and keep all your tickets in one place.',
      submit: 'Create Account',
      passwordPlaceholder: 'Minimum 8 characters',
      passwordAutocomplete: 'new-password'
    }
  };
  const authNodes = {
    eyebrow: document.querySelector('[data-auth-eyebrow]'),
    title: document.querySelector('[data-auth-title]'),
    copy: document.querySelector('[data-auth-copy]'),
    submit: document.querySelector('[data-auth-submit-label]'),
    password: document.getElementById('pwField')
  };

  if (canAnimateAuthMode && motionAllowed) {
    panel.classList.add('auth-panel--enter-forward');

    requestAnimationFrame(() => {
      panel.classList.add('auth-panel--ready');
    });
  }

  function setAuthMode(mode, shouldUpdateUrl) {
    const isSignup = mode === 'signup';
    const nextText = authText[mode];
    if (!nextText || !panel) return;

    panel.dataset.authMode = mode;
    document.body.classList.toggle('auth-mode-login', !isSignup);
    document.body.classList.toggle('auth-mode-signup', isSignup);
    if (modeInput) modeInput.value = mode;

    if (authNodes.eyebrow) authNodes.eyebrow.textContent = nextText.eyebrow;
    if (authNodes.title) authNodes.title.textContent = nextText.title;
    if (authNodes.copy) authNodes.copy.textContent = nextText.copy;
    if (authNodes.submit) authNodes.submit.textContent = nextText.submit;
    if (authNodes.password) {
      authNodes.password.placeholder = nextText.passwordPlaceholder;
      authNodes.password.setAttribute('autocomplete', nextText.passwordAutocomplete);
    }

    signupFields.forEach((field) => {
      field.classList.toggle('auth-field--hidden', !isSignup);
      field.querySelectorAll('input').forEach((input) => {
        input.disabled = !isSignup;
        input.required = isSignup;
      });
    });

    modeLinks.forEach((modeLink) => {
      const isActive = modeLink.dataset.authSwitch === mode;
      modeLink.classList.toggle('mode-tab--active', isActive);
      modeLink.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    if (authAlert) authAlert.classList.add('auth-alert--dismissed');

    if (shouldUpdateUrl) {
      const nextUrl = new URL(window.location.href);
      nextUrl.searchParams.set('mode', mode);
      window.history.pushState({ authMode: mode }, '', nextUrl);
    }
  }

  modeLinks.forEach((link) => {
    link.addEventListener('click', function (event) {
      event.preventDefault();
      const nextMode = link.dataset.authSwitch;
      if (!canAnimateAuthMode || link.getAttribute('aria-selected') === 'true') return;

      const currentMode = panel.dataset.authMode;
      const direction = currentMode === 'signup' && nextMode === 'login' ? 'back' : 'forward';

      if (!motionAllowed) {
        setAuthMode(nextMode, true);
        return;
      }

      document.body.classList.add('auth-bg--switching');
      panel.classList.add(`auth-panel--swap-out-${direction}`);
      window.setTimeout(() => {
        setAuthMode(nextMode, true);
        panel.classList.remove(`auth-panel--swap-out-${direction}`);
        panel.classList.add(`auth-panel--swap-in-${direction}`);

        requestAnimationFrame(() => {
          panel.classList.remove(`auth-panel--swap-in-${direction}`);
        });

        window.setTimeout(() => {
          document.body.classList.remove('auth-bg--switching');
        }, 280);
      }, 140);
    });
  });

  window.addEventListener('popstate', function () {
    const params = new URLSearchParams(window.location.search);
    const mode = params.get('mode') === 'signup' ? 'signup' : 'login';
    setAuthMode(mode, false);
  });

  function bindToggle(toggleId, fieldId) {
    const btn   = document.getElementById(toggleId);
    const field = document.getElementById(fieldId);
    if (!btn || !field) return;

    btn.addEventListener('click', function () {
      const isHidden = field.type === 'password';
      field.type = isHidden ? 'text' : 'password';
      btn.classList.toggle('pw-visible', isHidden);
      btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    });
  }

  bindToggle('pwToggle',        'pwField');
  bindToggle('pwConfirmToggle', 'pwConfirmField');

  /* ── Submit loading state ──────────────────────────── */
  const form   = document.getElementById('authForm');
  const submit = document.getElementById('authSubmit');

  if (form && submit) {
    form.addEventListener('submit', function () {
      submit.classList.add('loading');
      submit.disabled = true;
    });
  }

  /* ── Flash notification bar ────────────────────────── */
  const notifBar = document.getElementById('ckNotifBar');

  if (notifBar) {
    requestAnimationFrame(() => {
      requestAnimationFrame(() => notifBar.classList.add('ck-notif-bar--show'));
    });

    setTimeout(() => {
      notifBar.classList.remove('ck-notif-bar--show');
      setTimeout(() => notifBar.remove(), 450);
    }, 4200);
  }

})();
</script>
</body>
</html>
