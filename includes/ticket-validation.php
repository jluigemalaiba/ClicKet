<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/log.php';

function clicketTicketScanInput(string $value): string {
    return strtoupper((string) preg_replace('/\s+/', '', trim($value)));
}

function clicketTicketRowByValidationCode(string $validationCode, bool $lock = false): ?array {
    $sql = 'SELECT t.*,
                   o.order_id AS public_order_id,
                   o.buyer_name,
                   o.buyer_email,
                   o.payment_status,
                   o.order_status,
                   e.id AS event_pk,
                   e.event_key,
                   e.title AS event_title,
                   e.created_by_staff_id,
                   v.name AS venue_name,
                   ep.performance_date,
                   ep.performance_time
            FROM tickets t
            INNER JOIN orders o ON o.id = t.order_id
            INNER JOIN events e ON e.id = o.event_id
            INNER JOIN venues v ON v.id = e.venue_id
            INNER JOIN event_performances ep ON ep.id = o.performance_id
            WHERE t.validation_code = :validation_code
            LIMIT 1';
    if ($lock) {
        $sql .= ' FOR UPDATE';
    }

    return clicketDbFetch($sql, ['validation_code' => $validationCode]);
}

function clicketTicketRowByBarcodeValue(string $ticketIdOrBarcode, bool $lock = false): ?array {
    $sql = 'SELECT t.*,
                   o.order_id AS public_order_id,
                   o.buyer_name,
                   o.buyer_email,
                   o.payment_status,
                   o.order_status,
                   e.id AS event_pk,
                   e.event_key,
                   e.title AS event_title,
                   e.created_by_staff_id,
                   v.name AS venue_name,
                   ep.performance_date,
                   ep.performance_time
            FROM tickets t
            INNER JOIN orders o ON o.id = t.order_id
            INNER JOIN events e ON e.id = o.event_id
            INNER JOIN venues v ON v.id = e.venue_id
            INNER JOIN event_performances ep ON ep.id = o.performance_id
            WHERE t.ticket_id = :ticket_id OR t.barcode_value = :barcode_value
            ORDER BY CASE WHEN t.ticket_id = :sort_ticket_id THEN 0 ELSE 1 END
            LIMIT 1';
    if ($lock) {
        $sql .= ' FOR UPDATE';
    }

    return clicketDbFetch($sql, [
        'ticket_id' => $ticketIdOrBarcode,
        'barcode_value' => $ticketIdOrBarcode,
        'sort_ticket_id' => $ticketIdOrBarcode,
    ]);
}

function clicketTicketStaffCanAccessRow(array $staff, array $ticket): bool {
    if (($staff['role'] ?? '') === 'admin') {
        return true;
    }

    $staffId = clicketDbStaffIdBySession($staff);
    if (!$staffId) {
        return false;
    }

    return (int) ($ticket['created_by_staff_id'] ?? 0) === $staffId;
}

function clicketTicketValidationPayload(array $ticket): array {
    return [
        'ticket_id' => (string) ($ticket['ticket_id'] ?? ''),
        'validation_code' => (string) ($ticket['validation_code'] ?? ''),
        'barcode_value' => (string) ($ticket['barcode_value'] ?? ''),
        'status' => clicketDbDisplayTicketStatus((string) ($ticket['status'] ?? '')),
        'raw_status' => (string) ($ticket['status'] ?? ''),
        'order_id' => (string) ($ticket['public_order_id'] ?? ''),
        'event_title' => (string) ($ticket['event_title'] ?? ''),
        'event_key' => (string) ($ticket['event_key'] ?? ''),
        'venue' => (string) ($ticket['venue_name'] ?? ''),
        'performance_date' => (string) ($ticket['performance_date'] ?? ''),
        'performance_time' => (string) ($ticket['performance_time'] ?? ''),
        'buyer_name' => (string) ($ticket['buyer_name'] ?? ''),
        'section' => (string) ($ticket['section'] ?? ''),
        'row_label' => (string) ($ticket['row_label'] ?? ''),
        'seat_number' => (string) ($ticket['seat_number'] ?? ''),
        'category' => (string) ($ticket['category'] ?? ''),
        'price' => (float) ($ticket['price'] ?? 0),
        'issued_at' => clicketDbDisplayDateTime((string) ($ticket['issued_at'] ?? '')),
        'used_at' => clicketDbDisplayDateTime((string) ($ticket['used_at'] ?? '')),
    ];
}

function clicketRecordCheckinLog(?int $ticketPk, string $validationCode, ?int $staffId, string $scanResult, string $message, string $gateName = ''): void {
    $loggedCode = substr($validationCode !== '' ? $validationCode : 'UNKNOWN', 0, 190);
    $loggedGate = trim(substr($gateName, 0, 120));
    $loggedMessage = substr($message, 0, 255);

    clicketDbExecute(
        'INSERT INTO checkin_logs
           (ticket_id, validation_code, scanned_by_staff_id, gate_name, scan_result, message, scanned_at)
         VALUES
           (:ticket_id, :validation_code, :scanned_by_staff_id, :gate_name, :scan_result, :message, UTC_TIMESTAMP())',
        [
            'ticket_id' => $ticketPk,
            'validation_code' => $loggedCode,
            'scanned_by_staff_id' => $staffId,
            'gate_name' => $loggedGate !== '' ? $loggedGate : null,
            'scan_result' => $scanResult,
            'message' => $loggedMessage,
        ]
    );
}

function clicketTicketValidationResult(bool $success, string $scanResult, string $message, ?array $ticket = null, int $httpStatus = 200): array {
    return [
        'success' => $success,
        'scan_result' => $scanResult,
        'message' => $message,
        'ticket' => $ticket ? clicketTicketValidationPayload($ticket) : null,
        'http_status' => $httpStatus,
    ];
}

function clicketValidateTicketForEntry(array $staff, string $validationCodeInput, string $ticketIdInput, string $gateName = ''): array {
    $validationCode = clicketTicketScanInput($validationCodeInput);
    $ticketId = clicketTicketScanInput($ticketIdInput);
    $gateName = trim($gateName);

    if ($validationCode === '' && $ticketId === '') {
        return clicketTicketValidationResult(false, 'invalid', 'Enter a validation code or ticket ID.', null, 422);
    }

    $staffId = clicketDbStaffIdBySession($staff);
    $pdo = clicketDb();
    $pdo->beginTransaction();

    try {
        $ticket = null;
        $identifierMismatch = false;

        if ($validationCode !== '') {
            $ticket = clicketTicketRowByValidationCode($validationCode, true);
            if ($ticket && $ticketId !== '') {
                $matchesTicketId = hash_equals((string) $ticket['ticket_id'], $ticketId)
                    || hash_equals((string) $ticket['barcode_value'], $ticketId);
                $identifierMismatch = !$matchesTicketId;
            }
        } elseif ($ticketId !== '') {
            $ticket = clicketTicketRowByBarcodeValue($ticketId, true);
        }

        $logCode = $ticket ? (string) $ticket['validation_code'] : ($validationCode !== '' ? $validationCode : $ticketId);

        if (!$ticket) {
            $message = 'Ticket record was not found.';
            clicketRecordCheckinLog(null, $logCode, $staffId, 'invalid', $message, $gateName);
            $pdo->commit();
            return clicketTicketValidationResult(false, 'invalid', $message, null, 404);
        }

        if (!clicketTicketStaffCanAccessRow($staff, $ticket)) {
            $message = 'Ticket is outside your authorized event scope.';
            clicketRecordCheckinLog((int) $ticket['id'], $logCode, $staffId, 'blocked', $message, $gateName);
            $pdo->commit();
            return clicketTicketValidationResult(false, 'blocked', $message, $ticket, 403);
        }

        if ($identifierMismatch) {
            $message = 'Validation code and ticket ID do not match the same ticket.';
            clicketRecordCheckinLog((int) $ticket['id'], $logCode, $staffId, 'invalid', $message, $gateName);
            $pdo->commit();
            return clicketTicketValidationResult(false, 'invalid', $message, $ticket, 409);
        }

        $status = strtolower((string) ($ticket['status'] ?? ''));
        if ($status === 'used' || !empty($ticket['used_at'])) {
            $message = 'Ticket has already been used.';
            clicketRecordCheckinLog((int) $ticket['id'], $logCode, $staffId, 'already_used', $message, $gateName);
            $pdo->commit();
            return clicketTicketValidationResult(false, 'already_used', $message, $ticket, 409);
        }

        if ($status !== 'active') {
            $message = match ($status) {
                'cancelled' => 'Ticket is cancelled.',
                'void' => 'Ticket is void.',
                default => 'Ticket is not active for entry.',
            };
            clicketRecordCheckinLog((int) $ticket['id'], $logCode, $staffId, 'blocked', $message, $gateName);
            $pdo->commit();
            return clicketTicketValidationResult(false, 'blocked', $message, $ticket, 409);
        }

        $update = clicketDbExecute(
            'UPDATE tickets
             SET status = "used", used_at = UTC_TIMESTAMP()
             WHERE id = :id AND status = "active" AND used_at IS NULL',
            ['id' => (int) $ticket['id']]
        );

        if ($update->rowCount() !== 1) {
            $message = 'Ticket has already been used.';
            $fresh = clicketTicketRowByValidationCode((string) $ticket['validation_code'], true) ?: $ticket;
            clicketRecordCheckinLog((int) $ticket['id'], $logCode, $staffId, 'already_used', $message, $gateName);
            $pdo->commit();
            return clicketTicketValidationResult(false, 'already_used', $message, $fresh, 409);
        }

        $ticket['status'] = 'used';
        $ticket['used_at'] = clicketDbDateTime();
        $message = 'Ticket accepted. Entry recorded.';
        clicketRecordCheckinLog((int) $ticket['id'], $logCode, $staffId, 'valid', $message, $gateName);
        $pdo->commit();

        return clicketTicketValidationResult(true, 'valid', $message, $ticket, 200);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function clicketRecordTicketPrintByPublicId(string $ticketPublicId, ?array $staff = null, string $context = 'customer'): void {
    $identifier = clicketTicketScanInput($ticketPublicId);
    if ($identifier === '') {
        return;
    }

    $ticket = clicketTicketRowByBarcodeValue($identifier, false);
    if (!$ticket) {
        return;
    }

    $staffId = $staff ? clicketDbStaffIdBySession($staff) : null;
    clicketDbExecute(
        'INSERT INTO ticket_print_logs
           (ticket_id, order_id, printed_by_staff_id, print_context, printed_at)
         VALUES
           (:ticket_id, :order_id, :printed_by_staff_id, :print_context, UTC_TIMESTAMP())',
        [
            'ticket_id' => (int) $ticket['id'],
            'order_id' => (int) $ticket['order_id'],
            'printed_by_staff_id' => $staffId,
            'print_context' => substr($context, 0, 80),
        ]
    );
}

function clicketStaffAttendanceMetrics(array $staff): array {
    $isAdmin = ($staff['role'] ?? '') === 'admin';
    $staffId = clicketDbStaffIdBySession($staff) ?? 0;
    $ticketWhere = '';
    $ticketParams = [];

    if (!$isAdmin) {
        $ticketWhere = ' WHERE e.created_by_staff_id = :staff_id';
        $ticketParams['staff_id'] = $staffId;
    }

    $ticketCounts = clicketDbFetch(
        'SELECT COUNT(*) AS total_tickets,
                SUM(CASE WHEN t.status = "used" OR t.used_at IS NOT NULL THEN 1 ELSE 0 END) AS checked_in,
                SUM(CASE WHEN t.status = "active" THEN 1 ELSE 0 END) AS active_tickets
         FROM tickets t
         INNER JOIN orders o ON o.id = t.order_id
         INNER JOIN events e ON e.id = o.event_id' . $ticketWhere,
        $ticketParams
    ) ?: [];

    if ($isAdmin) {
        $logSql = 'SELECT COUNT(*) AS scan_attempts,
                          SUM(CASE WHEN cl.scan_result = "valid" THEN 1 ELSE 0 END) AS valid_scans,
                          SUM(CASE WHEN cl.scan_result = "already_used" THEN 1 ELSE 0 END) AS duplicate_scans,
                          SUM(CASE WHEN cl.scan_result = "invalid" THEN 1 ELSE 0 END) AS invalid_scans,
                          SUM(CASE WHEN cl.scan_result = "blocked" THEN 1 ELSE 0 END) AS blocked_scans,
                          MAX(cl.scanned_at) AS last_scan_at
                   FROM checkin_logs cl';
        $logParams = [];
    } else {
        $logSql = 'SELECT COUNT(*) AS scan_attempts,
                          SUM(CASE WHEN cl.scan_result = "valid" THEN 1 ELSE 0 END) AS valid_scans,
                          SUM(CASE WHEN cl.scan_result = "already_used" THEN 1 ELSE 0 END) AS duplicate_scans,
                          SUM(CASE WHEN cl.scan_result = "invalid" THEN 1 ELSE 0 END) AS invalid_scans,
                          SUM(CASE WHEN cl.scan_result = "blocked" THEN 1 ELSE 0 END) AS blocked_scans,
                          MAX(cl.scanned_at) AS last_scan_at
                   FROM checkin_logs cl
                   INNER JOIN tickets t ON t.id = cl.ticket_id
                   INNER JOIN orders o ON o.id = t.order_id
                   INNER JOIN events e ON e.id = o.event_id
                   WHERE e.created_by_staff_id = :staff_id';
        $logParams = ['staff_id' => $staffId];
    }

    $logCounts = clicketDbFetch($logSql, $logParams) ?: [];
    $total = (int) ($ticketCounts['total_tickets'] ?? 0);
    $checkedIn = (int) ($ticketCounts['checked_in'] ?? 0);

    return [
        'total_tickets' => $total,
        'checked_in' => $checkedIn,
        'active_tickets' => (int) ($ticketCounts['active_tickets'] ?? 0),
        'still_unused' => max(0, $total - $checkedIn),
        'attendance_rate' => $total > 0 ? (int) round(($checkedIn / $total) * 100) : 0,
        'scan_attempts' => (int) ($logCounts['scan_attempts'] ?? 0),
        'valid_scans' => (int) ($logCounts['valid_scans'] ?? 0),
        'duplicate_scans' => (int) ($logCounts['duplicate_scans'] ?? 0),
        'invalid_scans' => (int) ($logCounts['invalid_scans'] ?? 0),
        'blocked_scans' => (int) ($logCounts['blocked_scans'] ?? 0),
        'last_scan_at' => clicketDbDisplayDateTime((string) ($logCounts['last_scan_at'] ?? '')),
    ];
}
