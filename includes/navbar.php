<?php
// includes/navbar.php — ClicKet Top Navigation
require_once __DIR__ . '/log.php';
$navUser = currentUser();
$navUserLabel = userDisplayName($navUser);
?>
<nav class="navbar-clicket">
  <div class="navbar-inner">

    <!-- Logo -->
    <a href="index.php" class="nav-logo">
      <span class="logo-icon">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M20 12C20 7.58 16.42 4 12 4C7.58 4 4 7.58 4 12C4 14.85 5.44 17.37 7.64 18.9L6.5 21H17.5L16.36 18.9C18.56 17.37 20 14.85 20 12Z"
                fill="white"/>
          <rect x="9" y="10" width="6" height="1.5" rx=".75" fill="#E8162B"/>
          <rect x="9" y="12.5" width="4" height="1.5" rx=".75" fill="#E8162B"/>
        </svg>
      </span>
      <span class="logo-clic">Clic</span><span class="logo-ket">Ket</span>
    </a>

    <!-- Nav links (desktop) -->
    <ul class="nav-links">
      <li><a href="index.php" class="active">Home</a></li>
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
