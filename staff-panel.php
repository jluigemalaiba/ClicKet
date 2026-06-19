<?php

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';

$staff = currentStaff();
if (!$staff) {
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
    : 'Limited access for owned event management, reservation overview, and news posting only.';

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
        'paid', 'published', 'confirmed', 'enabled', 'active', 'valid', 'open', 'approved', 'success' => 'is-success',
        'pending', 'draft', 'review', 'held', 'processing', 'warning' => 'is-warning',
        'failed', 'cancelled', 'canceled', 'void', 'blocked', 'expired', 'suspended' => 'is-danger',
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

$adminModules = [
    ['Dashboard', 'Executive sales, tickets, events, payments, reservations, charts, and activity.'],
    ['Venues', 'Venue cards, details, SVG maps, capacity, revenue, organizers, and status.'],
    ['Events', 'Create events, schedules, performances, status controls, posters, banners, and archiving.'],
    ['Venue Tiers', 'Venue-specific tier structures with capacity, price, revenue, and inventory controls.'],
    ['Seats & Inventory', 'Seat map, available, sold, held, blocked, accessible, complimentary, and section analytics.'],
    ['Orders', 'Advanced filtering, buyer records, seats, payment references, reissue, refund, cancel, and archive.'],
    ['Payments', 'Payment review queue, proof viewer, status controls, revenue reports, and service fee analytics.'],
    ['Tickets', 'Ticket ID, validation code, voucher search, details, reissue, void, and print.'],
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
    ['Dashboard', 'Owned event snapshot, upcoming schedules, reservations, and posting workflow.'],
    ['Events', 'Create and manage owned event records, schedules, media, and event status.'],
    ['Reservations', 'Active holds, expired holds, release requests, and abandoned checkout monitoring.'],
    ['News Management', 'Create event news, save drafts, request publishing, and archive posts.'],
];

$adminPanelGroups = [
    'dashboard' => ['label' => 'Dashboard', 'eyebrow' => 'Command Center', 'partial' => 'dashboard.php', 'items' => ['overview' => 'Overview', 'analytics' => 'Analytics', 'orders' => 'Recent Orders', 'payments' => 'Payment Activity']],
    'venues' => ['label' => 'Venues', 'eyebrow' => 'Venue Operations', 'partial' => 'venues.php', 'items' => ['cards' => 'Venue Cards', 'details' => 'Details Page', 'maps' => 'SVG Seat Maps', 'assignment' => 'Organizer Assignment']],
    'events' => ['label' => 'Events', 'eyebrow' => 'Event Management', 'partial' => 'events.php', 'items' => ['listing' => 'Listing', 'create' => 'Create Event', 'schedule' => 'Schedules', 'performance' => 'Performance']],
    'tiers' => ['label' => 'Venue Tiers', 'eyebrow' => 'Pricing Architecture', 'partial' => 'tiers.php', 'items' => ['venue-tiers' => 'Venue Tiers', 'pricing' => 'Pricing', 'inventory' => 'Inventory', 'controls' => 'Controls']],
    'inventory' => ['label' => 'Seats & Inventory', 'eyebrow' => 'Seat Operations', 'partial' => 'inventory.php', 'items' => ['seat-map' => 'Seat Map', 'search' => 'Seat Search', 'section' => 'Sections', 'tier' => 'Tier Analytics']],
    'orders' => ['label' => 'Orders', 'eyebrow' => 'Order Operations', 'partial' => 'orders.php', 'items' => ['filters' => 'Filters', 'buyers' => 'Buyers', 'drawer' => 'Details Drawer', 'actions' => 'Actions']],
    'payments' => ['label' => 'Payments', 'eyebrow' => 'Finance Review', 'partial' => 'payments.php', 'items' => ['queue' => 'Review Queue', 'proof' => 'Proof Viewer', 'revenue' => 'Revenue', 'fees' => 'Service Fees']],
    'tickets' => ['label' => 'Tickets', 'eyebrow' => 'Ticketing', 'partial' => 'tickets.php', 'items' => ['search' => 'Ticket Search', 'details' => 'Details Modal', 'print' => 'Print']],
    'reservations' => ['label' => 'Reservations', 'eyebrow' => 'Seat Holds', 'partial' => 'reservations.php', 'items' => ['active' => 'Active Holds', 'expired' => 'Expired Holds', 'monitoring' => 'Monitoring', 'release' => 'Release']],
    'users' => ['label' => 'Users', 'eyebrow' => 'Identity & Roles', 'partial' => 'users.php', 'items' => ['table' => 'User Table', 'roles' => 'Roles', 'assignment' => 'Organizer Assignment', 'history' => 'Order History']],
    'favorites' => ['label' => 'Favorites', 'eyebrow' => 'Popularity Analytics', 'partial' => 'favorites.php', 'items' => ['top' => 'Most Favorited', 'trends' => 'Trends', 'analytics' => 'Popularity', 'segments' => 'Segments']],
    'reports' => ['label' => 'Reports', 'eyebrow' => 'Exports', 'partial' => 'reports.php', 'items' => ['sales' => 'Sales', 'venue' => 'Venue', 'attendance' => 'Attendance', 'exports' => 'PDF / Excel']],
    'news' => ['label' => 'News Management', 'eyebrow' => 'Content', 'partial' => 'news.php', 'items' => ['create' => 'Create Article', 'editor' => 'Editor', 'publish' => 'Publishing', 'archive' => 'Archive']],
    'archives' => ['label' => 'Archives', 'eyebrow' => 'Retention', 'partial' => 'archives.php', 'items' => ['events' => 'Events', 'orders' => 'Orders', 'scans' => 'Ticket Scans', 'restore' => 'Restore']],
    'audit' => ['label' => 'Audit Logs', 'eyebrow' => 'Traceability', 'partial' => 'audit.php', 'items' => ['activity' => 'Activity', 'payments' => 'Payments', 'seats' => 'Seats', 'archives' => 'Archives']],
    'settings' => ['label' => 'Settings', 'eyebrow' => 'Configuration', 'partial' => 'settings.php', 'items' => ['system' => 'System', 'payment' => 'Payment', 'ticket' => 'Ticket', 'roles' => 'Roles']],
];

$organizerPanelGroups = array_intersect_key($adminPanelGroups, array_flip([
    'dashboard',
    'events',
    'reservations',
    'news',
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
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/staff-panel.css?v=<?= filemtime(__DIR__ . '/css/staff-panel.css') ?>">
</head>
<body class="staff-shell staff-role-<?= sp_h($role) ?>" data-theme="light">
  <aside class="staff-sidebar" id="staffSidebar" aria-label="Admin navigation">
    <a class="staff-brand" href="index.php" aria-label="CLICKET home">
      <img src="assets/Icon_Logo.png" alt="" aria-hidden="true">
      <img src="assets/Name_Logo.png" alt="CLICKET">
    </a>

    <div class="staff-role-card">
      <span><?= $isAdmin ? 'System Role' : 'Organizer Role' ?></span>
      <strong><?= sp_h(ucwords(str_replace('_', ' ', $role))) ?></strong>
      <small><?= $isAdmin ? 'All venues and system settings' : sp_count(count($payload['events'])) . ' owned events' ?></small>
    </div>

    <nav class="staff-nav staff-nav-tree">
      <?php $navIndex = 1; ?>
      <?php foreach ($panelGroups as $groupKey => $group): ?>
        <section class="staff-nav-group <?= $groupKey === 'dashboard' ? 'is-open' : '' ?>" data-nav-group="<?= sp_h($groupKey) ?>">
          <button class="staff-nav-parent <?= $groupKey === 'dashboard' ? 'is-active' : '' ?>" type="button" data-panel-target="<?= sp_h($groupKey) ?>" aria-expanded="<?= $groupKey === 'dashboard' ? 'true' : 'false' ?>">
            <span class="staff-nav-number"><?= sp_h(str_pad((string) $navIndex, 2, '0', STR_PAD_LEFT)) ?></span>
            <span class="staff-nav-label"><?= sp_h($group['label']) ?></span>
            <small><?= sp_count(count($group['items'])) ?></small>
          </button>
          <div class="staff-nav-children">
            <?php foreach ($group['items'] as $itemKey => $itemLabel): ?>
              <button class="staff-nav-child <?= $groupKey === 'dashboard' && $itemKey === 'overview' ? 'is-active' : '' ?>" type="button" data-panel-target="<?= sp_h($groupKey) ?>" data-subtarget="<?= sp_h($itemKey) ?>">
                <?= sp_h($itemLabel) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </section>
        <?php $navIndex++; ?>
      <?php endforeach; ?>
    </nav>

    <div class="staff-sidebar-footer">
      <a href="auth.php?staff_logout=1">Sign out</a>
    </div>
  </aside>

  <main class="staff-main">
    <header class="staff-topbar">
      <button class="staff-icon-btn staff-mobile-nav" type="button" data-sidebar-toggle aria-label="Open navigation">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <div class="staff-title-block">
        <p>CLICKET <?= $isAdmin ? 'Enterprise Admin' : 'Organizer Operations' ?></p>
        <h1><?= sp_h($panelTitle) ?></h1>
        <span><?= sp_h($panelScope) ?></span>
      </div>
      <div class="staff-topbar-actions">
        <span class="staff-context-pill" id="staffContextPill">Dashboard / Overview</span>
        <label class="staff-search">
          <span>Search</span>
          <input type="search" id="staffPanelSearch" placeholder="Event, venue, order, ticket">
        </label>
        <button class="staff-icon-btn" type="button" data-theme-toggle aria-label="Toggle dark theme" title="Toggle theme">Aa</button>
        <span class="staff-live-pill" data-live-clock>Live sync ready</span>
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

  <script src="js/staff-panel.js?v=<?= filemtime(__DIR__ . '/js/staff-panel.js') ?>"></script>
</body>
</html>
