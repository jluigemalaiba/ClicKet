<?php

$order = $voucherRecord['order'];
$ticket = $voucherRecord['ticket'];
?>
<article class="voucher" id="voucherDocument" data-ticket-id="<?= htmlspecialchars((string) ($ticket['ticket_id'] ?? 'clicket-ticket')) ?>">
  <header class="voucher__masthead">
    <div class="voucher__brand">
      <img src="assets/Icon_Logo.png" alt="">
      <img src="assets/Name_Logo.png" alt="ClicKet">
    </div>
    <div class="voucher__reference">
      <span>Admission voucher</span>
      <strong><?= htmlspecialchars((string) ($ticket['voucher_id'] ?? '')) ?></strong>
    </div>
  </header>

  <section class="voucher__hero">
    <img src="<?= htmlspecialchars((string) ($order['event_banner'] ?? $order['event_poster'] ?? 'assets/Icon_Logo.png')) ?>" alt="<?= htmlspecialchars((string) ($order['event_title'] ?? 'Event')) ?>">
    <div class="voucher__hero-shade"></div>
    <div class="voucher__hero-copy">
      <span><?= htmlspecialchars((string) ($ticket['category'] ?? 'Admission')) ?></span>
      <h1><?= htmlspecialchars((string) ($order['event_title'] ?? 'ClicKet Event')) ?></h1>
      <p><?= htmlspecialchars(trim((string) ($order['event_date'] ?? '') . ' at ' . (string) ($order['event_time'] ?? ''))) ?></p>
      <p><?= htmlspecialchars((string) ($order['venue'] ?? '')) ?></p>
    </div>
    <strong class="voucher__status"><?= htmlspecialchars((string) ($ticket['status'] ?? 'Valid')) ?></strong>
  </section>

  <section class="voucher__admission">
    <div class="voucher__seat">
      <span>Section</span><strong><?= htmlspecialchars((string) ($ticket['section'] ?? '')) ?></strong>
    </div>
    <div class="voucher__seat">
      <span>Row</span><strong><?= htmlspecialchars((string) ($ticket['row'] ?? '')) ?></strong>
    </div>
    <div class="voucher__seat">
      <span>Seat</span><strong><?= htmlspecialchars((string) ($ticket['number'] ?? '')) ?></strong>
    </div>
    <div class="voucher__seat">
      <span>Category</span><strong><?= htmlspecialchars((string) ($ticket['category'] ?? 'Admission')) ?></strong>
    </div>
  </section>

  <section class="voucher__details">
    <div>
      <span>Ticket holder</span>
      <strong><?= htmlspecialchars((string) ($order['buyer_name'] ?? 'ClicKet account holder')) ?></strong>
      <small><?= htmlspecialchars((string) ($order['buyer_email'] ?? '')) ?></small>
    </div>
    <div>
      <span>Order ID</span>
      <strong><?= htmlspecialchars((string) ($order['order_id'] ?? '')) ?></strong>
    </div>
    <div>
      <span>Ticket ID</span>
      <strong><?= htmlspecialchars((string) ($ticket['ticket_id'] ?? '')) ?></strong>
    </div>
    <div>
      <span>Purchase date</span>
      <strong><?= htmlspecialchars(clicketOrderDate((string) ($order['booked_at'] ?? ''), 'F j, Y, g:i A')) ?></strong>
    </div>
    <div>
      <span>Payment status</span>
      <strong><?= htmlspecialchars((string) ($order['payment_status'] ?? 'Paid')) ?></strong>
    </div>
    <div>
      <span>Payment method</span>
      <strong><?= htmlspecialchars((string) ($order['payment_method_label'] ?? $order['payment_method'] ?? 'Payment')) ?></strong>
    </div>
    <div>
      <span>Payment reference</span>
      <strong><?= htmlspecialchars((string) ($order['payment_reference'] ?? $order['reference'] ?? '')) ?></strong>
    </div>
    <div>
      <span>Amount paid</span>
      <strong>PHP <?= number_format((int) ($ticket['price'] ?? 0)) ?></strong>
    </div>
  </section>

  <section class="voucher__validation">
    <div class="voucher__barcode-wrap">
      <?= clicketBarcodeSvg((string) ($ticket['barcode_value'] ?? $ticket['ticket_id'] ?? 'CLICKET')) ?>
      <strong><?= htmlspecialchars((string) ($ticket['validation_code'] ?? '')) ?></strong>
      <span>Present this barcode with a valid ID for ticket validation.</span>
    </div>
    <div class="voucher__notice">
      <span>Non-transferable</span>
      <p>This ticket is assigned to the named ClicKet account holder. The venue may require a matching valid government ID before admission.</p>
    </div>
  </section>

  <section class="voucher__reminders">
    <h2>Important reminders</h2>
    <div>
      <p>Arrive at least 60 minutes before the event for security and ticket validation.</p>
      <p>Keep this voucher and validation code private. A ticket may be scanned only once.</p>
      <p>Re-entry, prohibited-item, age, and accessibility rules are subject to the venue policy.</p>
      <p>Altered, duplicated, refunded, or cancelled tickets will not be accepted.</p>
    </div>
  </section>

  <footer class="voucher__footer">
    <span>ClicKet electronic ticket voucher</span>
    <strong>Proof of confirmed payment and admission entitlement</strong>
  </footer>
</article>
