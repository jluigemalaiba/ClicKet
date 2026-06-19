<section class="staff-grid-two" data-subsection="events">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Archives</p>
        <h2>Archived records and restore workflow</h2>
      </div>
      <span><?= $isAdmin ? 'System-wide' : 'Assigned scope' ?></span>
    </div>
    <div class="staff-control-grid">
      <?php foreach (['Archived events', 'Archived orders', 'Archived ticket scans', 'Cancelled/refunded records', 'Past performances', 'Export archive data'] as $item): ?>
        <button type="button"><?= sp_h($item) ?></button>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="staff-card" data-subsection="restore">
    <div class="staff-card-heading">
      <div>
        <p>Restore Functionality</p>
        <h2>Permission-controlled recovery</h2>
      </div>
    </div>
    <div class="staff-list">
      <div class="staff-list-row"><span>Admin restore access</span><strong>All records</strong><small>Can restore archived events, orders, and ticket scans</small></div>
      <div class="staff-list-row"><span>Organizer restore access</span><strong>Request only</strong><small>Assigned records can be submitted for admin approval</small></div>
      <div class="staff-list-row"><span>Audit requirement</span><strong>Reason required</strong><small>Every archive and restore action writes an audit row</small></div>
    </div>
  </article>
</section>

<section class="staff-section" data-subsection="orders">
  <div class="staff-section-heading">
    <div>
      <p>Archived Events, Orders, And Ticket Scans</p>
      <h2>Retention-ready archive table</h2>
    </div>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Type</th>
          <th>Record</th>
          <th>Scope</th>
          <th>Status</th>
          <th>Archived At</th>
          <th>Restore</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payload['archives'] as $archive): ?>
          <tr data-search-row>
            <td><?= sp_h($archive['type']) ?></td>
            <td><strong><?= sp_h($archive['title']) ?></strong></td>
            <td><?= sp_h($archive['scope']) ?></td>
            <td><span class="staff-status <?= sp_status_class($archive['status']) ?>"><?= sp_h($archive['status']) ?></span></td>
            <td><?= sp_h($archive['archived_at']) ?></td>
            <td><button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Restore</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
