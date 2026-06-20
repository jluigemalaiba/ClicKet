<?php
// includes/navbar.php — ClicKet Top Navigation with Dynamic Active State
require_once __DIR__ . '/log.php';
require_once __DIR__ . '/order-history-data.php';
require_once __DIR__ . '/ticket-data.php';
require_once __DIR__ . '/favorite-data.php';
require_once __DIR__ . '/data.php';
$navUser = currentUser();
$navOrderHistory = $navUser ? clicketOrdersForUser((string) ($navUser['id'] ?? '')) : [];
$navTickets = $navUser ? clicketTicketsForUser((string) ($navUser['id'] ?? '')) : [];
$navFavorites = $navUser ? clicketFavoritesForUser((string) ($navUser['id'] ?? '')) : [];
$navUserLabel = userDisplayName($navUser);
$navUserInitial = $navUserLabel !== '' ? strtoupper(substr($navUserLabel, 0, 1)) : 'U';
$currentPage = basename($_SERVER['PHP_SELF']);
$navCategory = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
$navEventLabels = [
    'concerts' => 'Concerts',
    'theater' => 'Theater Plays',
    'sports' => 'Sports Events',
];
$navEventsLabel = ($currentPage === 'events.php' && isset($navEventLabels[$navCategory]))
    ? $navEventLabels[$navCategory]
    : 'Events';
$navSearchValue = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
$navSearchEvents = [];
foreach ([
    'concerts' => ['events' => $concert_events, 'label' => 'Concert', 'poster' => 'concert'],
    'theater' => ['events' => $theater_events, 'label' => 'Theater', 'poster' => 'theater'],
    'sports' => ['events' => $sports_events, 'label' => 'Sports', 'poster' => 'sports'],
] as $navSearchCategoryKey => $navSearchCatalog) {
    foreach ($navSearchCatalog['events'] as $navSearchEventIndex => $navSearchEventItem) {
        $navSearchEvents[] = [
            'title' => (string) ($navSearchEventItem['title'] ?? ''),
            'venue' => (string) ($navSearchEventItem['venue'] ?? ''),
            'date' => (string) ($navSearchEventItem['date'] ?? ''),
            'category' => $navSearchCatalog['label'],
            'type' => (string) ($navSearchEventItem['type'] ?? ''),
            'performer' => (string) ($navSearchEventItem['artist'] ?? $navSearchEventItem['company'] ?? $navSearchEventItem['league'] ?? ''),
            'poster' => (string) ($navSearchEventItem['poster'] ?? '') !== '' ? (string) $navSearchEventItem['poster'] : posterUrl($navSearchCatalog['poster'], $navSearchEventIndex + 10),
            'url' => clicketEventDetailUrl($navSearchEventItem, $navSearchCategoryKey, $navSearchEventIndex),
        ];
    }
}
unset($navSearchCategoryKey, $navSearchCatalog, $navSearchEventIndex, $navSearchEventItem);
$navFlash = pullFlashMessage();
?>
<?php if ($navUser): ?>
<link rel="stylesheet" href="css/order-history.css?v=<?= filemtime(dirname(__DIR__) . '/css/order-history.css') ?>">
<link rel="stylesheet" href="css/my-tickets.css?v=<?= filemtime(dirname(__DIR__) . '/css/my-tickets.css') ?>">
<link rel="stylesheet" href="css/favorites.css">
<?php endif; ?>
<nav class="navbar-clicket navbar navbar-expand-xl">
  <div class="navbar-inner">

    <!-- Logo -->
    <a href="#top" class="nav-logo" data-scroll-top>
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
          <button class="nav-dropdown-toggle <?= ($currentPage === 'events.php') ? 'active' : '' ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span id="navEventsLabel"><?= htmlspecialchars($navEventsLabel) ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <ul class="dropdown-menu nav-events-menu">
            <li><a class="dropdown-item <?= ($currentPage === 'events.php' && $navCategory === '') ? 'active' : '' ?>" href="events.php">All Events</a></li>
            <li><a class="dropdown-item <?= ($currentPage === 'events.php' && $navCategory === 'concerts') ? 'active' : '' ?>" href="events.php?category=concerts">Concerts</a></li>
            <li><a class="dropdown-item <?= ($currentPage === 'events.php' && $navCategory === 'theater') ? 'active' : '' ?>" href="events.php?category=theater">Theater Plays</a></li>
            <li><a class="dropdown-item <?= ($currentPage === 'events.php' && $navCategory === 'sports') ? 'active' : '' ?>" href="events.php?category=sports">Sports Events</a></li>
          </ul>
        </li>
        <li><a href="venues.php" class="<?= ($currentPage === 'venues.php') ? 'active' : '' ?>">Venues</a></li>
      </ul>

      <!-- Actions -->
      <div class="nav-actions">
        <form class="nav-search-form <?= $navSearchValue !== '' ? 'is-open has-value' : '' ?>" action="events.php" method="get" role="search">
          <input type="search" name="search" value="<?= htmlspecialchars($navSearchValue) ?>" placeholder="Search events" aria-label="Search events" autocomplete="off" aria-controls="navSearchSuggestions" aria-expanded="false">
          <button class="nav-search-clear" type="button" aria-label="Clear search" <?= $navSearchValue === '' ? 'hidden' : '' ?>>
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="m7 7 10 10M17 7 7 17"/></svg>
          </button>
          <button class="nav-search-btn" type="button" aria-label="Open search">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </button>
          <div class="nav-search-suggestions" id="navSearchSuggestions" role="listbox" aria-label="Event suggestions" hidden>
            <div class="nav-search-suggestions-list"></div>
          </div>
        </form>
        <script type="application/json" id="navSearchEventData"><?= json_encode($navSearchEvents, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
        <?php if ($navUser): ?>
          <div class="nav-profile dropdown">
            <button class="nav-profile-toggle d-none d-xl-inline-flex" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Open profile menu">
              <span class="nav-profile-avatar" aria-hidden="true"><?php if (!empty($navUser['avatar_url'])): ?><img src="<?= htmlspecialchars($navUser['avatar_url']) ?>" alt=""><?php else: ?><?= htmlspecialchars($navUserInitial) ?><?php endif; ?></span>
            </button>
            <!-- Mobile trigger -->
            <button class="nav-profile-mobile-btn d-xl-none" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
              <span class="nav-profile-avatar" aria-hidden="true"><?php if (!empty($navUser['avatar_url'])): ?><img src="<?= htmlspecialchars($navUser['avatar_url']) ?>" alt=""><?php else: ?><?= htmlspecialchars($navUserInitial) ?><?php endif; ?></span>
              <span><?= htmlspecialchars($navUser['name']) ?></span>
              <svg class="nav-profile-mobile-chevron" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="dropdown-menu dropdown-menu-end nav-profile-menu">
              <div class="nav-profile-summary">
                <span class="nav-profile-summary-avatar" aria-hidden="true"><?php if (!empty($navUser['avatar_url'])): ?><img src="<?= htmlspecialchars($navUser['avatar_url']) ?>" alt=""><?php else: ?><?= htmlspecialchars($navUserInitial) ?><?php endif; ?></span>
                <span class="nav-profile-summary-text">
                  <span class="nav-profile-name"><?= htmlspecialchars($navUser['name']) ?></span>
                  <span class="nav-profile-email"><?= htmlspecialchars($navUser['email']) ?></span>
                </span>
              </div>

              <button class="nav-profile-item nav-profile-edit-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileEditPanel" aria-controls="profileEditPanel">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                <span>
                  <strong>Profile</strong>
                </span>
              </button>
              <button class="nav-profile-item nav-order-history-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#orderHistoryPanel" aria-controls="orderHistoryPanel">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>
                  <strong>Order History</strong>
                </span>
              </button>
              <button class="nav-profile-item nav-favorites-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#favoritesPanel" aria-controls="favoritesPanel">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2Z"/></svg>
                <span>
                  <strong>Favorites</strong>
                </span>
              </button>
              <button class="nav-profile-item nav-my-tickets-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#myTicketsPanel" aria-controls="myTicketsPanel">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 0 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 0 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
                <span>
                  <strong>My Tickets</strong>
                </span>
              </button>

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

<?php if ($navFlash): ?>
<div class="ck-toast ck-toast--<?= htmlspecialchars($navFlash['type']) ?>" id="ckToast" role="status" aria-live="polite">
  <span class="ck-toast__mark" aria-hidden="true"></span>
  <span class="ck-toast__message"><?= htmlspecialchars($navFlash['message']) ?></span>
  <button class="ck-toast__close" type="button" aria-label="Close notification">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <line x1="18" y1="6" x2="6" y2="18"></line>
      <line x1="6" y1="6" x2="18" y2="18"></line>
    </svg>
  </button>
</div>
<?php endif; ?>

<?php if ($navUser): ?>
<!-- Profile Edit Panel -->
<div class="offcanvas offcanvas-end profile-edit-panel" tabindex="-1" id="profileEditPanel" aria-labelledby="profileEditPanelLabel">
  <div class="profile-edit-header offcanvas-header">
    <div class="profile-edit-header-left">
      <h5 class="profile-edit-title" id="profileEditPanelLabel">Edit Profile</h5>
      <p class="profile-edit-subtitle">Update your personal information</p>
    </div>
    <button type="button" class="profile-edit-close" data-bs-dismiss="offcanvas" aria-label="Close panel">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
  </div>

  <div class="profile-edit-body offcanvas-body">
    <form class="profile-edit-form" id="profileEditForm" novalidate enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(clicketCsrfToken('profile_update')) ?>">

      <!-- Avatar -->
      <div class="profile-edit-avatar-section">
        <div class="profile-edit-avatar-wrap">
          <div class="profile-edit-avatar-display" id="profileAvatarDisplay">
            <span class="profile-edit-avatar-initial" id="profileAvatarInitial"<?= !empty($navUser['avatar_url']) ? ' style="display:none"' : '' ?>><?= htmlspecialchars($navUserInitial) ?></span>
            <img class="profile-edit-avatar-img" id="profileAvatarImg" src="<?= htmlspecialchars($navUser['avatar_url'] ?? '') ?>" alt="Profile photo"<?= empty($navUser['avatar_url']) ? ' style="display:none"' : '' ?>>
          </div>
          <label class="profile-edit-avatar-btn" for="profileAvatarInput" tabindex="0" role="button" aria-label="Change profile photo">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
              <circle cx="12" cy="13" r="4"/>
            </svg>
          </label>
          <input type="file" id="profileAvatarInput" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" class="visually-hidden">
        </div>
        <div class="profile-edit-avatar-info">
          <p class="profile-edit-avatar-label">Profile Photo</p>
          <p class="profile-edit-avatar-hint">JPG, PNG or GIF · Max 5 MB</p>
        </div>
      </div>

      <!-- Username -->
      <div class="profile-edit-field">
        <label class="profile-edit-label" for="peUsername" style="margin-top: 0.5rem;">Username</label>
        <div class="profile-edit-input-wrap">
          <span class="profile-edit-prefix">@</span>
          <input type="text" id="peUsername" name="username" class="profile-edit-input profile-edit-input--prefix" placeholder="yourname" autocomplete="username" value="<?= htmlspecialchars($navUser['username'] ?? $navUser['name'] ?? '') ?>">
        </div>
      </div>

      <!-- First & Last Name -->
      <div class="profile-edit-row">
        <div class="profile-edit-field">
          <label class="profile-edit-label" for="peFirstName" style="margin-top: 0.5rem;">First Name</label>
          <input type="text" id="peFirstName" name="first_name" class="profile-edit-input" placeholder="Juan" autocomplete="given-name" value="<?= htmlspecialchars($navUser['first_name'] ?? '') ?>">
        </div>
        <div class="profile-edit-field">
          <label class="profile-edit-label" for="peLastName" style="margin-top: 0.5rem;">Last Name</label>
          <input type="text" id="peLastName" name="last_name" class="profile-edit-input" placeholder="dela Cruz" autocomplete="family-name" value="<?= htmlspecialchars($navUser['last_name'] ?? '') ?>">
        </div>
      </div>

      <!-- Bio -->
      <div class="profile-edit-field">
        <label class="profile-edit-label" for="peBio" style="margin-top: 0.5rem;">Bio</label>
        <textarea id="peBio" name="bio" class="profile-edit-input profile-edit-textarea" placeholder="Tell us a little about yourself…" maxlength="200" rows="3"><?= htmlspecialchars($navUser['bio'] ?? '') ?></textarea>
        <span class="profile-edit-char-count"><span id="peBioCount">0</span>/200</span>
      </div>

      <!-- Gender -->
      <div class="profile-edit-field">
        <label class="profile-edit-label" style="margin-top: 0.5rem;">Gender</label>
        <div class="ck-select-wrap" id="peGenderWrap">
          <div class="ck-select-trigger profile-edit-input" id="peGenderTrigger" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false" style="height:42px;padding:0 36px 0 14px;display:flex;align-items:center;cursor:pointer;user-select:none;">
            <span id="peGenderValue" class="ck-select-placeholder"><?= !empty($navUser['gender']) ? ['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not'=>'Rather not say'][$navUser['gender']] : 'Select Gender' ?></span>
            <svg class="ck-select-arrow" viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);width:14px;height:14px;stroke:var(--gray-400);transition:transform .16s,stroke .16s;pointer-events:none;"><polyline points="6 9 12 15 18 9"/></svg>
          </div>
          <div class="ck-dropdown" id="peGenderDropdown" role="listbox">
            <div class="ck-option <?= ($navUser['gender'] ?? '') === 'male'       ? 'is-selected' : '' ?>" data-value="male"       role="option">Male<svg class="ck-option-check" viewBox="0 0 24 24" fill="none" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
            <div class="ck-option <?= ($navUser['gender'] ?? '') === 'female'     ? 'is-selected' : '' ?>" data-value="female"     role="option">Female<svg class="ck-option-check" viewBox="0 0 24 24" fill="none" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
            <div class="ck-option <?= ($navUser['gender'] ?? '') === 'other'      ? 'is-selected' : '' ?>" data-value="other"      role="option">Other<svg class="ck-option-check" viewBox="0 0 24 24" fill="none" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
            <div class="ck-option <?= ($navUser['gender'] ?? '') === 'prefer_not' ? 'is-selected' : '' ?>" data-value="prefer_not" role="option">Rather not say<svg class="ck-option-check" viewBox="0 0 24 24" fill="none" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
          </div>
        </div>
        <input type="hidden" id="peGender" name="gender" value="<?= htmlspecialchars($navUser['gender'] ?? '') ?>">
      </div>

      <!-- Birthday -->
      <div class="profile-edit-field">
        <label class="profile-edit-label" style="margin-top: 0.5rem;">Birthday</label>
        <div class="ck-date-wrap" id="peDateWrap">
          <svg class="ck-date-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;stroke:var(--gray-400);pointer-events:none;z-index:1;transition:stroke .16s;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <div class="ck-date-trigger profile-edit-input profile-edit-input--icon" id="peDateTrigger" tabindex="0" role="button" style="display:flex;align-items:center;cursor:pointer;user-select:none;">
            <span id="peDateDisplay" class="ck-select-placeholder"><?= !empty($navUser['birthday']) ? date('F j, Y', strtotime($navUser['birthday'])) : 'Select date' ?></span>
          </div>
          <div class="ck-calendar" id="peCalPanel"></div>
        </div>
        <input type="hidden" id="peBirthday" name="birthday" value="<?= htmlspecialchars($navUser['birthday'] ?? '') ?>">
      </div>

      <!-- Phone -->
      <div class="profile-edit-field">
        <label class="profile-edit-label" for="pePhone" style="margin-top: 0.5rem;">Phone Number</label>
        <div class="profile-edit-input-wrap">
          <span class="profile-edit-prefix">+63</span>
          <input type="tel" id="pePhone" name="phone" class="profile-edit-input profile-edit-input--prefix" placeholder="9XXXXXXXXX" maxlength="10" pattern="9[0-9]{9}" autocomplete="tel-national" value="<?= htmlspecialchars(preg_replace('/^\+?63/', '', (string) ($navUser['phone'] ?? ''))) ?>">
        </div>
      </div>

      <!-- Email -->
      <div class="profile-edit-field">
        <label class="profile-edit-label" for="peEmail" style="margin-top: 0.5rem;">Email</label>
        <div class="profile-edit-input-wrap profile-edit-input-wrap--icon">
          <svg class="profile-edit-field-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <input type="email" id="peEmail" name="email" class="profile-edit-input profile-edit-input--icon" placeholder="you@example.com" autocomplete="email" value="<?= htmlspecialchars($navUser['email'] ?? '') ?>">
        </div>
      </div>

      <!-- Address Section -->
      <div class="profile-edit-section-divider" style="margin-top: 0.8rem;">
        <span>Address</span>
      </div>

      <!-- Map Picker -->
      <div class="profile-edit-field" style="margin-top: 0.9rem;">
        <div class="pe-map-wrap" id="peMapWrap">
          <div id="peMap"></div>
          <div class="pe-map-status" id="peMapStatus">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span id="peMapStatusText">Click anywhere on the map to auto-fill your address</span>
          </div>
          <button type="button" class="pe-map-locate-btn" id="peMapLocateBtn" title="Use my current location">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><circle cx="12" cy="12" r="8" stroke-dasharray="2 4"/></svg>
          </button>
        </div>
      </div>

      <div class="profile-edit-field">
        <label class="profile-edit-label" for="peStreet" style="margin-top: 0.5rem;">Street / Barangay</label>
        <input type="text" id="peStreet" name="street" class="profile-edit-input" placeholder="123 Rizal St, Brgy. San Jose" autocomplete="address-line1" value="<?= htmlspecialchars($navUser['street'] ?? '') ?>">
      </div>

      <div class="profile-edit-row">
        <div class="profile-edit-field">
          <label class="profile-edit-label" for="peCity" style="margin-top: 0.5rem;">City / Municipality</label>
          <input type="text" id="peCity" name="city" class="profile-edit-input" placeholder="Calamba" autocomplete="address-level2" value="<?= htmlspecialchars($navUser['city'] ?? '') ?>">
        </div>
        <div class="profile-edit-field">
          <label class="profile-edit-label" for="peProvince" style="margin-top: 0.5rem;">Province</label>
          <input type="text" id="peProvince" name="province" class="profile-edit-input" placeholder="Laguna" autocomplete="address-level1" value="<?= htmlspecialchars($navUser['province'] ?? '') ?>">
        </div>
      </div>

      <div class="profile-edit-row">
        <div class="profile-edit-field">
          <label class="profile-edit-label" for="peZip" style="margin-top: 0.5rem;">ZIP Code</label>
          <input type="text" id="peZip" name="zip" class="profile-edit-input" placeholder="4027" maxlength="4" pattern="[0-9]{4}" autocomplete="postal-code" value="<?= htmlspecialchars($navUser['zip'] ?? '') ?>">
        </div>
        <div class="profile-edit-field">
          <label class="profile-edit-label" for="peCountry" style="margin-top: 0.5rem;">Country</label>
          <input type="text" id="peCountry" name="country" class="profile-edit-input" placeholder="Philippines" autocomplete="country-name" value="<?= htmlspecialchars($navUser['country'] ?? 'Philippines') ?>">
        </div>
      </div>

    </form>
  </div>

  <div class="profile-edit-footer offcanvas-footer">
    <button type="button" class="profile-edit-btn profile-edit-btn--ghost" data-bs-dismiss="offcanvas">Cancel</button>
    <button type="button" class="profile-edit-btn profile-edit-btn--save" id="profileEditSave">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      Save Changes
    </button>
  </div>
</div>

<!-- Order History Panel -->
<div class="offcanvas offcanvas-end order-history-panel" tabindex="-1" id="orderHistoryPanel" aria-labelledby="orderHistoryPanelLabel">
  <div class="order-history-panel__header offcanvas-header">
    <div>
      <p>My purchases</p>
      <h5 id="orderHistoryPanelLabel">Order History</h5>
      <span><?= count($navOrderHistory) ?> <?= count($navOrderHistory) === 1 ? 'confirmed order' : 'confirmed orders' ?></span>
    </div>
    <button type="button" class="order-history-panel__close" data-bs-dismiss="offcanvas" aria-label="Close order history">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
    </button>
  </div>
  <div class="order-history-panel__body offcanvas-body">
    <?php
    $orderHistory = $navOrderHistory;
    $orderHistoryCompact = true;
    require __DIR__ . '/order-history-ui.php';
    ?>
  </div>
</div>

<!-- Favorites Panel -->
<div class="offcanvas offcanvas-end favorites-panel" tabindex="-1" id="favoritesPanel" aria-labelledby="favoritesPanelLabel">
  <div class="favorites-panel__header offcanvas-header">
    <div>
      <p>Saved events</p>
      <h5 id="favoritesPanelLabel">Favorites</h5>
      <span data-favorites-count><?= count($navFavorites) ?> <?= count($navFavorites) === 1 ? 'saved event' : 'saved events' ?></span>
    </div>
    <button type="button" class="favorites-panel__close" data-bs-dismiss="offcanvas" aria-label="Close favorites">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
    </button>
  </div>
  <div class="favorites-panel__body offcanvas-body">
    <?php
    $favorites = $navFavorites;
    require __DIR__ . '/favorites-ui.php';
    ?>
  </div>
</div>

<!-- My Tickets Panel -->
<div class="offcanvas offcanvas-end my-tickets-panel" tabindex="-1" id="myTicketsPanel" aria-labelledby="myTicketsPanelLabel">
  <div class="my-tickets-panel__header offcanvas-header">
    <div>
      <p>Ready for admission</p>
      <h5 id="myTicketsPanelLabel">My Tickets</h5>
      <span><?= count($navTickets) ?> <?= count($navTickets) === 1 ? 'valid ticket' : 'valid tickets' ?></span>
    </div>
    <button type="button" class="my-tickets-panel__close" data-bs-dismiss="offcanvas" aria-label="Close My Tickets">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
    </button>
  </div>
  <div class="my-tickets-panel__body offcanvas-body">
    <?php
    $myTickets = $navTickets;
    require __DIR__ . '/my-tickets-ui.php';
    ?>
  </div>
</div>

<?php
$orderHistory = $navOrderHistory;
require __DIR__ . '/order-details-modal.php';
$myTickets = $navTickets;
require __DIR__ . '/ticket-details-panel.php';
?>
<?php endif; ?>

<script src="js/navbar.js" defer></script>
<script src="js/favorites.js" defer></script>
<?php if ($navUser): ?>
<script src="js/order-history.js?v=<?= filemtime(dirname(__DIR__) . '/js/order-history.js') ?>" defer></script>
<script src="js/my-tickets.js?v=<?= filemtime(dirname(__DIR__) . '/js/my-tickets.js') ?>" defer></script>
<?php endif; ?>

