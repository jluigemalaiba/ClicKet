<?php
// includes/navbar.php — ClicKet Top Navigation with Dynamic Active State
require_once __DIR__ . '/log.php';
$navUser = currentUser();
$navUserLabel = userDisplayName($navUser);
$navUserInitial = $navUserLabel !== '' ? strtoupper(substr($navUserLabel, 0, 1)) : 'U';
$currentPage = basename($_SERVER['PHP_SELF']);
$navCategory = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
$navSearchValue = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
$navFlash = pullFlashMessage();
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
          <button class="nav-dropdown-toggle <?= ($currentPage === 'events.php') ? 'active' : '' ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Events
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
            <button class="nav-profile-toggle d-none d-xl-inline-flex" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Open profile menu">
              <span class="nav-profile-avatar" aria-hidden="true"><?= htmlspecialchars($navUserInitial) ?></span>
            </button>
            <!-- Mobile trigger -->
            <button class="nav-profile-mobile-btn d-xl-none" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
              <span class="nav-profile-avatar" aria-hidden="true"><?= htmlspecialchars($navUserInitial) ?></span>
              <span><?= htmlspecialchars($navUser['name']) ?></span>
              <svg class="nav-profile-mobile-chevron" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="dropdown-menu dropdown-menu-end nav-profile-menu">
              <div class="nav-profile-summary">
                <span class="nav-profile-summary-avatar" aria-hidden="true"><?= htmlspecialchars($navUserInitial) ?></span>
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
    <form class="profile-edit-form" id="profileEditForm" novalidate>

      <!-- Avatar -->
      <div class="profile-edit-avatar-section">
        <div class="profile-edit-avatar-wrap">
          <div class="profile-edit-avatar-display" id="profileAvatarDisplay">
            <span class="profile-edit-avatar-initial" id="profileAvatarInitial"><?= htmlspecialchars($navUserInitial) ?></span>
            <img class="profile-edit-avatar-img" id="profileAvatarImg" src="" alt="Profile photo" style="display:none">
          </div>
          <label class="profile-edit-avatar-btn" for="profileAvatarInput" tabindex="0" role="button" aria-label="Change profile photo">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
              <circle cx="12" cy="13" r="4"/>
            </svg>
          </label>
          <input type="file" id="profileAvatarInput" accept="image/*" class="visually-hidden">
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
          <input type="text" id="peUsername" name="username" class="profile-edit-input profile-edit-input--prefix" placeholder="yourname" autocomplete="username" value="<?= htmlspecialchars($navUser['username'] ?? '') ?>">
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
        <label class="profile-edit-label" for="peGender" style="margin-top: 0.5rem;">Gender</label>
        <div class="profile-edit-select-wrap">
          <select id="peGender" name="gender" class="profile-edit-input profile-edit-select">
            <option value="" disabled <?= empty($navUser['gender']) ? 'selected' : '' ?>>Select Gender</option>
            <option value="male"         <?= ($navUser['gender'] ?? '') === 'male'           ? 'selected' : '' ?>>Male</option>
            <option value="female"       <?= ($navUser['gender'] ?? '') === 'female'         ? 'selected' : '' ?>>Female</option>
            <option value="other"        <?= ($navUser['gender'] ?? '') === 'other'          ? 'selected' : '' ?>>Other</option>
            <option value="prefer_not"   <?= ($navUser['gender'] ?? '') === 'prefer_not'     ? 'selected' : '' ?>>Rather not to say</option>
          </select>
          <svg class="profile-edit-select-arrow" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
      </div>

      <!-- Birthday -->
      <div class="profile-edit-field">
        <label class="profile-edit-label" for="peBirthday" style="margin-top: 0.5rem;">Birthday</label>
        <div class="profile-edit-input-wrap profile-edit-input-wrap--icon">
          <svg class="profile-edit-field-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <input type="date" id="peBirthday" name="birthday" class="profile-edit-input profile-edit-input--icon" value="<?= htmlspecialchars($navUser['birthday'] ?? '') ?>" max="<?= date('Y-m-d', strtotime('-13 years')) ?>">
        </div>
      </div>

      <!-- Phone -->
      <div class="profile-edit-field">
        <label class="profile-edit-label" for="pePhone" style="margin-top: 0.5rem;">Phone Number</label>
        <div class="profile-edit-input-wrap">
          <span class="profile-edit-prefix">+63</span>
          <input type="tel" id="pePhone" name="phone" class="profile-edit-input profile-edit-input--prefix" placeholder="9XXXXXXXXX" maxlength="10" pattern="9[0-9]{9}" autocomplete="tel-national" value="<?= htmlspecialchars(ltrim($navUser['phone'] ?? '', '+630')) ?>">
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
<?php endif; ?>

<script>
  (() => {
    const searchForms = document.querySelectorAll('.nav-search-form');

    function closeSearch(form) {
      const input = form.querySelector('input[type="search"]');
      form.classList.remove('is-open');
      if (input) input.blur();
    }

    searchForms.forEach((form) => {
      const input = form.querySelector('input[type="search"]');
      const button = form.querySelector('.nav-search-btn');

      button.addEventListener('click', (event) => {
        event.stopPropagation();

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

      form.addEventListener('click', (event) => event.stopPropagation());
      input.addEventListener('focus', () => form.classList.add('is-open'));
      input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSearch(form);
      });
    });

    document.addEventListener('click', (event) => {
      searchForms.forEach((form) => {
        if (form.classList.contains('is-open') && !form.contains(event.target)) {
          closeSearch(form);
        }
      });
    });

    // --- Profile Edit Panel ---
    const avatarInput  = document.getElementById('profileAvatarInput');
    const avatarImg    = document.getElementById('profileAvatarImg');
    const avatarInitial= document.getElementById('profileAvatarInitial');
    const bioTextarea  = document.getElementById('peBio');
    const bioCount     = document.getElementById('peBioCount');

    if (avatarInput) {
      avatarInput.addEventListener('change', () => {
        const file = avatarInput.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = (e) => {
          avatarImg.src = e.target.result;
          avatarImg.style.display = 'block';
          if (avatarInitial) avatarInitial.style.display = 'none';
        };
        reader.readAsDataURL(file);
      });
    }

    if (bioTextarea && bioCount) {
      const update = () => { bioCount.textContent = bioTextarea.value.length; };
      bioTextarea.addEventListener('input', update);
      update();
    }

    // Close dropdown when profile panel opens
    document.addEventListener('show.bs.offcanvas', (e) => {
      if (e.target && e.target.id === 'profileEditPanel') {
        document.querySelectorAll('.nav-profile .dropdown-menu.show').forEach(m => {
          const dropdownEl = m.closest('.dropdown');
          if (dropdownEl) {
            const toggle = dropdownEl.querySelector('[data-bs-toggle="dropdown"]');
            if (toggle) {
              const dd = bootstrap.Dropdown.getInstance(toggle);
              if (dd) dd.hide();
            }
          }
        });
      }
    });

    const toast = document.getElementById('ckToast');

    if (toast) {
      const closeBtn = toast.querySelector('.ck-toast__close');
      const closeToast = () => {
        toast.classList.remove('is-visible');
        window.setTimeout(() => toast.remove(), 260);
      };

      requestAnimationFrame(() => toast.classList.add('is-visible'));
      if (closeBtn) closeBtn.addEventListener('click', closeToast);
      window.setTimeout(closeToast, 5200);
    }
  })();
</script>
