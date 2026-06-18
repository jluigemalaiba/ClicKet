<section class="staff-section">
  <div class="staff-section-heading">
    <div>
      <p><?= $isAdmin ? 'Venue Management' : 'My Venues' ?></p>
      <h2>Supported venues, SVG seat maps, capacity, sales, organizers</h2>
    </div>
  </div>
  <div class="staff-venue-grid">
    <?php foreach ($payload['venues'] as $venue): ?>
      <article class="staff-venue-card" data-search-row>
        <div class="staff-venue-card-head">
          <div>
            <h3><?= sp_h($venue['venue']) ?></h3>
            <span><?= sp_h($venue['variant']) ?> &middot; <?= sp_h($venue['svg']) ?></span>
          </div>
          <span class="staff-status is-success">Enabled</span>
        </div>
        <div class="staff-meter" aria-label="Occupancy">
          <span style="width: <?= (int) $venue['occupancy'] ?>%"></span>
        </div>
        <div class="staff-venue-stats">
          <span><strong><?= sp_count($venue['capacity']) ?></strong> capacity</span>
          <span><strong><?= sp_count($venue['sold']) ?></strong> sold</span>
          <span><strong><?= sp_money((int) $venue['sales']) ?></strong> sales</span>
        </div>
        <div class="staff-card-actions">
          <button type="button">View SVG seat map</button>
          <button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Assign organizers</button>
          <button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Enable/disable</button>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
