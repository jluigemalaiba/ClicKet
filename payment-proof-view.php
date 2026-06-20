<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/order-history-data.php';
require_once __DIR__ . '/includes/staff-panel-data.php';

$orderId = trim((string) ($_GET['order'] ?? ''));
if ($orderId === '') {
    http_response_code(404);
    exit;
}

$order = null;
foreach (clicketReadOrders() as $candidate) {
    if ((string) ($candidate['order_id'] ?? '') === $orderId) {
        $order = $candidate;
        break;
    }
}
if (!$order) {
    http_response_code(404);
    exit;
}

$allowed = false;
$auth = currentAuth();
if (($auth['role'] ?? '') === 'customer') {
    $allowed = (string) ($auth['user_id'] ?? '') === (string) ($order['user_id'] ?? '')
        || strtolower((string) ($auth['email'] ?? '')) === strtolower((string) ($order['buyer_email'] ?? ''));
} elseif (in_array((string) ($auth['role'] ?? ''), ['admin', 'organizer'], true)) {
    $staff = currentStaff();
    $allowed = is_array($staff) && clicketStaffCanAccessOrder($staff, $order);
}

if (!$allowed) {
    http_response_code($auth ? 403 : 401);
    exit;
}

$proofName = basename((string) ($order['proof_of_payment'] ?? ''));
if ($proofName === '') {
    http_response_code(404);
    exit;
}

$paths = [
    __DIR__ . '/uploads/payment_proofs/' . $proofName,
    __DIR__ . '/storage/payment-proofs/' . $proofName,
];
$proofPath = '';
foreach ($paths as $path) {
    if (is_file($path)) {
        $proofPath = $path;
        break;
    }
}
if ($proofPath === '') {
    http_response_code(404);
    exit;
}

$mime = function_exists('mime_content_type') ? mime_content_type($proofPath) : 'application/octet-stream';
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    http_response_code(415);
    exit;
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($proofPath));
header('Content-Disposition: inline; filename="payment-proof.' . pathinfo($proofName, PATHINFO_EXTENSION) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, max-age=0');
readfile($proofPath);
