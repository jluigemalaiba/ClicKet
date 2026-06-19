<?php
$organizerPage = 'orders';
$organizerTitle = 'Orders';
require __DIR__ . '/includes/header.php';

$orders = $payload['orders'] ?? [];
?>
<section class="staff-section">
  <div class="staff-section-heading">
    <div><p>Orders</p><h2>Payment verification for your events</h2></div>
    <span><?= sp_count(count($orders)) ?> orders</span>
  </div>

  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Buyer</th>
          <th>Event</th>
          <th>Payment</th>
          <th>Proof</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
          <?php
          $status = strtolower((string) ($order['payment_status'] ?? ''));
          $canReview = in_array($status, ['for verification', 'pending payment'], true) && (string) ($order['proof_of_payment'] ?? '') !== '';
          ?>
          <tr data-search-row>
            <td><strong><?= sp_h($order['order_id'] ?? 'Order') ?></strong><small><?= sp_h($order['booked_at'] ?? '') ?></small></td>
            <td><?= sp_h($order['buyer_name'] ?? '') ?><small><?= sp_h($order['buyer_email'] ?? '') ?></small></td>
            <td><strong><?= sp_h($order['event_title'] ?? $order['event'] ?? '') ?></strong><small><?= sp_h($order['venue'] ?? '') ?></small></td>
            <td><strong><?= sp_money((int) ($order['total'] ?? 0)) ?></strong><small><?= sp_h($order['payment_method_label'] ?? $order['payment_method'] ?? '') ?></small></td>
            <td>
              <?php if (!empty($order['proof_url'])): ?>
                <a class="staff-proof-link" href="<?= sp_h($order['proof_url']) ?>" target="_blank" rel="noopener">View proof</a>
                <small><?= sp_h($order['proof_uploaded_at'] ?? '') ?></small>
              <?php else: ?>
                <span>No proof uploaded</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="staff-status <?= sp_status_class($order['payment_status'] ?? 'Pending Payment') ?>"><?= sp_h($order['payment_status'] ?? 'Pending Payment') ?></span>
              <?php if (($order['rejection_reason'] ?? '') !== ''): ?><small><?= sp_h($order['rejection_reason']) ?></small><?php endif; ?>
            </td>
            <td>
              <?php if ($canReview): ?>
                <form class="organizer-order-action" method="post" action="organizer/order-action.php">
                  <input type="hidden" name="order_id" value="<?= sp_h($order['order_id'] ?? '') ?>">
                  <button class="staff-action-btn" type="submit" name="action" value="approve">Approve Payment</button>
                </form>
                <form class="organizer-order-action" method="post" action="organizer/order-action.php">
                  <input type="hidden" name="order_id" value="<?= sp_h($order['order_id'] ?? '') ?>">
                  <textarea name="reason" rows="2" placeholder="Rejection reason" required></textarea>
                  <button class="staff-secondary-btn" type="submit" name="action" value="reject">Reject Payment</button>
                </form>
              <?php else: ?>
                <span class="staff-empty-state">No action available</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?><tr><td colspan="7">No orders are available for your events.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
