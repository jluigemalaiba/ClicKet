<?php
// includes/navbar.php — ClicKet Top Navigation with Dynamic Active State
require_once __DIR__ . '/log.php';
$navUser = currentUser();
$navUserLabel = userDisplayName($navUser);
$navUserInitial = $navUserLabel !== '' ? strtoupper(substr($navUserLabel, 0, 1)) : 'U';
$currentPage = basename($_SERVER['PHP_SELF']);
$eventPages = ['events.php', 'concerts.php', 'theater.php', 'sports.php'];
$isEventsActive = in_array($currentPage, $eventPages, true);
$navSearchValue = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
?>
<nav class="navbar-clicket navbar navbar-expand-xl">
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

    <button class="nav-hamburger navbar-toggler d-xl-none" type="button" data-bs-toggle="collapse" data-bs-target="#clicketNavbar" aria-controls="clicketNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="nav-hamburger-lines" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
      </span>
    </button>

    <div class="collapse navbar-collapse clicket-navbar-collapse" id="clicketNavbar">
      <!-- Nav links -->
      <ul class="nav-links navbar-nav">
        <li><a href="index.php" class="<?= ($currentPage === 'index.php') ? 'active' : '' ?>">Home</a></li>
        <li><a href="about.php" class="<?= ($currentPage === 'about.php') ? 'active' : '' ?>">About Us</a></li>
        <li class="nav-item dropdown nav-events-dropdown">
          <button class="nav-dropdown-toggle <?= $isEventsActive ? 'active' : '' ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Events
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <ul class="dropdown-menu nav-events-menu">
            <li><a class="dropdown-item <?= ($currentPage === 'events.php') ? 'active' : '' ?>" href="events.php">All</a></li>
            <li><a class="dropdown-item <?= ($currentPage === 'concerts.php') ? 'active' : '' ?>" href="concerts.php">Concerts</a></li>
            <li><a class="dropdown-item <?= ($currentPage === 'theater.php') ? 'active' : '' ?>" href="theater.php">Theater Plays</a></li>
            <li><a class="dropdown-item <?= ($currentPage === 'sports.php') ? 'active' : '' ?>" href="sports.php">Sports Events</a></li>
          </ul>
        </li>
        <li><a href="venues.php" class="<?= ($currentPage === 'venues.php') ? 'active' : '' ?>">Venues</a></li>
      </ul>

      <!-- Actions -->
      <div class="nav-actions">
        <form class="nav-search-form <?= $navSearchValue !== '' ? 'is-open' : '' ?>" action="events.php" method="get" role="search">
          <input type="search" name="search" value="<?= htmlspecialchars($navSearchValue) ?>" placeholder="Search events" aria-label="Search events">
          <button class="nav-search-btn" type="button" aria-label="Open search">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </button>
        </form>
        <?php if ($navUser): ?>
          <div class="nav-profile dropdown">
            <button class="nav-profile-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Open profile menu">
              <span class="nav-profile-avatar" aria-hidden="true"><?= htmlspecialchars($navUserInitial) ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end nav-profile-menu">
              <div class="nav-profile-summary">
                <span class="nav-profile-name"><?= htmlspecialchars($navUser['name']) ?></span>
                <span class="nav-profile-email"><?= htmlspecialchars($navUser['email']) ?></span>
              </div>

              <a class="nav-profile-item" href="auth.php?mode=account">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                <span>
                  <strong>Profile</strong>
                </span>
              </a>
              <a class="nav-profile-item" href="auth.php?mode=account">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>
                  <strong>Order History</strong>
                </span>
              </a>
              <a class="nav-profile-item" href="auth.php?mode=account">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2Z"/></svg>
                <span>
                  <strong>Favorite</strong>
                </span>
              </a>
              <a class="nav-profile-item" href="auth.php?mode=account">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 0 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 0 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
                <span>
                  <strong>My Tickets</strong>
                </span>
              </a>

              <div class="nav-profile-divider"></div>

              <a class="nav-profile-item nav-profile-logout" href="auth.php?logout=1">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                <span>
                  <strong>Log Out</strong>
                </span>
              </a>
            </div>
          </div>
          <a href="auth.php?logout=1" class="nav-btn-signup nav-btn-logout-inline">Log Out</a>
        <?php else: ?>
          <a href="auth.php?mode=login" class="nav-btn-login">Log In</a>
          <a href="auth.php?mode=signup" class="nav-btn-signup">Sign Up</a>
        <?php endif; ?>
      </div>
    </div>

  </div>
</nav>

<script>
  (() => {
    const searchForms = document.querySelectorAll('.nav-search-form');

    searchForms.forEach((form) => {
      const input = form.querySelector('input[type="search"]');
      const button = form.querySelector('.nav-search-btn');

      button.addEventListener('click', () => {
        if (!form.classList.contains('is-open')) {
          form.classList.add('is-open');
          input.focus();
          return;
        }

        if (input.value.trim()) {
          form.submit();
          return;
        }

        input.focus();
      });

      input.addEventListener('focus', () => form.classList.add('is-open'));
    });
  })();
</script>
