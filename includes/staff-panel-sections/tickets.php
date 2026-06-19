<?php
$tickets = $payload['tickets'] ?? [];
$ticketJson = array_map(static function (array $ticket): array {
    return [
        'ticket_id' => (string) ($ticket['ticket_id'] ?? ''),
        'voucher_id' => (string) ($ticket['voucher_id'] ?? ''),
        'validation_code' => (string) ($ticket['validation_code'] ?? ''),
        'status' => (string) ($ticket['status'] ?? 'Valid'),
        'category' => (string) ($ticket['category'] ?? ''),
        'section' => (string) ($ticket['section'] ?? ''),
        'row' => (string) ($ticket['row'] ?? ''),
        'number' => (string) ($ticket['number'] ?? ''),
        'price' => (int) ($ticket['price'] ?? 0),
        'order_id' => (string) ($ticket['order_id'] ?? ''),
        'event_title' => (string) ($ticket['event_title'] ?? ''),
        'venue' => (string) ($ticket['venue'] ?? ''),
        'buyer_name' => (string) ($ticket['buyer_name'] ?? ''),
    ];
}, $tickets);
$validTickets = count(array_filter($tickets, static fn (array $ticket): bool => strtolower((string) ($ticket['status'] ?? '')) === 'valid'));
?>

<section class="staff-tickets-workspace" data-subsection="search">
  <header class="staff-tickets-head"><div><p>Ticket registry</p><h2>A clean record for every seat issued.</h2><span>View ticket details without changing, reissuing, voiding, or printing records.</span></div><div class="staff-tickets-head__stats"><span><b><?= sp_count(count($tickets)) ?></b> tickets</span><span><b><?= sp_count($validTickets) ?></b> valid</span></div></header>
  <div class="staff-tickets-toolbar"><div class="staff-ticket-status-tabs"><button class="is-active" type="button" data-ticket-filter="all">All tickets</button><button type="button" data-ticket-filter="valid">Valid</button><button type="button" data-ticket-filter="used">Used</button><button type="button" data-ticket-filter="closed">Closed</button></div><label><span>Find ticket</span><input type="search" data-ticket-local-search placeholder="Ticket, voucher, buyer, or order ID"></label></div>
  <div class="staff-tickets-table-wrap"><table class="staff-table staff-tickets-table"><thead><tr><th>Ticket</th><th>Event</th><th>Customer</th><th>Seat</th><th>Voucher</th><th>Status</th><th></th></tr></thead><tbody>
    <?php foreach ($tickets as $ticket): ?>
      <?php $ticketStatus = strtolower((string) ($ticket['status'] ?? 'valid')); $filterStatus = in_array($ticketStatus, ['cancelled', 'refunded', 'void'], true) ? 'closed' : $ticketStatus; ?>
      <tr data-search-row data-ticket-row="<?= sp_h($ticket['ticket_id'] ?? '') ?>" data-ticket-filter-row="<?= sp_h($filterStatus) ?>">
        <td><strong><?= sp_h($ticket['ticket_id'] ?? '') ?></strong><small><?= sp_h($ticket['order_id'] ?? '') ?></small></td>
        <td><strong><?= sp_h($ticket['event_title'] ?? '') ?></strong><small><?= sp_h($ticket['venue'] ?? '') ?></small></td>
        <td><?= sp_h($ticket['buyer_name'] ?? '') ?></td>
        <td><strong><?= sp_h($ticket['section'] ?? '') ?> <?= sp_h($ticket['row'] ?? '') ?>-<?= sp_h($ticket['number'] ?? '') ?></strong><small><?= sp_h($ticket['category'] ?? '') ?></small></td>
        <td><?= sp_h($ticket['voucher_id'] ?? '') ?></td>
        <td><span class="staff-status <?= sp_status_class($ticket['status'] ?? 'Valid') ?>"><?= sp_h($ticket['status'] ?? 'Valid') ?></span></td>
        <td class="staff-tickets-table__action"><button type="button" data-ticket-details="<?= sp_h($ticket['ticket_id'] ?? '') ?>">View details <span>→</span></button></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$tickets): ?><tr><td colspan="7">No tickets are available in the current scope.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>

<script type="application/json" id="staffTicketsJson"><?= json_encode($ticketJson, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
