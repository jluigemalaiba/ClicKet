<section class="staff-section">
  <div class="staff-section-heading">
    <div>
      <p>Orders</p>
      <h2><?= $isAdmin ? 'All orders' : 'Orders for assigned events only' ?></h2>
    </div>
  </div>
  <div class="staff-action-strip">
    <?php foreach (['Filter by event', 'Filter by venue', 'Buyer details', 'Selected seats', 'Reissue tickets', 'Cancel/refund', 'Archive order'] as $item): ?>
      <button type="button"><?= sp_h($item) ?></button>
    <?php endforeach; ?>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Buyer</th>
          <th>Event / Seats</th>
          <th>Payment Ref</th>
          <th>Total</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($payload['orders'], 0, 12) as $order): ?>
          <tr data-search-row>
            <td><strong><?= sp_h($order['order_id'] ?? 'Order') ?></strong><small><?= sp_h($order['venue'] ?? '') ?></small></td>
            <td><?= sp_h($order['buyer_name'] ?? '') ?><small><?= sp_h($order['buyer_email'] ?? '') ?></small></td>
            <td><?= sp_h($order['event_title'] ?? $order['event'] ?? '') ?><small><?= sp_count(count($order['seats'] ?? [])) ?> selected seats</small></td>
            <td><?= sp_h($order['payment_reference'] ?? $order['reference'] ?? '') ?></td>
            <td><?= sp_money((int) ($order['total'] ?? 0)) ?></td>
            <td><span class="staff-status <?= strtolower((string) ($order['payment_status'] ?? '')) === 'paid' ? 'is-success' : 'is-warning' ?>"><?= sp_h($order['payment_status'] ?? 'Pending') ?></span></td>
            <td><button type="button">Reissue</button><button type="button">Refund</button><button type="button">Archive</button></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$payload['orders']): ?>
          <tr><td colspan="7">No orders in the current scope yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
