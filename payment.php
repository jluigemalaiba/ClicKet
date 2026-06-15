<?php

require_once __DIR__ . '/includes/ticketing.php';
require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/order-history-data.php';
require_once __DIR__ . '/includes/ticket-data.php';
require_once __DIR__ . '/includes/reservation.php';

$status = trim((string) ($_GET['status'] ?? ''));
$lastBooking = $_SESSION['clicket_last_booking'] ?? null;

if (in_array($status, ['processing', 'success'], true)) {
    if (!isLoggedIn() || !is_array($lastBooking)) {
        header('Location: events.php');
        exit;
    }

    if ($status === 'processing') {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <meta http-equiv="refresh" content="2;url=payment.php?status=success">
          <title>Processing Payment | ClicKet</title>
          <link rel="stylesheet" href="css/ticket.css">
        </head>
        <body class="checkout-page processing-page">
          <main class="checkout-success processing-card" aria-live="polite">
            <span class="payment-spinner" aria-hidden="true"></span>
            <p class="ticket-panel-kicker">Secure payment</p>
            <h1>Processing your payment</h1>
            <p>Please keep this page open while we confirm your transaction.</p>
            <a class="processing-fallback" href="payment.php?status=success">Continue</a>
          </main>
        </body>
        </html>
        <?php
        exit;
    }

    $paymentMethodLabels = [
        'visa' => 'Visa',
        'mastercard' => 'Mastercard',
        'jcb' => 'JCB',
        'gcash' => 'GCash',
        'maya' => 'Maya',
        'bpi' => 'BPI Online',
        'bdo' => 'BDO Online',
        'qrph' => 'QR Ph',
    ];
    $receiptSeats = is_array($lastBooking['seats'] ?? null) ? $lastBooking['seats'] : [];
    $receiptSubtotal = (int) ($lastBooking['subtotal'] ?? array_sum(array_map(fn($seat) => (int) ($seat['price'] ?? 0), $receiptSeats)));
    $receiptServiceFee = (int) ($lastBooking['service_fee'] ?? max(0, (int) ($lastBooking['total'] ?? 0) - $receiptSubtotal));
    $receiptPaymentMethod = $lastBooking['payment_method_label']
        ?? ($paymentMethodLabels[$lastBooking['payment_method'] ?? ''] ?? ucfirst((string) ($lastBooking['payment_method'] ?? 'Payment')));
    $receiptTimezone = new DateTimeZone('Asia/Manila');
    $receiptTransactionDate = !empty($lastBooking['booked_at'])
        ? (new DateTimeImmutable($lastBooking['booked_at']))->setTimezone($receiptTimezone)->format('F j, Y \a\t g:i A')
        : (new DateTimeImmutable('now', $receiptTimezone))->format('F j, Y \a\t g:i A');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Booking Confirmed | ClicKet</title>
      <link rel="stylesheet" href="css/ticket.css">
    </head>
    <body class="checkout-page">
      <main class="receipt-shell">
        <section class="receipt-card" id="receiptSection" data-reference="<?= htmlspecialchars($lastBooking['reference'] ?? '') ?>" aria-labelledby="receipt-title">
          <h1 class="receipt-visually-hidden" id="receipt-title">ClicKet transaction receipt</h1>
          <header class="receipt-company">
            <div class="receipt-company-brand">
              <a href="index.php" aria-label="ClicKet home">
                <img class="receipt-company-icon" src="assets/Icon_Logo.png" alt="">
                <img class="receipt-company-name" src="assets/Name_Logo.png" alt="ClicKet">
              </a>
              <p>Web-based ticketing for concerts, theater, and sports events.</p>
            </div>
            <address class="receipt-company-details">
              <strong>ClicKet Ticketing Services</strong>
              <span>Sto. Tomas City, Batangas, Philippines</span>
              <span><a href="contact.php">Customer Support</a> &middot; <a href="help.php">Help Center</a></span>
              <span>Electronic receipt &middot; Asia/Manila (PHT)</span>
            </address>
          </header>

          <div class="receipt-body">
            <section class="receipt-section" aria-labelledby="receipt-booking-heading">
              <div class="receipt-section-heading">
                <div>
                  <p class="ticket-panel-kicker">Booking summary</p>
                  <h2 id="receipt-booking-heading"><?= htmlspecialchars($lastBooking['event_title'] ?? 'ClicKet Event') ?></h2>
                </div>
                <span><?= count($receiptSeats) ?> <?= count($receiptSeats) === 1 ? 'ticket' : 'tickets' ?></span>
              </div>

              <dl class="receipt-detail-grid">
                <div><dt>Order ID</dt><dd><?= htmlspecialchars($lastBooking['order_id'] ?? '') ?></dd></div>
                <div><dt>Payment date &amp; time</dt><dd><?= htmlspecialchars($receiptTransactionDate) ?></dd></div>
                <div><dt>Event date &amp; time</dt><dd><?= htmlspecialchars(trim(($lastBooking['event_date'] ?? 'See ticket details') . ' ' . ($lastBooking['event_time'] ?? ''))) ?></dd></div>
                <div><dt>Venue</dt><dd><?= htmlspecialchars($lastBooking['venue'] ?? 'See ticket details') ?></dd></div>
                <div><dt>Payment method</dt><dd><?= htmlspecialchars($receiptPaymentMethod) ?><?php if (!empty($lastBooking['payment_account'])): ?><small><?= htmlspecialchars($lastBooking['payment_account']) ?></small><?php endif; ?></dd></div>
                <div><dt>Reference number</dt><dd class="receipt-mono"><?= htmlspecialchars($lastBooking['payment_reference'] ?? $lastBooking['reference'] ?? '') ?></dd></div>
              </dl>
            </section>

            <section class="receipt-section receipt-customer-section" aria-labelledby="receipt-customer-heading">
              <div class="receipt-section-heading receipt-section-heading--compact">
                <div>
                  <p class="ticket-panel-kicker">Billing summary</p>
                  <h2 id="receipt-customer-heading">Customer details</h2>
                </div>
              </div>
              <dl class="receipt-customer-grid">
                <div><dt>Customer name</dt><dd><?= htmlspecialchars($lastBooking['buyer_name'] ?? 'ClicKet account holder') ?></dd></div>
                <div><dt>Account email</dt><dd><?= htmlspecialchars($lastBooking['buyer_email'] ?? '') ?></dd></div>
                <div><dt>Payment information</dt><dd><?= htmlspecialchars($receiptPaymentMethod) ?><?php if (!empty($lastBooking['payment_account'])): ?> &middot; <?= htmlspecialchars($lastBooking['payment_account']) ?><?php endif; ?></dd></div>
              </dl>
            </section>

            <section class="receipt-section receipt-seat-section" aria-labelledby="receipt-seat-heading">
              <div class="receipt-section-heading receipt-section-heading--compact">
                <div>
                  <p class="ticket-panel-kicker">Selected seats</p>
                  <h2 id="receipt-seat-heading"><?= count($receiptSeats) ?> <?= count($receiptSeats) === 1 ? 'Seat' : 'Seats' ?></h2>
                </div>
              </div>
              <div class="receipt-seat-list">
                <?php foreach ($receiptSeats as $seat): ?>
                  <article class="receipt-seat-row">
                    <span class="receipt-seat-number"><?= htmlspecialchars($seat['number'] ?? '') ?></span>
                    <div>
                      <strong><?= htmlspecialchars($seat['section'] ?? '') ?> &middot; Row <?= htmlspecialchars($seat['row'] ?? '') ?></strong>
                      <span><?= htmlspecialchars($seat['category'] ?? 'Ticket') ?></span>
                      <?php if (!empty($seat['ticket_code'])): ?><small>Ticket no. <?= htmlspecialchars($seat['ticket_code']) ?></small><?php endif; ?>
                    </div>
                    <b>PHP <?= number_format((int) ($seat['price'] ?? 0)) ?></b>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>

            <section class="receipt-payment" aria-label="Payment breakdown">
              <div>
                <p class="ticket-panel-kicker">Payment breakdown</p>
                <p class="receipt-payment-note">This receipt confirms your completed transaction.</p>
              </div>
              <div class="receipt-totals">
                <div><span>Tickets (<?= count($receiptSeats) ?>)</span><strong>PHP <?= number_format($receiptSubtotal) ?></strong></div>
                <div><span>Service fee</span><strong>PHP <?= number_format($receiptServiceFee) ?></strong></div>
                <div class="is-total"><span>Total paid</span><strong>PHP <?= number_format((int) ($lastBooking['total'] ?? 0)) ?></strong></div>
              </div>
            </section>
          </div>

          <div class="receipt-footer">
            <span>This computer-generated receipt is valid without a signature.</span>
            <span>Thank you for booking with ClicKet.</span>
          </div>
        </section>

        <div class="receipt-actions" aria-label="Receipt actions">
          <button class="receipt-action" id="downloadReceipt" type="button">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4v11m0 0 4-4m-4 4-4-4M5 20h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Download Receipt</span>
          </button>
          <button class="receipt-action" id="printReceipt" type="button">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 9V4h10v5M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M7 14h10v6H7z" stroke="currentColor" stroke-width="1.8"/></svg>
            Print Receipt
          </button>
          <a class="receipt-action" href="index.php?panel=tickets">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5V9a3 3 0 0 0 0 6v1.5a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5V15a3 3 0 0 0 0-6V7.5Z" stroke="currentColor" stroke-width="1.8"/><path d="M13.5 8.5h3M13.5 12h3M13.5 15.5h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            View My Tickets
          </a>
          <a class="receipt-action" href="index.php">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 11 8-7 8 7v9h-6v-6h-4v6H4v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
            Back to Home
          </a>
        </div>
      </main>
      <script src="js/vendor/html2pdf.bundle.min.js" defer></script>
      <script src="js/payment.js" defer></script>
    </body>
    </html>
    <?php
    exit;
}

$eventKey = trim((string) ($_GET['event'] ?? $_POST['event'] ?? ''));
$performanceIndex = max(0, min(3, (int) ($_GET['performance'] ?? $_POST['performance'] ?? 0)));
$resolved = clicketResolveEvent($eventKey);
$selection = $_SESSION['clicket_ticket_selection'] ?? null;

if (is_array($selection) && !clicketReservationIsActive($eventKey, $performanceIndex)) {
    clicketRedirectExpiredReservation($eventKey, $performanceIndex);
}

if (!$resolved || !isLoggedIn() || !is_array($selection) || ($selection['event'] ?? '') !== $eventKey || empty($selection['seats'])) {
    header('Location: checkout.php?event=' . rawurlencode($eventKey) . '&performance=' . $performanceIndex);
    exit;
}

$event = $resolved['event'];
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
$paymentError = '';
$selectedPaymentMethod = trim((string) ($_POST['payment_method'] ?? ''));
$qrReference = 'QR-' . strtoupper(substr(hash('sha256', session_id() . $eventKey . $total), 0, 10));
$cardNumber = '';
$walletMobile = '';
$paymentAccount = '';
$proofName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $selectedPaymentMethod;
    $allowedMethods = ['visa', 'mastercard', 'jcb', 'gcash', 'maya', 'bpi', 'bdo', 'qrph'];
    $paymentErrors = [];

    if (!in_array($paymentMethod, $allowedMethods, true)) {
        $paymentErrors[] = 'Select a valid payment method.';
    }
    if (($_POST['terms_accepted'] ?? '') !== '1') {
        $paymentErrors[] = 'Agree to the Terms and Conditions before confirming payment.';
    }

    $cardMethods = ['visa', 'mastercard', 'jcb'];
    if (in_array($paymentMethod, $cardMethods, true)) {
        $cardNumber = preg_replace('/\D/', '', (string) ($_POST['card_number'] ?? ''));
        $cardholderName = trim((string) ($_POST['cardholder_name'] ?? ''));
        $cardExpiry = trim((string) ($_POST['card_expiry'] ?? ''));
        $cardCvv = preg_replace('/\D/', '', (string) ($_POST['card_cvv'] ?? ''));

        if (strlen($cardNumber) < 16 || strlen($cardNumber) > 19) {
            $paymentErrors[] = 'Enter a card number containing 16 to 19 digits.';
        }
        if ($cardholderName === '') {
            $paymentErrors[] = 'Enter the cardholder name.';
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $cardExpiry, $expiryParts)) {
            $paymentErrors[] = 'Enter the expiration date as MM/YY.';
        } else {
            $expiryMonth = (int) substr($cardExpiry, 0, 2);
            $expiryYear = 2000 + (int) $expiryParts[2];
            $expiryDate = DateTimeImmutable::createFromFormat('!Y-n-j', $expiryYear . '-' . $expiryMonth . '-1');
            if (!$expiryDate || $expiryDate->modify('last day of this month 23:59:59') < new DateTimeImmutable()) {
                $paymentErrors[] = 'Enter a future card expiration date.';
            }
        }
        if (strlen($cardCvv) < 3 || strlen($cardCvv) > 4) {
            $paymentErrors[] = 'Enter a 3 or 4 digit CVV.';
        }
    }

    if (in_array($paymentMethod, ['gcash', 'maya'], true)) {
        $walletMobile = preg_replace('/\D/', '', (string) ($_POST['wallet_mobile'] ?? ''));
        $walletName = trim((string) ($_POST['wallet_name'] ?? ''));
        if ($walletMobile === '' || $walletName === '') $paymentErrors[] = 'Complete the wallet account details.';
    }

    if ($paymentMethod === 'qrph' && ($_POST['qr_confirmed'] ?? '') !== '1') {
        $paymentErrors[] = 'Confirm that you completed the QR Ph payment.';
    }

    if (in_array($paymentMethod, ['gcash', 'maya', 'qrph'], true)) {
        $proof = $_FILES['payment_proof'] ?? null;
        if (!is_array($proof) || ($proof['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $paymentErrors[] = 'Choose a proof of payment image.';
        } else {
            $proofName = basename((string) ($proof['name'] ?? 'payment-proof'));
        }
    }

    if ($paymentErrors) {
        $paymentError = implode(' ', $paymentErrors);
    } else {
        if (in_array($paymentMethod, $cardMethods, true)) {
            $paymentAccount = 'card ending in ' . substr(preg_replace('/\D/', '', $cardNumber), -4);
        } elseif (in_array($paymentMethod, ['gcash', 'maya'], true)) {
            $paymentAccount = 'Mobile ending in ' . substr($walletMobile, -4);
        } else {
            $paymentAccount = $qrReference;
        }

        $currentBuyer = currentUser();
        $orderSeed = session_id() . $eventKey . microtime(true);
        $orderId = 'CKO-' . strtoupper(substr(hash('sha256', $orderSeed), 0, 10));
        $paymentReference = 'PAY-' . strtoupper(substr(hash('sha256', $orderSeed . $paymentMethod), 0, 12));
        $seatRows = array_map(function (array $seat, int $index) use ($orderId): array {
            $seat['ticket_code'] = 'TKT-' . strtoupper(substr(hash('sha256', $orderId . '-' . $index), 0, 12));
            return $seat;
        }, $seatRows, array_keys($seatRows));
        $booking = [
            'order_id' => $orderId,
            'reference' => $paymentReference,
            'payment_reference' => $paymentReference,
            'user_id' => (string) ($currentBuyer['id'] ?? ''),
            'buyer_name' => (string) ($currentBuyer['name'] ?? ''),
            'buyer_email' => (string) ($currentBuyer['email'] ?? ''),
            'event' => $eventKey,
            'event_title' => $event['title'],
            'event_poster' => $resolved['poster'],
            'event_banner' => $resolved['banner'],
            'event_date' => trim((string) ($selection['performance_date'] ?? '')) ?: $resolved['date']->format('l, F j, Y'),
            'event_time' => trim((string) ($selection['performance_time'] ?? '')) ?: $resolved['time'],
            'venue' => $event['venue'],
            'seats' => $seatRows,
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'total' => $total,
            'payment_method' => $paymentMethod,
            'payment_method_label' => [
                'visa' => 'Visa', 'mastercard' => 'Mastercard', 'jcb' => 'JCB',
                'gcash' => 'GCash', 'maya' => 'Maya', 'bpi' => 'BPI Online',
                'bdo' => 'BDO Online', 'qrph' => 'QR Ph',
            ][$paymentMethod],
            'payment_account' => $paymentAccount,
            'proof_of_payment' => $proofName,
            'non_transferable' => true,
            'payment_status' => 'Paid',
            'order_status' => 'Confirmed',
            'booked_at' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('c'),
        ];

        if (!clicketReservationIsActive($eventKey, $performanceIndex)) {
            clicketRedirectExpiredReservation($eventKey, $performanceIndex);
        }
        $booking = \clicketHydrateOrderTickets($booking);

        if (!clicketSaveOrder($booking)) {
            $paymentError = 'The order could not be completed because one or more seats are no longer available. Please return to seat selection or contact support.';
        } else {
            $_SESSION['clicket_bookings'][] = $booking;
            $_SESSION['clicket_last_booking'] = $booking;
            clicketClearReservation();

            header('Location: payment.php?status=processing');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment | <?= htmlspecialchars($event['title']) ?> | ClicKet</title>
  <link rel="stylesheet" href="css/ticket.css">
</head>
<body class="checkout-page">
  <header class="ticket-topbar">
    <a class="ticket-brand" href="index.php">
      <img src="assets/Icon_Logo.png" alt="">
      <img src="assets/Name_Logo.png" alt="ClicKet">
    </a>
    <div class="ticket-progress" aria-label="Checkout progress">
      <div class="ticket-session-clock" aria-label="Time remaining to complete payment">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        <span data-reservation-timer data-expires-at="<?= (int) (clicketReservation()['expires_at'] ?? 0) * 1000 ?>" data-expiry-url="<?= htmlspecialchars(clicketReservationExpiryUrl($eventKey, $performanceIndex)) ?>">--:--</span>
      </div>
      <ol>
        <li><span>1</span><b>Seats</b></li>
        <li><span>2</span><b>Review</b></li>
        <li class="is-active"><span>3</span><b>Payment</b></li>
        <li><span>4</span><b>Done</b></li>
      </ol>
    </div>
  </header>

  <main class="checkout-shell payment-shell">
    <div class="checkout-heading">
      <div><p>Secure checkout</p><h1>Choose how to pay</h1></div>
      <a href="checkout.php?event=<?= urlencode($eventKey) ?>&amp;performance=<?= $performanceIndex ?>">Back to review</a>
    </div>

    <form id="paymentForm" class="checkout-grid payment-grid" method="post" action="payment.php" enctype="multipart/form-data" novalidate>
      <section class="checkout-card payment-methods" aria-labelledby="payment-method-title">
        <div class="payment-card-heading">
          <div>
            <p class="ticket-panel-kicker">Secure payment</p>
            <h2 id="payment-method-title">Payment Method</h2>
          </div>
          <span class="payment-secure-note">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
            Encrypted checkout
          </span>
        </div>

        <?php
        $methodGroups = [
            'Cards' => [
                ['visa', 'Visa', 'Credit or debit card', 'assets/payment/visa.svg'],
                ['mastercard', 'Mastercard', 'Credit or debit card', 'assets/payment/mastercard.svg'],
                ['jcb', 'JCB', 'Credit or debit card', 'assets/payment/jcb.svg'],
            ],
            'Digital Wallets' => [
                ['gcash', 'GCash', 'Pay using your GCash wallet', 'assets/payment/gcash.svg'],
                ['maya', 'Maya', 'Pay using your Maya wallet', 'assets/payment/maya.svg'],
            ],
            
            'QR Payments' => [
                ['qrph', 'QR Ph', 'Scan with a participating bank or wallet', 'assets/payment/qrph.svg'],
            ],
        ];
        ?>

        <?php foreach ($methodGroups as $groupName => $methods): ?>
          <fieldset class="payment-group">
            <legend><?= htmlspecialchars($groupName) ?></legend>
            <div class="payment-options">
              <?php foreach ($methods as [$value, $name, $description, $logo]): ?>
                <label class="payment-option payment-option--<?= htmlspecialchars($value) ?>">
                  <input type="radio" name="payment_method" value="<?= htmlspecialchars($value) ?>" <?= $selectedPaymentMethod === $value ? 'checked' : '' ?>>
                  <span class="payment-radio" aria-hidden="true"></span>
                  <span class="payment-logo"><img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($name) ?> logo"></span>
                  <span class="payment-option-copy">
                    <strong><?= htmlspecialchars($name) ?></strong>
                    <small><?= htmlspecialchars($description) ?></small>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
          </fieldset>
        <?php endforeach; ?>
        <p class="payment-validation" id="paymentMethodError" role="alert"></p>

        <section class="payment-details" id="paymentDetails" aria-live="polite">
          <div class="payment-detail-panel" data-payment-panel="card" hidden>
            <div class="payment-detail-heading">
              <p class="ticket-panel-kicker">Card details</p>
              <h3>Enter your card information</h3>
            </div>
            <div class="payment-field-grid">
              <label class="payment-field payment-field--full">
                <span>Card number</span>
                <input type="text" name="card_number" inputmode="numeric" autocomplete="cc-number" maxlength="23" placeholder="1234 5678 9012 3456" aria-describedby="cardNumberHint" value="<?= htmlspecialchars((string) ($_POST['card_number'] ?? '')) ?>" data-payment-required="card">
                <small id="cardNumberHint">Enter 16 to 19 digits. Spaces are added automatically.</small>
              </label>
              <label class="payment-field payment-field--full">
                <span>Cardholder name</span>
                <input type="text" name="cardholder_name" autocomplete="cc-name" placeholder="Name on card" value="<?= htmlspecialchars((string) ($_POST['cardholder_name'] ?? '')) ?>" data-payment-required="card">
              </label>
              <label class="payment-field">
                <span>Expiration date</span>
                <input type="text" name="card_expiry" inputmode="numeric" autocomplete="cc-exp" maxlength="5" placeholder="MM/YY" value="<?= htmlspecialchars((string) ($_POST['card_expiry'] ?? '')) ?>" data-payment-required="card">
              </label>
              <label class="payment-field">
                <span>CVV</span>
                <input type="password" name="card_cvv" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="123" aria-describedby="cardCvvHint" data-payment-required="card">
                <small id="cardCvvHint">3 or 4 digits found on the card.</small>
              </label>
            </div>
          </div>

          <div class="payment-detail-panel" data-payment-panel="wallet" hidden>
            <div class="payment-detail-heading">
              <p class="ticket-panel-kicker">E-wallet details</p>
              <h3>Confirm your wallet account</h3>
            </div>
            <div class="payment-instruction">After confirmation, a secure payment request will be sent to your selected wallet. Open the app and approve the PHP <?= number_format($total) ?> charge.</div>
            <div class="payment-field-grid">
              <label class="payment-field payment-field--full">
                <span>Mobile number</span>
                <input type="text" name="wallet_mobile" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" maxlength="12" placeholder="09XXXXXXXXX" value="<?= htmlspecialchars((string) ($_POST['wallet_mobile'] ?? '')) ?>" data-payment-required="wallet">
              </label>
              <label class="payment-field payment-field--full">
                <span>Account name</span>
                <input type="text" name="wallet_name" autocomplete="name" placeholder="Name registered to the wallet" value="<?= htmlspecialchars((string) ($_POST['wallet_name'] ?? '')) ?>" data-payment-required="wallet">
              </label>
              <label class="payment-field payment-field--full payment-file-field">
                <span>Proof of payment</span>
                <input type="file" name="payment_proof" accept="image/jpeg,image/png,.jpg,.jpeg,.png" data-payment-required="wallet">
                <small>Mock upload only. The selected image is not permanently stored.</small>
              </label>
            </div>
          </div>

          <div class="payment-detail-panel" data-payment-panel="qr" hidden>
            <div class="payment-detail-heading">
              <p class="ticket-panel-kicker">QR Ph payment</p>
              <h3>Scan and confirm payment</h3>
            </div>
            <div class="qr-payment-layout">
              <div class="qr-placeholder" role="img" aria-label="QR Ph payment code placeholder"><span>QR Ph</span></div>
              <div class="qr-payment-copy">
                <span>Reference number</span><strong><?= htmlspecialchars($qrReference) ?></strong>
                <span>Amount due</span><strong>PHP <?= number_format($total) ?></strong>
                <p>Scan this code using a participating banking or wallet app. Verify the amount and reference number before completing payment.</p>
              </div>
            </div>
            <label class="qr-confirmation">
              <input type="checkbox" name="qr_confirmed" value="1" <?= ($_POST['qr_confirmed'] ?? '') === '1' ? 'checked' : '' ?> data-payment-required="qr">
              <span>I have completed the QR Ph payment using the reference shown above.</span>
            </label>
            <label class="payment-field payment-file-field qr-proof-field">
              <span>Proof of payment</span>
              <input type="file" name="payment_proof" accept="image/jpeg,image/png,.jpg,.jpeg,.png" data-payment-required="qr">
              <small>upload only. The selected image is not permanently stored.</small>
            </label>
          </div>
          <p class="payment-validation" id="paymentDetailsError" role="alert"></p>
        </section>
      </section>

      <aside class="checkout-card checkout-summary payment-summary">
        <p class="ticket-panel-kicker">Order summary</p>
        <h2><?= count($seatRows) ?> <?= count($seatRows) === 1 ? 'ticket' : 'tickets' ?></h2>
        <div class="payment-event-name"><?= htmlspecialchars($event['title']) ?></div>
        <div class="payment-ticket-list">
          <?php foreach ($seatRows as $seat): ?>
            <article class="payment-ticket-detail">
              <div>
                <strong><?= htmlspecialchars($seat['section']) ?>, Row <?= htmlspecialchars($seat['row']) ?>, Seat <?= htmlspecialchars($seat['number']) ?></strong>
                <span><?= htmlspecialchars($seat['category']) ?> &middot; Non-transferable</span>
              </div>
              <b>PHP <?= number_format($seat['price']) ?></b>
            </article>
          <?php endforeach; ?>
        </div>
        <div class="checkout-total-row"><span>Tickets</span><strong>PHP <?= number_format($subtotal) ?></strong></div>
        <div class="checkout-total-row"><span>Service fee</span><strong>PHP <?= number_format($serviceFee) ?></strong></div>
        <div class="checkout-total-row is-total"><span>Total amount</span><strong>PHP <?= number_format($total) ?></strong></div>

        <input type="hidden" name="event" value="<?= htmlspecialchars($eventKey) ?>">
        <input type="hidden" name="performance" value="<?= $performanceIndex ?>">
        <label class="payment-terms">
          <input id="termsAccepted" type="checkbox" name="terms_accepted" value="1" <?= ($_POST['terms_accepted'] ?? '') === '1' ? 'checked' : '' ?>>
          <span>I have read and agree to the <a href="terms.php" target="_blank">Terms and Conditions</a>, <a href="privacy.php" target="_blank">Privacy Policy</a>, and <a href="terms.php#purchases" target="_blank">Ticket Purchase Guidelines</a>.</span>
        </label>
        <p class="payment-validation" id="termsError" role="alert"></p>
        <?php if ($paymentError !== ''): ?>
          <p class="payment-validation is-visible" role="alert"><?= htmlspecialchars($paymentError) ?></p>
        <?php endif; ?>
        <button class="checkout-action payment-submit" id="paymentSubmit" type="submit" disabled>Confirm Payment</button>
        <p class="payment-footnote">Your payment details are handled securely by the selected payment provider.</p>
      </aside>
    </form>
  </main>
  <script src="js/ticket-topbar.js" defer></script>
  <script src="js/reservation-timer.js" defer></script>
  <script src="js/payment.js" defer></script>
</body>
</html>
