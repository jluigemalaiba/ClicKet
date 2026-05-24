<?php
// includes/navbar.php — ClicKet Top Navigation with Dynamic Active State
require_once __DIR__ . '/log.php';
$navUser = currentUser();
$navUserLabel = userDisplayName($navUser);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar-clicket">
  <div class="navbar-inner">

    <!-- Logo -->
    <a href="index.php" class="nav-logo">
      <span class="logo-icon">
        <img src="assets/Icon_Logo.png" alt="" aria-hidden="true">
      </span>
      <span class="logo-name">
        <img src="assets/Name_Logo.png" alt="ClicKet">
      </span>
    </a>

    <!-- Nav links (desktop) -->
    <ul class="nav-links">
      <li><a href="index.php" class="<?= ($currentPage === 'index.php') ? 'active' : '' ?>">Home</a></li>
      <li><a href="about.php" class="<?= ($currentPage === 'about.php') ? 'active' : '' ?>">About Us</a></li>
      <li><a href="#">Concerts</a></li>
      <li><a href="#">Theater Plays</a></li>
      <li><a href="#">Sports Events</a></li>
      <li><a href="#">Venues</a></li>
      <li><a href="auth.php?mode=account">My Tickets</a></li>
    </ul>

    <!-- Actions -->
    <div class="nav-actions">
      <button class="nav-search-btn" aria-label="Search">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
      </button>
      <?php if ($navUser): ?>
        <a href="auth.php?mode=account" class="nav-btn-login nav-user-pill" title="<?= htmlspecialchars($navUser['name']) ?>"><?= htmlspecialchars($navUserLabel) ?></a>
        <a href="auth.php?logout=1" class="nav-btn-signup">Log Out</a>
      <?php else: ?>
        <a href="auth.php?mode=login" class="nav-btn-login">Log In</a>
        <a href="auth.php?mode=signup" class="nav-btn-signup">Sign Up</a>
      <?php endif; ?>
      <button class="nav-hamburger d-lg-none" aria-label="Menu" id="navHamburger">
        <span></span><span></span><span></span>
      </button>
    </div>

  </div>
</nav>

<!-- Mobile Nav Drawer -->
<div class="mobile-nav-drawer" id="mobileNavDrawer">
  <button class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Close menu">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
  </button>
  <a href="index.php" class="mobile-nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>">Home</a>
  <a href="about.php" class="mobile-nav-link <?= ($currentPage === 'about.php') ? 'active' : '' ?>">About Us</a>
  <a href="#" class="mobile-nav-link">Concerts</a>
  <a href="#" class="mobile-nav-link">Theater Plays</a>
  <a href="#" class="mobile-nav-link">Sports Events</a>
  <a href="#" class="mobile-nav-link">Venues</a>
  <a href="auth.php?mode=account" class="mobile-nav-link">My Tickets</a>
</div>

<script>
  // Mobile drawer toggle
  document.getElementById('navHamburger').addEventListener('click', () => {
    document.getElementById('mobileNavDrawer').classList.add('open');
  });
  document.getElementById('mobileDrawerClose').addEventListener('click', () => {
    document.getElementById('mobileNavDrawer').classList.remove('open');
  });
  document.getElementById('mobileNavDrawer').addEventListener('click', (e) => {
    if (e.target.classList.contains('mobile-nav-link')) {
      document.getElementById('mobileNavDrawer').classList.remove('open');
    }
  });
</script>
