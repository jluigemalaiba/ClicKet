<?php

$myTickets = $myTickets ?? [];
?>
<div class="ticket-detail-modal" id="ticketDetailModal" hidden>
  <div class="ticket-detail-modal__backdrop" data-ticket-close></div>
  <section class="ticket-detail-modal__panel" role="dialog" aria-modal="true" aria-labelledby="ticketDetailTitle" tabindex="-1">
    <header class="ticket-detail-modal__header">
      <div>
        <p>Digital admission ticket</p>
        <h2 id="ticketDetailTitle">Ticket Details</h2>
      </div>
      <button type="button" data-ticket-close aria-label="Close ticket details">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
      </button>
    </header>
    <div class="ticket-detail-modal__body" id="ticketDetailBody"></div>
  </section>
</div>

<script type="application/json" id="myTicketsData"><?= json_encode(
    $myTickets,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
) ?></script>

