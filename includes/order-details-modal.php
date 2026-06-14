<?php

$orderHistory = $orderHistory ?? [];
?>
<div class="order-modal" id="orderDetailsModal" hidden>
  <div class="order-modal__backdrop" data-order-close></div>
  <section class="order-modal__panel" role="dialog" aria-modal="true" aria-labelledby="orderModalTitle" tabindex="-1">
    <header class="order-modal__header">
      <div>
        <p>Confirmed booking</p>
        <h2 id="orderModalTitle">Order details</h2>
      </div>
      <button type="button" class="order-modal__close" data-order-close aria-label="Close order details">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
      </button>
    </header>
    <div class="order-modal__body" id="orderModalBody"></div>
  </section>
</div>

<script type="application/json" id="orderHistoryData"><?= json_encode(
    $orderHistory,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
) ?></script>

