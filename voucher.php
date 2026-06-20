<?php

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/ticket-data.php';
require_once __DIR__ . '/includes/ticket-validation.php';
require_once __DIR__ . '/includes/voucher-generator.php';

if (!isLoggedIn()) {
    header('Location: auth.php?mode=login');
    exit;
}

$ticketId = trim((string) ($_GET['ticket'] ?? ''));
$user = currentUser();
$voucherRecord = clicketTicketForUser($ticketId, (string) ($user['id'] ?? ''));

if (!$voucherRecord) {
    http_response_code(404);
    exit('Ticket voucher not found.');
}

foreach ($voucherRecord['tickets'] ?? [$voucherRecord['ticket']] as $printedTicket) {
    clicketRecordTicketPrintByPublicId((string) ($printedTicket['ticket_id'] ?? ''), null, 'customer_voucher');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($ticketId) ?> Voucher | ClicKet</title>
  <link rel="stylesheet" href="css/voucher.css">
</head>
<body class="voucher-page">
  <div class="voucher-toolbar">
    <a href="index.php?panel=tickets" class="voucher-toolbar__back">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
      <span>Back to My Tickets</span>
    </a>
    <div>
      <button type="button" id="voucherPrint">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
        <span>Print Form</span>
      </button>
      <button type="button" class="is-primary" id="voucherPdf">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
        <span>Download PDF</span>
      </button>
    </div>
  </div>

  <main class="voucher-shell">
    <?php require __DIR__ . '/includes/voucher-template.php'; ?>
  </main>

  <script src="js/vendor/html2pdf.bundle.min.js"></script>
  <script src="js/ticket-print.js"></script>
</body>
</html>

