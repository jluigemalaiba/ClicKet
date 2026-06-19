<?php

declare(strict_types=1);

require_once __DIR__ . '/log.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/payment-qr-config.php';

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

    $paymentMethod = (string) ($row['payment_method'] ?? '');
    $venueName = (string) $row['venue_name'];
    $qr = clicketPaymentQrForVenue($venueName, $paymentMethod);
    $proofName = (string) ($row['proof_file_name'] ?? '');
    $proofExists = false;
    if ($proofName !== '') {
        $proofBaseName = basename($proofName);
        $proofLocations = [
            dirname(__DIR__) . '/uploads/payment_proofs/' . $proofBaseName,
            dirname(__DIR__) . '/storage/payment-proofs/' . $proofBaseName,
        ];
        foreach ($proofLocations as $location) {
            if (is_file($location)) {
                $proofExists = true;
                break;
            }
        }
    }
    $proofUrl = $proofExists
        ? 'payment-proof-view.php?order=' . rawurlencode((string) $row['order_id'])
        : '';

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
        'venue' => $venueName,
        'seats' => $seatRows,
        'subtotal' => (int) round((float) $row['subtotal']),
        'service_fee' => (int) round((float) $row['service_fee']),
        'total' => (int) round((float) $row['total']),
        'payment_method' => $paymentMethod,
        'payment_method_label' => (string) ($row['method_label'] ?? clicketOrderPaymentMethodLabel((string) ($row['payment_method'] ?? ''))),
        'payment_account' => (string) ($row['payment_account'] ?? ''),
        'proof_of_payment' => $proofName,
        'proof_url' => $proofUrl,
        'proof_uploaded_at' => clicketDbDisplayDateTime((string) ($row['proof_uploaded_at'] ?? '')),
        'rejection_reason' => (string) ($row['proof_review_note'] ?? ''),
        'payment_qr' => $qr,
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
                pp.uploaded_at AS proof_uploaded_at, pp.review_note AS proof_review_note,
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
    $seatCodes = clicketSelectedSeatCodes($order);

    if (!$event || !$performance || !$seatCodes) {
        return true;
    }

    $placeholders = implode(',', array_fill(0, count($seatCodes), '?'));
    $params = array_merge(
        [(int) $event['id'], (int) $performance['id']],
        $seatCodes
    );
    $existing = clicketDbExecute(
        'SELECT os.seat_code
         FROM orders o
         INNER JOIN order_seats os ON os.order_id = o.id
         WHERE o.event_id = ?
           AND o.performance_id = ?
           AND o.payment_status = "approved"
           AND o.order_status IN ("approved", "completed")
           AND os.seat_code IN (' . $placeholders . ')
         LIMIT 1',
        $params
    )->fetch();

    return is_array($existing);
}

function clicketTicketStatusForOrder(array $order): string {
    $paymentStatus = strtolower((string) ($order['payment_status'] ?? ''));
    $orderStatus = strtolower((string) ($order['order_status'] ?? ''));

    if (in_array($orderStatus, ['cancelled', 'refunded', 'void', 'rejected', 'payment rejected'], true)) {
        return 'void';
    }

    return in_array($paymentStatus, ['paid', 'approved', 'payment verified'], true)
        && in_array($orderStatus, ['confirmed', 'completed', 'approved', 'payment verified'], true)
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
                    'file_path' => (string) ($order['proof_file_path'] ?? 'uploads/payment_proofs/' . $proofName),
                    'mime_type' => null,
                    'uploaded_at' => clicketDbDateTime((string) ($order['booked_at'] ?? 'now')),
                    'review_status' => $paymentStatus,
                ]
            );
        }

        $tickets = is_array($order['tickets'] ?? null) ? $order['tickets'] : [];
        $ticketStatus = clicketTicketStatusForOrder($order);
        $voucherId = (string) ($order['voucher']['voucher_id'] ?? ('VCH-' . strtoupper(substr(hash('sha256', $orderId), 0, 12))));

        foreach ($seats as $index => $seat) {
            $seatCode = (string) ($seat['id'] ?? $seat['seat_code'] ?? '');
            if ($seatCode === '') {
                continue;
            }

            $seatId = clicketDbEnsureSeat($eventKey, $seatCode, $seat);
            $ticketCode = (string) ($seat['ticket_code'] ?? '');
            if ($ticketCode === '') {
                $ticketCode = 'TKT-' . strtoupper(substr(hash('sha256', $orderId . '-' . $index), 0, 12));
            }

            clicketDbExecute(
                'INSERT INTO order_seats
                   (order_id, seat_id, seat_code, section, row_label, seat_number, category, price, ticket_code)
                 VALUES
                   (:order_id, :seat_id, :seat_code, :section, :row_label, :seat_number, :category, :price, :ticket_code)',
                [
                    'order_id' => $orderPk,
                    'seat_id' => $seatId,
                    'seat_code' => $seatCode,
                    'section' => (string) ($seat['section'] ?? ''),
                    'row_label' => (string) ($seat['row'] ?? $seat['row_label'] ?? ''),
                    'seat_number' => (string) ($seat['number'] ?? $seat['seat_number'] ?? ''),
                    'category' => (string) ($seat['category'] ?? 'Admission'),
                    'price' => clicketDbMoneyValue($seat['price'] ?? 0),
                    'ticket_code' => $ticketCode,
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
                'SELECT id FROM orders WHERE order_id = :order_id LIMIT 1',
                ['order_id' => $orderId]
            );
            if (!$existing) {
                continue;
            }

            $approvedStaffId = clicketOrderStaffIdFromField($order, 'approved_by');
            $rejectedStaffId = clicketOrderStaffIdFromField($order, 'rejected_by');
            $paymentStatus = clicketDbNormalizePaymentStatus((string) ($order['payment_status'] ?? 'pending'));
            $orderStatus = clicketDbNormalizeOrderStatus((string) ($order['order_status'] ?? 'pending'));

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
                    'approved_at' => !empty($order['approved_at']) ? clicketDbDateTime((string) $order['approved_at']) : null,
                    'rejected_by_staff_id' => $rejectedStaffId,
                    'rejected_at' => !empty($order['rejected_at']) ? clicketDbDateTime((string) $order['rejected_at']) : null,
                ]
            );

            clicketDbExecute(
                'UPDATE payments
                 SET status = :status,
                     reviewed_by_staff_id = COALESCE(:staff_id, reviewed_by_staff_id),
                     reviewed_at = COALESCE(:reviewed_at, reviewed_at)
                 WHERE order_id = :order_id',
                [
                    'order_id' => (int) $existing['id'],
                    'status' => $paymentStatus,
                    'staff_id' => $approvedStaffId ?? $rejectedStaffId,
                    'reviewed_at' => !empty($order['approved_at'])
                        ? clicketDbDateTime((string) $order['approved_at'])
                        : (!empty($order['rejected_at']) ? clicketDbDateTime((string) $order['rejected_at']) : null),
                ]
            );

            clicketDbExecute(
                'UPDATE payment_proofs
                 SET review_status = :status,
                     review_note = CASE WHEN :review_note <> "" THEN :review_note ELSE review_note END
                 WHERE id = (
                     SELECT proof_id FROM (
                         SELECT id AS proof_id
                         FROM payment_proofs
                         WHERE order_id = :order_id_for_proof
                         ORDER BY id DESC
                         LIMIT 1
                     ) latest_proof
                 )',
                [
                    'status' => $paymentStatus,
                    'review_note' => (string) ($order['rejection_reason'] ?? ''),
                    'order_id_for_proof' => (int) $existing['id'],
                ]
            );

            clicketDbExecute(
                'UPDATE tickets
                 SET status = :status
                 WHERE order_id = :order_id',
                [
                    'order_id' => (int) $existing['id'],
                    'status' => clicketTicketStatusForOrder($order),
                ]
            );
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
           AND o.payment_status = "approved"
           AND o.order_status IN ("approved", "completed")
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

    return in_array($normalized, ['paid', 'payment verified', 'confirmed', 'completed', 'approved'], true)
        ? 'is-success'
        : (in_array($normalized, ['pending', 'pending payment', 'processing', 'under review', 'for verification', 'payment submitted'], true) ? 'is-pending' : 'is-neutral');
}
