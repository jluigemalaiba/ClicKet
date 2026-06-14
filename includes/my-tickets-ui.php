<?php

$myTickets = $myTickets ?? [];
?>
<section class="my-tickets-list" aria-label="Purchased tickets">
  <?php if (!$myTickets): ?>
    <div class="my-tickets-empty">
      <span>
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 0 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 0 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2M13 17v2M13 11v2"/></svg>
      </span>
      <h3>No tickets yet</h3>
      <p>Successfully paid tickets will appear here automatically.</p>
      <a href="events.php">Browse events</a>
    </div>
  <?php else: ?>
    <?php foreach ($myTickets as $ticketRecord): ?>
      <?php require __DIR__ . '/ticket-card.php'; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

