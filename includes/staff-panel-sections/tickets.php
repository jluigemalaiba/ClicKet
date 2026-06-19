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

<section class="staff-grid-two" data-subsection="search">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Ticket Search</p>
        <h2>Find by ticket, validation, or voucher</h2>
      </div>
    </div>
    <div class="staff-ticket-search-grid">
      <label>
        <span>Ticket ID</span>
        <input type="search" placeholder="TKT-5C53379DEDF7">
      </label>
      <label>
        <span>Validation Code</span>
        <input type="search" placeholder="VAL-509A0D02A81E30E9">
      </label>
      <label>
        <span>Voucher ID</span>
        <input type="search" placeholder="VCH-83A2CFDB3FAD">
      </label>
      <button class="staff-action-btn" type="button" data-open-modal data-modal-title="Ticket Details" data-modal-type="ticket-detail">Search Ticket</button>
    </div>
  </article>

  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Ticket Operations</p>
        <h2>Status and print controls</h2>
      </div>
      <span>Admin mode</span>
    </div>
    <div class="staff-control-grid">
      <?php foreach (['Search ticket ID', 'Search voucher ID', 'Search validation code', 'Reissue ticket', 'Void ticket', 'Print ticket', 'Export tickets', 'Review ticket status'] as $item): ?>
        <button type="button"><?= sp_h($item) ?></button>
      <?php endforeach; ?>
    </div>
  </article>
</section>

<section class="staff-section" data-subsection="details">
  <div class="staff-section-heading">
    <div>
      <p>Ticket Details Modal</p>
      <h2>Ticket registry and status controls</h2>
    </div>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Ticket</th>
          <th>Voucher</th>
          <th>Validation</th>
          <th>Event / Venue</th>
          <th>Seat</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($payload['tickets'], 0, 18) as $ticket): ?>
          <tr data-search-row>
            <td><strong><?= sp_h($ticket['ticket_id']) ?></strong><small><?= sp_h($ticket['order_id']) ?></small></td>
            <td><?= sp_h($ticket['voucher_id']) ?></td>
            <td><?= sp_h($ticket['validation_code']) ?></td>
            <td><?= sp_h($ticket['event_title']) ?><small><?= sp_h($ticket['venue']) ?></small></td>
            <td><?= sp_h($ticket['section']) ?> <?= sp_h($ticket['row']) ?>-<?= sp_h($ticket['number']) ?><small><?= sp_h($ticket['category']) ?></small></td>
            <td><span class="staff-status <?= sp_status_class($ticket['status']) ?>"><?= sp_h($ticket['status']) ?></span></td>
            <td>
              <button type="button" data-open-modal data-modal-title="<?= sp_h($ticket['ticket_id']) ?>" data-modal-type="ticket-detail">Details</button>
              <button type="button">Reissue</button>
              <button type="button">Void</button>
              <button type="button">Print</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$payload['tickets']): ?>
          <tr><td colspan="7">No tickets are available in the current scope.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="staff-section" data-subsection="print">
  <div class="staff-section-heading">
    <div>
      <p>Print Ticket</p>
      <h2>Search order ID, then print one ticket or all tickets in that order</h2>
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
