<?php
$activeReservationRows = array_values(array_filter(
    $payload['reservationRows'],
    static fn (array $row): bool => ($row['status'] ?? '') === 'Active' && (int) ($row['db_id'] ?? 0) > 0
));
$firstActiveHold = $activeReservationRows[0] ?? null;
?>
<section class="staff-grid-two" data-subsection="active">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Reservations</p>
        <h2>Active holds and countdowns</h2>
      </div>
      <span><?= sp_count($metrics['activeReservations']) ?> active</span>
    </div>
    <div class="staff-reservation-list">
      <?php foreach (array_slice($activeReservationRows, 0, 6) as $hold): ?>
        <div class="staff-reservation-card" data-search-row>
          <div>
            <strong><?= sp_h($hold['id']) ?></strong>
            <span><?= sp_h($hold['event']) ?></span>
            <small><?= sp_h($hold['venue']) ?> &middot; <?= sp_h($hold['buyer']) ?> &middot; <?= sp_count($hold['seats']) ?> seats</small>
          </div>
          <em class="staff-countdown" data-countdown="<?= (int) $hold['expires_at'] ?>"><?= sp_h($hold['expires_label']) ?></em>
          <form method="post" action="staff-reservation-api.php">
            <input type="hidden" name="action" value="release">
            <input type="hidden" name="hold_id" value="<?= sp_h((string) ($hold['db_id'] ?? '')) ?>">
            <button type="submit">Release</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="staff-card" data-subsection="monitoring">
    <div class="staff-card-heading">
      <div>
        <p>Seat Hold Monitoring</p>
        <h2>Checkout hold health</h2>
      </div>
    </div>
    <div class="staff-status-grid">
      <div class="staff-status-tile"><strong><?= sp_count($metrics['activeReservations']) ?></strong><small>Active holds</small></div>
      <div class="staff-status-tile"><strong><?= sp_count(count(array_filter($payload['reservationRows'], static fn (array $row): bool => $row['status'] === 'Expired'))) ?></strong><small>Expired holds</small></div>
      <div class="staff-status-tile"><strong>Auto</strong><small>Release policy</small></div>
      <div class="staff-status-tile"><strong>15 min</strong><small>Default timer</small></div>
    </div>
    <div class="staff-card-actions">
      <form method="post" action="staff-reservation-api.php">
        <input type="hidden" name="action" value="release">
        <input type="hidden" name="hold_id" value="<?= sp_h((string) ($firstActiveHold['db_id'] ?? '')) ?>">
        <button type="submit" <?= $firstActiveHold ? '' : 'disabled' ?>>Release Hold</button>
      </form>
      <form method="post" action="staff-reservation-api.php">
        <input type="hidden" name="action" value="extend">
        <input type="hidden" name="hold_id" value="<?= sp_h((string) ($firstActiveHold['db_id'] ?? '')) ?>">
        <input type="hidden" name="minutes" value="15">
        <button type="submit" <?= $firstActiveHold ? '' : 'disabled' ?>>Extend Hold</button>
      </form>
      <form method="get" action="staff-reservation-api.php">
        <input type="hidden" name="action" value="export">
        <button type="submit">Export Reservations</button>
      </form>
    </div>
  </article>
</section>

<section class="staff-section" data-subsection="expired">
  <div class="staff-section-heading">
    <div>
      <p>Expired Holds</p>
      <h2>Released and stale checkout sessions</h2>
    </div>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Hold</th>
          <th>Event</th>
          <th>Venue</th>
          <th>Buyer</th>
          <th>Seats</th>
          <th>Status</th>
          <th>Control</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payload['reservationRows'] as $hold): ?>
          <tr data-search-row>
            <td><strong><?= sp_h($hold['id']) ?></strong></td>
            <td><?= sp_h($hold['event']) ?></td>
            <td><?= sp_h($hold['venue']) ?></td>
            <td><?= sp_h($hold['buyer']) ?></td>
            <td><?= sp_count($hold['seats']) ?></td>
            <td><span class="staff-status <?= sp_status_class($hold['status']) ?>"><?= sp_h($hold['status']) ?></span></td>
            <td>
              <form method="post" action="staff-reservation-api.php">
                <input type="hidden" name="action" value="release">
                <input type="hidden" name="hold_id" value="<?= sp_h((string) ($hold['db_id'] ?? '')) ?>">
                <button type="submit" <?= (int) ($hold['db_id'] ?? 0) > 0 ? '' : 'disabled' ?>><?= $hold['status'] === 'Expired' ? 'Audit Release' : 'Release Hold' ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
