<?php $eventRows = $payload['events']; ?>

<section class="staff-section" data-subsection="listing">
  <div class="staff-section-heading">
    <div>
      <p><?= $isAdmin ? 'Events Management' : 'My Events' ?></p>
      <h2><?= $isAdmin ? 'Create, edit, archive, and assign every event' : 'Create and manage only events owned by your organizer account' ?></h2>
    </div>
    <button class="staff-action-btn" type="button" data-open-modal data-modal-title="Create Event" data-modal-type="event-form">Create Event</button>
  </div>

  <div class="staff-filter-bar">
    <label>
      <span>Venue</span>
      <select>
        <option>All venues in scope</option>
        <?php foreach ($payload['venues'] as $venue): ?>
          <option><?= sp_h($venue['venue']) ?> - <?= sp_h($venue['variant']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      <span>Category</span>
      <select>
        <option>All categories</option>
        <option>Concert</option>
        <option>Sports</option>
        <option>Theater</option>
      </select>
    </label>
    <label>
      <span>Status</span>
      <select>
        <option>Published, draft, sold out</option>
        <option>Published</option>
        <option>Draft</option>
        <option>Sold Out</option>
        <option>Cancelled</option>
        <option>Archived</option>
      </select>
    </label>
    <button type="button">Apply Filters</button>
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
          <th>Performance</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($eventRows as $event): ?>
          <tr data-search-row>
            <td><strong><?= sp_h($event['title']) ?></strong><small><?= sp_h($event['owner']) ?></small></td>
            <td><?= sp_h($event['venue']) ?></td>
            <td><?= sp_h($event['category_label']) ?></td>
            <td><?= sp_h($event['date']) ?><small>Primary performance</small></td>
            <td><?= sp_h($event['price']) ?></td>
            <td><span class="staff-status <?= sp_status_class($event['status']) ?>"><?= sp_h($event['status']) ?></span></td>
            <td>
              <strong><?= sp_money(clicketStaffMoneyValue($event['price']) * 14) ?></strong>
              <small>projected gross</small>
            </td>
            <td>
              <button type="button" data-open-modal data-modal-title="<?= sp_h($event['title']) ?>" data-modal-type="event-performance">Details</button>
              <button type="button">Status</button>
              <button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Archive</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$eventRows): ?>
          <tr><td colspan="8">No events are available in this role scope.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="staff-grid-two" data-subsection="create">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Create Event</p>
        <h2>Event setup form</h2>
      </div>
      <span><?= $isAdmin ? 'All venues' : 'Owned-event venues only' ?></span>
    </div>
    <form class="staff-form-grid" action="#" method="post">
      <label>
        <span>Event Title</span>
        <input type="text" placeholder="Event name">
      </label>
      <label>
        <span>Venue</span>
        <select>
          <?php foreach ($payload['venues'] as $venue): ?>
            <option><?= sp_h($venue['venue']) ?> - <?= sp_h($venue['variant']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        <span>Category</span>
        <select>
          <option>Concert</option>
          <option>Sports</option>
          <option>Theater</option>
        </select>
      </label>
      <label>
        <span>Base Price</span>
        <input type="number" placeholder="2500">
      </label>
      <label>
        <span>Poster Upload</span>
        <input type="file" accept="image/*">
      </label>
      <label>
        <span>Banner Upload</span>
        <input type="file" accept="image/*">
      </label>
      <div class="staff-form-actions">
        <button class="staff-secondary-btn" type="button">Save Draft</button>
        <button class="staff-action-btn" type="button">Publish Event</button>
      </div>
    </form>
  </article>

  <article class="staff-card" data-subsection="schedule">
    <div class="staff-card-heading">
      <div>
        <p>Schedule Management</p>
        <h2>Performances and status controls</h2>
      </div>
    </div>
    <div class="staff-timeline">
      <?php foreach (array_slice($eventRows, 0, 5) as $index => $event): ?>
        <div class="staff-timeline-item" data-search-row>
          <span><?= sp_h(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
          <div>
            <strong><?= sp_h($event['title']) ?></strong>
            <small><?= sp_h($event['date']) ?> &middot; <?= sp_h($event['venue']) ?></small>
          </div>
          <em class="staff-status <?= sp_status_class($event['status']) ?>"><?= sp_h($event['status']) ?></em>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="staff-card-actions">
      <button type="button">Add Performance</button>
      <button type="button">Duplicate Schedule</button>
      <button type="button">Close Sales</button>
      <button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Archive Event</button>
    </div>
  </article>
</section>
