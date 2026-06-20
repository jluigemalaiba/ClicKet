<?php
$organizerPage = 'dashboard';
$organizerTitle = 'Organizer Dashboard';
require __DIR__ . '/includes/header.php';

function organizer_dashboard_icon(string $type): string {
    $path = match ($type) {
        'events' => '<path d="M8 2v4M16 2v4"></path><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M3 10h18M8 14h.01M12 14h.01M16 14h.01"></path>',
        'tickets' => '<path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7zM13 5v14"></path>',
        'sales' => '<path d="M4 19V5M4 19h16M8 15l3-3 3 2 5-7M17 7h2v2"></path>',
        'attendance' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>',
        default => '<path d="M4 7h12M4 12h16M4 17h10"></path><circle cx="19" cy="7" r="2"></circle>',
    };
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . $path . '</svg>';
}

$dashboardCards = [
    ['Owned Events', sp_count($metrics['activeEvents']), 'Active records in your scope', 'events', 'is-events'],
    ['Tickets Sold', sp_count($metrics['ticketsSold']), 'Issued from paid orders', 'tickets', 'is-tickets'],
    ['Total Sales', sp_money($metrics['sales']), 'Paid organizer revenue', 'sales', 'is-revenue'],
    ['Issued Tickets', sp_count($metrics['tickets']), 'All ticket records', 'tickets', 'is-tickets'],
    ['Attendance', sp_count($metrics['checkedIn'] ?? 0), sp_count($metrics['attendanceRate'] ?? 0) . '% attendance rate', 'attendance', 'is-events'],
    ['Virtual Queue', sp_count($metrics['queueSize'] ?? 0), sp_count($metrics['queueActiveSessions'] ?? 0) . ' active sessions', 'queue', 'is-reservations'],
];
?>

<section class="staff-dashboard-shell is-ready" data-subsection="overview">
  <div class="staff-dashboard-head">
    <div>
      <p>Organizer Overview</p>
      <h2>Good day, <?= sp_h($staff['name'] ?? 'Organizer') ?></h2>
    </div>
    <div class="staff-dashboard-actions">
      <a class="staff-action-btn" href="organizer/events.php">Manage Events</a>
      <a class="staff-secondary-btn" href="organizer/reports.php">View Reports</a>
    </div>
  </div>

  <div class="staff-dashboard-kpis" aria-label="Organizer dashboard summary">
    <?php foreach ($dashboardCards as $index => [$label, $value, $description, $icon, $class]): ?>
      <article class="staff-dashboard-kpi <?= sp_h($class) ?>" style="--dashboard-index: <?= (int) $index ?>">
        <span><?= organizer_dashboard_icon($icon) ?></span>
        <small><?= sp_h($label) ?></small>
        <strong><?= sp_h($value) ?></strong>
        <em><?= sp_h($description) ?></em>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
