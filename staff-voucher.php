<?php

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';
require_once __DIR__ . '/includes/ticket-data.php';
require_once __DIR__ . '/includes/voucher-generator.php';

$staff = currentStaff();
if (!$staff) {
    header('Location: auth.php?mode=admin');
    exit;
}

$orderId = trim((string) ($_GET['order'] ?? ''));
$ticketId = trim((string) ($_GET['ticket'] ?? ''));
$records = [];

foreach (clicketReadOrders() as $order) {
    if (!clicketStaffCanAccessOrder($staff, $order)) {
        continue;
    }

    $hydrated = clicketHydrateOrderTickets($order);
    if ($orderId !== '' && (string) ($hydrated['order_id'] ?? '') !== $orderId) {
        continue;
    }

    foreach ($hydrated['tickets'] as $ticket) {
        if ($ticketId !== '' && (string) ($ticket['ticket_id'] ?? '') !== $ticketId) {
            continue;
        }
        $records[] = ['order' => $hydrated, 'ticket' => $ticket];
    }
}

if (!$records) {
    http_response_code(404);
    exit('Ticket voucher not found or outside your assigned venue scope.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Ticket Print | ClicKet</title>
  <link rel="stylesheet" href="css/voucher.css">
  <style>
    @media print {
      .voucher-toolbar { display: none; }
      .voucher-shell + .voucher-shell { break-before: page; }
    }
  </style>
</head>
<body class="voucher-page">
  <div class="voucher-toolbar">
    <a href="staff-panel.php#tickets:onsite" class="voucher-toolbar__back">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
      <span>Back to Staff Panel</span>
    </a>
    <div>
      <button type="button" id="voucherPrint">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
        <span>Print <?= count($records) ?> Ticket<?= count($records) === 1 ? '' : 's' ?></span>
      </button>
    </div>
  </div>

  <?php foreach ($records as $voucherRecord): ?>
    <main class="voucher-shell">
      <?php require __DIR__ . '/includes/voucher-template.php'; ?>
    </main>
  <?php endforeach; ?>

  <script src="js/ticket-print.js"></script>
</body>
</html>
