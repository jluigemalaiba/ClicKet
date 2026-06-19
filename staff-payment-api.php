<?php

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';
require_once __DIR__ . '/includes/order-history-data.php';
require_once __DIR__ . '/includes/ticket-data.php';

header('Content-Type: application/json');

$staff = currentStaff();
if (!$staff) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin login required.']);
    exit;
}

if (($staff['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST required.']);
    exit;
}

$orderId = trim((string) ($_POST['order_id'] ?? ''));
$action = strtolower(trim((string) ($_POST['action'] ?? '')));

if ($orderId === '' || !in_array($action, ['approve', 'reject'], true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid payment action.']);
    exit;
}

$orders = clicketReadOrders();
$updatedOrder = null;

foreach ($orders as $index => $order) {
    if ((string) ($order['order_id'] ?? '') !== $orderId) {
        continue;
    }

    if (!clicketStaffCanAccessOrder($staff, $order)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Order is outside admin scope.']);
        exit;
    }

    if ($action === 'approve') {
        $order['payment_status'] = 'Paid';
        $order['order_status'] = 'Confirmed';
        $order['approved_by'] = (string) ($staff['email'] ?? $staff['name'] ?? 'staff');
        $order['approved_at'] = date('c');
        $order = clicketHydrateOrderTickets($order);
    } else {
        $order['payment_status'] = 'Failed';
        $order['order_status'] = 'Payment Rejected';
        $order['rejected_by'] = (string) ($staff['email'] ?? $staff['name'] ?? 'staff');
        $order['rejected_at'] = date('c');
    }

    $orders[$index] = $order;
    $updatedOrder = $order;
    break;
}

if (!$updatedOrder) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit;
}

if (!clicketWriteOrders($orders)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save order status.']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => $action === 'approve' ? 'Payment approved.' : 'Payment rejected.',
    'order' => [
        'order_id' => $updatedOrder['order_id'] ?? '',
        'payment_status' => $updatedOrder['payment_status'] ?? '',
        'order_status' => $updatedOrder['order_status'] ?? '',
    ],
]);
