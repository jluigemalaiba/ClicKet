<?php

require_once __DIR__ . '/includes/ticketing.php';
require_once __DIR__ . '/includes/log.php';

$eventKey = trim((string) ($_GET['event'] ?? $_POST['event'] ?? ''));
$performanceIndex = max(0, min(3, (int) ($_GET['performance'] ?? $_POST['performance'] ?? 0)));
$resolved = clicketResolveEvent($eventKey);
$selection = $_SESSION['clicket_ticket_selection'] ?? null;

if (!$resolved || !is_array($selection) || ($selection['event'] ?? '') !== $eventKey || empty($selection['seats'])) {
    header('Location: ticket.php?event=' . rawurlencode($eventKey));
    exit;
}

$event = $resolved['event'];
$categories = clicketTicketCategories();
$basePrice = (int) preg_replace('/\D/', '', (string) ($event['price'] ?? '2500'));
if ($basePrice < 500) {
    $basePrice = 2500;
}
$priceFactors = ['VIP' => 1, 'Platinum' => .82, 'Gold' => .64, 'Silver' => .46, 'Bronze' => .3, 'General Admission' => .24];
$seatRows = [];
$subtotal = 0;

foreach ($selection['seats'] as $seat) {
    $factor = $priceFactors[$seat['category']] ?? .5;
    $price = (int) (round(($basePrice * $factor) / 50) * 50);
    $subtotal += $price;
    $seatRows[] = $seat + ['price' => $price];
}

$serviceFee = count($seatRows) * 75;
$total = $subtotal + $serviceFee;
$confirmed = false;
$bookingReference = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $bookingReference = 'CK-' . strtoupper(substr(hash('sha256', session_id() . $eventKey . microtime(true)), 0, 10));
    $_SESSION['clicket_bookings'][] = [
        'reference' => $bookingReference,
        'event' => $eventKey,
        'event_title' => $event['title'],
        'seats' => $seatRows,
        'total' => $total,
        'non_transferable' => true,
        'booked_at' => date('c'),
    ];
    unset($_SESSION['clicket_ticket_selection']);
    $confirmed = true;
}

$performanceDateLabel = trim((string) ($selection['performance_date'] ?? ''));
$performanceTimeLabel = trim((string) ($selection['performance_time'] ?? ''));
if ($performanceDateLabel === '') {
    $performanceDateLabel = $resolved['date']->format('l, F j, Y');
}
if ($performanceTimeLabel === '') {
    $performanceTimeLabel = $resolved['time'];
}
$returnUrl = 'checkout.php?event=' . rawurlencode($eventKey) . '&performance=' . $performanceIndex;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout | <?= htmlspecialchars($event['title']) ?> | ClicKet</title>
  <link rel="stylesheet" href="css/ticket.css">
</head>
<body class="checkout-page">
  <header class="ticket-topbar">
    <a class="ticket-brand" href="index.php">
      <img src="assets/Icon_Logo.png" alt="">
      <img src="assets/Name_Logo.png" alt="ClicKet">
    </a>
    <div class="ticket-progress">
      <ol>
        <li><span>1</span><b>Seats</b></li>
        <li class="is-active"><span>2</span><b>Review</b></li>
        <li><span>3</span><b>Payment</b></li>
        <li><span>4</span><b>Done</b></li>
      </ol>
    </div>
  </header>

  <?php if ($confirmed): ?>
    <main class="checkout-success">
      <span class="checkout-success-icon">✓</span>
      <p class="ticket-panel-kicker">Booking confirmed</p>
      <h1>Your seats are secured</h1>
      <p><?= htmlspecialchars($event['title']) ?> tickets are now linked to <?= htmlspecialchars(currentUser()['email'] ?? 'your ClicKet account') ?> and cannot be transferred.</p>
      <span class="checkout-reference"><?= htmlspecialchars($bookingReference) ?></span>
      <a class="checkout-action" href="auth.php?mode=account">View My Tickets</a>
    </main>
  <?php else: ?>
    <main class="checkout-shell">
      <div class="checkout-heading">
        <div><p>Final review</p><h1>Review your booking</h1></div>
        <a href="ticket.php?event=<?= urlencode($eventKey) ?>&amp;performance=<?= $performanceIndex ?>">Change seats</a>
      </div>

      <div class="checkout-grid">
        <section class="checkout-card">
          <div class="checkout-event">
            <img src="<?= htmlspecialchars($resolved['poster']) ?>" alt="<?= htmlspecialchars($event['title']) ?> poster">
            <div>
              <small><?= htmlspecialchars($resolved['categoryLabel']) ?></small>
              <strong><?= htmlspecialchars($event['title']) ?></strong>
              <span><?= htmlspecialchars($performanceDateLabel) ?> at <?= htmlspecialchars($performanceTimeLabel) ?></span>
              <span><?= htmlspecialchars($event['venue']) ?></span>
            </div>
          </div>

          <div class="checkout-seat-list">
            <?php foreach ($seatRows as $seat): ?>
              <article class="checkout-seat">
                <div>
                  <strong><?= htmlspecialchars($seat['section']) ?>, Row <?= htmlspecialchars($seat['row']) ?>, Seat <?= htmlspecialchars($seat['number']) ?></strong>
                  <span><?= htmlspecialchars($seat['category']) ?> &middot; Non-transferable</span>
                </div>
                <b>PHP <?= number_format($seat['price']) ?></b>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

        <aside class="checkout-card checkout-summary">
          <p class="ticket-panel-kicker">Order summary</p>
          <h2><?= count($seatRows) ?> <?= count($seatRows) === 1 ? 'ticket' : 'tickets' ?></h2>
          <div class="checkout-total-row"><span>Tickets</span><strong>PHP <?= number_format($subtotal) ?></strong></div>
          <div class="checkout-total-row"><span>Service fee</span><strong>PHP <?= number_format($serviceFee) ?></strong></div>
          <div class="checkout-total-row is-total"><span>Total</span><strong>PHP <?= number_format($total) ?></strong></div>
          <div class="checkout-policy">By continuing, you confirm that these tickets are for your account and are non-transferable. Seat availability remains subject to final confirmation.</div>

          <?php if (isLoggedIn()): ?>
            <form method="post">
              <input type="hidden" name="event" value="<?= htmlspecialchars($eventKey) ?>">
              <input type="hidden" name="performance" value="<?= $performanceIndex ?>">
              <button class="checkout-action" type="submit">Confirm booking</button>
            </form>
          <?php else: ?>
            <a class="checkout-action checkout-action--dark" href="auth.php?mode=login&amp;return=<?= urlencode($returnUrl) ?>">Sign in to complete purchase</a>
          <?php endif; ?>
        </aside>
      </div>
    </main>
  <?php endif; ?>
</body>
</html>
