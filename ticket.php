<?php

require_once __DIR__ . '/includes/ticketing.php';
require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/reservation.php';
require_once __DIR__ . '/includes/order-history-data.php';
require_once __DIR__ . '/includes/virtual-queue.php';

$eventKey = trim((string) ($_GET['event'] ?? ''));
$resolved = clicketResolveEvent($eventKey);

if (!$resolved) {
    header('Location: events.php');
    exit;
}

$event = $resolved['event'];
$venueProfile = clicketVenueProfile($event['venue'], $resolved['categoryKey']);
$categories = clicketTicketCategories();
$performanceIndex = max(0, (int) ($_GET['performance'] ?? 0));
$reservationExpired = ($_GET['reservation'] ?? '') === 'expired';
if ($reservationExpired) {
    clicketClearReservation();
}
clicketVirtualQueueRequireAdmission($eventKey, $performanceIndex);
clicketVirtualQueueMarkBookingStarted($eventKey, $performanceIndex);
$reservation = clicketStartReservation($eventKey, $performanceIndex);
$performanceDate = $resolved['date'];
$performanceTime = $resolved['time'];

if ($resolved['categoryKey'] === 'theater' && $performanceIndex > 0) {
    $theaterSlots = [
        [$resolved['date'], $resolved['time']],
        [$resolved['date']->modify('+1 day'), '2:00 PM'],
        [$resolved['date']->modify('+1 day'), '7:30 PM'],
        [$resolved['date']->modify('+2 days'), '3:00 PM'],
    ];
    [$performanceDate, $performanceTime] = $theaterSlots[min($performanceIndex, 3)];
}

$unavailableSeatIds = array_values(array_unique(array_merge(
    clicketBookedSeatIds($eventKey, $performanceDate->format('l, F j, Y'), $performanceTime),
    clicketHeldSeatIds($eventKey, $performanceIndex),
    clicketInventoryBlockedSeatCodes($eventKey, $performanceIndex)
)));

$basePrice = (int) preg_replace('/\D/', '', (string) ($event['price'] ?? '2500'));
if ($basePrice < 500) {
    $basePrice = 2500;
}

$priceFactors = [
    'vip' => 1,
    'platinum' => .82,
    'gold' => .64,
    'silver' => .46,
    'bronze' => .3,
    'general' => .24,
];

$categoryPayload = [];
foreach ($categories as $key => $category) {
    $categoryPayload[$key] = $category + [
        'price' => (int) (round(($basePrice * $priceFactors[$key]) / 50) * 50),
    ];
}

$ticketConfig = [
    'event' => [
        'key' => $eventKey,
        'title' => $event['title'],
        'venue' => $event['venue'],
        'date' => $performanceDate->format('l, F j, Y'),
        'time' => $performanceTime,
        'poster' => $resolved['poster'],
        'banner' => $resolved['banner'],
        'performance' => $performanceIndex,
    ],
    'venue' => $venueProfile,
    'categories' => $categoryPayload,
    'selectionLimit' => 4,
    'reservationExpired' => $reservationExpired,
    'unavailableSeatIds' => $unavailableSeatIds,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Choose seats for <?= htmlspecialchars($event['title']) ?> at <?= htmlspecialchars($event['venue']) ?>.">
  <title>Choose Tickets | <?= htmlspecialchars($event['title']) ?> | ClicKet</title>
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/ticket.css?v=<?= filemtime(__DIR__ . '/css/ticket.css') ?>">
</head>
<body class="ticket-page">
  <header class="ticket-topbar">
    <a class="ticket-brand" href="index.php" aria-label="ClicKet home">
      <img src="assets/Icon_Logo.png" alt="" aria-hidden="true">
      <img src="assets/Name_Logo.png" alt="ClicKet">
    </a>

    <div class="ticket-progress" aria-label="Ticket purchase progress">
      <div class="ticket-session-clock">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        <span id="ticketTimer" data-reservation-timer data-expires-at="<?= (int) $reservation['expires_at'] * 1000 ?>" data-expiry-url="<?= htmlspecialchars(clicketReservationExpiryUrl($eventKey, $performanceIndex)) ?>">15:00</span>
      </div>
      <ol>
        <li class="is-active"><span>1</span><b>Seats</b></li>
        <li><span>2</span><b>Details</b></li>
        <li><span>3</span><b>Payment</b></li>
        <li><span>4</span><b>Done</b></li>
      </ol>
    </div>
  </header>

  <main class="ticket-shell">
    <?php if ($reservationExpired): ?>
      <div class="reservation-expired-notice" role="alert">
        <div>
          <strong>Your reservation expired.</strong>
          <span>The pending order was cancelled and your selected seats were released. Please choose your seats again.</span>
        </div>
        <button type="button" class="reservation-expired-close" aria-label="Dismiss reservation expired notice">&times;</button>
      </div>
    <?php endif; ?>
    <section class="ticket-workspace">
      <div class="ticket-event-mobile">
        <img src="<?= htmlspecialchars($resolved['poster']) ?>" alt="">
        <div>
          <small><?= htmlspecialchars($resolved['categoryLabel']) ?></small>
          <strong><?= htmlspecialchars($event['title']) ?></strong>
          <span><?= htmlspecialchars($performanceDate->format('M j')) ?>, <?= htmlspecialchars($performanceTime) ?></span>
        </div>
      </div>

      <div class="ticket-category-bar" id="categoryBar" aria-label="Seat categories">
        <button class="ticket-category is-active" type="button" data-category="all">
          <span class="ticket-category-dot ticket-category-dot--all"></span>
          <span><strong>All sections</strong><small id="totalAvailability">Checking availability</small></span>
        </button>
        <?php foreach ($categoryPayload as $key => $category): ?>
          <button class="ticket-category" type="button" data-category="<?= htmlspecialchars($key) ?>" style="--category-color:<?= htmlspecialchars($category['color']) ?>">
            <span class="ticket-category-dot"></span>
            <span>
              <strong><?= htmlspecialchars($category['label']) ?></strong>
              <small><span data-availability-for="<?= htmlspecialchars($key) ?>">0</span> available &middot; PHP <?= number_format($category['price']) ?></small>
            </span>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="ticket-map-card">
        <div class="ticket-map-heading">
          <div>
            <p>Interactive venue map</p>
            <h1><?= htmlspecialchars($event['venue']) ?></h1>
            <span>
              <?= htmlspecialchars($venueProfile['subtitle']) ?>
              <?php if (!empty($venueProfile['capacity'])): ?>
                &middot; <?= number_format((int) $venueProfile['capacity']) ?>-seat capacity
              <?php endif; ?>
            </span>
          </div>
          <div class="ticket-map-legend">
            <span><i class="is-available"></i> Available</span>
            <span><i class="is-selected"></i> Selected</span>
            <span><i class="is-unavailable"></i> Unavailable</span>
          </div>
        </div>

        <div class="ticket-map-viewport" id="mapViewport">
          <svg id="seatMap" viewBox="0 0 1000 720" role="img" aria-label="Interactive seating map for <?= htmlspecialchars($event['venue']) ?>">
            <g id="mapCanvas"></g>
          </svg>
          <div class="ticket-map-hint" id="mapHint">Select a section to view seats. Drag, scroll, or pinch to navigate.</div>
          <div class="ticket-map-controls" aria-label="Map controls">
            <button type="button" data-map-action="zoom-in" aria-label="Zoom in">+</button>
            <button type="button" data-map-action="zoom-out" aria-label="Zoom out">-</button>
            <button type="button" data-map-action="reset" aria-label="Reset map">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 3v6h6"/></svg>
            </button>
          </div>
          <div class="ticket-seat-tooltip" id="seatTooltip" role="tooltip" hidden></div>
        </div>
      </div>
    </section>

    <aside class="ticket-sidebar">
      <section class="ticket-event-card" style="--ticket-event-bg:url('<?= htmlspecialchars($resolved['banner']) ?>')">
        <div class="ticket-event-card-shade"></div>
        <div class="ticket-event-card-copy">
          <span><?= htmlspecialchars($resolved['categoryLabel']) ?></span>
          <h2><?= htmlspecialchars($event['title']) ?></h2>
          <p><?= htmlspecialchars($performanceDate->format('D, M j, Y')) ?> &middot; <?= htmlspecialchars($performanceTime) ?></p>
          <small><?= htmlspecialchars($event['venue']) ?></small>
        </div>
      </section>

      <div class="ticket-mode-tabs" role="tablist" aria-label="Seat selection mode">
        <button class="is-active" type="button" data-mode="manual">Choose your seats</button>
        <button type="button" data-mode="best">Best available</button>
      </div>

      <section class="ticket-best-panel" id="bestAvailablePanel" hidden>
        <p class="ticket-panel-kicker">Smart suggestion</p>
        <h2>Find seats together</h2>
        <p>We will prioritize contiguous seats closest to the stage within available sections.</p>
        <div class="ticket-quantity" aria-label="Number of seats">
          <?php for ($quantity = 1; $quantity <= 4; $quantity++): ?>
            <button class="<?= $quantity === 2 ? 'is-active' : '' ?>" type="button" data-best-quantity="<?= $quantity ?>"><?= $quantity ?></button>
          <?php endfor; ?>
        </div>
        <button class="ticket-best-action" id="findBestSeats" type="button">Suggest best seats</button>
        <div class="ticket-suggestion" id="bestSuggestion" hidden>
          <div>
            <small>Suggested seats</small>
            <strong id="bestSuggestionLabel"></strong>
          </div>
          <div class="ticket-suggestion-actions">
            <button type="button" id="acceptSuggestion">Keep seats</button>
            <button type="button" id="tryAnotherSuggestion">Try another</button>
          </div>
        </div>
      </section>

      <section class="ticket-selection-panel">
        <div class="ticket-selection-heading">
          <div>
            <p class="ticket-panel-kicker">Your selection</p>
            <h2>Selected seats</h2>
          </div>
          <span id="selectedCount">0 / 4</span>
        </div>

        <div class="ticket-selection-empty" id="selectionEmpty">
          <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 44V27a7 7 0 0 1 14 0v17"/><path d="M34 44V21a7 7 0 0 1 14 0v23"/><path d="M11 44h42v8H11z"/></svg>
          <strong>No seats selected</strong>
          <p>Choose up to four available seats from the map.</p>
        </div>

        <div class="ticket-selected-list" id="selectedSeats"></div>

        <div class="ticket-policy-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
          <span><strong>Non-transferable tickets</strong>Seats are tied to the purchasing ClicKet account to discourage scalping.</span>
        </div>

        <p class="ticket-status" id="ticketStatus" role="status" aria-live="polite"></p>
        <button class="ticket-continue" id="continueBooking" type="button" disabled>Continue with selected seats</button>
        <a class="ticket-back" href="show.php?event=<?= urlencode($eventKey) ?>">Back to event details</a>
      </section>
    </aside>
  </main>

  <script id="ticketConfig" type="application/json"><?= json_encode($ticketConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
  <script src="js/ticket-topbar.js"></script>
  <script src="js/reservation-timer.js"></script>
  <script src="js/ticket.js?v=<?= filemtime(__DIR__ . '/js/ticket.js') ?>"></script>
</body>
</html>
