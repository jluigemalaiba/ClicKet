<?php

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';
require_once __DIR__ . '/includes/order-history-data.php';
require_once __DIR__ . '/includes/ticket-data.php';

header('Content-Type: application/json');

clicketRequireRoleJson('admin', 'Admin access required.');
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
$reason = trim((string) ($_POST['reason'] ?? ''));
$actions = ['approve', 'reject', 'cancel', 'refund', 'reissue'];
if ($orderId === '' || !in_array($action, $actions, true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid order action.']);
    exit;
}
if (in_array($action, ['reject', 'cancel', 'refund'], true) && $reason === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'A reason is required for this action.']);
    exit;
}

$orders = clicketReadOrders();
$updatedOrder = null;
$actor = (string) ($staff['email'] ?? $staff['name'] ?? 'admin');

foreach ($orders as $index => $order) {
    if ((string) ($order['order_id'] ?? '') !== $orderId) {
        continue;
    }

    if (!clicketStaffCanAccessOrder($staff, $order)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Order is outside admin scope.']);
        exit;
    }

    $paymentStatus = strtolower((string) ($order['payment_status'] ?? ''));
    $orderStatus = strtolower((string) ($order['order_status'] ?? ''));
    if ($action === 'approve') {
        if (!in_array($paymentStatus, ['pending', 'pending payment', 'for verification'], true)) {
            http_response_code(409); echo json_encode(['success' => false, 'message' => 'Only pending or submitted payments can be approved.']); exit;
        }
        $order['payment_status'] = 'Payment Verified'; $order['order_status'] = 'Payment Verified';
        $order['approved_by'] = $actor; $order['approved_at'] = date('c');
        $order = clicketHydrateOrderTickets($order);
    } elseif ($action === 'reject') {
        if (!in_array($paymentStatus, ['pending', 'pending payment', 'for verification'], true)) {
            http_response_code(409); echo json_encode(['success' => false, 'message' => 'Only pending or submitted payments can be rejected.']); exit;
        }
        $order['payment_status'] = 'Rejected'; $order['order_status'] = 'Rejected';
        $order['rejected_by'] = $actor; $order['rejected_at'] = date('c'); $order['rejection_reason'] = $reason;
    } elseif ($action === 'cancel') {
        if (in_array($orderStatus, ['cancelled', 'refunded'], true)) {
            http_response_code(409); echo json_encode(['success' => false, 'message' => 'This order is already closed.']); exit;
        }
        $order['order_status'] = 'Cancelled'; $order['cancelled_by'] = $actor; $order['cancelled_at'] = date('c'); $order['cancellation_reason'] = $reason;
        foreach ((array) ($order['tickets'] ?? []) as &$ticket) $ticket['status'] = 'Cancelled'; unset($ticket);
    } elseif ($action === 'refund') {
        if (!in_array($paymentStatus, ['paid', 'payment verified'], true) || in_array($orderStatus, ['refunded', 'cancelled'], true)) {
            http_response_code(409); echo json_encode(['success' => false, 'message' => 'Only active paid orders can be refunded.']); exit;
        }
        $order['payment_status'] = 'Refunded'; $order['order_status'] = 'Refunded';
        $order['refunded_by'] = $actor; $order['refunded_at'] = date('c'); $order['refund_reason'] = $reason;
        foreach ((array) ($order['tickets'] ?? []) as &$ticket) $ticket['status'] = 'Refunded'; unset($ticket);
    } else {
        if (!in_array($paymentStatus, ['paid', 'payment verified'], true) || !in_array($orderStatus, ['confirmed', 'completed', 'payment verified'], true)) {
            http_response_code(409); echo json_encode(['success' => false, 'message' => 'Only confirmed paid orders can be reissued.']); exit;
        }
        $order['reissued_by'] = $actor; $order['reissued_at'] = date('c');
        $order['reissue_count'] = (int) ($order['reissue_count'] ?? 0) + 1;
        $order['reissue_reason'] = $reason;
        foreach ((array) ($order['tickets'] ?? []) as $ticketIndex => &$ticket) {
            $ticket['previous_ticket_id'] = $ticket['ticket_id'] ?? '';
            $ticket['ticket_id'] = 'TKT-' . strtoupper(substr(hash('sha256', $orderId . '-reissue-' . $order['reissue_count'] . '-' . $ticketIndex), 0, 12));
            $ticket['barcode_value'] = $ticket['ticket_id'];
            $ticket['validation_code'] = 'VAL-' . strtoupper(substr(hash('sha256', $ticket['ticket_id'] . $orderId), 0, 16));
            $ticket['status'] = 'Valid';
        } unset($ticket);
    }

    $labels = ['approve' => 'Payment approved', 'reject' => 'Payment rejected', 'cancel' => 'Order cancelled', 'refund' => 'Order refunded', 'reissue' => 'Tickets reissued'];
    $order['payment_logs'] = is_array($order['payment_logs'] ?? null) ? $order['payment_logs'] : [];
    $order['payment_logs'][] = ['action' => $labels[$action], 'note' => $reason !== '' ? $reason : 'No reason provided.', 'actor' => $actor, 'at' => date('c')];
    $orders[$index] = $order; $updatedOrder = $order; break;
}

if (!$updatedOrder) { http_response_code(404); echo json_encode(['success' => false, 'message' => 'Order not found.']); exit; }
if (!clicketWriteOrders($orders)) { http_response_code(500); echo json_encode(['success' => false, 'message' => 'Could not save the order update.']); exit; }

echo json_encode(['success' => true, 'message' => 'Order updated.', 'order' => ['order_id' => $updatedOrder['order_id'] ?? '', 'payment_status' => $updatedOrder['payment_status'] ?? '', 'order_status' => $updatedOrder['order_status'] ?? '', 'payment_logs' => $updatedOrder['payment_logs'] ?? []]]);
