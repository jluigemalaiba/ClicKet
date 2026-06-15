<?php

$order = $voucherRecord['order'];
$ticket = $voucherRecord['ticket'];
$orderSeats = is_array($order['seats'] ?? null) ? $order['seats'] : [];
$ticketCount = max(1, count($orderSeats));
$allocatedFee = (int) round(((int) ($order['service_fee'] ?? 0)) / $ticketCount);
$ticketPrice = (int) ($ticket['price'] ?? 0);
$voucherTotal = $ticketPrice + $allocatedFee;
$paymentMethod = (string) ($order['payment_method_label'] ?? $order['payment_method'] ?? 'Payment');
$paymentAccount = trim((string) ($order['payment_account'] ?? ''));
$voucherNumber = (string) ($ticket['voucher_id'] ?? $order['voucher']['voucher_id'] ?? '');
$ticketStatus = (string) ($ticket['status'] ?? 'Valid');
?>
<article class="voucher" id="voucherDocument" data-ticket-id="<?= htmlspecialchars((string) ($ticket['ticket_id'] ?? 'clicket-ticket')) ?>">
  <header class="voucher__header">
    <div class="voucher__company">
      <div class="voucher__brand">
        <img src="assets/Icon_Logo.png" alt="">
        <img src="assets/Name_Logo.png" alt="ClicKet">
      </div>
      <p><strong>ClicKet Ticketing Services</strong></p>
      <p>Sto. Tomas City, Batangas, Philippines</p>
      <p>Online ticketing for concerts, theater, and sports events</p>
      <p>Customer Support: clicket.local/contact.php</p>
    </div>
    <div class="voucher__title">
      <span><?= htmlspecialchars(strtoupper($ticketStatus)) ?> ADMISSION DOCUMENT</span>
      <h1>CLAIM TICKET VOUCHER</h1>
      <p>VOUCHER NUMBER <strong><?= htmlspecialchars($voucherNumber) ?></strong></p>
    </div>
  </header>

  <div class="voucher__columns">
    <section class="voucher__block">
      <h2>ACCOUNT DETAILS</h2>
      <dl class="voucher__data-list">
        <div><dt>ACCOUNT ID</dt><dd><?= htmlspecialchars((string) ($order['user_id'] ?? '')) ?></dd></div>
        <div><dt>ACCOUNT NAME</dt><dd><?= htmlspecialchars((string) ($order['buyer_name'] ?? 'ClicKet account holder')) ?></dd></div>
        <div><dt>E-MAIL ADDRESS</dt><dd><?= htmlspecialchars((string) ($order['buyer_email'] ?? '')) ?></dd></div>
        <div><dt>ORDER ID</dt><dd><?= htmlspecialchars((string) ($order['order_id'] ?? '')) ?></dd></div>
        <div><dt>PAYMENT REFERENCE</dt><dd><?= htmlspecialchars((string) ($order['payment_reference'] ?? $order['reference'] ?? '')) ?></dd></div>
        <div><dt>PAYMENT METHOD</dt><dd><?= htmlspecialchars($paymentMethod) ?></dd></div>
        <?php if ($paymentAccount !== ''): ?><div><dt>PAYMENT ACCOUNT</dt><dd><?= htmlspecialchars($paymentAccount) ?></dd></div><?php endif; ?>
      </dl>
    </section>

    <section class="voucher__block">
      <h2>EVENT DETAILS</h2>
      <dl class="voucher__data-list">
        <div><dt>EVENT TITLE</dt><dd><?= htmlspecialchars((string) ($order['event_title'] ?? 'ClicKet Event')) ?></dd></div>
        <div><dt>DATE / TIME</dt><dd><?= htmlspecialchars(trim((string) ($order['event_date'] ?? '') . ' / ' . (string) ($order['event_time'] ?? ''))) ?></dd></div>
        <div><dt>VENUE</dt><dd><?= htmlspecialchars((string) ($order['venue'] ?? '')) ?></dd></div>
        <div><dt>TRANSACTED</dt><dd><?= htmlspecialchars(clicketOrderDate((string) ($order['booked_at'] ?? ''), 'd-M-Y h:i A')) ?></dd></div>
        <div><dt>PAYMENT TYPE</dt><dd><?= htmlspecialchars($paymentMethod) ?></dd></div>
        <div><dt>ORDER TOTAL</dt><dd>PHP <?= number_format((int) ($order['total'] ?? 0)) ?></dd></div>
      </dl>
    </section>
  </div>

  <section class="voucher__block voucher__transaction">
    <h2>TRANSACTION DETAILS</h2>
    <table>
      <thead><tr><th>QUANTITY</th><th>PARTICULARS</th><th>PRICE</th><th>AMOUNT</th></tr></thead>
      <tbody>
        <tr><td>1</td><td><?= htmlspecialchars((string) ($ticket['category'] ?? 'Admission')) ?> ticket</td><td>PHP <?= number_format($ticketPrice) ?></td><td>PHP <?= number_format($ticketPrice) ?></td></tr>
        <tr><td>1</td><td>Online service fee</td><td>PHP <?= number_format($allocatedFee) ?></td><td>PHP <?= number_format($allocatedFee) ?></td></tr>
        <tr class="voucher__total-row"><td colspan="3">VOUCHER TOTAL</td><td>PHP <?= number_format($voucherTotal) ?></td></tr>
      </tbody>
    </table>
  </section>

  <section class="voucher__block voucher__ticket-details">
    <h2>TICKET DETAILS</h2>
    <table>
      <thead><tr><th>PRICE CATEGORY</th><th>TICKET ID</th><th>TICKET PRICE</th><th>SECTION</th><th>ROW</th><th>SEAT</th></tr></thead>
      <tbody><tr>
        <td><?= htmlspecialchars((string) ($ticket['category'] ?? 'Admission')) ?></td>
        <td><?= htmlspecialchars((string) ($ticket['ticket_id'] ?? '')) ?></td>
        <td>PHP <?= number_format($ticketPrice) ?></td>
        <td><?= htmlspecialchars((string) ($ticket['section'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string) ($ticket['row'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string) ($ticket['number'] ?? '')) ?></td>
      </tr></tbody>
    </table>
  </section>

  <section class="voucher__claim-notice">
    <strong>THIS IS NOT YET YOUR ACTUAL EVENT TICKET.</strong>
    <span>Present this claim voucher with a matching valid government ID at the venue ticketing or validation counter. Admission is subject to successful voucher verification.</span>
  </section>

  <section class="voucher__notice-title">
    <strong>NOTICE TO ONLINE CUSTOMER</strong>
    <span>ONLINE TICKET PURCHASE AND ADMISSION VALIDATION PROCESS</span>
  </section>

  <section class="voucher__terms">
    <div>
      <p>1. The account holder named on this voucher must present a matching valid government ID.</p>
      <p>2. This voucher and its validation code are non-transferable and may be redeemed only once.</p>
      <p>3. Keep the barcode private. Duplicated, altered, cancelled, or refunded vouchers are invalid.</p>
      <p>4. Venue admission, prohibited-item, age, re-entry, and accessibility rules remain applicable.</p>
    </div>
    <div>
      <p><strong>CUSTOMER ACKNOWLEDGEMENT</strong></p>
      <p>The purchaser accepts responsibility for the accuracy of the account and event information shown on this document.</p>
      <div class="voucher__signature"><span>ACCOUNT HOLDER SIGNATURE</span><span>DATE</span></div>
      <div class="voucher__id-line">TYPE / NUMBER OF ID PRESENTED</div>
    </div>
  </section>

  <section class="voucher__support">
    For ticket concerns, visit the ClicKet Help Center or Customer Support page.<br>
    Thank you for your purchase. Please arrive early for ticket validation and venue security checks.
  </section>

  <section class="voucher__redemption">
    <div class="voucher__redemption-info">
      <span>ClicKet Ticketing Services</span>
      <strong>CLAIM TICKET VOUCHER <?= htmlspecialchars($voucherNumber) ?></strong>
      <small>TRANSACTION DETAILS</small>
      <table>
        <thead><tr><th>QTY</th><th>PARTICULARS</th><th>PRICE</th><th>AMOUNT</th></tr></thead>
        <tbody>
          <tr><td>1</td><td><?= htmlspecialchars((string) ($ticket['category'] ?? 'Admission')) ?> ticket</td><td>PHP <?= number_format($ticketPrice) ?></td><td>PHP <?= number_format($voucherTotal) ?></td></tr>
        </tbody>
      </table>
      <p><b>Buyer:</b> <?= htmlspecialchars((string) ($order['buyer_name'] ?? 'ClicKet account holder')) ?></p>
      <p><b>Order:</b> <?= htmlspecialchars((string) ($order['order_id'] ?? '')) ?></p>
    </div>
    <div class="voucher__barcode-wrap">
      <span>TICKET REDEMPTION CODE</span>
      <?= clicketBarcodeSvg((string) ($ticket['barcode_value'] ?? $ticket['ticket_id'] ?? 'CLICKET'), 70) ?>
      <strong><?= htmlspecialchars((string) ($ticket['validation_code'] ?? '')) ?></strong>
      <small>THIS SERVES AS YOUR PROOF OF PAYMENT</small>
    </div>
  </section>

  <footer class="voucher__footer">
    <span>Computer-generated ClicKet claim voucher. Valid without a signature.</span>
    <strong>Page 1 / 1</strong>
  </footer>
</article>
