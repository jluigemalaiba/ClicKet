<?php
$attendance = $payload['attendance'] ?? [];
$checkinStats = [
    ['Checked In', (int) ($attendance['checked_in'] ?? 0)],
    ['Still Unused', (int) ($attendance['still_unused'] ?? 0)],
    ['Scan Attempts', (int) ($attendance['scan_attempts'] ?? 0)],
    ['Duplicate Scans', (int) ($attendance['duplicate_scans'] ?? 0)],
];
?>

<section class="staff-checkin-workspace" data-subsection="entry">
  <header class="staff-tickets-head staff-checkin-head">
    <div>
      <p>Gate Operations</p>
      <h2>Ticket Check-In</h2>
      <span><?= sp_h($isAdmin ? 'All active CLICKET tickets' : 'Your owned event tickets') ?></span>
    </div>
    <div class="staff-tickets-head__stats">
      <span><b><?= sp_count($attendance['checked_in'] ?? 0) ?></b> in</span>
      <span><b><?= sp_count($attendance['attendance_rate'] ?? 0) ?>%</b> rate</span>
    </div>
  </header>

  <div class="staff-report-kpis">
    <?php foreach ($checkinStats as $stat): ?>
      <article><span><?= sp_h($stat[0]) ?></span><strong><?= sp_count($stat[1]) ?></strong><small>Live database count</small></article>
    <?php endforeach; ?>
  </div>

  <div class="staff-checkin-grid">
    <article class="staff-report-card staff-checkin-card">
      <header><div><p>Validation</p><h3>Entry scan</h3></div><span><?= sp_h($staff['role'] ?? 'staff') ?></span></header>
      <form class="staff-checkin-form" data-checkin-form>
        <input type="hidden" name="csrf_token" value="<?= sp_h(clicketCsrfToken('staff_checkin')) ?>">
        <label>
          <span>Validation code</span>
          <input name="validation_code" type="text" autocomplete="off" placeholder="VAL-..." data-checkin-primary>
        </label>
        <label>
          <span>Ticket / barcode ID</span>
          <input name="ticket_id" type="text" autocomplete="off" placeholder="TKT-...">
        </label>
        <label>
          <span>Gate</span>
          <input name="gate_name" type="text" autocomplete="off" placeholder="Main Gate">
        </label>
        <button class="staff-action-btn" type="submit">Validate Entry</button>
        <p class="staff-checkin-message" data-checkin-message role="status" aria-live="polite"></p>
      </form>
    </article>

    <article class="staff-report-card staff-checkin-result" data-checkin-result>
      <header><div><p>Result</p><h3>Awaiting scan</h3></div><span>Ready</span></header>
      <div class="staff-checkin-result-body">
        <strong>No ticket scanned yet.</strong>
        <span>Result details will appear here.</span>
      </div>
    </article>
  </div>
</section>
