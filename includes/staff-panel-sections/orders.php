<section class="staff-section" data-subsection="filters">
  <div class="staff-section-heading">
    <div>
      <p>Orders</p>
      <h2><?= $isAdmin ? 'Advanced order management across all venues' : 'Orders for assigned events only' ?></h2>
    </div>
    <button class="staff-action-btn" type="button" data-open-modal data-modal-title="Order Details Drawer" data-modal-type="order-detail">Open Details Drawer</button>
  </div>

  <div class="staff-filter-bar">
    <label>
      <span>Event</span>
      <select>
        <option>All events in scope</option>
        <?php foreach (array_slice($payload['events'], 0, 10) as $event): ?>
          <option><?= sp_h($event['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      <span>Venue</span>
      <select>
        <option>All venues in scope</option>
        <?php foreach ($payload['venues'] as $venue): ?>
          <option><?= sp_h($venue['venue']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      <span>Status</span>
      <select>
        <option>All order statuses</option>
        <option>Confirmed</option>
        <option>Pending</option>
        <option>Refunded</option>
        <option>Cancelled</option>
        <option>Archived</option>
      </select>
    </label>
    <label>
      <span>Date Range</span>
      <input type="text" placeholder="Jun 1 - Jun 30">
    </label>
    <button type="button">Apply Filters</button>
  </div>

  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Buyer Information</th>
          <th>Event / Seat Details</th>
          <th>Payment Reference</th>
          <th>Total</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payload['orders'] as $order): ?>
          <tr data-search-row>
            <td>
              <strong><?= sp_h($order['order_id'] ?? 'Order') ?></strong>
              <small><?= sp_h($order['booked_at'] ?? '') ?></small>
            </td>
            <td>
              <?= sp_h($order['buyer_name'] ?? '') ?>
              <small><?= sp_h($order['buyer_email'] ?? '') ?></small>
            </td>
            <td>
              <?= sp_h($order['event_title'] ?? $order['event'] ?? '') ?>
              <small><?= sp_h($order['venue'] ?? '') ?> &middot; <?= sp_count(clicketStaffTicketCount($order)) ?> selected seats</small>
            </td>
            <td>
              <strong><?= sp_h($order['payment_reference'] ?? $order['reference'] ?? '') ?></strong>
              <small><?= sp_h($order['payment_method_label'] ?? $order['payment_method'] ?? '') ?></small>
            </td>
            <td><?= sp_money((int) ($order['total'] ?? 0)) ?></td>
            <td>
              <span class="staff-status <?= sp_status_class($order['payment_status'] ?? 'Pending') ?>"><?= sp_h($order['payment_status'] ?? 'Pending') ?></span>
              <small><?= sp_h($order['order_status'] ?? 'Open') ?></small>
            </td>
            <td>
              <button type="button" data-open-modal data-modal-title="<?= sp_h($order['order_id'] ?? 'Order') ?>" data-modal-type="order-detail">View</button>
              <button type="button">Reissue</button>
              <button type="button">Refund</button>
              <button type="button">Cancel</button>
              <button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Archive</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$payload['orders']): ?>
          <tr><td colspan="7">No orders are available in the current scope yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="staff-grid-three" data-subsection="buyers">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Buyer Details</p>
        <h2>Profile snapshot</h2>
      </div>
    </div>
    <?php $firstOrder = $payload['orders'][0] ?? []; ?>
    <div class="staff-profile-card">
      <span><?= sp_h(sp_initials((string) ($firstOrder['buyer_name'] ?? 'Buyer'))) ?></span>
      <strong><?= sp_h($firstOrder['buyer_name'] ?? 'No buyer selected') ?></strong>
      <small><?= sp_h($firstOrder['buyer_email'] ?? 'Open an order to inspect buyer information') ?></small>
    </div>
    <div class="staff-detail-list">
      <div><span>Orders</span><strong><?= sp_count($metrics['orders']) ?></strong></div>
      <div><span>Tickets</span><strong><?= sp_count($metrics['tickets']) ?></strong></div>
      <div><span>Latest Reference</span><strong><?= sp_h($firstOrder['payment_reference'] ?? 'None') ?></strong></div>
    </div>
  </article>

  <article class="staff-card" data-subsection="drawer">
    <div class="staff-card-heading">
      <div>
        <p>Order Details Drawer</p>
        <h2>Seat and payment view</h2>
      </div>
    </div>
    <div class="staff-drawer-preview">
      <strong><?= sp_h($firstOrder['order_id'] ?? 'Select an order') ?></strong>
      <span><?= sp_h($firstOrder['event_title'] ?? 'Event details appear here') ?></span>
      <div>
        <?php foreach (array_slice((array) ($firstOrder['seats'] ?? []), 0, 4) as $seat): ?>
          <small><?= sp_h($seat['section'] ?? '') ?> <?= sp_h($seat['row'] ?? '') ?>-<?= sp_h($seat['number'] ?? '') ?></small>
        <?php endforeach; ?>
      </div>
    </div>
  </article>

  <article class="staff-card" data-subsection="actions">
    <div class="staff-card-heading">
      <div>
        <p>Order Actions</p>
        <h2>Controlled workflows</h2>
      </div>
    </div>
    <div class="staff-control-grid">
      <?php foreach (['Reissue ticket', 'Refund order', 'Cancel order', 'Archive order', 'Print receipt', 'Export selection'] as $item): ?>
        <button type="button" <?= (!$isAdmin && $item === 'Archive order') ? 'disabled' : '' ?>><?= sp_h($item) ?></button>
      <?php endforeach; ?>
    </div>
  </article>
</section>
