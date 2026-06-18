<?php

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';

$staff = currentStaff();
if (!$staff) {
    setFlashMessage('error', 'Please sign in with a staff account to open the management portal.');
    header('Location: auth.php?mode=admin');
    exit;
}

$role = (string) ($staff['role'] ?? 'organizer');
$isAdmin = $role === 'admin';
$payload = clicketStaffPanelPayload($staff);
$metrics = $payload['metrics'];
$panelTitle = $isAdmin ? 'Admin Control Center' : 'Organizer Workspace';
$panelScope = $isAdmin
    ? 'Full system access across every venue, event, order, payment, report, archive, and audit log.'
    : 'Venue-scoped access for assigned venues and events only. Admin approval may be required for archive actions.';

function sp_h(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sp_money(int|float $value): string {
    return 'PHP ' . number_format((float) $value, 0);
}

function sp_count(mixed $value): string {
    return number_format((int) $value);
}

$adminModules = [
    ['Dashboard', 'Sales, ticket volume, active events, reservations, top events/venues, inventory alerts'],
    ['Events Management', 'Create, edit, archive, assign venues, schedules, categories, prices, event status'],
    ['Venue Management', 'Supported venues, SVG maps, capacity, sales, organizer assignment, enable/disable'],
    ['Tier Management', 'Capacity, sold/available/held seats, revenue, price, status, block/release tiers'],
    ['Seat Inventory', 'Availability, sold, held, blocked, accessible, complimentary, section/tier inventory'],
    ['Orders', 'Filters, buyer details, seats, payment references, reissue, refund, archive'],
    ['Payments', 'Proof review, pending/paid/failed/refunded controls, revenue and service fee reports'],
    ['Tickets', 'Search ticket/voucher/validation code, valid/used/cancelled/refunded/reissued, void/reissue'],
    ['Gate / Check-in', 'Scanning, manual validation, entry logs, duplicate entry warning, gate staff view'],
    ['Users / Accounts', 'User search, order history, suspension, roles, organizer assignments'],
    ['Reservations', 'Active holds, expired holds, timers, abandoned checkout detection'],
    ['Favorites Analytics', 'Saved event counts, popular favorited events, user favorite insight'],
    ['Reports', 'Sales by venue/event/tier/section, occupancy, payment method, attendance, user purchase'],
    ['Archives', 'Archived events/orders, past performances, cancelled/refunded records, exports'],
    ['Settings / Audit Logs', 'Admin, organizer, price, seat, order, payment, archive, and activity logs'],
];

$organizerModules = [
    ['Organizer Dashboard', 'Assigned venue/event overview, sales today, tickets sold, availability, pending payments, check-ins'],
    ['My Events', 'Create and manage events only for assigned venues; archive may require admin approval'],
    ['My Venues', 'Assigned venue SVG map, capacity, sales, active events, venue details'],
    ['Venue Tiers', 'Assigned venue/event tiers, capacity, revenue, price, status, block/open if permitted'],
    ['Seats', 'Assigned event availability, sold, held, accessible, complimentary, block/release if permitted'],
    ['Orders', 'Orders for assigned events only, buyer and seat details, export lists'],
    ['Payments', 'Assigned event payment status, proof review if permitted, revenue by event/tier'],
    ['Tickets & Check-in', 'Ticket search, validation, mark used, scanned/remaining counts, entry log'],
    ['Reports', 'Sales, tier, section, occupancy, attendance, payment reports for assigned scope'],
    ['Archives', 'Past assigned events/orders/scans, cancellation/refund records, export history'],
];

$statusClasses = [
    'Published' => 'is-success',
    'Draft' => 'is-muted',
    'Sold Out' => 'is-warning',
    'Cancelled' => 'is-danger',
    'Archived' => 'is-muted',
];

$panelGroups = [
    'dashboard' => [
        'label' => 'Dashboard',
        'eyebrow' => $isAdmin ? 'ClicKet Operations' : 'Assigned Operations',
        'partial' => 'dashboard.php',
        'items' => ['overview' => 'Overview', 'sales' => 'Sales', 'reservations' => 'Reservations', 'alerts' => 'Alerts'],
    ],
    'events' => [
        'label' => $isAdmin ? 'Events' : 'My Events',
        'eyebrow' => $isAdmin ? 'Events Management' : 'Assigned Events',
        'partial' => 'events.php',
        'items' => ['create' => 'Create Event', 'schedules' => 'Schedules', 'pricing' => 'Pricing', 'status' => 'Status'],
    ],
    'venues' => [
        'label' => $isAdmin ? 'Venues / Arenas' : 'My Venues',
        'eyebrow' => 'Venue Management',
        'partial' => 'venues.php',
        'items' => ['maps' => 'SVG Seat Maps', 'capacity' => 'Capacity', 'sales' => 'Venue Sales', 'organizers' => 'Organizers'],
    ],
    'tiers' => [
        'label' => 'Tier Management',
        'eyebrow' => 'Venue Tiers',
        'partial' => 'tiers.php',
        'items' => ['capacity' => 'Tier Capacity', 'inventory' => 'Inventory', 'pricing' => 'Pricing', 'blocking' => 'Block / Open'],
    ],
    'inventory' => [
        'label' => 'Seats / Inventory',
        'eyebrow' => 'Seat Controls',
        'partial' => 'inventory.php',
        'items' => ['availability' => 'Availability', 'holds' => 'Held Seats', 'blocked' => 'Blocked Seats', 'sections' => 'Sections'],
    ],
    'orders' => [
        'label' => 'Orders',
        'eyebrow' => 'Order Management',
        'partial' => 'orders.php',
        'items' => ['all' => 'Order List', 'buyers' => 'Buyer Details', 'refunds' => 'Refunds', 'archives' => 'Archive'],
    ],
    'payments' => [
        'label' => 'Payments',
        'eyebrow' => 'Payment Review',
        'partial' => 'payments.php',
        'items' => ['proof' => 'Proof Review', 'approvals' => 'Approve / Reject', 'revenue' => 'Revenue', 'fees' => 'Service Fees'],
    ],
    'tickets' => [
        'label' => 'Tickets & Check-in',
        'eyebrow' => 'Ticket Validation',
        'partial' => 'tickets.php',
        'items' => ['search' => 'Search Tickets', 'onsite' => 'F2F / On-site Print', 'scan' => 'Gate Scan', 'logs' => 'Entry Logs'],
    ],
    'users' => [
        'label' => $isAdmin ? 'Users / Accounts' : 'Access Scope',
        'eyebrow' => $isAdmin ? 'Account Roles' : 'Assigned Access',
        'partial' => 'users.php',
        'items' => ['users' => 'Users', 'roles' => 'Roles', 'assignments' => 'Assignments', 'history' => 'History'],
    ],
    'reports' => [
        'label' => 'Reports',
        'eyebrow' => 'Analytics',
        'partial' => 'reports.php',
        'items' => ['sales' => 'Sales', 'tiers' => 'Tiers', 'attendance' => 'Attendance', 'exports' => 'Exports'],
    ],
    'archives' => [
        'label' => 'Archives',
        'eyebrow' => 'Historical Records',
        'partial' => 'archives.php',
        'items' => ['events' => 'Events', 'orders' => 'Orders', 'scans' => 'Scans', 'exports' => 'Exports'],
    ],
    'audit' => [
        'label' => 'Audit Logs',
        'eyebrow' => 'System Trace',
        'partial' => 'audit.php',
        'items' => ['activity' => 'Activity', 'prices' => 'Price Changes', 'seats' => 'Seat Actions', 'payments' => 'Payments'],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= sp_h($panelTitle) ?> | ClicKet</title>
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/staff-panel.css?v=<?= filemtime(__DIR__ . '/css/staff-panel.css') ?>">
</head>
<body class="staff-shell staff-role-<?= sp_h($role) ?>">
  <aside class="staff-sidebar" aria-label="Staff navigation">
    <a class="staff-brand" href="index.php" aria-label="ClicKet home">
      <img src="assets/Icon_Logo.png" alt="" aria-hidden="true">
      <img src="assets/Name_Logo.png" alt="ClicKet">
    </a>
    <div class="staff-role-card">
      <span><?= $isAdmin ? 'System Role' : 'Venue Role' ?></span>
      <strong><?= sp_h(ucfirst($role)) ?></strong>
      <small><?= $isAdmin ? 'All venues enabled' : sp_h(implode(', ', clicketStaffAssignedVenueNames($staff)) ?: 'No venue assigned') ?></small>
    </div>
    <nav class="staff-nav staff-nav-tree">
      <?php foreach ($panelGroups as $groupKey => $group): ?>
        <section class="staff-nav-group <?= $groupKey === 'dashboard' ? 'is-open' : '' ?>" data-nav-group="<?= sp_h($groupKey) ?>">
          <button class="staff-nav-parent <?= $groupKey === 'dashboard' ? 'is-active' : '' ?>" type="button" data-panel-target="<?= sp_h($groupKey) ?>" aria-expanded="<?= $groupKey === 'dashboard' ? 'true' : 'false' ?>">
            <span><?= sp_h($group['label']) ?></span>
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
      <?php endforeach; ?>
    </nav>
    <div class="staff-sidebar-footer">
      <a href="auth.php?staff_logout=1">Sign out</a>
    </div>
  </aside>

  <main class="staff-main">
    <header class="staff-topbar">
      <div>
        <p><?= $isAdmin ? 'ClicKet Operations' : 'Assigned Operations' ?></p>
        <h1><?= sp_h($panelTitle) ?></h1>
        <span><?= sp_h($panelScope) ?></span>
      </div>
      <div class="staff-topbar-actions">
        <span class="staff-context-pill" id="staffContextPill">Dashboard / Overview</span>
        <label class="staff-search">
          <span>Search</span>
          <input type="search" id="staffPanelSearch" placeholder="Event, venue, order, tier">
        </label>
        <span class="staff-live-pill" data-live-clock>Live sync ready</span>
      </div>
    </header>

    <div class="staff-panel-stage">
      <?php foreach ($panelGroups as $groupKey => $group): ?>
        <section class="staff-panel-view <?= $groupKey === 'dashboard' ? 'is-active' : '' ?>" id="panel-<?= sp_h($groupKey) ?>" data-panel-view="<?= sp_h($groupKey) ?>" data-panel-label="<?= sp_h($group['label']) ?>">
          <?php require __DIR__ . '/includes/staff-panel-sections/' . $group['partial']; ?>
        </section>
      <?php endforeach; ?>
    </div>
  </main>

  <script src="js/staff-panel.js?v=<?= filemtime(__DIR__ . '/js/staff-panel.js') ?>"></script>
</body>
</html>
