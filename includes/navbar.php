<?php
// includes/navbar.php — ClicKet Top Navigation with Dynamic Active State
require_once __DIR__ . '/log.php';
$navUser = currentUser();
$navUserLabel = userDisplayName($navUser);
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
        <li><a href="auth.php?mode=account">My Tickets</a></li>
      </ul>

      <!-- Actions -->
      <div class="nav-actions">
        <button class="nav-cart-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas" aria-controls="cartOffcanvas" title="Shopping cart">
          🛒
          <span class="cart-badge" id="cartBadge" style="display: none;">0</span>
        </button>
        <form class="nav-search-form <?= $navSearchValue !== '' ? 'is-open' : '' ?>" action="events.php" method="get" role="search">
          <input type="search" name="search" value="<?= htmlspecialchars($navSearchValue) ?>" placeholder="Search events" aria-label="Search events">
          <button class="nav-search-btn" type="button" aria-label="Open search">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </button>
        </form>
        <?php if ($navUser): ?>
          <a href="auth.php?mode=account" class="nav-btn-login nav-user-pill" title="<?= htmlspecialchars($navUser['name']) ?>"><?= htmlspecialchars($navUserLabel) ?></a>
          <a href="auth.php?logout=1" class="nav-btn-signup">Log Out</a>
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
