<?php
$paymentReviewOrders = array_values(array_filter($payload['orders'], static function (array $order): bool {
    return (string) ($order['proof_of_payment'] ?? '') !== ''
        || in_array(strtolower((string) ($order['payment_status'] ?? '')), ['pending', 'pending payment', 'for verification', 'payment submitted'], true);
}));
$proofOrder = $paymentReviewOrders[0] ?? ($payload['orders'][0] ?? []);
?>

<section class="staff-grid-two" data-subsection="queue">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Payments</p>
        <h2>Payment review queue</h2>
      </div>
      <span><?= $isAdmin ? 'All venues' : 'Assigned scope' ?></span>
    </div>
    <div class="staff-status-grid">
      <div class="staff-status-tile"><strong><?= sp_count($metrics['pendingPayments']) ?></strong><small>Pending payments</small></div>
      <div class="staff-status-tile"><strong><?= sp_money($metrics['sales']) ?></strong><small>Paid revenue</small></div>
      <div class="staff-status-tile"><strong><?= sp_money($metrics['serviceFees']) ?></strong><small>Service fees</small></div>
      <div class="staff-status-tile"><strong><?= sp_count(count($payload['paymentMethods'])) ?></strong><small>Payment methods</small></div>
    </div>
  </article>

  <article class="staff-card" data-subsection="proof">
    <div class="staff-card-heading">
      <div>
        <p>Proof Of Payment Viewer</p>
        <h2><?= sp_h($proofOrder['payment_reference'] ?? 'No proof selected') ?></h2>
      </div>
      <span><?= sp_h($proofOrder['payment_status'] ?? 'Pending') ?></span>
    </div>
    <div class="staff-proof-viewer">
      <div>
        <strong><?= sp_h(($proofOrder['proof_of_payment'] ?? '') !== '' ? $proofOrder['proof_of_payment'] : 'No uploaded proof') ?></strong>
        <small><?= sp_h($proofOrder['buyer_name'] ?? 'Buyer') ?> &middot; <?= sp_money((int) ($proofOrder['total'] ?? 0)) ?></small>
      </div>
      <button type="button" data-open-modal data-modal-title="Proof Viewer" data-modal-type="proof-viewer">Open Proof</button>
    </div>
  </article>
</section>

<section class="staff-section" data-subsection="queue">
  <div class="staff-section-heading">
    <div>
      <p>Organizer Payment Queue</p>
      <h2>Review uploaded screenshots, then approve or reject</h2>
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
          <th>Amount</th>
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
            <td><strong><?= sp_h($order['proof_of_payment'] ?? 'No screenshot yet') ?></strong><small>Submitted for review</small></td>
            <td><?= sp_money((int) ($order['total'] ?? 0)) ?></td>
            <td><span class="staff-status <?= sp_status_class($order['payment_status'] ?? 'Pending') ?>" data-payment-status><?= sp_h($order['payment_status'] ?? 'Pending') ?></span></td>
            <td>
              <button type="button" data-payment-action="approve" data-order-id="<?= sp_h($order['order_id'] ?? '') ?>">Approve</button>
              <button type="button" data-payment-action="reject" data-order-id="<?= sp_h($order['order_id'] ?? '') ?>">Reject</button>
              <button type="button" data-open-modal data-modal-title="Payment Status" data-modal-type="payment-status">Manage</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$paymentReviewOrders): ?>
          <tr><td colspan="7">No payment screenshots are waiting in the current scope.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="staff-grid-two" data-subsection="revenue">
  <article class="staff-card staff-card--flush">
    <div class="staff-card-heading staff-card-heading--padded">
      <div>
        <p>Revenue Reports</p>
        <h2>Payment method breakdown</h2>
      </div>
    </div>
    <div class="staff-table-wrap staff-table-wrap--embedded">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Method</th>
            <th>Orders</th>
            <th>Sales</th>
            <th>Service Fees</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payload['paymentMethods'] as $method): ?>
            <tr data-search-row>
              <td><strong><?= sp_h($method['method']) ?></strong></td>
              <td><?= sp_count($method['orders']) ?></td>
              <td><?= sp_money((int) $method['sales']) ?></td>
              <td><?= sp_money((int) $method['fees']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>

  <article class="staff-card" data-subsection="fees">
    <div class="staff-card-heading">
      <div>
        <p>Service Fee Analytics</p>
        <h2>Fee capture and controls</h2>
      </div>
    </div>
    <div class="staff-detail-list">
      <div><span>Total Fees</span><strong><?= sp_money($metrics['serviceFees']) ?></strong></div>
      <div><span>Average Fee</span><strong><?= sp_money($metrics['orders'] ? $metrics['serviceFees'] / max(1, $metrics['orders']) : 0) ?></strong></div>
      <div><span>Review SLA</span><strong>Under 15 min</strong></div>
      <div><span>Manual Proofs</span><strong><?= sp_count(count($paymentReviewOrders)) ?></strong></div>
    </div>
    <div class="staff-card-actions">
      <button type="button">Export Revenue</button>
      <button type="button">Export Service Fees</button>
      <button type="button">Configure Methods</button>
    </div>
  </article>
</section>
