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
        $message = $mode === 'signup'
            ? 'Your account has been created successfully.'
            : 'Signed in successfully. Welcome back.';

        setFlashMessage('success', $message);
        header('Location: auth.php?mode=account');
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
  <title><?= $mode === 'signup' ? 'Sign Up' : ($mode === 'account' ? 'My Account' : 'Log In') ?> | ClicKet</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/partners-footer.css">
  <link rel="stylesheet" href="css/auth.css">
</head>
<body>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<?php if ($notif): ?>
<div class="ck-notif-bar ck-notif-bar--<?= htmlspecialchars($notif['type']) ?>" id="ckNotifBar" role="status" aria-live="polite">
  <span class="ck-notif-bar__dot"></span>
  <?= htmlspecialchars($notif['message']) ?>
</div>
<?php endif; ?>

<div class="mobile-nav-drawer" id="mobileDrawer">
  <button class="mobile-drawer-close" id="drawerClose" aria-label="Close menu">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
  </button>
  <a href="index.php">Home</a>
  <a href="index.php#concerts">Concerts</a>
  <a href="index.php#theater">Theater Plays</a>
  <a href="index.php#sports">Sports Events</a>
  <a href="#">Venues</a>
  <a href="auth.php?mode=account">My Tickets</a>
  <?php if ($user): ?>
    <a href="auth.php?logout=1">Log Out</a>
  <?php else: ?>
    <a href="auth.php?mode=login">Log In</a>
    <a href="auth.php?mode=signup">Sign Up</a>
  <?php endif; ?>
</div>

<main class="auth-page">
  <section class="auth-wrap">
    <div class="auth-visual">
      <div class="auth-logo" aria-label="ClicKet">
        <div class="auth-logo-mark">
          <svg viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="1.5" y="5" width="19" height="12" rx="2.5" stroke="#0f0f0f" stroke-width="1.6"/>
            <path d="M7.5 5V4a2 2 0 0 1 4 0v1" stroke="#e8162b" stroke-width="1.6" stroke-linecap="round"/>
            <path d="M14.5 5V4a1.5 1.5 0 0 1 3 0v1" stroke="#e8162b" stroke-width="1.6" stroke-linecap="round"/>
            <line x1="7" y1="11" x2="15" y2="11" stroke="#0f0f0f" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="7" y1="14" x2="11" y2="14" stroke="#0f0f0f" stroke-width="1.4" stroke-linecap="round"/>
          </svg>
        </div>
        <span class="auth-logo-name">ClicKet</span>
      </div>

      <div class="auth-badge" aria-hidden="true">
        <span class="auth-badge-dot"></span>
        Events Live
      </div>

      <div class="auth-visual-body">
        <p class="auth-kicker">ClicKet</p>
        <h1>
          <?php if ($mode === 'signup'): ?>
            Your seat <em>awaits</em> you.
          <?php elseif ($mode === 'account'): ?>
            Your ticket <em>hub.</em>
          <?php else: ?>
            Every great show<br><em>starts</em> here.
          <?php endif; ?>
        </h1>
        <p>Book concerts, theater plays, and sports events with secure checkout, instant e-tickets, and one clean place to manage your account.</p>
      </div>
    </div>

    <div class="auth-panel">
      <?php if ($mode === 'account' && $user): ?>
        <div class="auth-panel-header">
          <p class="auth-eyebrow">My Account</p>
          <h2>Hello, <?= htmlspecialchars($user['name']) ?></h2>
          <p class="auth-copy">You are signed in and ready to continue booking. Your ticket activity and saved events can be surfaced here next.</p>
        </div>

        <div class="account-summary">
          <div class="account-summary-item">
            <span class="account-summary-label">Account Status</span>
            <strong>Active</strong>
          </div>
          <div class="account-summary-item">
            <span class="account-summary-label">Email</span>
            <strong><?= htmlspecialchars($user['email']) ?></strong>
          </div>
        </div>

        <div class="auth-actions">
          <a href="index.php#concerts" class="auth-button-primary">Browse Events</a>
          <a href="auth.php?logout=1" class="auth-secondary">Log Out</a>
        </div>
      <?php else: ?>
        <div class="auth-panel-header">
          <p class="auth-eyebrow"><?= $mode === 'signup' ? 'Create Account' : 'Account Access' ?></p>
          <h2><?= $mode === 'signup' ? 'Create your ClicKet account' : 'Welcome back' ?></h2>
          <p class="auth-copy">
            <?= $mode === 'signup'
              ? 'Set up your account to book events faster and keep all your tickets in one place.'
              : 'Sign in to continue to your tickets, event activity, and booking details.' ?>
          </p>
        </div>

        <?php if ($errors): ?>
          <div class="auth-alert" role="alert">
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form class="auth-form" method="post" action="auth.php" id="authForm" novalidate>
          <input type="hidden" name="mode" value="<?= $mode === 'signup' ? 'signup' : 'login' ?>">

          <?php if ($mode === 'signup'): ?>
            <label>
              Full Name
              <input
                type="text"
                name="name"
                value="<?= oldInput('name') ?>"
                placeholder="e.g. Maria Santos"
                autocomplete="name"
                required
              >
            </label>
          <?php endif; ?>

          <label>
            Email Address
            <input
              type="email"
              name="email"
              value="<?= oldInput('email') ?>"
              placeholder="you@email.com"
              autocomplete="email"
              required
            >
          </label>

          <label>
            Password
            <div class="auth-input-wrap">
              <input
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
          </label>

          <?php if ($mode === 'signup'): ?>
            <label>
              Confirm Password
              <div class="auth-input-wrap">
                <input
                  type="password"
                  name="confirm_password"
                  id="pwConfirmField"
                  placeholder="Repeat your password"
                  autocomplete="new-password"
                  required
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
            </label>
          <?php endif; ?>

          <button type="submit" class="auth-submit" id="authSubmit">
            <span class="btn-label"><?= $mode === 'signup' ? 'Create Account' : 'Log In' ?></span>
            <span class="spinner" aria-hidden="true"></span>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
(function () {
  const nav = document.querySelector('.navbar-clicket');
  const hamburger = document.getElementById('navHamburger');
  const drawer = document.getElementById('mobileDrawer');
  const close = document.getElementById('drawerClose');

  function onScroll() {
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 20);
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  if (hamburger && drawer) hamburger.addEventListener('click', () => drawer.classList.add('open'));
  if (close && drawer) close.addEventListener('click', () => drawer.classList.remove('open'));

  function bindToggle(toggleId, fieldId) {
    const btn = document.getElementById(toggleId);
    const field = document.getElementById(fieldId);

    if (!btn || !field) {
      return;
    }

    btn.addEventListener('click', function () {
      const isHidden = field.type === 'password';
      field.type = isHidden ? 'text' : 'password';
      btn.classList.toggle('pw-visible', isHidden);
      btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    });
  }

  bindToggle('pwToggle', 'pwField');
  bindToggle('pwConfirmToggle', 'pwConfirmField');

  const form = document.getElementById('authForm');
  const submit = document.getElementById('authSubmit');

  if (form && submit) {
    form.addEventListener('submit', function () {
      submit.classList.add('loading');
      submit.disabled = true;
    });
  }

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
