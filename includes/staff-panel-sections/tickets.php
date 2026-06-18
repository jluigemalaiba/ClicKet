<?php
require_once __DIR__ . '/../ticket-data.php';

$ticketPrintOrders = array_map(static fn (array $order): array => clicketHydrateOrderTickets($order), $payload['orders']);
$ticketPrintJson = array_map(static function (array $order): array {
    return [
        'order_id' => (string) ($order['order_id'] ?? ''),
        'buyer_name' => (string) ($order['buyer_name'] ?? ''),
        'buyer_email' => (string) ($order['buyer_email'] ?? ''),
        'event_title' => (string) ($order['event_title'] ?? $order['event'] ?? ''),
        'venue' => (string) ($order['venue'] ?? ''),
        'payment_status' => (string) ($order['payment_status'] ?? ''),
        'order_status' => (string) ($order['order_status'] ?? ''),
        'tickets' => array_map(static function (array $ticket): array {
            return [
                'ticket_id' => (string) ($ticket['ticket_id'] ?? ''),
                'section' => (string) ($ticket['section'] ?? ''),
                'row' => (string) ($ticket['row'] ?? ''),
                'number' => (string) ($ticket['number'] ?? ''),
                'category' => (string) ($ticket['category'] ?? ''),
                'status' => (string) ($ticket['status'] ?? ''),
            ];
        }, is_array($order['tickets'] ?? null) ? $order['tickets'] : []),
    ];
}, $ticketPrintOrders);
?>
<script type="application/json" id="staffTicketOrdersJson"><?= json_encode($ticketPrintJson, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>

<section class="staff-grid-two">
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Tickets</h2>
      <span>Search, validation, reissue, void</span>
    </div>
    <div class="staff-control-grid">
      <?php foreach (['Search ticket ID', 'Search voucher ID', 'Search validation code', 'Valid', 'Used', 'Cancelled', 'Refunded', 'Reissued', 'Reissue ticket', 'Void ticket'] as $item): ?>
        <button type="button"><?= sp_h($item) ?></button>
      <?php endforeach; ?>
    </div>
  </article>
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Gate / Check-in</h2>
      <span>Validation and gate staff flow</span>
    </div>
    <div class="staff-control-grid">
      <?php foreach (['Scan ticket', 'Manual validation', 'Mark as used', 'Entry logs', 'Duplicate entry warning', 'Gate staff view', 'Scanned count', 'Remaining tickets'] as $item): ?>
        <button type="button"><?= sp_h($item) ?></button>
      <?php endforeach; ?>
    </div>
  </article>
</section>

<section class="staff-section">
  <div class="staff-section-heading">
    <div>
      <p>F2F / On-site Ticket Printing</p>
      <h2>Search order ID, then print 1 ticket or all tickets in that order</h2>
    </div>
  </div>
  <div class="staff-onsite-print">
    <label class="staff-order-lookup">
      <span>Order ID from user form</span>
      <input type="search" id="staffOrderPrintSearch" placeholder="Example: CKO-D01E702108" autocomplete="off">
    </label>
    <div class="staff-print-result" id="staffPrintResult">
      <p>Enter an order ID from the user form or on-site request. Results are limited to your assigned venue scope.</p>
    </div>
  </div>
</section>
