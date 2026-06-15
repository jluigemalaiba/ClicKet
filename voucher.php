<?php

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/ticket-data.php';
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
    <a href="index.php?panel=tickets">Back to My Tickets</a>
    <div>
      <button type="button" id="voucherPrint">Print Form</button>
      <button type="button" class="is-primary" id="voucherPdf">Download PDF</button>
    </div>
  </div>

  <main class="voucher-shell">
    <?php require __DIR__ . '/includes/voucher-template.php'; ?>
  </main>

  <script src="js/vendor/html2pdf.bundle.min.js"></script>
  <script src="js/ticket-print.js"></script>
</body>
</html>

