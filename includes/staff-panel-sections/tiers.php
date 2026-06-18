<section class="staff-section">
  <div class="staff-section-heading">
    <div>
      <p>Tier Management</p>
      <h2>Predefined tier structures with seat-map colors</h2>
    </div>
    <button class="staff-action-btn" type="button">Export Tier Report</button>
  </div>
  <div class="staff-tier-grid">
    <?php foreach ($payload['venues'] as $venue): ?>
      <article class="staff-tier-card" data-search-row>
        <header>
          <strong><?= sp_h($venue['venue']) ?> - <?= sp_h($venue['variant']) ?></strong>
          <span><?= sp_count(count($venue['tiers'])) ?> tiers &middot; <?= sp_count($venue['capacity']) ?> capacity</span>
        </header>
        <div class="staff-tier-list">
          <?php foreach ($venue['tiers'] as $tierIndex => $tier):
              $tierCapacity = max(1, (int) floor($venue['capacity'] / max(1, count($venue['tiers']))));
              $sold = min($tierCapacity, max(0, (int) floor(($venue['sold'] + $tierIndex * 3) / max(1, count($venue['tiers'])))));
              $held = $tierIndex % 3;
          ?>
            <div class="staff-tier-row">
              <span class="staff-tier-color" style="--tier-color: <?= sp_h($tier['color']) ?>"></span>
              <span><strong><?= sp_h($tier['name']) ?></strong><small><?= sp_count($tierCapacity) ?> cap &middot; <?= sp_count($sold) ?> sold &middot; <?= sp_count(max(0, $tierCapacity - $sold - $held)) ?> available &middot; <?= sp_count($held) ?> held</small></span>
              <em><?= sp_h($tier['status']) ?></em>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="staff-card-actions">
          <button type="button">Edit price</button>
          <button type="button">Hide/Sold Out</button>
          <button type="button">Block tier</button>
          <button type="button">Release tier</button>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
