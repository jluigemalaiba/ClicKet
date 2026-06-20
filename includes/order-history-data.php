<?php

declare(strict_types=1);

require_once __DIR__ . '/log.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/inventory-sync.php';

function clicketEnsureOrderStore(): void {
    clicketDb();
}

function clicketOrderPaymentMethodLabel(string $method): string {
    return [
        'visa' => 'Visa',
        'mastercard' => 'Mastercard',
        'jcb' => 'JCB',
        'gcash' => 'GCash',
        'maya' => 'Maya',
        'bpi' => 'BPI Online',
        'bdo' => 'BDO Online',
        'qrph' => 'QR Ph',
    ][$method] ?? ($method !== '' ? ucfirst($method) : 'Manual Review');
}

function clicketOrderRowToApp(array $row): array {
    $seats = clicketDbFetchAll(
        'SELECT os.*, s.seat_code AS db_seat_code
         FROM order_seats os
         INNER JOIN seats s ON s.id = os.seat_id
         WHERE os.order_id = :order_pk
         ORDER BY os.id',
        ['order_pk' => (int) $row['id']]
    );
    $tickets = clicketDbFetchAll(
        'SELECT *
         FROM tickets
         WHERE order_id = :order_pk
         ORDER BY id',
        ['order_pk' => (int) $row['id']]
    );

    $seatRows = array_map(static function (array $seat): array {
        return [
            'id' => (string) ($seat['seat_code'] ?: $seat['db_seat_code']),
            'section' => (string) $seat['section'],
            'row' => (string) ($seat['row_label'] ?? ''),
            'row_label' => (string) ($seat['row_label'] ?? ''),
            'number' => (string) ($seat['seat_number'] ?? ''),
            'seat_number' => (string) ($seat['seat_number'] ?? ''),
            'category' => (string) ($seat['category'] ?? 'Admission'),
            'price' => (int) round((float) $seat['price']),
            'ticket_code' => (string) ($seat['ticket_code'] ?? ''),
        ];
    }, $seats);

    $ticketRows = array_map(static function (array $ticket): array {
        return [
            'ticket_id' => (string) $ticket['ticket_id'],
            'voucher_id' => (string) ($ticket['voucher_id'] ?? ''),
            'validation_code' => (string) $ticket['validation_code'],
            'barcode_value' => (string) $ticket['barcode_value'],
            'status' => clicketDbDisplayTicketStatus((string) $ticket['status']),
            'issued_at' => clicketDbDisplayDateTime((string) $ticket['issued_at']),
            'used_at' => clicketDbDisplayDateTime((string) ($ticket['used_at'] ?? '')),
            'section' => (string) $ticket['section'],
            'row' => (string) ($ticket['row_label'] ?? ''),
            'row_label' => (string) ($ticket['row_label'] ?? ''),
            'number' => (string) ($ticket['seat_number'] ?? ''),
            'seat_number' => (string) ($ticket['seat_number'] ?? ''),
            'category' => (string) ($ticket['category'] ?? 'Admission'),
            'price' => (int) round((float) $ticket['price']),
        ];
    }, $tickets);

    $voucherId = $ticketRows[0]['voucher_id'] ?? '';
    if ($voucherId === '') {
        $voucherId = 'VCH-' . strtoupper(substr(hash('sha256', (string) $row['order_id']), 0, 12));
    }

    return [
        'db_id' => (int) $row['id'],
        'order_id' => (string) $row['order_id'],
        'reference' => (string) ($row['reference'] ?? ''),
        'payment_reference' => (string) ($row['payment_reference'] ?? $row['payment_row_reference'] ?? ''),
        'user_id' => (string) $row['user_id'],
        'buyer_name' => (string) $row['buyer_name'],
        'buyer_email' => (string) $row['buyer_email'],
        'event' => (string) $row['event_key'],
        'event_title' => (string) $row['event_title'],
        'event_poster' => (string) ($row['poster_url'] ?? ''),
        'event_banner' => (string) ($row['banner_url'] ?? $row['poster_url'] ?? ''),
        'event_date' => clicketDbDisplayDate((string) $row['performance_date']),
        'event_time' => clicketDbDisplayTime((string) $row['performance_time']),
        'venue' => (string) $row['venue_name'],
        'seats' => $seatRows,
        'subtotal' => (int) round((float) $row['subtotal']),
        'service_fee' => (int) round((float) $row['service_fee']),
        'total' => (int) round((float) $row['total']),
        'payment_method' => (string) ($row['payment_method'] ?? ''),
        'payment_method_label' => (string) ($row['method_label'] ?? clicketOrderPaymentMethodLabel((string) ($row['payment_method'] ?? ''))),
        'payment_account' => (string) ($row['payment_account'] ?? ''),
        'proof_of_payment' => (string) ($row['proof_file_name'] ?? ''),
        'proof_file_path' => (string) ($row['proof_file_path'] ?? ''),
        'proof_review_status' => (string) ($row['proof_review_status'] ?? ''),
        'proof_review_note' => (string) ($row['proof_review_note'] ?? ''),
        'non_transferable' => (bool) $row['non_transferable'],
        'payment_status' => clicketDbDisplayPaymentStatus((string) $row['payment_status']),
        'order_status' => clicketDbDisplayOrderStatus((string) $row['order_status']),
        'booked_at' => clicketDbDisplayDateTime((string) $row['booked_at']),
        'approved_by' => (string) ($row['approved_by_email'] ?? ''),
        'approved_at' => clicketDbDisplayDateTime((string) ($row['approved_at'] ?? '')),
        'rejected_by' => (string) ($row['rejected_by_email'] ?? ''),
        'rejected_at' => clicketDbDisplayDateTime((string) ($row['rejected_at'] ?? '')),
        'voucher' => [
            'voucher_id' => $voucherId,
            'format_version' => 1,
            'issued_at' => clicketDbDisplayDateTime((string) $row['booked_at']),
            'notice' => 'Tickets are non-transferable and linked to the purchasing ClicKet account.',
        ],
        'tickets' => $ticketRows,
    ];
}

function clicketReadOrders(): array {
    $rows = clicketDbFetchAll(
        'SELECT o.*,
                e.event_key, e.title AS event_title, e.poster_url, e.banner_url,
                v.name AS venue_name,
                ep.performance_date, ep.performance_time,
                p.payment_reference AS payment_row_reference, p.method AS payment_method,
                p.method_label, p.payment_account,
                pp.file_name AS proof_file_name, pp.file_path AS proof_file_path,
                pp.review_status AS proof_review_status, pp.review_note AS proof_review_note,
                approved.email AS approved_by_email,
                rejected.email AS rejected_by_email
         FROM orders o
         INNER JOIN events e ON e.id = o.event_id
         INNER JOIN venues v ON v.id = e.venue_id
         INNER JOIN event_performances ep ON ep.id = o.performance_id
         LEFT JOIN payments p ON p.id = (
             SELECT p2.id FROM payments p2
             WHERE p2.order_id = o.id
             ORDER BY p2.id DESC
             LIMIT 1
         )
         LEFT JOIN payment_proofs pp ON pp.id = (
             SELECT pp2.id FROM payment_proofs pp2
             WHERE pp2.order_id = o.id
             ORDER BY pp2.id DESC
             LIMIT 1
         )
         LEFT JOIN staff_accounts approved ON approved.id = o.approved_by_staff_id
         LEFT JOIN staff_accounts rejected ON rejected.id = o.rejected_by_staff_id
         ORDER BY o.booked_at DESC, o.id DESC'
    );

    return array_map('clicketOrderRowToApp', $rows);
}

function clicketSelectedSeatCodes(array $order): array {
    return array_values(array_unique(array_filter(array_map(
        static fn (array $seat): string => (string) ($seat['id'] ?? $seat['seat_code'] ?? ''),
        is_array($order['seats'] ?? null) ? $order['seats'] : []
    ))));
}

function clicketResolveOrderUserId(array $order): ?int {
    $userId = clicketDbUserIdFromSession((string) ($order['user_id'] ?? ''));
    if ($userId) {
        return $userId;
    }

    $email = (string) ($order['buyer_email'] ?? '');
    return $email !== '' ? clicketDbUserIdByEmail($email) : null;
}

function clicketResolveOrderPerformance(string $eventKey, array $order): ?array {
    $date = trim((string) ($order['event_date'] ?? ''));
    $time = trim((string) ($order['event_time'] ?? ''));

    if ($date !== '' && $time !== '') {
        return clicketDbEnsurePerformance($eventKey, $date, $time);
    }

    return clicketDbPerformanceByIndex($eventKey, 0);
}

function clicketOrderHasSeatConflict(array $order): bool {
    $eventKey = (string) ($order['event'] ?? '');
    $event = clicketDbEventByKey($eventKey);
    $performance = clicketResolveOrderPerformance($eventKey, $order);
    $seats = is_array($order['seats'] ?? null) ? $order['seats'] : [];

    if (!$event || !$performance || !$seats) {
        return true;
    }

    $seatIds = [];
    foreach ($seats as $seat) {
        $seatCode = (string) ($seat['id'] ?? $seat['seat_code'] ?? '');
        if ($seatCode === '') {
            continue;
        }
        $seatIds[] = clicketDbEnsureSeat($eventKey, $seatCode, $seat);
    }

    if (!$seatIds) {
        return true;
    }

    $reservation = function_exists('clicketReservation') ? clicketReservation() : null;
    $ownToken = is_array($reservation) ? (string) ($reservation['token'] ?? '') : '';

    return clicketInventoryUnavailableSeatIds(
        (int) $event['id'],
        (int) $performance['id'],
        $seatIds,
        $ownToken !== '' ? $ownToken : null
    ) !== [];
}

function clicketPreparedOrderSeats(string $eventKey, array $seats): array {
    $prepared = [];
    foreach ($seats as $seat) {
        $seatCode = (string) ($seat['id'] ?? $seat['seat_code'] ?? '');
        if ($seatCode === '') {
            continue;
        }

        $seat['__seat_code'] = $seatCode;
        $seat['__seat_id'] = clicketDbEnsureSeat($eventKey, $seatCode, $seat);
        $prepared[] = $seat;
    }

    return $prepared;
}

function clicketOrderSeatIds(array $preparedSeats): array {
    return array_values(array_unique(array_filter(array_map(
        static fn (array $seat): int => (int) ($seat['__seat_id'] ?? 0),
        $preparedSeats
    ))));
}

function clicketSetOrderSeatsStatus(int $orderPk, string $status): void {
    $reservationStatus = in_array($status, ['held', 'sold'], true) ? $status : 'released';
    $activeReservationKey = in_array($status, ['held', 'sold'], true) ? 'active' : null;

    clicketDbExecute(
        'UPDATE order_seats
         SET reservation_status = :reservation_status,
             active_reservation_key = :active_reservation_key
         WHERE order_id = :order_id',
        [
            'reservation_status' => $reservationStatus,
            'active_reservation_key' => $activeReservationKey,
            'order_id' => $orderPk,
        ]
    );

    if (in_array($status, ['held', 'sold'], true)) {
        clicketDbExecute(
            'UPDATE seats s
             INNER JOIN order_seats os ON os.seat_id = s.id
             SET s.status = :status
             WHERE os.order_id = :order_id',
            ['status' => $status, 'order_id' => $orderPk]
        );
        return;
    }

    clicketDbExecute(
        'UPDATE seats s
         INNER JOIN order_seats os ON os.seat_id = s.id
         SET s.status = "available"
         WHERE os.order_id = :order_id
           AND NOT EXISTS (
             SELECT 1
             FROM order_seats os2
             WHERE os2.seat_id = s.id
               AND os2.active_reservation_key = "active"
           )
           AND NOT EXISTS (
             SELECT 1
             FROM seat_blocks sb
             WHERE sb.event_id = os.event_id
               AND sb.performance_id = os.performance_id
               AND sb.seat_id = s.id
               AND sb.status = "active"
           )',
        ['order_id' => $orderPk]
    );
}

function clicketTicketStatusForOrder(array $order): string {
    $paymentStatus = strtolower((string) ($order['payment_status'] ?? ''));
    $orderStatus = strtolower((string) ($order['order_status'] ?? ''));

    if (in_array($orderStatus, ['cancelled', 'refunded', 'void', 'rejected', 'payment rejected'], true)) {
        return 'void';
    }

    return in_array($paymentStatus, ['paid', 'approved'], true)
        && in_array($orderStatus, ['confirmed', 'completed', 'approved'], true)
            ? 'active'
            : 'issued';
}

function clicketOrderStaffIdFromField(array $order, string $field): ?int {
    $value = trim((string) ($order[$field] ?? ''));
    if ($value === '') {
        return null;
    }

    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return clicketDbStaffIdByEmail($value);
    }

    return null;
}

function clicketOrderReviewNote(array $order, string $paymentStatus): string {
    $note = '';
    if ($paymentStatus === 'rejected') {
        $note = trim((string) ($order['rejection_reason'] ?? ''));
    }

    $logs = is_array($order['payment_logs'] ?? null) ? $order['payment_logs'] : [];
    if ($note === '' && $logs) {
        $lastLog = end($logs);
        if (is_array($lastLog)) {
            $note = trim((string) ($lastLog['note'] ?? ''));
        }
    }

    if ($note !== '' && strtolower($note) !== 'no reason provided.') {
        return $note;
    }

    return $paymentStatus === 'approved'
        ? 'Payment approved.'
        : ($paymentStatus === 'rejected' ? 'Payment rejected.' : 'Awaiting staff review.');
}

function clicketSyncPaymentReviewRecords(int $orderPk, array $order, string $paymentStatus, ?int $reviewedByStaffId, ?string $reviewedAt): void {
    $reviewStatus = in_array($paymentStatus, ['pending', 'under_review', 'approved', 'rejected'], true)
        ? $paymentStatus
        : 'pending';
    $reviewNote = clicketOrderReviewNote($order, $reviewStatus);

    clicketDbExecute(
        'UPDATE payments
         SET status = :status,
             reviewed_by_staff_id = COALESCE(:staff_id, reviewed_by_staff_id),
             reviewed_at = COALESCE(:reviewed_at, reviewed_at)
         WHERE order_id = :order_id',
        [
            'order_id' => $orderPk,
            'status' => $reviewStatus,
            'staff_id' => $reviewedByStaffId,
            'reviewed_at' => $reviewedAt,
        ]
    );

    clicketDbExecute(
        'UPDATE payment_proofs
         SET review_status = :review_status,
             review_note = CASE
               WHEN review_status <> :review_status_compare OR review_note IS NULL OR review_note = "" THEN :review_note
               ELSE review_note
             END
         WHERE order_id = :order_id',
        [
            'order_id' => $orderPk,
            'review_status' => $reviewStatus,
            'review_status_compare' => $reviewStatus,
            'review_note' => $reviewNote,
        ]
    );
}

function clicketUniqueTicketValue(string $prefix, string $seed, string $column): string {
    for ($attempt = 0; $attempt < 8; $attempt++) {
        $candidate = $prefix . '-' . strtoupper(substr(hash('sha256', $seed . '-' . $attempt), 0, $prefix === 'VAL' ? 16 : 12));
        if (!clicketDbFetch('SELECT id FROM tickets WHERE ' . $column . ' = :value LIMIT 1', ['value' => $candidate])) {
            return $candidate;
        }
    }

    return $prefix . '-' . strtoupper(bin2hex(random_bytes($prefix === 'VAL' ? 8 : 6)));
}

function clicketEnsureOrderTickets(int $orderPk, array $order): void {
    $existing = clicketDbFetch(
        'SELECT order_id, booked_at FROM orders WHERE id = :order_id LIMIT 1',
        ['order_id' => $orderPk]
    );
    if (!$existing) {
        return;
    }

    $ticketStatus = clicketTicketStatusForOrder($order);
    $voucherId = (string) ($order['voucher']['voucher_id'] ?? ('VCH-' . strtoupper(substr(hash('sha256', (string) $existing['order_id']), 0, 12))));
    $rows = clicketDbFetchAll(
        'SELECT os.*
         FROM order_seats os
         LEFT JOIN tickets t ON t.order_id = os.order_id AND t.seat_id = os.seat_id
         WHERE os.order_id = :order_id
           AND t.id IS NULL
         ORDER BY os.id',
        ['order_id' => $orderPk]
    );

    foreach ($rows as $index => $seat) {
        $seed = (string) $existing['order_id'] . '-' . (string) $seat['id'] . '-' . (string) $index;
        $ticketId = trim((string) ($seat['ticket_code'] ?? ''));
        if ($ticketId === '' || clicketDbFetch('SELECT id FROM tickets WHERE ticket_id = :ticket_id LIMIT 1', ['ticket_id' => $ticketId])) {
            $ticketId = clicketUniqueTicketValue('TKT', $seed, 'ticket_id');
        }

        $validationCode = clicketUniqueTicketValue('VAL', $ticketId . '-' . $seed, 'validation_code');

        clicketDbExecute(
            'INSERT INTO tickets
               (ticket_id, order_id, seat_id, voucher_id, validation_code, barcode_value, status,
                section, row_label, seat_number, category, price, issued_at, used_at, reissued_from_ticket_id)
             VALUES
               (:ticket_id, :order_id, :seat_id, :voucher_id, :validation_code, :barcode_value, :status,
                :section, :row_label, :seat_number, :category, :price, :issued_at, NULL, NULL)',
            [
                'ticket_id' => $ticketId,
                'order_id' => $orderPk,
                'seat_id' => (int) $seat['seat_id'],
                'voucher_id' => $voucherId,
                'validation_code' => $validationCode,
                'barcode_value' => $ticketId,
                'status' => $ticketStatus,
                'section' => (string) ($seat['section'] ?? ''),
                'row_label' => (string) ($seat['row_label'] ?? ''),
                'seat_number' => (string) ($seat['seat_number'] ?? ''),
                'category' => (string) ($seat['category'] ?? 'Admission'),
                'price' => clicketDbMoneyValue($seat['price'] ?? 0),
                'issued_at' => clicketDbDateTime((string) ($existing['booked_at'] ?? 'now')),
            ]
        );
    }
}

function clicketSaveOrder(array $order, bool $allowDuplicateSeats = false): bool {
    clicketEnsureOrderStore();

    $orderId = trim((string) ($order['order_id'] ?? ''));
    $eventKey = trim((string) ($order['event'] ?? ''));
    $event = clicketDbEventByKey($eventKey);
    $performance = clicketResolveOrderPerformance($eventKey, $order);
    $userId = clicketResolveOrderUserId($order);
    $seats = is_array($order['seats'] ?? null) ? $order['seats'] : [];

    if ($orderId === '' || !$event || !$performance || !$userId || !$seats) {
        return false;
    }

    if (clicketDbFetch('SELECT id FROM orders WHERE order_id = :order_id LIMIT 1', ['order_id' => $orderId])) {
        return true;
    }

    $preparedSeats = clicketPreparedOrderSeats($eventKey, $seats);
    if (!$preparedSeats) {
        return false;
    }

    if (!$allowDuplicateSeats && clicketOrderHasSeatConflict($order)) {
        return false;
    }

    $pdo = clicketDb();
    $pdo->beginTransaction();

    try {
        $approvedStaffId = clicketOrderStaffIdFromField($order, 'approved_by');
        $rejectedStaffId = clicketOrderStaffIdFromField($order, 'rejected_by');
        $paymentStatus = clicketDbNormalizePaymentStatus((string) ($order['payment_status'] ?? 'pending'));
        $orderStatus = clicketDbNormalizeOrderStatus((string) ($order['order_status'] ?? 'pending'));
        $seatReservationStatus = $paymentStatus === 'approved' && in_array($orderStatus, ['approved', 'completed'], true)
            ? 'sold'
            : (($paymentStatus === 'pending' || $paymentStatus === 'under_review') && $orderStatus === 'pending' ? 'held' : 'released');
        $activeReservationKey = in_array($seatReservationStatus, ['held', 'sold'], true) ? 'active' : null;
        $seatIds = clicketOrderSeatIds($preparedSeats);
        if (!$allowDuplicateSeats && $seatIds) {
            $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
            clicketDbExecute(
                'SELECT id FROM seats WHERE id IN (' . $placeholders . ') ORDER BY id FOR UPDATE',
                $seatIds
            )->fetchAll();
            $reservation = function_exists('clicketReservation') ? clicketReservation() : null;
            $ownToken = is_array($reservation) ? (string) ($reservation['token'] ?? '') : '';
            if (clicketInventoryUnavailableSeatIds(
                (int) $event['id'],
                (int) $performance['id'],
                $seatIds,
                $ownToken !== '' ? $ownToken : null,
                null,
                true
            )) {
                $pdo->rollBack();
                return false;
            }
        }

        clicketDbExecute(
            'INSERT INTO orders
               (order_id, reference, payment_reference, user_id, buyer_name, buyer_email, event_id, performance_id,
                subtotal, service_fee, total, non_transferable, payment_status, order_status, booked_at,
                approved_by_staff_id, approved_at, rejected_by_staff_id, rejected_at, archived_at)
             VALUES
               (:order_id, :reference, :payment_reference, :user_id, :buyer_name, :buyer_email, :event_id, :performance_id,
                :subtotal, :service_fee, :total, :non_transferable, :payment_status, :order_status, :booked_at,
                :approved_by_staff_id, :approved_at, :rejected_by_staff_id, :rejected_at, :archived_at)',
            [
                'order_id' => $orderId,
                'reference' => (string) ($order['reference'] ?? $order['payment_reference'] ?? ''),
                'payment_reference' => (string) ($order['payment_reference'] ?? $order['reference'] ?? ''),
                'user_id' => $userId,
                'buyer_name' => (string) ($order['buyer_name'] ?? 'ClicKet Customer'),
                'buyer_email' => (string) ($order['buyer_email'] ?? ''),
                'event_id' => (int) $event['id'],
                'performance_id' => (int) $performance['id'],
                'subtotal' => clicketDbMoneyValue($order['subtotal'] ?? 0),
                'service_fee' => clicketDbMoneyValue($order['service_fee'] ?? 0),
                'total' => clicketDbMoneyValue($order['total'] ?? 0),
                'non_transferable' => !empty($order['non_transferable']) ? 1 : 0,
                'payment_status' => $paymentStatus,
                'order_status' => $orderStatus,
                'booked_at' => clicketDbDateTime((string) ($order['booked_at'] ?? 'now')),
                'approved_by_staff_id' => $approvedStaffId,
                'approved_at' => !empty($order['approved_at']) ? clicketDbDateTime((string) $order['approved_at']) : null,
                'rejected_by_staff_id' => $rejectedStaffId,
                'rejected_at' => !empty($order['rejected_at']) ? clicketDbDateTime((string) $order['rejected_at']) : null,
                'archived_at' => !empty($order['archived_at']) ? clicketDbDateTime((string) $order['archived_at']) : null,
            ]
        );
        $orderPk = (int) $pdo->lastInsertId();

        clicketDbExecute(
            'INSERT INTO payments
               (order_id, payment_reference, method, method_label, payment_account, amount, status,
                reviewed_by_staff_id, reviewed_at, created_at)
             VALUES
               (:order_id, :payment_reference, :method, :method_label, :payment_account, :amount, :status,
                :reviewed_by_staff_id, :reviewed_at, :created_at)',
            [
                'order_id' => $orderPk,
                'payment_reference' => (string) ($order['payment_reference'] ?? $order['reference'] ?? $orderId),
                'method' => (string) ($order['payment_method'] ?? 'manual'),
                'method_label' => (string) ($order['payment_method_label'] ?? clicketOrderPaymentMethodLabel((string) ($order['payment_method'] ?? 'manual'))),
                'payment_account' => (string) ($order['payment_account'] ?? ''),
                'amount' => clicketDbMoneyValue($order['total'] ?? 0),
                'status' => $paymentStatus,
                'reviewed_by_staff_id' => $approvedStaffId ?? $rejectedStaffId,
                'reviewed_at' => !empty($order['approved_at'])
                    ? clicketDbDateTime((string) $order['approved_at'])
                    : (!empty($order['rejected_at']) ? clicketDbDateTime((string) $order['rejected_at']) : null),
                'created_at' => clicketDbDateTime((string) ($order['booked_at'] ?? 'now')),
            ]
        );
        $paymentPk = (int) $pdo->lastInsertId();

        $proofName = trim((string) ($order['proof_of_payment'] ?? ''));
        if ($proofName !== '') {
            clicketDbExecute(
                'INSERT INTO payment_proofs
                   (payment_id, order_id, file_name, file_path, mime_type, uploaded_at, review_status)
                 VALUES
                   (:payment_id, :order_id, :file_name, :file_path, :mime_type, :uploaded_at, :review_status)',
                [
                    'payment_id' => $paymentPk,
                    'order_id' => $orderPk,
                    'file_name' => $proofName,
                    'file_path' => (string) ($order['proof_file_path'] ?? 'storage/payment-proofs/' . $proofName),
                    'mime_type' => null,
                    'uploaded_at' => clicketDbDateTime((string) ($order['booked_at'] ?? 'now')),
                    'review_status' => $paymentStatus,
                ]
            );
        }

        $tickets = is_array($order['tickets'] ?? null) ? $order['tickets'] : [];
        $ticketStatus = clicketTicketStatusForOrder($order);
        $voucherId = (string) ($order['voucher']['voucher_id'] ?? ('VCH-' . strtoupper(substr(hash('sha256', $orderId), 0, 12))));

        foreach ($preparedSeats as $index => $seat) {
            $seatCode = (string) ($seat['__seat_code'] ?? '');
            if ($seatCode === '') {
                continue;
            }

            $seatId = (int) ($seat['__seat_id'] ?? 0);
            $ticketCode = (string) ($seat['ticket_code'] ?? '');
            if ($ticketCode === '') {
                $ticketCode = 'TKT-' . strtoupper(substr(hash('sha256', $orderId . '-' . $index), 0, 12));
            }

            clicketDbExecute(
                'INSERT INTO order_seats
                   (order_id, event_id, performance_id, seat_id, seat_code, section, row_label, seat_number, category, price, ticket_code, reservation_status, active_reservation_key)
                 VALUES
                   (:order_id, :event_id, :performance_id, :seat_id, :seat_code, :section, :row_label, :seat_number, :category, :price, :ticket_code, :reservation_status, :active_reservation_key)',
                [
                    'order_id' => $orderPk,
                    'event_id' => (int) $event['id'],
                    'performance_id' => (int) $performance['id'],
                    'seat_id' => $seatId,
                    'seat_code' => $seatCode,
                    'section' => (string) ($seat['section'] ?? ''),
                    'row_label' => (string) ($seat['row'] ?? $seat['row_label'] ?? ''),
                    'seat_number' => (string) ($seat['number'] ?? $seat['seat_number'] ?? ''),
                    'category' => (string) ($seat['category'] ?? 'Admission'),
                    'price' => clicketDbMoneyValue($seat['price'] ?? 0),
                    'ticket_code' => $ticketCode,
                    'reservation_status' => $seatReservationStatus,
                    'active_reservation_key' => $activeReservationKey,
                ]
            );

            $ticket = $tickets[$index] ?? [];
            $ticketId = (string) ($ticket['ticket_id'] ?? $ticketCode);
            $validationCode = (string) ($ticket['validation_code'] ?? '');
            if ($validationCode === '') {
                $validationCode = 'VAL-' . strtoupper(substr(hash('sha256', $ticketId . $orderId), 0, 16));
            }

            clicketDbExecute(
                'INSERT INTO tickets
                   (ticket_id, order_id, seat_id, voucher_id, validation_code, barcode_value, status,
                    section, row_label, seat_number, category, price, issued_at, used_at, reissued_from_ticket_id)
                 VALUES
                   (:ticket_id, :order_id, :seat_id, :voucher_id, :validation_code, :barcode_value, :status,
                    :section, :row_label, :seat_number, :category, :price, :issued_at, :used_at, :reissued_from_ticket_id)',
                [
                    'ticket_id' => $ticketId,
                    'order_id' => $orderPk,
                    'seat_id' => $seatId,
                    'voucher_id' => (string) ($ticket['voucher_id'] ?? $voucherId),
                    'validation_code' => $validationCode,
                    'barcode_value' => (string) ($ticket['barcode_value'] ?? $ticketId),
                    'status' => clicketDbNormalizeTicketStatus((string) ($ticket['status'] ?? $ticketStatus)),
                    'section' => (string) ($ticket['section'] ?? $seat['section'] ?? ''),
                    'row_label' => (string) ($ticket['row'] ?? $ticket['row_label'] ?? $seat['row'] ?? $seat['row_label'] ?? ''),
                    'seat_number' => (string) ($ticket['number'] ?? $ticket['seat_number'] ?? $seat['number'] ?? $seat['seat_number'] ?? ''),
                    'category' => (string) ($ticket['category'] ?? $seat['category'] ?? 'Admission'),
                    'price' => clicketDbMoneyValue($ticket['price'] ?? $seat['price'] ?? 0),
                    'issued_at' => clicketDbDateTime((string) ($ticket['issued_at'] ?? $order['booked_at'] ?? 'now')),
                    'used_at' => !empty($ticket['used_at']) ? clicketDbDateTime((string) $ticket['used_at']) : null,
                    'reissued_from_ticket_id' => $ticket['reissued_from_ticket_id'] ?? null,
                ]
            );

            if ($paymentStatus === 'approved' && in_array($orderStatus, ['approved', 'completed'], true)) {
                clicketDbExecute(
                    'UPDATE seats SET status = "sold" WHERE id = :seat_id',
                    ['seat_id' => $seatId]
                );
            } elseif ($paymentStatus === 'pending' || $paymentStatus === 'under_review') {
                clicketDbExecute(
                    'UPDATE seats SET status = "held" WHERE id = :seat_id AND status <> "blocked"',
                    ['seat_id' => $seatId]
                );
            }
        }

        $reservation = function_exists('clicketReservation') ? clicketReservation() : null;
        if (is_array($reservation) && !empty($reservation['token'])) {
            clicketDbExecute(
                'UPDATE seat_holds SET status = "converted" WHERE token = :token',
                ['token' => (string) $reservation['token']]
            );
        }

        $pdo->commit();
        clicketInventorySyncEventPerformance((int) $event['id'], (int) $performance['id']);
        return true;
    } catch (Throwable) {
        $pdo->rollBack();
        return false;
    }
}

function clicketWriteOrders(array $orders): bool {
    $pdo = clicketDb();
    $pdo->beginTransaction();

    try {
        foreach ($orders as $order) {
            $orderId = (string) ($order['order_id'] ?? '');
            if ($orderId === '') {
                continue;
            }

            $existing = clicketDbFetch(
                'SELECT id, event_id, performance_id FROM orders WHERE order_id = :order_id LIMIT 1',
                ['order_id' => $orderId]
            );
            if (!$existing) {
                continue;
            }

            $approvedStaffId = clicketOrderStaffIdFromField($order, 'approved_by');
            $rejectedStaffId = clicketOrderStaffIdFromField($order, 'rejected_by');
            $paymentStatus = clicketDbNormalizePaymentStatus((string) ($order['payment_status'] ?? 'pending'));
            $orderStatus = clicketDbNormalizeOrderStatus((string) ($order['order_status'] ?? 'pending'));
            $reviewedAt = !empty($order['approved_at'])
                ? clicketDbDateTime((string) $order['approved_at'])
                : (!empty($order['rejected_at']) ? clicketDbDateTime((string) $order['rejected_at']) : null);
            $reviewedByStaffId = $approvedStaffId ?? $rejectedStaffId;

            clicketDbExecute(
                'UPDATE orders
                 SET payment_status = :payment_status,
                     order_status = :order_status,
                     approved_by_staff_id = COALESCE(:approved_by_staff_id, approved_by_staff_id),
                     approved_at = COALESCE(:approved_at, approved_at),
                     rejected_by_staff_id = COALESCE(:rejected_by_staff_id, rejected_by_staff_id),
                     rejected_at = COALESCE(:rejected_at, rejected_at)
                 WHERE id = :id',
                [
                    'id' => (int) $existing['id'],
                    'payment_status' => $paymentStatus,
                    'order_status' => $orderStatus,
                    'approved_by_staff_id' => $approvedStaffId,
                    'approved_at' => !empty($order['approved_at']) ? $reviewedAt : null,
                    'rejected_by_staff_id' => $rejectedStaffId,
                    'rejected_at' => !empty($order['rejected_at']) ? $reviewedAt : null,
                ]
            );

            clicketSyncPaymentReviewRecords((int) $existing['id'], $order, $paymentStatus, $reviewedByStaffId, $reviewedAt);
            clicketEnsureOrderTickets((int) $existing['id'], $order);

            clicketDbExecute(
                'UPDATE tickets
                 SET status = :status
                 WHERE order_id = :order_id',
                [
                    'order_id' => (int) $existing['id'],
                    'status' => clicketTicketStatusForOrder($order),
                ]
            );

            if ($paymentStatus === 'approved' && in_array($orderStatus, ['approved', 'completed'], true)) {
                clicketSetOrderSeatsStatus((int) $existing['id'], 'sold');
            } elseif ($paymentStatus === 'rejected' || $orderStatus === 'rejected') {
                clicketSetOrderSeatsStatus((int) $existing['id'], 'available');
            }

            clicketInventorySyncEventPerformance((int) $existing['event_id'], (int) $existing['performance_id']);
        }

        $pdo->commit();
        return true;
    } catch (Throwable) {
        $pdo->rollBack();
        return false;
    }
}

function clicketBookedSeatIds(string $eventKey, string $eventDate, string $eventTime): array {
    $event = clicketDbEventByKey($eventKey);
    $performance = clicketDbPerformanceByLabels($eventKey, $eventDate, $eventTime);
    if (!$event || !$performance) {
        return [];
    }

    $rows = clicketDbFetchAll(
        'SELECT DISTINCT os.seat_code
         FROM orders o
         INNER JOIN order_seats os ON os.order_id = o.id
         WHERE o.event_id = :event_id
           AND o.performance_id = :performance_id
           AND os.active_reservation_key = "active"
         ORDER BY os.seat_code',
        [
            'event_id' => (int) $event['id'],
            'performance_id' => (int) $performance['id'],
        ]
    );

    return array_values(array_map(static fn (array $row): string => (string) $row['seat_code'], $rows));
}

function clicketOrdersForUser(string $userId): array {
    $dbUserId = clicketDbUserIdFromSession($userId);
    if (!$dbUserId) {
        $current = currentUser();
        $dbUserId = is_array($current) && !empty($current['email'])
            ? clicketDbUserIdByEmail((string) $current['email'])
            : null;
    }
    if (!$dbUserId) {
        return [];
    }

    return array_values(array_filter(
        clicketReadOrders(),
        static fn (array $order): bool => (string) ($order['user_id'] ?? '') === (string) $dbUserId
    ));
}

function clicketOrderDate(string $date, string $format = 'M j, Y, g:i A'): string {
    try {
        return (new DateTimeImmutable($date))
            ->setTimezone(new DateTimeZone('Asia/Manila'))
            ->format($format);
    } catch (Throwable) {
        return $date;
    }
}

function clicketOrderStatusClass(string $status): string {
    $normalized = strtolower(trim($status));

    return in_array($normalized, ['paid', 'confirmed', 'completed', 'approved'], true)
        ? 'is-success'
        : (in_array($normalized, ['pending', 'processing', 'under review'], true) ? 'is-pending' : 'is-neutral');
}
