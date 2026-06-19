<section class="staff-section" data-subsection="venue-tiers">
  <div class="staff-section-heading">
    <div>
      <p>Venue Tier Management</p>
      <h2>Venue-specific tiers with capacity, inventory, price, and revenue controls</h2>
    </div>
    <button class="staff-action-btn" type="button" data-open-modal data-modal-title="Tier Report" data-modal-type="report-export">Export Tier Report</button>
  </div>

  <div class="staff-tier-grid">
    <?php foreach ($payload['venues'] as $venue): ?>
      <article class="staff-tier-card" data-search-row>
        <header>
          <div>
            <strong><?= sp_h($venue['venue']) ?> - <?= sp_h($venue['variant']) ?></strong>
            <small><?= sp_count(count($venue['tiers'])) ?> tiers &middot; <?= sp_count($venue['capacity']) ?> capacity</small>
          </div>
          <span class="staff-status is-success">Open</span>
        </header>
        <div class="staff-tier-list">
          <?php foreach ($venue['tiers'] as $tierIndex => $tier):
              $tierCapacity = max(1, (int) floor($venue['capacity'] / max(1, count($venue['tiers']))));
              $sold = min($tierCapacity, max(0, (int) floor(($venue['sold'] + $tierIndex * 3) / max(1, count($venue['tiers'])))));
              $held = $tierIndex % 3;
              $available = max(0, $tierCapacity - $sold - $held);
          ?>
            <div class="staff-tier-row">
              <span class="staff-tier-color" style="--tier-color: <?= sp_h($tier['color']) ?>"></span>
              <span>
                <strong><?= sp_h($tier['name']) ?></strong>
                <small><?= sp_count($tierCapacity) ?> cap &middot; <?= sp_count($sold) ?> sold &middot; <?= sp_count($available) ?> available &middot; <?= sp_count($held) ?> held</small>
              </span>
              <em><?= sp_h($tier['status']) ?></em>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="staff-card-actions">
          <button type="button" data-open-modal data-modal-title="Edit Tier Price" data-modal-type="tier-price">Edit Price</button>
          <button type="button">Hide/Sold Out</button>
          <button type="button">Block Tier</button>
          <button type="button">Release Tier</button>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="staff-section" data-subsection="inventory">
  <div class="staff-section-heading">
    <div>
      <p>Tier Inventory Analytics</p>
      <h2>Capacity, sold, held, available, and revenue by tier</h2>
    </div>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Venue</th>
          <th>Variant</th>
          <th>Tier</th>
          <th>Capacity</th>
          <th>Sold</th>
          <th>Held</th>
          <th>Available</th>
          <th>Revenue</th>
          <th>Control</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($payload['tierInventory'], 0, 24) as $row): ?>
          <tr data-search-row>
            <td><?= sp_h($row['venue']) ?></td>
            <td><?= sp_h($row['variant']) ?></td>
            <td><strong><?= sp_h($row['tier']) ?></strong></td>
            <td><?= sp_count($row['capacity']) ?></td>
            <td><?= sp_count($row['sold']) ?></td>
            <td><?= sp_count($row['held']) ?></td>
            <td><?= sp_count($row['available']) ?></td>
            <td><?= sp_money((int) $row['revenue']) ?></td>
            <td><button type="button">Manage</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
