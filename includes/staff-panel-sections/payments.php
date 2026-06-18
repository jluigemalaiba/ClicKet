<?php
$paymentReviewOrders = array_values(array_filter($payload['orders'], static function (array $order): bool {
    return (string) ($order['proof_of_payment'] ?? '') !== ''
        || strtolower((string) ($order['payment_status'] ?? '')) === 'pending';
}));
?>
<section class="staff-grid-two">
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Payments</h2>
      <span><?= $isAdmin ? 'All venue proof review and status controls' : 'Proof screenshots for your assigned venue only' ?></span>
    </div>
    <div class="staff-control-grid">
      <?php foreach (['Pending', 'Paid', 'Failed', 'Refunded', 'Proof review', 'Approve payment', 'Reject payment', 'Payment method breakdown'] as $item): ?>
        <button type="button"><?= sp_h($item) ?></button>
      <?php endforeach; ?>
    </div>
  </article>
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Revenue Pulse</h2>
      <span>Scoped payment summary</span>
    </div>
    <div class="staff-list">
      <div class="staff-list-row"><span>Paid revenue</span><strong><?= sp_money($metrics['sales']) ?></strong><small>Paid orders only</small></div>
      <div class="staff-list-row"><span>Pending payments</span><strong><?= sp_count($metrics['pendingPayments']) ?></strong><small>Needs review</small></div>
      <div class="staff-list-row"><span>Orders in scope</span><strong><?= sp_count($metrics['orders']) ?></strong><small>Role-filtered queue</small></div>
    </div>
  </article>
</section>

<section class="staff-section">
  <div class="staff-section-heading">
    <div>
      <p>Organizer Payment Queue</p>
      <h2>Review uploaded payment screenshots, then approve or reject</h2>
    </div>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Buyer</th>
          <th>Venue / Event</th>
          <th>Proof Screenshot</th>
          <th>Status</th>
          <th>Decision</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($paymentReviewOrders as $order): ?>
          <tr data-search-row data-payment-row="<?= sp_h($order['order_id'] ?? '') ?>">
            <td><strong><?= sp_h($order['order_id'] ?? 'Order') ?></strong><small><?= sp_h($order['payment_reference'] ?? $order['reference'] ?? '') ?></small></td>
            <td><?= sp_h($order['buyer_name'] ?? '') ?><small><?= sp_h($order['buyer_email'] ?? '') ?></small></td>
            <td><?= sp_h($order['venue'] ?? '') ?><small><?= sp_h($order['event_title'] ?? $order['event'] ?? '') ?></small></td>
            <td><strong><?= sp_h($order['proof_of_payment'] ?? 'No screenshot yet') ?></strong><small>Submitted to assigned organizer for review</small></td>
            <td><span class="staff-status <?= strtolower((string) ($order['payment_status'] ?? '')) === 'paid' ? 'is-success' : (strtolower((string) ($order['payment_status'] ?? '')) === 'failed' ? 'is-danger' : 'is-warning') ?>" data-payment-status><?= sp_h($order['payment_status'] ?? 'Pending') ?></span></td>
            <td>
              <button type="button" data-payment-action="approve" data-order-id="<?= sp_h($order['order_id'] ?? '') ?>">Approve</button>
              <button type="button" data-payment-action="reject" data-order-id="<?= sp_h($order['order_id'] ?? '') ?>">Reject</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$paymentReviewOrders): ?>
          <tr><td colspan="6">No payment screenshots are waiting in the current scope.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
