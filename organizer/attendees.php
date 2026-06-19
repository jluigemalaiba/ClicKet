<?php
$organizerPage = 'attendees';
$organizerTitle = 'Attendees';
require __DIR__ . '/includes/header.php';
$attendees = [];
foreach ($payload['tickets'] ?? [] as $ticket) {
    $key = strtolower((string) ($ticket['order_id'] ?? '') . '|' . (string) ($ticket['buyer_name'] ?? '') . '|' . (string) ($ticket['event_title'] ?? ''));
    if (!isset($attendees[$key])) {
        $attendees[$key] = [
            'name' => (string) ($ticket['buyer_name'] ?? 'Attendee'),
            'event' => (string) ($ticket['event_title'] ?? ''),
            'venue' => (string) ($ticket['venue'] ?? ''),
            'order' => (string) ($ticket['order_id'] ?? ''),
            'tickets' => 0,
            'status' => (string) ($ticket['status'] ?? 'Valid'),
        ];
    }
    $attendees[$key]['tickets']++;
}
?>
<section class="staff-section">
  <div class="staff-section-heading"><div><p>Attendees</p><h2>Customers attending your events</h2></div><span><?= sp_count(count($attendees)) ?> records</span></div>
  <div class="staff-table-wrap"><table class="staff-table"><thead><tr><th>Attendee</th><th>Event</th><th>Venue</th><th>Order</th><th>Tickets</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($attendees as $attendee): ?><tr data-search-row><td><strong><?= sp_h($attendee['name']) ?></strong></td><td><?= sp_h($attendee['event']) ?></td><td><?= sp_h($attendee['venue']) ?></td><td><?= sp_h($attendee['order']) ?></td><td><?= sp_count($attendee['tickets']) ?></td><td><span class="staff-status <?= sp_status_class($attendee['status']) ?>"><?= sp_h($attendee['status']) ?></span></td></tr><?php endforeach; ?>
    <?php if (!$attendees): ?><tr><td colspan="6">No attendees are available for your events.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
