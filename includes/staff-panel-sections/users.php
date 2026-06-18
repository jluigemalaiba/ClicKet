<section class="staff-grid-two">
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2><?= $isAdmin ? 'Users / Accounts' : 'Organizer Access Scope' ?></h2>
      <span><?= $isAdmin ? 'Roles and account controls' : 'Your assigned venues and permissions' ?></span>
    </div>
    <?php if ($isAdmin): ?>
      <div class="staff-control-grid">
        <?php foreach (['View users', 'Search name/email', 'Order history', 'Disable/suspend', 'Admin', 'Organizer', 'Gate Staff', 'Customer', 'Assign organizers'] as $item): ?>
          <button type="button"><?= sp_h($item) ?></button>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="staff-list">
        <?php foreach ($payload['venues'] as $venue): ?>
          <div class="staff-list-row" data-search-row><span><?= sp_h($venue['venue']) ?></span><strong><?= sp_h($venue['variant']) ?></strong><small>event/tier/seat scope enabled</small></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </article>
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Role Rules</h2>
      <span>Permission boundaries</span>
    </div>
    <div class="staff-list">
      <div class="staff-list-row"><span>Admin</span><strong>Full access</strong><small>Can view and manage all organizer-created data</small></div>
      <div class="staff-list-row"><span>Organizer</span><strong>Venue scoped</strong><small>Can manage assigned venues/events only</small></div>
      <div class="staff-list-row"><span>Gate Staff</span><strong>Check-in only</strong><small>Ticket validation and entry logs</small></div>
    </div>
  </article>
</section>
