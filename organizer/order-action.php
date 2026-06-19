<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once dirname(__DIR__) . '/includes/ticket-data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit;
}

$orderId = trim((string) ($_POST['order_id'] ?? ''));
$action = strtolower(trim((string) ($_POST['action'] ?? '')));
$reason = trim((string) ($_POST['reason'] ?? ''));

if ($orderId === '' || !in_array($action, ['approve', 'reject'], true)) {
    setFlashMessage('error', 'Invalid order action.');
    header('Location: orders.php');
    exit;
}
if ($action === 'reject' && $reason === '') {
    setFlashMessage('error', 'Add a rejection reason.');
    header('Location: orders.php');
    exit;
}

$orders = clicketReadOrders();
$updated = false;
$actor = (string) ($staff['email'] ?? $staff['name'] ?? 'organizer');

foreach ($orders as $index => $order) {
    if ((string) ($order['order_id'] ?? '') !== $orderId) {
        continue;
    }
    if (!clicketStaffCanAccessOrder($staff, $order)) {
        setFlashMessage('error', 'That order is outside your event scope.');
        header('Location: orders.php');
        exit;
    }

    $status = strtolower((string) ($order['payment_status'] ?? ''));
    if (!in_array($status, ['for verification', 'pending payment'], true)) {
        setFlashMessage('error', 'This order is not ready for verification.');
        header('Location: orders.php');
        exit;
    }
    if ((string) ($order['proof_of_payment'] ?? '') === '') {
        setFlashMessage('error', 'A proof image is required before verification.');
        header('Location: orders.php');
        exit;
    }

    if ($action === 'approve') {
        $order['payment_status'] = 'Payment Verified';
        $order['order_status'] = 'Payment Verified';
        $order['approved_by'] = $actor;
        $order['approved_at'] = date('c');
        $order['rejection_reason'] = '';
        $order = clicketHydrateOrderTickets($order);
    } else {
        $order['payment_status'] = 'Rejected';
        $order['order_status'] = 'Rejected';
        $order['rejected_by'] = $actor;
        $order['rejected_at'] = date('c');
        $order['rejection_reason'] = $reason;
    }

    $order['payment_logs'] = is_array($order['payment_logs'] ?? null) ? $order['payment_logs'] : [];
    $order['payment_logs'][] = [
        'action' => $action === 'approve' ? 'Payment approved' : 'Payment rejected',
        'note' => $reason !== '' ? $reason : 'Verified by organizer.',
        'actor' => $actor,
        'at' => date('c'),
    ];
    $orders[$index] = $order;
    $updated = true;
    break;
}

if (!$updated) {
    setFlashMessage('error', 'Order not found.');
    header('Location: orders.php');
    exit;
}

if (!clicketWriteOrders($orders)) {
    setFlashMessage('error', 'Could not save the order update.');
    header('Location: orders.php');
    exit;
}

setFlashMessage('success', $action === 'approve' ? 'Payment approved.' : 'Payment rejected.');
header('Location: orders.php');
exit;
