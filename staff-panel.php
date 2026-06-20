<?php

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';

clicketRequireStaff($clicketPanelRole ?? null);
$staff = currentStaff();
if (!$staff) {
    logoutStaff();
    setFlashMessage('error', 'Please sign in with an admin or organizer account.');
    header('Location: auth.php?mode=admin');
    exit;
}

$role = strtolower(str_replace([' ', '-'], '_', (string) ($staff['role'] ?? 'organizer')));
$isAdmin = $role === 'admin';
$requestedRole = $clicketPanelRole ?? null;

if (!in_array($role, ['admin', 'organizer'], true)) {
    logoutStaff();
    setFlashMessage('error', 'Only admin and organizer accounts can access this dashboard.');
    header('Location: auth.php?mode=admin');
    exit;
}

$roleEntry = $isAdmin ? 'admin-panel.php' : 'organizer-panel.php';
if ($requestedRole === null) {
    header('Location: ' . $roleEntry);
    exit;
}

if ($requestedRole !== $role) {
    setFlashMessage('error', 'You do not have permission to open that dashboard.');
    header('Location: ' . $roleEntry);
    exit;
}

$payload = clicketStaffPanelPayload($staff);
$metrics = $payload['metrics'];
$panelTitle = $isAdmin ? 'Admin Panel' : 'Organizer Dashboard';
$panelScope = $isAdmin
    ? 'Full access across venues, events, inventory, orders, payments, reports, archives, audit logs, and settings.'
    : 'Organizer access for assigned venues, owned events, tickets, reports, and archives.';

function sp_h(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sp_money(int|float $value): string {
    return 'PHP ' . number_format((float) $value, 0);
}

function sp_count(mixed $value): string {
    return number_format((int) $value);
}

function sp_status_class(mixed $status): string {
    $status = strtolower(trim((string) $status));

    return match ($status) {
        'paid', 'payment verified', 'published', 'confirmed', 'enabled', 'active', 'valid', 'open', 'approved', 'success' => 'is-success',
        'pending', 'pending payment', 'for verification', 'payment submitted', 'draft', 'review', 'held', 'processing', 'warning' => 'is-warning',
        'failed', 'rejected', 'cancelled', 'canceled', 'void', 'blocked', 'expired', 'suspended' => 'is-danger',
        'refunded', 'archived', 'used', 'disabled' => 'is-muted',
        'info' => 'is-info',
        default => 'is-info',
    };
}

function sp_percent(int|float $value, int|float $max): int {
    if ($max <= 0) {
        return 0;
    }

    return max(0, min(100, (int) round(($value / $max) * 100)));
}

function sp_initials(string $label): string {
    $words = preg_split('/\s+/', trim($label)) ?: [];
    $initials = '';
    foreach ($words as $word) {
        if ($word !== '' && strlen($initials) < 2) {
            $initials .= strtoupper($word[0]);
        }
    }

    return $initials !== '' ? $initials : 'CK';
}

function sp_panel_icon(string $key): string {
    $paths = match ($key) {
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect>',
        'sales' => '<path d="M4 19V5"></path><path d="M4 19h16"></path><path d="M8 15l3-3 3 2 5-7"></path><path d="M17 7h2v2"></path>',
        'revenue' => '<circle cx="12" cy="12" r="9"></circle><path d="M8 12h8"></path><path d="M12 7v10"></path><path d="M15 9.5A3 3 0 0 0 12 8H9.8a1.8 1.8 0 0 0 0 3.6h4.4a1.8 1.8 0 0 1 0 3.6H12a3 3 0 0 1-3-1.5"></path>',
        'venues' => '<path d="M4 21V9l8-5 8 5v12"></path><path d="M9 21v-8h6v8"></path><path d="M4 9h16"></path>',
        'events' => '<path d="M8 2v4"></path><path d="M16 2v4"></path><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M3 10h18"></path><path d="M8 14h.01"></path><path d="M12 14h.01"></path><path d="M16 14h.01"></path>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 10h18"></path>',
        'location' => '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0z"></path><circle cx="12" cy="10" r="2.5"></circle>',
        'performer' => '<path d="M12 3v12"></path><path d="M12 3c2 0 3.5 1.1 4.8 2.5-1.3 1.4-2.8 2.5-4.8 2.5"></path><path d="M12 7c-2 0-3.5 1.1-4.8 2.5C8.5 10.9 10 12 12 12"></path><path d="M8 21h8"></path><path d="M10 15h4"></path>',
        'tiers' => '<path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7"></path><path d="M2 7h20v5H2z"></path><path d="M12 22V7"></path><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>',
        'inventory' => '<path d="M6 3v18"></path><path d="M18 3v18"></path><path d="M6 7h12"></path><path d="M6 12h12"></path><path d="M6 17h12"></path>',
        'orders' => '<path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z"></path><path d="M9 7h6"></path><path d="M9 11h6"></path><path d="M9 15h4"></path>',
        'payments' => '<rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 10h18"></path><path d="M7 15h4"></path>',
        'tickets' => '<path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7z"></path><path d="M13 5v14"></path>',
        'checkin' => '<path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7z"></path><path d="M9 12l2 2 4-5"></path>',
        'virtual_queue' => '<path d="M4 7h12"></path><path d="M4 12h16"></path><path d="M4 17h10"></path><circle cx="19" cy="7" r="2"></circle><path d="M18 17h2"></path>',
        'reservations' => '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
        'favorites' => '<path d="m12 3 2.7 5.47 6.03.88-4.37 4.25 1.03 6-5.39-2.83-5.39 2.83 1.03-6-4.37-4.25 6.03-.88L12 3z"></path>',
        'reports' => '<path d="M4 19V5"></path><path d="M4 19h16"></path><rect x="7" y="11" width="3" height="5"></rect><rect x="12" y="7" width="3" height="9"></rect><rect x="17" y="9" width="3" height="7"></rect>',
        'news' => '<path d="M4 5h13a3 3 0 0 1 3 3v11H7a3 3 0 0 1-3-3V5z"></path><path d="M8 9h8"></path><path d="M8 13h6"></path>',
        'archives' => '<rect x="3" y="4" width="18" height="5" rx="1"></rect><path d="M5 9v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9"></path><path d="M10 13h4"></path>',
        'audit' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M8 13h8"></path><path d="M8 17h5"></path>',
        'settings' => '<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.8 1.8 0 0 0 .36 1.98l.04.04a2 2 0 1 1-2.83 2.83l-.04-.04A1.8 1.8 0 0 0 15 19.4a1.8 1.8 0 0 0-1 .6 1.8 1.8 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.06A1.8 1.8 0 0 0 8.6 19.4a1.8 1.8 0 0 0-1.98.36l-.04.04a2 2 0 1 1-2.83-2.83l.04-.04A1.8 1.8 0 0 0 4.6 15a1.8 1.8 0 0 0-.6-1 1.8 1.8 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.06A1.8 1.8 0 0 0 4.6 8.6a1.8 1.8 0 0 0-.36-1.98l-.04-.04a2 2 0 1 1 2.83-2.83l.04.04A1.8 1.8 0 0 0 9 4.6a1.8 1.8 0 0 0 1-.6 1.8 1.8 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.06A1.8 1.8 0 0 0 15.4 4.6a1.8 1.8 0 0 0 1.98-.36l.04-.04a2 2 0 1 1 2.83 2.83l-.04.04A1.8 1.8 0 0 0 19.4 9c.36.22.74.4 1.1.4H21a2 2 0 1 1 0 4h-.06a1.8 1.8 0 0 0-1.54 1.6z"></path>',
        default => '<circle cx="12" cy="12" r="8"></circle>',
    };

    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $paths . '</svg>';
}

$adminModules = [
    ['Dashboard', 'Executive sales, tickets, events, payments, reservations, charts, and activity.'],
    ['Venues', 'Venue capacity, event revenue, ticket sales, tier setup, and organizer rosters.'],
    ['Events', 'Organizer event submissions, banners, performers, sales, and tier availability by venue.'],
    ['Venue Tiers', 'Venue-specific tier structures with capacity, price, revenue, and inventory controls.'],
    ['Seats & Inventory', 'Seat map, available, sold, held, blocked, accessible, complimentary, and section analytics.'],
    ['Orders', 'Order records with buyer information, seats, payment references, proof screenshots, and complete details.'],
    ['Tickets', 'Ticket ID, validation code, voucher, seat assignment, status, and complete view-only details.'],
    ['Check-In', 'Ticket validation, venue entry logging, duplicate scan handling, and attendance counts.'],
    ['Virtual Queue', 'Waiting room admission caps, queue timeout, throughput, and live demand metrics.'],
    ['Reservations', 'Active holds, expired holds, countdown monitoring, and release controls.'],
    ['Users', 'Customer, organizer, role management, assignments, history, suspension, and disabling.'],
    ['Favorites', 'Most favorited events, favorite trends, and popularity analytics.'],
    ['Reports', 'Sales, venue, event, tier, section, attendance, payment, PDF, and Excel exports.'],
    ['News Management', 'Create articles, rich text, cover upload, drafts, publishing, archives, and featured news.'],
    ['Archives', 'Archived events, orders, ticket scans, restore controls, and retention views.'],
    ['Audit Logs', 'Admin, organizer, payment approval, seat block, price change, event creation, and archive logs.'],
    ['Settings', 'System, payment, ticket, venue, and user role settings.'],
];

$organizerModules = [
    ['Dashboard', 'Owned event snapshot, ticket pace, reservations, and reporting shortcuts.'],
    ['Venues', 'Assigned venue capacity, event revenue, ticket sales, and organizer coverage.'],
    ['Events', 'Create and manage owned event records, schedules, media, and event status.'],
    ['Tickets', 'Ticket ID, validation code, voucher, seat assignment, and view-only details.'],
    ['Check-In', 'Validate owned event tickets and track venue entry.'],
    ['Virtual Queue', 'View waiting room demand for your owned events.'],
    ['Reports', 'Scoped sales, venue, event, ticket, attendance, PDF, and Excel exports.'],
    ['News Management', 'Create event news, save drafts, and publish updates.'],
    ['Archives', 'Archived owned events, closed records, and retention views.'],
];

$adminPanelGroups = [
    'dashboard' => ['label' => 'Dashboard', 'eyebrow' => 'Command Center', 'partial' => 'dashboard.php', 'items' => ['overview' => 'Overview', 'analytics' => 'Analytics', 'orders' => 'Recent Orders', 'payments' => 'Payment Activity']],
    'venues' => ['label' => 'Venues', 'eyebrow' => 'Venue Operations', 'partial' => 'venues.php', 'items' => ['cards' => 'Venue List', 'details' => 'Revenue & Tickets', 'tiers' => 'Tier Setup', 'organizers' => 'Organizer Roster']],
    'events' => ['label' => 'Events', 'eyebrow' => 'Organizer Event Review', 'partial' => 'events.php', 'items' => ['listing' => 'Event Gallery', 'details' => 'Submission Details', 'sales' => 'Sales & Seats', 'venues' => 'Venue Filter']],
    'orders' => ['label' => 'Orders', 'eyebrow' => 'Orders & Payments', 'partial' => 'orders.php', 'items' => ['table' => 'All Orders', 'details' => 'Order Details', 'queue' => 'Payment Review', 'proof' => 'Proof Viewer', 'revenue' => 'Revenue']],
    'tickets' => ['label' => 'Tickets', 'eyebrow' => 'Ticket Registry', 'partial' => 'tickets.php', 'items' => ['search' => 'All Tickets', 'details' => 'Ticket Details']],
    'checkin' => ['label' => 'Check-In', 'eyebrow' => 'Gate Operations', 'partial' => 'checkin.php', 'items' => ['entry' => 'Entry Scan', 'logs' => 'Scan Logs']],
    'virtual_queue' => ['label' => 'Virtual Queue', 'eyebrow' => 'Waiting Room', 'partial' => 'virtual-queue.php', 'items' => ['overview' => 'Metrics', 'events' => 'Event Queues']],
    'users' => ['label' => 'Users', 'eyebrow' => 'Identity & Roles', 'partial' => 'users.php', 'items' => ['table' => 'User Table', 'roles' => 'Roles', 'assignment' => 'Organizer Assignment', 'history' => 'Order History']],
    'reports' => ['label' => 'Reports', 'eyebrow' => 'Exports', 'partial' => 'reports.php', 'items' => ['sales' => 'Sales', 'venue' => 'Venue', 'attendance' => 'Attendance', 'exports' => 'PDF / Excel']],
    'news' => ['label' => 'News Management', 'eyebrow' => 'Content', 'partial' => 'news.php', 'items' => ['create' => 'Create Article', 'editor' => 'Editor', 'publish' => 'Publishing', 'archive' => 'Archive']],
    'archives' => ['label' => 'Archives', 'eyebrow' => 'Retention', 'partial' => 'archives.php', 'items' => ['events' => 'Events', 'orders' => 'Orders', 'scans' => 'Ticket Scans', 'restore' => 'Restore']],
];

$organizerPanelGroups = array_intersect_key($adminPanelGroups, array_flip([
    'dashboard',
    'venues',
    'events',
    'tickets',
    'checkin',
    'virtual_queue',
    'reports',
    'news',
    'archives',
]));

$panelGroups = $isAdmin ? $adminPanelGroups : $organizerPanelGroups;
$moduleCards = $isAdmin ? $adminModules : $organizerModules;
$assignedVenueNames = clicketStaffAssignedVenueNames($staff);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= sp_h($panelTitle) ?> | CLICKET</title>
  <meta name="clicket-csrf-token" content="<?= sp_h(clicketCsrfToken('staff_payment')) ?>">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/staff-panel.css?v=<?= filemtime(__DIR__ . '/css/staff-panel.css') ?>">
</head>
<body class="staff-shell staff-role-<?= sp_h($role) ?>">
  <aside class="staff-sidebar" id="staffSidebar" aria-label="<?= sp_h(ucfirst($role)) ?> navigation">
    <div class="staff-sidebar-header">
      <a class="staff-brand" href="index.php" aria-label="CLICKET home">
        <img src="assets/Icon_Logo.png" alt="" aria-hidden="true">
        <img src="assets/Name_Logo.png" alt="CLICKET">
      </a>
      <button class="staff-sidebar-collapse" type="button" data-sidebar-collapse aria-label="Collapse sidebar" aria-expanded="true">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path d="M15 18l-6-6 6-6"></path>
        </svg>
      </button>
    </div>

    <nav class="staff-nav staff-nav-tree">
      <?php foreach ($panelGroups as $groupKey => $group): ?>
        <section class="staff-nav-group" data-nav-group="<?= sp_h($groupKey) ?>">
          <a class="staff-nav-parent <?= $groupKey === 'dashboard' ? 'is-active' : '' ?>" href="<?= sp_h($roleEntry) ?>#<?= sp_h($groupKey) ?>" data-panel-target="<?= sp_h($groupKey) ?>" title="<?= sp_h($group['label']) ?>">
            <span class="staff-nav-icon"><?= sp_panel_icon((string) $groupKey) ?></span>
            <span class="staff-nav-label"><?= sp_h($group['label']) ?></span>
          </a>
        </section>
      <?php endforeach; ?>
    </nav>

    <div class="staff-sidebar-footer">
      <div class="staff-account-card">
        <span class="staff-account-avatar"><?= sp_h(sp_initials((string) ($staff['name'] ?? 'Admin'))) ?></span>
        <span class="staff-account-meta">
          <strong><?= sp_h($staff['name'] ?? 'Authorized User') ?></strong>
          <small><?= sp_h(ucwords(str_replace('_', ' ', $role))) ?></small>
        </span>
      </div>
      <a class="staff-signout-link" href="auth.php?staff_logout=1">
        <span aria-hidden="true">
          <svg viewBox="0 0 24 24" focusable="false">
            <path d="M10 17l5-5-5-5"></path>
            <path d="M15 12H3"></path>
            <path d="M21 3v18h-7"></path>
          </svg>
        </span>
        <strong>Sign out</strong>
      </a>
    </div>
  </aside>

  <main class="staff-main">
    <header class="staff-topbar">
      <button class="staff-icon-btn staff-mobile-nav" type="button" data-sidebar-toggle aria-label="Open navigation">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <a class="staff-topbar-logo" href="index.php" aria-label="CLICKET home">
        <img src="assets/Icon_Logo.png" alt="" aria-hidden="true">
        <img src="assets/Name_Logo.png" alt="CLICKET">
      </a>
      <div class="staff-topbar-search">
        <button class="staff-search-filter" type="button">
          <span>All</span>
          <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M6 9l6 6 6-6"></path>
          </svg>
        </button>
        <label class="staff-search staff-search--topbar">
          <input type="search" id="staffPanelSearch" placeholder="Search">
          <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <circle cx="11" cy="11" r="7"></circle>
            <path d="M20 20l-3.5-3.5"></path>
          </svg>
        </label>
      </div>
      <div class="staff-topbar-actions">
        <button class="staff-topbar-icon staff-topbar-icon--notify" type="button" data-panel-shortcut="<?= $isAdmin ? 'orders' : 'tickets' ?>" aria-label="<?= $isAdmin ? 'Open orders' : 'Open tickets' ?>">
          <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
            <path d="M10 21h4"></path>
          </svg>
          <?php if ($metrics['pendingPayments'] > 0): ?><span><?= sp_count($metrics['pendingPayments']) ?></span><?php endif; ?>
        </button>
        <button class="staff-topbar-icon" type="button" data-panel-shortcut="<?= $isAdmin ? 'settings' : 'reports' ?>" aria-label="<?= $isAdmin ? 'Open settings' : 'Open reports' ?>">
          <?= sp_panel_icon($isAdmin ? 'settings' : 'reports') ?>
        </button>
        <div class="staff-topbar-profile" aria-label="<?= sp_h(ucfirst($role)) ?> profile">
          <span><?= sp_h(sp_initials((string) ($staff['name'] ?? 'Admin'))) ?></span>
          <strong><?= sp_h($staff['name'] ?? 'Authorized User') ?></strong>
          <small><?= sp_h($staff['email'] ?? '') ?></small>
        </div>
      </div>
    </header>

    <div class="staff-panel-stage">
      <?php foreach ($panelGroups as $groupKey => $group): ?>
        <section class="staff-panel-view <?= $groupKey === 'dashboard' ? 'is-active' : '' ?>" id="panel-<?= sp_h($groupKey) ?>" data-panel-view="<?= sp_h($groupKey) ?>" data-panel-label="<?= sp_h($group['label']) ?>">
          <?php
          if (!$isAdmin && !array_key_exists($groupKey, $organizerPanelGroups)) {
              http_response_code(403);
              exit('Organizer access denied for this module.');
          }
          ?>
          <?php require __DIR__ . '/includes/staff-panel-sections/' . $group['partial']; ?>
        </section>
      <?php endforeach; ?>
    </div>
  </main>

  <div class="staff-modal" data-staff-modal hidden>
    <div class="staff-modal-backdrop" data-modal-close></div>
    <section class="staff-modal-panel" role="dialog" aria-modal="true" aria-labelledby="staffModalTitle">
      <button class="staff-modal-close" type="button" data-modal-close aria-label="Close modal">x</button>
      <p class="staff-eyebrow" id="staffModalEyebrow">Workflow</p>
      <h2 id="staffModalTitle">Details</h2>
      <div class="staff-modal-body" data-modal-body></div>
    </section>
  </div>

  <script type="application/json" id="staffEventLayoutOptionsJson"><?= json_encode($payload['eventVenueOptions'] ?? [], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
  <script src="js/staff-panel.js?v=<?= filemtime(__DIR__ . '/js/staff-panel.js') ?>"></script>
</body>
</html>
