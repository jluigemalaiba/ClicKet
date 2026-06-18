<section class="staff-grid-two">
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Seats / Inventory</h2>
      <span>Section and tier level controls</span>
    </div>
    <div class="staff-control-grid">
      <?php foreach (['Available seats', 'Sold seats', 'Held/reserved seats', 'Blocked seats', 'Accessible seats', 'Complimentary seats', 'Section inventory', 'Tier inventory', 'Manual block/release'] as $item): ?>
        <button type="button"><?= sp_h($item) ?></button>
      <?php endforeach; ?>
    </div>
  </article>
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Reservations</h2>
      <span>Seat holds and abandoned checkout</span>
    </div>
    <div class="staff-list">
      <div class="staff-list-row"><span>Active seat holds</span><strong><?= sp_count($metrics['activeReservations']) ?></strong><small>timer/session tracked</small></div>
      <div class="staff-list-row"><span>Expired holds</span><strong>Auto</strong><small>released by reservation store</small></div>
      <div class="staff-list-row"><span>Abandoned checkout</span><strong>Detect</strong><small>flag stale sessions</small></div>
    </div>
  </article>
</section>

<section class="staff-section">
  <div class="staff-section-heading">
    <div>
      <p>Inventory by Venue</p>
      <h2>Capacity, held seats, sold seats, and available counts</h2>
    </div>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Venue</th>
          <th>Variant</th>
          <th>Capacity</th>
          <th>Sold</th>
          <th>Held</th>
          <th>Available</th>
          <th>Occupancy</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payload['venues'] as $venue): ?>
          <tr data-search-row>
            <td><strong><?= sp_h($venue['venue']) ?></strong><small><?= sp_h($venue['svg']) ?></small></td>
            <td><?= sp_h($venue['variant']) ?></td>
            <td><?= sp_count($venue['capacity']) ?></td>
            <td><?= sp_count($venue['sold']) ?></td>
            <td><?= sp_count($venue['held']) ?></td>
            <td><?= sp_count($venue['available']) ?></td>
            <td><?= sp_count($venue['occupancy']) ?>%</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
