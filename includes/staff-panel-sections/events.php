<section class="staff-section">
  <div class="staff-section-heading">
    <div>
      <p><?= $isAdmin ? 'Events Management' : 'My Events' ?></p>
      <h2><?= $isAdmin ? 'Create, edit, archive, and assign events' : 'Create and manage assigned venue events' ?></h2>
    </div>
    <button class="staff-action-btn" type="button" data-permission="<?= $isAdmin ? 'admin:create:any-event' : 'organizer:create:assigned-event' ?>">Add Event</button>
  </div>
  <div class="staff-action-strip">
    <?php foreach (['Add/edit events', 'Assign venue', 'Set category', 'Date/time schedule', 'Poster/banner', 'Base price', 'Draft/Published/Sold Out/Cancelled/Archived'] as $item): ?>
      <button type="button" data-subsection="<?= strtolower(preg_replace('/[^a-z0-9]+/i', '-', $item)) ?>"><?= sp_h($item) ?></button>
    <?php endforeach; ?>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Event</th>
          <th>Venue</th>
          <th>Category</th>
          <th>Schedule</th>
          <th>Base Price</th>
          <th>Status</th>
          <th>Permission</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payload['events'] as $event): ?>
          <tr data-search-row>
            <td><strong><?= sp_h($event['title']) ?></strong><small><?= sp_h($event['type']) ?></small></td>
            <td><?= sp_h($event['venue']) ?></td>
            <td><?= sp_h($event['category_label']) ?></td>
            <td><?= sp_h($event['date']) ?></td>
            <td><?= sp_h($event['price']) ?></td>
            <td><span class="staff-status <?= sp_h($statusClasses[$event['status']] ?? 'is-muted') ?>"><?= sp_h($event['status']) ?></span></td>
            <td><?= $isAdmin ? 'Full edit/archive' : 'Assigned scope only' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
