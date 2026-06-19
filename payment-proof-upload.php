<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/payment-proof-service.php';

clicketRequireRole('customer', 'Please sign in to upload payment proof.');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user = currentUser();
$orderId = trim((string) ($_POST['order_id'] ?? ''));
$userId = clicketDbUserIdFromSession((string) ($user['id'] ?? '')) ?? clicketDbUserIdByEmail((string) ($user['email'] ?? '')) ?? 0;

if ($orderId === '' || $userId <= 0) {
    setFlashMessage('error', 'Unable to identify your order.');
    header('Location: index.php?panel=orders');
    exit;
}

$stored = clicketPaymentProofStore($_FILES['payment_proof'] ?? []);
if (!$stored['success']) {
    setFlashMessage('error', (string) ($stored['error'] ?? 'Proof upload failed.'));
    header('Location: index.php?panel=orders');
    exit;
}

if (!clicketPaymentProofAttach($orderId, $userId, $stored, (string) ($user['email'] ?? 'customer'))) {
    $storedPath = __DIR__ . '/' . ltrim((string) ($stored['file_path'] ?? ''), '/');
    if (is_file($storedPath)) {
        unlink($storedPath);
    }
    setFlashMessage('error', 'This order cannot accept a new payment proof.');
    header('Location: index.php?panel=orders');
    exit;
}

setFlashMessage('success', 'Payment proof uploaded. Your order is now for verification.');
header('Location: index.php?panel=orders');
exit;
