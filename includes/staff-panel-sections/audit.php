<section class="staff-grid-two">
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Settings / Audit Logs</h2>
      <span>Full accountability trail</span>
    </div>
    <div class="staff-list">
      <?php foreach ($payload['audit'] as $audit): ?>
        <div class="staff-list-row" data-search-row>
          <span><?= sp_h($audit['type']) ?></span>
          <strong><?= sp_h($audit['actor']) ?></strong>
          <small><?= sp_h($audit['scope']) ?> &middot; <?= sp_h($audit['time']) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Favorites Analytics</h2>
      <span>Saved-event insight</span>
    </div>
    <div class="staff-kpi-grid staff-kpi-grid--compact">
      <article class="staff-kpi-card"><span>Saved events count</span><strong><?= sp_count(count($payload['favorites'])) ?></strong><small>user favorites store</small></article>
      <article class="staff-kpi-card"><span>Popular favorited events</span><strong>Insight</strong><small>rank by saved count</small></article>
      <article class="staff-kpi-card"><span>User favorites insight</span><strong>Export</strong><small>marketing report ready</small></article>
    </div>
  </article>
</section>
