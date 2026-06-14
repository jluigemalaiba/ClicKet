<?php

require_once __DIR__ . '/order-history-data.php';
require_once __DIR__ . '/ticketing.php';

function clicketTicketStatus(array $order): string {
    $paymentStatus = strtolower((string) ($order['payment_status'] ?? ''));
    $orderStatus = strtolower((string) ($order['order_status'] ?? ''));

    if (in_array($orderStatus, ['cancelled', 'refunded', 'void'], true)) {
        return 'Invalid';
    }

    return $paymentStatus === 'paid' && in_array($orderStatus, ['confirmed', 'completed'], true)
        ? 'Valid'
        : 'Pending';
}

function clicketHydrateOrderTickets(array $order): array {
    $resolved = clicketResolveEvent((string) ($order['event'] ?? ''));
    $orderId = (string) ($order['order_id'] ?? 'CKO-UNKNOWN');
    $bookedAt = (string) ($order['booked_at'] ?? date('c'));
    $seats = is_array($order['seats'] ?? null) ? $order['seats'] : [];
    $tickets = is_array($order['tickets'] ?? null) ? $order['tickets'] : [];
    $status = clicketTicketStatus($order);

    $order['event_poster'] = (string) ($order['event_poster'] ?? ($resolved['poster'] ?? 'assets/Icon_Logo.png'));
    $order['event_banner'] = (string) ($order['event_banner'] ?? ($resolved['banner'] ?? $order['event_poster']));
    $order['voucher'] = array_merge([
        'voucher_id' => 'VCH-' . strtoupper(substr(hash('sha256', $orderId), 0, 12)),
        'format_version' => 1,
        'issued_at' => $bookedAt,
        'notice' => 'Tickets are non-transferable and linked to the purchasing ClicKet account.',
    ], is_array($order['voucher'] ?? null) ? $order['voucher'] : []);

    if (!$tickets) {
        foreach ($seats as $index => $seat) {
            $ticketId = (string) ($seat['ticket_code'] ?? '');
            if ($ticketId === '') {
                $ticketId = 'TKT-' . strtoupper(substr(hash('sha256', $orderId . '-' . $index), 0, 12));
            }

            $tickets[] = [
                'ticket_id' => $ticketId,
                'voucher_id' => 'VCH-' . strtoupper(substr(hash('sha256', $ticketId), 0, 12)),
                'validation_code' => 'VAL-' . strtoupper(substr(hash('sha256', $ticketId . $orderId), 0, 16)),
                'barcode_value' => $ticketId,
                'status' => $status,
                'issued_at' => $bookedAt,
                'section' => (string) ($seat['section'] ?? ''),
                'row' => (string) ($seat['row'] ?? ''),
                'number' => (string) ($seat['number'] ?? ''),
                'category' => (string) ($seat['category'] ?? 'Admission'),
                'price' => (int) ($seat['price'] ?? 0),
            ];
        }
    } else {
        foreach ($tickets as $index => &$ticket) {
            $ticketId = (string) ($ticket['ticket_id'] ?? ($seats[$index]['ticket_code'] ?? ''));
            if ($ticketId === '') {
                $ticketId = 'TKT-' . strtoupper(substr(hash('sha256', $orderId . '-' . $index), 0, 12));
            }

            $ticket = array_merge([
                'ticket_id' => $ticketId,
                'voucher_id' => 'VCH-' . strtoupper(substr(hash('sha256', $ticketId), 0, 12)),
                'validation_code' => 'VAL-' . strtoupper(substr(hash('sha256', $ticketId . $orderId), 0, 16)),
                'barcode_value' => $ticketId,
                'status' => $status,
                'issued_at' => $bookedAt,
                'section' => (string) ($seats[$index]['section'] ?? ''),
                'row' => (string) ($seats[$index]['row'] ?? ''),
                'number' => (string) ($seats[$index]['number'] ?? ''),
                'category' => (string) ($seats[$index]['category'] ?? 'Admission'),
                'price' => (int) ($seats[$index]['price'] ?? 0),
            ], $ticket);
        }
        unset($ticket);
    }

    $order['tickets'] = $tickets;

    return $order;
}

function clicketTicketsForUser(string $userId): array {
    $orders = clicketReadOrders();
    $changed = false;
    $tickets = [];

    foreach ($orders as $orderIndex => $order) {
        $hydrated = clicketHydrateOrderTickets($order);
        if ($hydrated !== $order) {
            $orders[$orderIndex] = $hydrated;
            $changed = true;
        }

        if (!hash_equals((string) ($hydrated['user_id'] ?? ''), $userId)) {
            continue;
        }

        foreach ($hydrated['tickets'] as $ticket) {
            if (($ticket['status'] ?? '') !== 'Valid') {
                continue;
            }

            $tickets[] = [
                'order' => $hydrated,
                'ticket' => $ticket,
            ];
        }
    }

    if ($changed) {
        clicketWriteOrders($orders);
    }

    usort($tickets, fn(array $left, array $right): int => strcmp(
        (string) ($right['order']['booked_at'] ?? ''),
        (string) ($left['order']['booked_at'] ?? '')
    ));

    return $tickets;
}

function clicketTicketForUser(string $ticketId, string $userId): ?array {
    foreach (clicketTicketsForUser($userId) as $record) {
        if (hash_equals((string) ($record['ticket']['ticket_id'] ?? ''), $ticketId)) {
            return $record;
        }
    }

    return null;
}
