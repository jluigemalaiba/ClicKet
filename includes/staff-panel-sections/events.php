<?php
$eventRows = $payload['events'];
$eventReviewRows = $eventRows;
?>

<section class="staff-section" data-subsection="listing">
  <div class="staff-section-heading">
    <div>
      <p><?= $isAdmin ? 'Organizer Event Review' : 'Organizer Event Management' ?></p>
      <h2><?= $isAdmin ? 'Published event submissions across every venue' : 'Add, edit, and manage your events' ?></h2>
    </div>
    <button class="staff-action-btn" type="button" data-event-create>+ Add Event</button>
  </div>

  <div class="staff-event-review-filter">
    <label>
      <span>Venue</span>
      <select data-event-venue-filter>
        <option value="">All venues in scope</option>
        <?php foreach ($payload['venues'] as $venue): ?>
          <option value="<?= sp_h($venue['venue']) ?>"><?= sp_h($venue['venue']) ?> - <?= sp_h($venue['variant']) ?></option>
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
          <?php $eventJson = json_encode($event, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
          <tr data-search-row data-event-review-card>
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
              <button type="button" data-event-edit data-event="<?= sp_h($eventJson ?: '{}') ?>">Edit</button>
              <button type="button" data-event-card="<?= sp_h($event['key']) ?>" data-event-venue="<?= sp_h($event['venue']) ?>">Details</button>
              <?php if ($isAdmin): ?>
                <form action="staff-events-api.php" method="post" style="display:inline">
                  <input type="hidden" name="action" value="archive">
                  <input type="hidden" name="event_key" value="<?= sp_h($event['key']) ?>">
                  <button type="submit">Archive</button>
                </form>
              <?php endif; ?>
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
        <?php
        $eventBanner = (string) ($event['banner'] ?? $event['banner_url'] ?? $event['poster_url'] ?? '');
        $eventSales = (int) ($event['sales'] ?? 0);
        $eventSold = (int) ($event['sold'] ?? 0);
        $eventAvailable = (int) ($event['available'] ?? 0);
        $eventVenueCapacity = (int) ($event['venue_capacity'] ?? 0);
        $eventTiers = is_array($event['tiers'] ?? null) ? $event['tiers'] : [];
        $eventSchedules = is_array($event['schedules'] ?? null) ? $event['schedules'] : [];
        $runningMinutes = (int) ($event['running_minutes'] ?? 0);
        $doorsOpen = (int) ($event['doors_open_minutes'] ?? 0);
        ?>
        <article class="staff-event-review-detail" data-event-panel="<?= sp_h($event['key']) ?>">
      <div class="staff-event-review-banner">
        <?php if ($eventBanner !== ''): ?><img src="<?= sp_h($eventBanner) ?>" alt="<?= sp_h($event['title']) ?> banner"><?php endif; ?>
        <div class="staff-event-review-banner-copy">
          <p><?= sp_h($event['category_label']) ?> submission</p>
          <h2><?= sp_h($event['title']) ?></h2>
          <span><?= sp_h($event['date']) ?> &middot; <?= sp_h($event['venue']) ?></span>
        </div>
      </div>

      <div class="staff-detail-list">
        <div><span>About</span><strong><?= sp_h((string) ($event['description'] ?? 'No description yet.')) ?></strong></div>
        <div><span>Cast / Performers</span><strong><?= sp_h((string) ($event['cast_performers'] ?? $event['owner'] ?? 'Not set')) ?></strong></div>
        <div><span>Type</span><strong><?= sp_h((string) ($event['type'] ?? 'Not set')) ?></strong></div>
        <div><span>Running time</span><strong><?= $runningMinutes > 0 ? sp_count($runningMinutes) . ' minutes' : 'Not set' ?></strong></div>
        <div><span>Age range</span><strong><?= sp_h((string) ($event['age_range'] ?? 'Not set')) ?></strong></div>
        <div><span>Doors open</span><strong><?= $doorsOpen > 0 ? sp_count($doorsOpen) . ' minutes before' : 'Not set' ?></strong></div>
      </div>

      <section class="staff-event-review-tiers">
        <div class="staff-venue-subheading">
          <div>
            <p>Schedule</p>
            <h3>Published date and time slots</h3>
          </div>
          <span><?= sp_count(count($eventSchedules)) ?> schedule<?= count($eventSchedules) === 1 ? '' : 's' ?></span>
        </div>
        <div class="staff-detail-list">
          <?php foreach ($eventSchedules as $schedule): ?>
            <div><span><?= sp_h(ucfirst((string) ($schedule['status'] ?? 'scheduled'))) ?></span><strong><?= sp_h((string) ($schedule['label'] ?? '')) ?></strong></div>
          <?php endforeach; ?>
          <?php if (!$eventSchedules): ?><div><span>Schedule</span><strong>No schedule saved.</strong></div><?php endif; ?>
        </div>
      </section>

      <div class="staff-event-review-stats">
        <div><span>Paid sales</span><strong><?= sp_money($eventSales) ?></strong></div>
        <div><span>Tickets sold</span><strong><?= sp_count($eventSold) ?></strong></div>
        <div><span>Available seats</span><strong><?= sp_count($eventAvailable) ?></strong></div>
      </div>

      <section class="staff-event-review-tiers">
        <div class="staff-venue-subheading">
          <div>
            <p>Availability</p>
            <h3>Seats remaining per tier</h3>
          </div>
          <span><?= sp_count($eventVenueCapacity) ?> venue capacity</span>
        </div>
        <div class="staff-event-tier-list">
          <?php foreach ($eventTiers as $tier): ?>
            <div class="staff-event-tier-row">
              <span class="staff-event-tier-swatch" style="--tier-color: <?= sp_h($tier['color']) ?>"></span>
              <span><strong><?= sp_h($tier['name']) ?></strong><small><?= sp_h((string) ($tier['price_label'] ?? '₱0.00')) ?> &middot; <?= sp_count($tier['sold']) ?> sold of <?= sp_count($tier['capacity']) ?></small></span>
              <strong><?= sp_count($tier['available']) ?> left</strong>
              <i><b style="width: <?= sp_percent((int) $tier['available'], max(1, (int) $tier['capacity'])) ?>%"></b></i>
            </div>
          <?php endforeach; ?>
          <?php if (!$eventTiers): ?><p class="staff-empty-state">Seat-tier availability will appear after a venue layout is connected.</p><?php endif; ?>
        </div>
      </section>
        </article>
      <?php endforeach; ?>
    </section>
  </div>
</section>
