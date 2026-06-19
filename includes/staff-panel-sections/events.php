<?php $eventRows = $payload['events']; ?>

<section class="staff-section" data-subsection="listing">
  <div class="staff-section-heading">
    <div>
      <p>Organizer Event Review</p>
      <h2>Published event submissions across every venue</h2>
    </div>
  </div>

  <div class="staff-event-review-filter">
    <label>
      <span>Venue</span>
      <select>
        <option>All venues in scope</option>
        <?php foreach ($payload['venues'] as $venue): ?>
          <option><?= sp_h($venue['venue']) ?> - <?= sp_h($venue['variant']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <span data-event-filter-count><?= sp_count(count($eventReviewRows)) ?> events</span>
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

  <div class="staff-event-review-modal" data-event-review-modal hidden>
    <div class="staff-event-review-modal-backdrop" data-event-modal-close></div>
    <section class="staff-event-review-modal-panel" role="dialog" aria-modal="true" aria-label="Event submission details">
      <button class="staff-event-review-modal-close" type="button" data-event-modal-close aria-label="Close event details">x</button>
      <?php foreach ($eventReviewRows as $eventIndex => $event): ?>
        <article class="staff-event-review-detail" data-event-panel="<?= sp_h($event['key']) ?>">
      <div class="staff-event-review-banner">
        <img src="<?= sp_h($event['banner']) ?>" alt="<?= sp_h($event['title']) ?> banner">
        <div class="staff-event-review-banner-copy">
          <p><?= sp_h($event['category_label']) ?> submission</p>
          <h2><?= sp_h($event['title']) ?></h2>
          <span><?= sp_h($event['date']) ?> &middot; <?= sp_h($event['venue']) ?></span>
        </div>
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

      <div class="staff-event-review-stats">
        <div><span>Paid sales</span><strong><?= sp_money((int) $event['sales']) ?></strong></div>
        <div><span>Tickets sold</span><strong><?= sp_count($event['sold']) ?></strong></div>
        <div><span>Available seats</span><strong><?= sp_count($event['available']) ?></strong></div>
      </div>

      <section class="staff-event-review-tiers">
        <div class="staff-venue-subheading">
          <div>
            <p>Availability</p>
            <h3>Seats remaining per tier</h3>
          </div>
          <span><?= sp_count($event['venue_capacity']) ?> venue capacity</span>
        </div>
        <div class="staff-event-tier-list">
          <?php foreach ($event['tiers'] as $tier): ?>
            <div class="staff-event-tier-row">
              <span class="staff-event-tier-swatch" style="--tier-color: <?= sp_h($tier['color']) ?>"></span>
              <span><strong><?= sp_h($tier['name']) ?></strong><small><?= sp_count($tier['sold']) ?> sold of <?= sp_count($tier['capacity']) ?></small></span>
              <strong><?= sp_count($tier['available']) ?> left</strong>
              <i><b style="width: <?= sp_percent((int) $tier['available'], max(1, (int) $tier['capacity'])) ?>%"></b></i>
            </div>
          <?php endforeach; ?>
          <?php if (!$event['tiers']): ?><p class="staff-empty-state">Seat-tier availability will appear after a venue layout is connected.</p><?php endif; ?>
        </div>
      </section>
        </article>
      <?php endforeach; ?>
    </section>
  </div>
</section>
