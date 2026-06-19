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
      <?php foreach (array_slice($payload['reservationRows'], 0, 6) as $hold): ?>
        <div class="staff-reservation-card" data-search-row>
          <div>
            <strong><?= sp_h($hold['id']) ?></strong>
            <span><?= sp_h($hold['event']) ?></span>
            <small><?= sp_h($hold['venue']) ?> &middot; <?= sp_h($hold['buyer']) ?> &middot; <?= sp_count($hold['seats']) ?> seats</small>
          </div>
          <em class="staff-countdown" data-countdown="<?= (int) $hold['expires_at'] ?>"><?= sp_h($hold['expires_label']) ?></em>
          <button type="button">Release</button>
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
      <button type="button">Release Held Seats</button>
      <button type="button">Extend Hold</button>
      <button type="button">Export Holds</button>
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
            <td><button type="button"><?= $hold['status'] === 'Expired' ? 'Audit Release' : 'Release Held Seats' ?></button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
