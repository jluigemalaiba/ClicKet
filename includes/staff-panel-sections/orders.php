<?php
$orders = $payload['orders'] ?? [];
$ordersForClient = array_map(static function (array $order): array {
    $order['proof_url'] = clicketStaffOrderProofUrl($order);
    return $order;
}, $orders);
$confirmedCount = count(array_filter($orders, static fn (array $order): bool => strtolower((string) ($order['order_status'] ?? '')) === 'confirmed'));
$pendingCount = count(array_filter($orders, static fn (array $order): bool => in_array(strtolower((string) ($order['payment_status'] ?? '')), ['pending', 'pending payment', 'for verification', 'payment submitted'], true)));
?>

<section class="staff-orders-workspace" data-subsection="table">
  <header class="staff-orders-head">
    <div><p>Order registry</p><h2>Every order, ready for a closer look.</h2><span>Open one record to see buyer details, seats, payment reference, and proof of payment.</span></div>
    <div class="staff-orders-head__summary"><span><b><?= sp_count(count($orders)) ?></b> total</span><span><b><?= sp_count($confirmedCount) ?></b> confirmed</span><span><b><?= sp_count($pendingCount) ?></b> pending</span></div>
  </header>

  <div class="staff-orders-toolbar">
    <div class="staff-orders-filters" role="tablist" aria-label="Order filters">
      <button class="is-active" type="button" data-order-filter="all">All orders <b><?= sp_count(count($orders)) ?></b></button>
      <button type="button" data-order-filter="confirmed">Confirmed <b><?= sp_count($confirmedCount) ?></b></button>
      <button type="button" data-order-filter="pending">Pending <b><?= sp_count($pendingCount) ?></b></button>
      <button type="button" data-order-filter="closed">Closed</button>
    </div>
    <div class="staff-orders-toolbar__filters"><label><span>Event</span><select><option>All events</option><?php foreach (array_slice($payload['events'], 0, 10) as $event): ?><option><?= sp_h($event['title']) ?></option><?php endforeach; ?></select></label><label><span>Date range</span><input type="text" placeholder="Any date"></label></div>
  </div>

  <div class="staff-orders-table-wrap"><table class="staff-table staff-orders-table">
    <thead><tr><th>Order ID</th><th>Event</th><th>Customer</th><th>Payment</th><th>Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($orders as $order): ?>
        <?php $state = strtolower((string) ($order['payment_status'] ?? 'pending')); $orderState = strtolower((string) ($order['order_status'] ?? '')); $filterState = in_array($orderState, ['cancelled', 'refunded'], true) ? 'closed' : (in_array($state, ['pending payment', 'for verification', 'payment submitted'], true) ? 'pending' : $state); ?>
        <tr data-search-row data-order-row="<?= sp_h($order['order_id'] ?? '') ?>" data-order-filter-row="<?= sp_h($filterState) ?>">
          <td><strong><?= sp_h($order['order_id'] ?? 'Order') ?></strong><small><?= sp_h($order['booked_at'] ?? '') ?></small></td>
          <td><strong><?= sp_h($order['event_title'] ?? $order['event'] ?? '') ?></strong><small><?= sp_h($order['venue'] ?? '') ?> · <?= sp_count(clicketStaffTicketCount($order)) ?> seats</small></td>
          <td><?= sp_h($order['buyer_name'] ?? '') ?><small><?= sp_h($order['buyer_email'] ?? '') ?></small></td>
          <td><strong><?= sp_h($order['payment_method_label'] ?? $order['payment_method'] ?? '') ?></strong><small><?= sp_h($order['payment_reference'] ?? $order['reference'] ?? '') ?></small></td>
          <td><strong><?= sp_money((int) ($order['total'] ?? 0)) ?></strong></td>
          <td><span class="staff-status <?= sp_status_class($order['payment_status'] ?? 'Pending') ?>" data-order-payment-status><?= sp_h($order['payment_status'] ?? 'Pending') ?></span><small data-order-status><?= sp_h($order['order_status'] ?? 'Open') ?></small></td>
          <td class="staff-orders-table__action"><button type="button" data-order-details="<?= sp_h($order['order_id'] ?? '') ?>">View details <span>→</span></button></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$orders): ?><tr><td colspan="7">No orders are available in the current scope.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</section>

<script type="application/json" id="staffOrdersJson"><?= json_encode($ordersForClient, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>

<?php require __DIR__ . '/payments.php'; ?>
