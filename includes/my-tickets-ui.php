<?php

$myTickets = $myTickets ?? [];
?>
<section class="my-tickets-list" aria-label="Purchased tickets">
  <?php if (!$myTickets): ?>
    <div class="my-tickets-empty">
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

