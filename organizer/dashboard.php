<?php $organizerPage = 'dashboard'; $organizerTitle = 'Organizer Dashboard'; require __DIR__ . '/includes/header.php'; ?>
<section class="staff-section">
  <div class="staff-section-heading"><div><p>Organizer Dashboard</p><h2>Your events and ticket activity</h2></div></div>
  <div class="staff-kpi-grid staff-kpi-grid--compact">
    <article class="staff-kpi-card"><span>Owned Events</span><strong><?= sp_count($metrics['activeEvents']) ?></strong><small>Active records</small></article>
    <article class="staff-kpi-card"><span>Tickets Sold</span><strong><?= sp_count($metrics['ticketsSold']) ?></strong><small>Paid orders</small></article>
    <article class="staff-kpi-card"><span>Sales</span><strong><?= sp_money($metrics['sales']) ?></strong><small>Scoped revenue</small></article>
    <article class="staff-kpi-card"><span>Tickets</span><strong><?= sp_count($metrics['tickets']) ?></strong><small>Issued records</small></article>
    <article class="staff-kpi-card"><span>Checked In</span><strong><?= sp_count($metrics['checkedIn'] ?? 0) ?></strong><small><?= sp_count($metrics['attendanceRate'] ?? 0) ?>% attendance</small></article>
    <article class="staff-kpi-card"><span>Virtual Queue</span><strong><?= sp_count($metrics['queueSize'] ?? 0) ?></strong><small><?= sp_count($metrics['queueActiveSessions'] ?? 0) ?> active sessions</small></article>
  </div>
  <div class="staff-module-grid">
    <a class="staff-module-card" href="organizer/events.php"><strong>Manage Events</strong><span>Add, edit, view, and manage owned events.</span></a>
    <a class="staff-module-card" href="organizer/tickets.php"><strong>View Tickets</strong><span>Review tickets issued for your events.</span></a>
    <a class="staff-module-card" href="organizer/checkin.php"><strong>Check In Tickets</strong><span>Validate event entry and log scans.</span></a>
    <a class="staff-module-card" href="organizer/queue.php"><strong>Virtual Queue</strong><span>Monitor waiting room size, active sessions, and average wait.</span></a>
    <a class="staff-module-card" href="organizer/reports.php"><strong>Open Reports</strong><span>Review sales and attendance activity.</span></a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
