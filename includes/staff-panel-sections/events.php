<?php
$eventRows = $payload['events'];
$eventLayoutOptions = $payload['eventVenueOptions'] ?? [];
$eventLayoutOptionsJson = json_encode($eventLayoutOptions, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>
<script type="application/json" id="staffEventLayoutOptionsJson"><?= $eventLayoutOptionsJson ?: '[]' ?></script>

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
        <?php foreach ($eventLayoutOptions as $option): ?>
          <option><?= sp_h($option['label']) ?></option>
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
            <td><?= sp_h($event['date']) ?><small><?= sp_count($event['performance_count'] ?? 0) ?> performance<?= (int) ($event['performance_count'] ?? 0) === 1 ? '' : 's' ?></small></td>
            <td><?= sp_h($event['price']) ?></td>
            <td><span class="staff-status <?= sp_status_class($event['status']) ?>"><?= sp_h($event['status']) ?></span></td>
            <td>
              <strong><?= sp_h($event['performance_status'] ?? 'Scheduled') ?></strong>
              <small>primary schedule</small>
            </td>
            <td>
              <button type="button" data-open-modal data-modal-title="<?= sp_h($event['title']) ?>" data-modal-type="event-performance">Details</button>
              <button
                type="button"
                data-event-edit
                data-event="<?= sp_h(json_encode($event, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '{}') ?>"
              >Edit</button>
              <form class="staff-inline-form" action="staff-events-api.php" method="post">
                <input type="hidden" name="action" value="archive">
                <input type="hidden" name="event_key" value="<?= sp_h($event['event_key']) ?>">
                <button type="submit" <?= ($isAdmin && strtolower((string) ($event['status_value'] ?? '')) !== 'archived') ? '' : 'disabled' ?>>Archive</button>
              </form>
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
    <form class="staff-form-grid" action="staff-events-api.php" method="post">
      <input type="hidden" name="action" value="create">
      <label>
        <span>Event Title</span>
        <input type="text" name="title" placeholder="Event name" required>
      </label>
      <label>
        <span>Venue</span>
        <select name="venue_layout_id" required>
          <?php foreach ($eventLayoutOptions as $option): ?>
            <option value="<?= (int) $option['venue_layout_id'] ?>" data-category="<?= sp_h($option['category']) ?>"><?= sp_h($option['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        <span>Category</span>
        <select name="category" required>
          <option value="concert">Concert</option>
          <option value="sports">Sports</option>
          <option value="theater">Theater</option>
        </select>
      </label>
      <label>
        <span>Event Type</span>
        <input type="text" name="type" placeholder="International, Musical, Basketball">
      </label>
      <label>
        <span>Base Price</span>
        <input type="number" name="base_price" placeholder="2500" min="0" step="0.01" required>
      </label>
      <label>
        <span>Performance Date</span>
        <input type="date" name="performance_date" required>
      </label>
      <label>
        <span>Performance Time</span>
        <input type="time" name="performance_time" required>
      </label>
      <label>
        <span>Poster URL</span>
        <input type="url" name="poster_url" placeholder="https://...">
      </label>
      <label>
        <span>Banner URL</span>
        <input type="url" name="banner_url" placeholder="https://...">
      </label>
      <div class="staff-form-actions">
        <button class="staff-secondary-btn" type="submit" name="status" value="draft">Save Draft</button>
        <button class="staff-action-btn" type="submit" name="status" value="published">Publish Event</button>
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
