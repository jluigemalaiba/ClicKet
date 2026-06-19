<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

if (!defined('CLICKET_RESERVATION_SECONDS')) {
    define('CLICKET_RESERVATION_SECONDS', 15 * 60);
}

function clicketExpireOldSeatHolds(): void {
    clicketDbExecute(
        'UPDATE seat_holds
         SET status = "expired"
         WHERE status = "active" AND expires_at <= UTC_TIMESTAMP()'
    );
}

function clicketReservation(): ?array {
    $reservation = $_SESSION['clicket_reservation'] ?? null;

    return is_array($reservation) ? $reservation : null;
}

function clicketClearReservation(bool $clearSelection = true): void {
    $reservation = clicketReservation();
    $token = (string) ($reservation['token'] ?? '');
    if ($token !== '') {
        clicketDbExecute(
            'UPDATE seat_holds SET status = "released" WHERE token = :token AND status = "active"',
            ['token' => $token]
        );
    }

    unset($_SESSION['clicket_reservation']);

    if ($clearSelection) {
        unset($_SESSION['clicket_ticket_selection']);
    }
}

function clicketStartReservation(string $eventKey, int $performance): array {
    clicketExpireOldSeatHolds();

    $performance = max(0, min(3, $performance));
    $reservation = clicketReservation();
    if (
        $reservation
        && ($reservation['event'] ?? '') === $eventKey
        && (int) ($reservation['performance'] ?? -1) === $performance
        && (int) ($reservation['expires_at'] ?? 0) > time()
    ) {
        $active = clicketDbFetch(
            'SELECT id FROM seat_holds WHERE token = :token AND status = "active" AND expires_at > UTC_TIMESTAMP() LIMIT 1',
            ['token' => (string) ($reservation['token'] ?? '')]
        );
        if ($active) {
            return $reservation;
        }
    }

    clicketClearReservation();

    $event = clicketDbEventByKey($eventKey);
    $performanceRow = clicketDbPerformanceByIndex($eventKey, $performance);
    if (!$event || !$performanceRow) {
        throw new RuntimeException('Cannot start reservation for unknown event or performance.');
    }

    $currentUser = function_exists('currentUser') ? currentUser() : null;
    $dbUserId = is_array($currentUser)
        ? (clicketDbUserIdFromSession((string) ($currentUser['id'] ?? '')) ?? clicketDbUserIdByEmail((string) ($currentUser['email'] ?? '')))
        : null;
    $startedAt = time();
    $expiresAt = $startedAt + CLICKET_RESERVATION_SECONDS;
    $reservation = [
        'token' => hash('sha256', session_id() . '|' . $eventKey . '|' . microtime(true)),
        'event' => $eventKey,
        'performance' => $performance,
        'started_at' => $startedAt,
        'expires_at' => $expiresAt,
    ];

    clicketDbExecute(
        'INSERT INTO seat_holds (token, user_id, event_id, performance_id, started_at, expires_at, status)
         VALUES (:token, :user_id, :event_id, :performance_id, :started_at, :expires_at, "active")',
        [
            'token' => $reservation['token'],
            'user_id' => $dbUserId,
            'event_id' => (int) $event['id'],
            'performance_id' => (int) $performanceRow['id'],
            'started_at' => clicketDbTimestamp($startedAt),
            'expires_at' => clicketDbTimestamp($expiresAt),
        ]
    );

    $_SESSION['clicket_reservation'] = $reservation;

    return $reservation;
}

function clicketNormalizeHeldSeats(array $seats): array {
    $normalized = [];
    $seen = [];

    foreach ($seats as $seat) {
        if (is_array($seat)) {
            $seatCode = trim((string) ($seat['id'] ?? $seat['seat_code'] ?? ''));
            $details = $seat;
        } else {
            $seatCode = trim((string) $seat);
            $details = ['id' => $seatCode];
        }

        if ($seatCode === '' || isset($seen[$seatCode])) {
            continue;
        }

        $seen[$seatCode] = true;
        $normalized[] = [$seatCode, $details];
    }

    return $normalized;
}

function clicketHoldReservationSeats(array $seats): bool {
    clicketExpireOldSeatHolds();

    $reservation = clicketReservation();
    if (!$reservation || (int) ($reservation['expires_at'] ?? 0) <= time()) {
        return false;
    }

    $token = (string) ($reservation['token'] ?? '');
    $eventKey = (string) ($reservation['event'] ?? '');
    $performance = (int) ($reservation['performance'] ?? 0);
    $event = clicketDbEventByKey($eventKey);
    $performanceRow = clicketDbPerformanceByIndex($eventKey, $performance);
    if (!$event || !$performanceRow || $token === '') {
        return false;
    }

    $normalized = clicketNormalizeHeldSeats($seats);
    if (!$normalized) {
        return false;
    }

    $seatIds = [];
    foreach ($normalized as [$seatCode, $details]) {
        $seatIds[] = clicketDbEnsureSeat($eventKey, $seatCode, $details);
    }

    $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
    $params = array_merge(
        [(int) $event['id'], (int) $performanceRow['id'], $token],
        $seatIds
    );
    $conflict = clicketDbExecute(
        'SELECT s.seat_code
         FROM seat_holds h
         INNER JOIN seat_hold_items hi ON hi.seat_hold_id = h.id
         INNER JOIN seats s ON s.id = hi.seat_id
         WHERE h.event_id = ?
           AND h.performance_id = ?
           AND h.token <> ?
           AND h.status = "active"
           AND h.expires_at > UTC_TIMESTAMP()
           AND hi.seat_id IN (' . $placeholders . ')
         LIMIT 1',
        $params
    )->fetch();

    if ($conflict) {
        return false;
    }

    $pdo = clicketDb();
    $pdo->beginTransaction();

    try {
        $hold = clicketDbFetch(
            'SELECT id FROM seat_holds WHERE token = :token LIMIT 1',
            ['token' => $token]
        );

        if (!$hold) {
            clicketDbExecute(
                'INSERT INTO seat_holds (token, user_id, event_id, performance_id, started_at, expires_at, status)
                 VALUES (:token, NULL, :event_id, :performance_id, :started_at, :expires_at, "active")',
                [
                    'token' => $token,
                    'event_id' => (int) $event['id'],
                    'performance_id' => (int) $performanceRow['id'],
                    'started_at' => clicketDbTimestamp((int) ($reservation['started_at'] ?? time())),
                    'expires_at' => clicketDbTimestamp((int) ($reservation['expires_at'] ?? (time() + CLICKET_RESERVATION_SECONDS))),
                ]
            );
            $holdId = (int) $pdo->lastInsertId();
        } else {
            $holdId = (int) $hold['id'];
            clicketDbExecute(
                'UPDATE seat_holds
                 SET event_id = :event_id, performance_id = :performance_id, expires_at = :expires_at, status = "active"
                 WHERE id = :id',
                [
                    'id' => $holdId,
                    'event_id' => (int) $event['id'],
                    'performance_id' => (int) $performanceRow['id'],
                    'expires_at' => clicketDbTimestamp((int) ($reservation['expires_at'] ?? (time() + CLICKET_RESERVATION_SECONDS))),
                ]
            );
            clicketDbExecute(
                'DELETE FROM seat_hold_items WHERE seat_hold_id = :hold_id',
                ['hold_id' => $holdId]
            );
        }

        foreach ($seatIds as $seatId) {
            clicketDbExecute(
                'INSERT IGNORE INTO seat_hold_items (seat_hold_id, seat_id) VALUES (:hold_id, :seat_id)',
                ['hold_id' => $holdId, 'seat_id' => $seatId]
            );
        }

        $pdo->commit();
        return true;
    } catch (Throwable) {
        $pdo->rollBack();
        return false;
    }
}

function clicketHeldSeatIds(string $eventKey, int $performance): array {
    clicketExpireOldSeatHolds();

    $event = clicketDbEventByKey($eventKey);
    $performanceRow = clicketDbPerformanceByIndex($eventKey, max(0, min(3, $performance)));
    if (!$event || !$performanceRow) {
        return [];
    }

    $reservation = clicketReservation();
    $ownToken = (string) ($reservation['token'] ?? '');
    $rows = clicketDbFetchAll(
        'SELECT DISTINCT s.seat_code
         FROM seat_holds h
         INNER JOIN seat_hold_items hi ON hi.seat_hold_id = h.id
         INNER JOIN seats s ON s.id = hi.seat_id
         WHERE h.event_id = :event_id
           AND h.performance_id = :performance_id
           AND h.status = "active"
           AND h.expires_at > UTC_TIMESTAMP()
           AND (:own_token = "" OR h.token <> :own_token)
         ORDER BY s.seat_code',
        [
            'event_id' => (int) $event['id'],
            'performance_id' => (int) $performanceRow['id'],
            'own_token' => $ownToken,
        ]
    );

    return array_values(array_map(static fn (array $row): string => (string) $row['seat_code'], $rows));
}

function clicketReservationIsActive(string $eventKey, int $performance): bool {
    clicketExpireOldSeatHolds();

    $reservation = clicketReservation();
    if (
        !$reservation
        || ($reservation['event'] ?? '') !== $eventKey
        || (int) ($reservation['performance'] ?? -1) !== max(0, min(3, $performance))
        || (int) ($reservation['expires_at'] ?? 0) <= time()
    ) {
        return false;
    }

    $active = clicketDbFetch(
        'SELECT id FROM seat_holds WHERE token = :token AND status = "active" AND expires_at > UTC_TIMESTAMP() LIMIT 1',
        ['token' => (string) ($reservation['token'] ?? '')]
    );

    return $active !== null;
}

function clicketReadReservationRows(): array {
    clicketExpireOldSeatHolds();

    $rows = clicketDbFetchAll(
        'SELECT h.*, e.event_key, e.title AS event_title, v.name AS venue_name,
                u.name AS buyer_name, u.email AS buyer_email,
                ep.performance_date, ep.performance_time,
                GROUP_CONCAT(s.seat_code ORDER BY s.seat_code SEPARATOR "\n") AS seat_codes
         FROM seat_holds h
         INNER JOIN events e ON e.id = h.event_id
         INNER JOIN venues v ON v.id = e.venue_id
         INNER JOIN event_performances ep ON ep.id = h.performance_id
         LEFT JOIN users u ON u.id = h.user_id
         LEFT JOIN seat_hold_items hi ON hi.seat_hold_id = h.id
         LEFT JOIN seats s ON s.id = hi.seat_id
         GROUP BY h.id
         ORDER BY h.expires_at DESC, h.id DESC'
    );

    return array_map(static function (array $row): array {
        $seatCodes = array_values(array_filter(explode("\n", (string) ($row['seat_codes'] ?? ''))));
        $expiresAt = strtotime((string) $row['expires_at']) ?: time();

        return [
            'id' => 'HLD-' . str_pad((string) $row['id'], 5, '0', STR_PAD_LEFT),
            'token' => (string) $row['token'],
            'event' => (string) $row['event_key'],
            'event_title' => (string) $row['event_title'],
            'venue' => (string) $row['venue_name'],
            'buyer_name' => (string) ($row['buyer_name'] ?: 'Guest checkout'),
            'buyer_email' => (string) ($row['buyer_email'] ?? ''),
            'user_id' => (string) ($row['user_id'] ?? ''),
            'event_date' => clicketDbDisplayDate((string) $row['performance_date']),
            'event_time' => clicketDbDisplayTime((string) $row['performance_time']),
            'seats' => array_map(static fn (string $code): array => ['id' => $code], $seatCodes),
            'status' => (string) $row['status'],
            'expires_at' => $expiresAt,
            'created_at' => clicketDbDisplayDateTime((string) $row['started_at']),
        ];
    }, $rows);
}

function clicketReservationExpiryUrl(string $eventKey, int $performance): string {
    return 'ticket.php?event=' . rawurlencode($eventKey)
        . '&performance=' . max(0, min(3, $performance))
        . '&reservation=expired';
}

function clicketRedirectExpiredReservation(string $eventKey, int $performance): never {
    clicketClearReservation();
    header('Location: ticket.php?event=' . rawurlencode($eventKey)
        . '&performance=' . max(0, min(3, $performance))
        . '&reservation=expired');
    exit;
}
