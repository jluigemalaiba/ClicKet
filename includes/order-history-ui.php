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
      <h3>No orders yet</h3>
      <p>Your successfully paid tickets will appear here automatically.</p>
      <a href="events.php">Explore events</a>
    </div>
  <?php else: ?>
    <div class="order-history__list">
      <?php foreach ($orderHistory as $order): ?>
        <?php
        $seats = is_array($order['seats'] ?? null) ? $order['seats'] : [];
        $paymentMethod = strtolower((string) ($order['payment_method'] ?? ''));
        $paymentStatus = strtolower((string) ($order['payment_status_key'] ?? $order['payment_status'] ?? ''));
        $isDigitalPayment = $paymentMethod === 'qrph';
        $canUploadProof = $isDigitalPayment && in_array($paymentStatus, ['pending', 'rejected'], true);
        $isForVerification = $isDigitalPayment && $paymentStatus === 'under_review';
        $isVerified = $paymentStatus === 'approved';
        $qr = is_array($order['payment_qr'] ?? null) ? $order['payment_qr'] : null;
        $seatSummary = array_map(
            fn(array $seat): string => trim(($seat['section'] ?? '') . ' R' . ($seat['row'] ?? '') . '-S' . ($seat['number'] ?? '')),
            $seats
        );
        ?>
        <article class="order-card">
          <span class="order-card__accent" aria-hidden="true"></span>
          <span class="order-card__main">
            <span class="order-card__topline">
              <span class="order-card__id"><?= htmlspecialchars((string) ($order['order_id'] ?? 'Order')) ?></span>
              <span class="order-status <?= clicketOrderStatusClass((string) ($order['order_status'] ?? 'Pending Payment')) ?>"><?= htmlspecialchars((string) ($order['order_status'] ?? 'Pending Payment')) ?></span>
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
            <span><small>Payment</small><strong class="order-status <?= clicketOrderStatusClass((string) ($order['payment_status'] ?? 'Pending Payment')) ?>"><?= htmlspecialchars((string) ($order['payment_status'] ?? 'Pending Payment')) ?></strong></span>
            <span><small>Purchased</small><strong><?= htmlspecialchars(clicketOrderDate((string) ($order['booked_at'] ?? ''))) ?></strong></span>
          </span>
          <span class="order-card__total">
            <small><?= $isVerified ? 'Total paid' : 'Amount due' ?></small>
            <strong>PHP <?= number_format((int) ($order['total'] ?? 0)) ?></strong>
            <button class="order-card__details-button" type="button" data-order-id="<?= htmlspecialchars((string) ($order['order_id'] ?? '')) ?>" aria-haspopup="dialog">View details</button>
          </span>
          <?php if ($isDigitalPayment): ?>
            <div class="order-payment-panel">
              <div class="order-payment-panel__summary">
                <div>
                  <span>Payment method</span>
                  <strong><?= htmlspecialchars((string) ($order['payment_method_label'] ?? 'Digital payment')) ?></strong>
                  <small><?= htmlspecialchars((string) ($order['venue'] ?? '')) ?></small>
                </div>
                <?php if ($qr): ?>
                  <div class="order-payment-qr">
                    <?php if (!empty($qr['exists']) && !empty($qr['path'])): ?>
                      <img src="<?= htmlspecialchars((string) $qr['path']) ?>" loading="lazy" decoding="async" alt="<?= htmlspecialchars((string) ($qr['venue_label'] ?? $order['venue'] ?? 'Venue')) ?> <?= htmlspecialchars((string) ($order['payment_method_label'] ?? 'payment')) ?> QR">
                    <?php else: ?>
                      <span>QR pending</span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
              <?php if ($paymentStatus === 'rejected' && trim((string) ($order['rejection_reason'] ?? '')) !== ''): ?>
                <p class="order-payment-panel__reason"><strong>Rejection reason:</strong> <?= htmlspecialchars((string) $order['rejection_reason']) ?></p>
              <?php endif; ?>
              <?php if ($canUploadProof): ?>
                <p class="order-payment-panel__state">Open View details to upload your proof of payment.</p>
              <?php elseif ($isForVerification): ?>
                <p class="order-payment-panel__state">Payment submitted. Organizer verification is in progress.</p>
              <?php elseif ($isVerified): ?>
                <p class="order-payment-panel__state is-verified">Payment Verified</p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
