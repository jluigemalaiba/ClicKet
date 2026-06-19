<?php
$eventRows = $payload['events'] ?? [];
$eventOrders = $payload['orders'] ?? [];
$eventVenues = array_values(array_unique(array_filter(array_map(static fn (array $event): string => (string) ($event['venue'] ?? ''), $eventRows))));
$eventReviewRows = [];

foreach ($eventRows as $event) {
    $matchedVenue = null;
    foreach ($payload['venues'] as $venue) {
        if (clicketStaffVenueNamesMatch((string) ($event['venue'] ?? ''), (string) ($venue['venue'] ?? ''))) {
            $matchedVenue = $venue;
            break;
        }
    }
    $paidOrders = array_values(array_filter($eventOrders, static function (array $order) use ($event): bool {
        if (strtolower((string) ($order['payment_status'] ?? '')) !== 'paid') {
            return false;
        }
        $orderEvent = strtolower((string) ($order['event'] ?? ''));
        $orderTitle = strtolower((string) ($order['event_title'] ?? ''));
        return $orderEvent === strtolower((string) ($event['key'] ?? ''))
            || $orderTitle === strtolower((string) ($event['title'] ?? ''));
    }));
    $sold = array_sum(array_map(static fn (array $order): int => clicketStaffTicketCount($order), $paidOrders));
    $sales = array_sum(array_map(static fn (array $order): int => (int) ($order['total'] ?? 0), $paidOrders));
    $tiers = $matchedVenue['tiers'] ?? [];
    $venueCapacity = (int) ($matchedVenue['capacity'] ?? 0);
    $tierCapacity = max(1, (int) floor($venueCapacity / max(1, count($tiers))));
    $tierAvailability = [];
    foreach ($tiers as $tierIndex => $tier) {
        $tierSold = min($tierCapacity, max(0, (int) floor(($sold + ($tierIndex * 2)) / max(1, count($tiers)))));
        $tierAvailability[] = $tier + [
            'capacity' => $tierCapacity,
            'sold' => $tierSold,
            'available' => max(0, $tierCapacity - $tierSold),
        ];
    }
    $eventReviewRows[] = $event + [
        'sales' => $sales,
        'sold' => $sold,
        'available' => max(0, $venueCapacity - $sold),
        'venue_capacity' => $venueCapacity,
        'tiers' => $tierAvailability,
    ];
}
?>

<section class="staff-event-review" data-subsection="listing">
  <div class="staff-section-heading">
    <div>
      <p>Organizer Event Review</p>
      <h2>Published event submissions across every venue</h2>
    </div>
  </div>

  <div class="staff-event-review-filter">
    <label>
      <span>Venue</span>
      <select data-event-venue-filter>
        <option value="">All venues</option>
        <?php foreach ($eventVenues as $venueName): ?>
          <option value="<?= sp_h($venueName) ?>"><?= sp_h($venueName) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <span data-event-filter-count><?= sp_count(count($eventReviewRows)) ?> events</span>
  </div>

  <div class="staff-event-review-grid">
    <?php foreach ($eventReviewRows as $eventIndex => $event): ?>
      <article class="staff-event-review-card <?= $eventIndex === 0 ? 'is-active' : '' ?>" data-event-review-card data-search-row>
        <div class="staff-event-review-poster">
          <img src="<?= sp_h($event['poster']) ?>" alt="<?= sp_h($event['title']) ?> poster">
          <span class="staff-event-review-card-overlay"></span>
          <span class="staff-event-review-category"><?= sp_h($event['category_label']) ?></span>
          <span class="staff-event-review-card-meta"><strong><?= sp_count($event['sold']) ?></strong> sold</span>
          <span class="staff-event-review-poster-title"><small><?= sp_h($event['type']) ?></small><strong><?= sp_h($event['title']) ?></strong></span>
        </div>
        <div class="staff-event-review-card-body">
          <h3><?= sp_h($event['title']) ?></h3>
          <p><span><?= sp_panel_icon('calendar') ?></span><?= sp_h($event['date']) ?></p>
          <p><span><?= sp_panel_icon('location') ?></span><?= sp_h($event['venue']) ?></p>
          <p><span><?= sp_panel_icon('performer') ?></span><?= sp_h($event['performer']) ?></p>
          <div class="staff-event-review-card-footer">
            <span><?= sp_money((int) $event['sales']) ?> paid sales</span>
            <button
              type="button"
              data-event-card="<?= sp_h($event['key']) ?>"
              data-event-venue="<?= sp_h($event['venue']) ?>"
              aria-pressed="<?= $eventIndex === 0 ? 'true' : 'false' ?>"
            >View Details</button>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
    <?php if (!$eventReviewRows): ?><p class="staff-empty-state">No organizer event submissions are available.</p><?php endif; ?>
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

      <div class="staff-event-review-summary">
        <section>
          <p>About the event</p>
          <h3>Submission details</h3>
          <span><?= sp_h($event['description']) ?></span>
        </section>
        <section>
          <p>Performers</p>
          <h3><?= sp_h($event['performer']) ?></h3>
          <span><?= sp_h($event['organizer_name'] ?? 'Organizer submission') ?></span>
        </section>
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
