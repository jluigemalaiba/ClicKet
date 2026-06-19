<?php
$venueRows = $payload['venues'] ?? [];
$staffRows = $payload['staff'] ?? [];
$orders = $payload['orders'] ?? [];
$events = $payload['events'] ?? [];
$venueAccents = ['#e8162b', '#2563eb', '#0f766e', '#b45309', '#7c3aed', '#be185d', '#0f766e', '#1d4ed8', '#b45309'];
?>

<section class="staff-section staff-venues-workspace" data-subsection="cards">
  <div class="staff-section-heading">
    <div>
      <p>Venue Operations</p>
      <h2>Capacity, sales, tiers, and organizer coverage</h2>
    </div>
  </div>

  <div class="staff-venue-selector-grid" aria-label="Select a venue">
    <?php foreach ($venueRows as $venueIndex => $venue):
      $venueAccent = $venueAccents[$venueIndex % count($venueAccents)];
    ?>
      <button
        class="staff-venue-selector <?= $venueIndex === 0 ? 'is-active' : '' ?>"
        type="button"
        data-venue-selector="<?= sp_h($venue['id']) ?>"
        aria-pressed="<?= $venueIndex === 0 ? 'true' : 'false' ?>"
        style="--venue-accent: <?= sp_h($venueAccent) ?>"
      >
        <span class="staff-venue-selector-heading">
          <span class="staff-venue-selector-mark">
            <img src="assets/<?= sp_h($venue['logo'] ?? 'Icon_Logo.png') ?>" alt="<?= sp_h($venue['venue']) ?> logo">
          </span>
          <span class="staff-venue-selector-copy">
            <strong><?= sp_h($venue['venue']) ?></strong>
            <small><?= sp_h($venue['variant']) ?></small>
          </span>
        </span>
        <span class="staff-venue-selector-capacity"><?= sp_count($venue['capacity']) ?> seats</span>
        <span class="staff-venue-selector-progress" aria-label="<?= sp_count($venue['occupancy']) ?> percent venue fill">
          <i style="width: <?= (int) $venue['occupancy'] ?>%"></i>
        </span>
        <span class="staff-venue-selector-footer">
          <span><strong><?= sp_count($venue['sold']) ?></strong><small>tickets sold</small></span>
          <span><strong><?= sp_money((int) $venue['sales']) ?></strong><small>revenue</small></span>
        </span>
      </button>
    <?php endforeach; ?>
  </div>

  <?php foreach ($venueRows as $venueIndex => $venue):
      $venueAccent = $venueAccents[$venueIndex % count($venueAccents)];
      $venueEvents = array_values(array_filter($events, static function (array $event) use ($venue): bool {
          return clicketStaffVenueNamesMatch((string) ($event['venue'] ?? ''), (string) ($venue['venue'] ?? ''));
      }));
      $venueOrganizers = array_values(array_filter($staffRows, static function (array $account) use ($venue): bool {
          if (($account['role'] ?? '') !== 'organizer') {
              return false;
          }
          foreach ((array) ($account['venues'] ?? []) as $assignedVenue) {
              if (clicketStaffVenueNamesMatch((string) $assignedVenue, (string) ($venue['venue'] ?? ''))) {
                  return true;
              }
          }
          return false;
      }));
      $eventPerformance = [];
      foreach ($venueEvents as $event) {
          $eventOrders = array_values(array_filter($orders, static function (array $order) use ($event): bool {
              $orderEvent = strtolower((string) ($order['event'] ?? ''));
              $orderTitle = strtolower((string) ($order['event_title'] ?? ''));
              $eventKey = strtolower((string) ($event['key'] ?? ''));
              $eventTitle = strtolower((string) ($event['title'] ?? ''));
              return ($eventKey !== '' && $orderEvent === $eventKey) || ($eventTitle !== '' && $orderTitle === $eventTitle);
          }));
          $paidOrders = array_values(array_filter($eventOrders, static fn (array $order): bool => strtolower((string) ($order['payment_status'] ?? '')) === 'paid'));
          $eventPerformance[] = [
              'title' => (string) ($event['title'] ?? 'Untitled event'),
              'date' => (string) ($event['date'] ?? 'Schedule pending'),
              'tickets' => array_sum(array_map(static fn (array $order): int => clicketStaffTicketCount($order), $paidOrders)),
              'revenue' => array_sum(array_map(static fn (array $order): int => (int) ($order['total'] ?? 0), $paidOrders)),
          ];
      }
      usort($eventPerformance, static fn (array $left, array $right): int => $right['revenue'] <=> $left['revenue']);
      $eventRevenueMax = max(1, ...array_map(static fn (array $eventRow): int => (int) $eventRow['revenue'], $eventPerformance ?: [['revenue' => 0]]));
      $eventTicketMax = max(1, ...array_map(static fn (array $eventRow): int => (int) $eventRow['tickets'], $eventPerformance ?: [['tickets' => 0]]));
      $tiers = $venue['tiers'] ?? [];
      $tierCapacity = max(1, (int) floor(((int) ($venue['capacity'] ?? 0)) / max(1, count($tiers))));
  ?>
    <article class="staff-venue-detail <?= $venueIndex === 0 ? 'is-active' : '' ?>" data-venue-panel="<?= sp_h($venue['id']) ?>" style="--venue-accent: <?= sp_h($venueAccent) ?>">
      <header class="staff-venue-detail-head">
        <div class="staff-venue-detail-copy">
          <p><?= sp_h($venue['variant']) ?> venue</p>
          <h2><?= sp_h($venue['venue']) ?></h2>
          <span><?= sp_h($venue['profileVenue']) ?></span>
        </div>
        <div class="staff-venue-detail-brand">
          <span class="staff-venue-detail-logo"><img src="assets/<?= sp_h($venue['logo'] ?? 'Icon_Logo.png') ?>" alt="<?= sp_h($venue['venue']) ?> logo"></span>
          <div class="staff-venue-detail-capacity">
            <strong><?= sp_count($venue['capacity']) ?></strong>
            <span>total capacity</span>
          </div>
        </div>
      </header>

      <div class="staff-venue-overview-grid">
        <button class="staff-venue-overview is-revenue is-active" type="button" data-venue-tab-trigger="revenue" aria-pressed="true">
          <span>Venue revenue</span>
          <strong><?= sp_money((int) $venue['sales']) ?></strong>
          <small>Click to view event revenue</small>
        </button>
        <button class="staff-venue-overview" type="button" data-venue-tab-trigger="tickets" aria-pressed="false">
          <span>Tickets sold</span>
          <strong><?= sp_count($venue['sold']) ?></strong>
          <small>Click to view selling events</small>
        </button>
        <div class="staff-venue-overview">
          <span>Available seats</span>
          <strong><?= sp_count($venue['available']) ?></strong>
          <small><?= sp_count($venue['occupancy']) ?>% venue fill</small>
        </div>
      </div>

      <div class="staff-venue-data-tabs">
        <section class="staff-venue-data-tab is-active" data-venue-tab="revenue">
          <div class="staff-venue-subheading">
            <div>
              <p>Revenue by event</p>
              <h3>Paid revenue for <?= sp_h($venue['venue']) ?></h3>
            </div>
          </div>
          <div class="staff-venue-event-list">
            <?php foreach ($eventPerformance as $eventRow): ?>
              <div class="staff-venue-event-row">
                <span>
                  <strong><?= sp_h($eventRow['title']) ?></strong>
                  <small><?= sp_h($eventRow['date']) ?> &middot; <?= sp_count($eventRow['tickets']) ?> tickets</small>
                  <i class="staff-venue-event-meter"><b style="width: <?= (int) round(((int) $eventRow['revenue'] / $eventRevenueMax) * 100) ?>%"></b></i>
                </span>
                <strong><?= sp_money((int) $eventRow['revenue']) ?></strong>
              </div>
            <?php endforeach; ?>
            <?php if (!$eventPerformance): ?><p class="staff-empty-state">No event revenue in the current venue scope.</p><?php endif; ?>
          </div>
        </section>

        <section class="staff-venue-data-tab" data-venue-tab="tickets">
          <div class="staff-venue-subheading">
            <div>
              <p>Ticket velocity</p>
              <h3>Events with tickets sold</h3>
            </div>
          </div>
          <div class="staff-venue-event-list">
            <?php foreach (array_filter($eventPerformance, static fn (array $eventRow): bool => $eventRow['tickets'] > 0) as $eventRow): ?>
              <div class="staff-venue-event-row">
                <span>
                  <strong><?= sp_h($eventRow['title']) ?></strong>
                  <small><?= sp_h($eventRow['date']) ?> &middot; <?= sp_money((int) $eventRow['revenue']) ?></small>
                  <i class="staff-venue-event-meter"><b style="width: <?= (int) round(((int) $eventRow['tickets'] / $eventTicketMax) * 100) ?>%"></b></i>
                </span>
                <strong><?= sp_count($eventRow['tickets']) ?> sold</strong>
              </div>
            <?php endforeach; ?>
            <?php if (!array_filter($eventPerformance, static fn (array $eventRow): bool => $eventRow['tickets'] > 0)): ?><p class="staff-empty-state">No paid tickets have been recorded for this venue yet.</p><?php endif; ?>
          </div>
        </section>
      </div>

      <div class="staff-venue-detail-grid">
        <section class="staff-venue-tier-editor">
          <div class="staff-venue-subheading">
            <div>
              <p>Tier setup</p>
              <h3>Ticket tiers</h3>
            </div>
            <span><?= sp_count(count($tiers)) ?> tiers</span>
          </div>
          <div class="staff-venue-tier-list">
            <?php foreach ($tiers as $tierIndex => $tier): ?>
              <label class="staff-venue-tier-editor-row" data-tier-editor data-tier-key="<?= sp_h($venue['id'] . '-' . $tierIndex) ?>">
                <input class="staff-tier-color-input" type="color" value="<?= sp_h($tier['color']) ?>" aria-label="<?= sp_h($tier['name']) ?> color">
                <input class="staff-tier-name-input" type="text" value="<?= sp_h($tier['name']) ?>" aria-label="Tier name">
                <span><?= sp_count($tierCapacity) ?> seats</span>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="staff-tier-save-row">
            <button class="staff-action-btn" type="button" data-tier-save>Save tier changes</button>
            <span data-tier-save-status aria-live="polite"></span>
          </div>
        </section>

        <section class="staff-venue-organizers">
          <div class="staff-venue-subheading">
            <div>
              <p>Venue team</p>
              <h3>Organizers</h3>
            </div>
            <span><?= sp_count(count($venueOrganizers)) ?> assigned</span>
          </div>
          <div class="staff-venue-organizer-list">
            <?php foreach ($venueOrganizers as $organizer): ?>
              <div class="staff-venue-organizer-row">
                <span class="staff-venue-organizer-avatar"><?= sp_h(sp_initials((string) ($organizer['name'] ?? 'Organizer'))) ?></span>
                <span><strong><?= sp_h($organizer['name'] ?? 'Organizer') ?></strong><small><?= sp_h($organizer['email'] ?? '') ?></small></span>
              </div>
            <?php endforeach; ?>
            <?php if (!$venueOrganizers): ?><p class="staff-empty-state">No organizer is listed for this venue.</p><?php endif; ?>
          </div>
        </section>
      </div>
    </article>
  <?php endforeach; ?>
</section>
