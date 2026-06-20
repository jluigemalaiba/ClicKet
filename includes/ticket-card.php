<?php

$order = $ticketRecord['order'] ?? [];
$ticket = $ticketRecord['ticket'] ?? [];
$tickets = is_array($ticketRecord['tickets'] ?? null) ? $ticketRecord['tickets'] : [$ticket];
$ticketCount = count($tickets);
$seatLabels = array_map(static fn (array $item): string => trim(
    (string) ($item['section'] ?? '') . ' · Row ' . (string) ($item['row'] ?? '') . ' · Seat ' . (string) ($item['number'] ?? '')
), $tickets);
?>
<article class="my-ticket-card" data-ticket-id="<?= htmlspecialchars((string) ($order['order_id'] ?? $ticket['ticket_id'] ?? '')) ?>">
  <button class="my-ticket-card__open" type="button" data-ticket-open="<?= htmlspecialchars((string) ($ticket['ticket_id'] ?? '')) ?>" aria-label="View <?= htmlspecialchars((string) ($order['event_title'] ?? 'ticket')) ?> details">
    <span class="my-ticket-card__poster">
      <img src="<?= htmlspecialchars((string) ($order['event_poster'] ?? 'assets/Icon_Logo.png')) ?>" alt="<?= htmlspecialchars((string) ($order['event_title'] ?? 'Event')) ?> poster">
      <span class="my-ticket-card__status is-<?= strtolower((string) ($ticket['status'] ?? 'valid')) ?>"><?= htmlspecialchars((string) ($ticket['status'] ?? 'Valid')) ?></span>
    </span>
    <span class="my-ticket-card__content">
      <span class="my-ticket-card__eyebrow"><?= $ticketCount ?> <?= $ticketCount === 1 ? 'Ticket' : 'Tickets' ?> &middot; One transaction</span>
      <strong><?= htmlspecialchars((string) ($order['event_title'] ?? 'ClicKet Event')) ?></strong>
      <span><?= htmlspecialchars(trim((string) ($order['event_date'] ?? '') . ' at ' . (string) ($order['event_time'] ?? ''))) ?></span>
      <span><?= htmlspecialchars((string) ($order['venue'] ?? '')) ?></span>
      <span class="my-ticket-card__seat">
        <?= htmlspecialchars(implode(' / ', $seatLabels)) ?>
      </span>
      <span class="my-ticket-card__id"><?= htmlspecialchars((string) ($order['order_id'] ?? '')) ?></span>
    </span>
  </button>
  <a class="my-ticket-card__print" href="voucher.php?ticket=<?= urlencode((string) ($ticket['ticket_id'] ?? '')) ?>" target="_blank" rel="noopener">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
    Print Form
  </a>
</article>
