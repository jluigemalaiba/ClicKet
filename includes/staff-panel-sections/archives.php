<section class="staff-grid-two">
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Archives</h2>
      <span><?= $isAdmin ? 'System-wide archive records' : 'Assigned event history' ?></span>
    </div>
    <div class="staff-control-grid">
      <?php foreach (['Archived events', 'Archived orders', 'Past performances', 'Cancelled/refunded records', 'Past ticket scans', 'Export archive data'] as $item): ?>
        <button type="button"><?= sp_h($item) ?></button>
      <?php endforeach; ?>
    </div>
  </article>
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Archive Permission</h2>
      <span>Organizer scope remains protected</span>
    </div>
    <div class="staff-list">
      <div class="staff-list-row"><span>Admin archive access</span><strong>All records</strong><small>Can archive and restore across the platform</small></div>
      <div class="staff-list-row"><span>Organizer archive access</span><strong>Assigned only</strong><small>Archive requests may require admin approval</small></div>
    </div>
  </article>
</section>
