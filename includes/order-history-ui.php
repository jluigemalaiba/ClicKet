<?php

$orderHistory = $orderHistory ?? [];
?>
<section class="order-history<?= !empty($orderHistoryCompact) ? ' order-history--compact' : '' ?>" aria-labelledby="order-history-title">
  <div class="order-history__heading">
    <div>
      <h2 id="order-history-title">Order History</h2>
      <p>Review your confirmed bookings, payment information, and individual ticket details.</p>
    </div>
    <span class="order-history__count"><?= count($orderHistory) ?> <?= count($orderHistory) === 1 ? 'order' : 'orders' ?></span>
  </div>

  <?php if (!$orderHistory): ?>
    <div class="order-history__empty">
      <span class="order-history__empty-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16l-1 13H5L4 7Z"/><path d="M8 7a4 4 0 0 1 8 0"/><path d="M9 12h6"/></svg>
      </span>
      <h3>No orders yet</h3>
      <p>Your successfully paid tickets will appear here automatically.</p>
      <a href="events.php">Explore events</a>
    </div>
  <?php else: ?>
    <div class="order-history__list">
      <?php foreach ($orderHistory as $order): ?>
        <?php
        $seats = is_array($order['seats'] ?? null) ? $order['seats'] : [];
        $seatSummary = array_map(
            fn(array $seat): string => trim(($seat['section'] ?? '') . ' R' . ($seat['row'] ?? '') . '-S' . ($seat['number'] ?? '')),
            $seats
        );
        ?>
        <button class="order-card" type="button" data-order-id="<?= htmlspecialchars((string) ($order['order_id'] ?? '')) ?>" aria-haspopup="dialog">
          <span class="order-card__accent" aria-hidden="true"></span>
          <span class="order-card__main">
            <span class="order-card__topline">
              <span class="order-card__id"><?= htmlspecialchars((string) ($order['order_id'] ?? 'Order')) ?></span>
              <span class="order-status <?= clicketOrderStatusClass((string) ($order['order_status'] ?? 'Confirmed')) ?>"><?= htmlspecialchars((string) ($order['order_status'] ?? 'Confirmed')) ?></span>
            </span>
            <strong class="order-card__event"><?= htmlspecialchars((string) ($order['event_title'] ?? 'ClicKet Event')) ?></strong>
            <span class="order-card__schedule">
              <?= htmlspecialchars(trim((string) ($order['event_date'] ?? '') . ' at ' . (string) ($order['event_time'] ?? ''))) ?>
            </span>
            <span class="order-card__venue"><?= htmlspecialchars((string) ($order['venue'] ?? '')) ?></span>
          </span>
          <span class="order-card__facts">
            <span><small>Tickets</small><strong><?= count($seats) ?></strong></span>
            <span><small>Seats</small><strong><?= htmlspecialchars(implode(', ', $seatSummary)) ?></strong></span>
            <span><small>Payment</small><strong class="order-status <?= clicketOrderStatusClass((string) ($order['payment_status'] ?? 'Paid')) ?>"><?= htmlspecialchars((string) ($order['payment_status'] ?? 'Paid')) ?></strong></span>
            <span><small>Purchased</small><strong><?= htmlspecialchars(clicketOrderDate((string) ($order['booked_at'] ?? ''))) ?></strong></span>
          </span>
          <span class="order-card__total">
            <small>Total paid</small>
            <strong>PHP <?= number_format((int) ($order['total'] ?? 0)) ?></strong>
            <span>View details <b aria-hidden="true">&rarr;</b></span>
          </span>
        </button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
