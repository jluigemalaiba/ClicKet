<?php

if (!defined('CLICKET_RESERVATION_SECONDS')) {
    define('CLICKET_RESERVATION_SECONDS', 15 * 60);
}
if (!defined('CLICKET_RESERVATIONS_FILE')) {
    define('CLICKET_RESERVATIONS_FILE', __DIR__ . '/../storage/reservations.json');
}

function clicketReservationStore(callable $callback): mixed {
    $handle = fopen(CLICKET_RESERVATIONS_FILE, 'c+');
    if ($handle === false) {
        return null;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return null;
        }

        rewind($handle);
        $holds = json_decode(stream_get_contents($handle) ?: '[]', true);
        $holds = is_array($holds) ? $holds : [];
        $holds = array_values(array_filter(
            $holds,
            fn(array $hold): bool => (int) ($hold['expires_at'] ?? 0) > time()
        ));
        $result = $callback($holds);
        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode(array_values($holds), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        fflush($handle);

        return $result;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function clicketReservation(): ?array {
    $reservation = $_SESSION['clicket_reservation'] ?? null;

    return is_array($reservation) ? $reservation : null;
}

function clicketClearReservation(bool $clearSelection = true): void {
    $reservation = clicketReservation();
    $token = (string) ($reservation['token'] ?? '');
    if ($token !== '') {
        clicketReservationStore(function (array &$holds) use ($token): void {
            $holds = array_values(array_filter(
                $holds,
                fn(array $hold): bool => !hash_equals((string) ($hold['token'] ?? ''), $token)
            ));
        });
    }
    unset($_SESSION['clicket_reservation']);

    if ($clearSelection) {
        unset($_SESSION['clicket_ticket_selection']);
    }
}

function clicketStartReservation(string $eventKey, int $performance): array {
    $performance = max(0, min(3, $performance));
    $reservation = clicketReservation();

    if (
        $reservation
        && ($reservation['event'] ?? '') === $eventKey
        && (int) ($reservation['performance'] ?? -1) === $performance
        && (int) ($reservation['expires_at'] ?? 0) > time()
    ) {
        return $reservation;
    }

    clicketClearReservation();
    $reservation = [
        'token' => hash('sha256', session_id() . '|' . $eventKey . '|' . microtime(true)),
        'event' => $eventKey,
        'performance' => $performance,
        'started_at' => time(),
        'expires_at' => time() + CLICKET_RESERVATION_SECONDS,
    ];
    $_SESSION['clicket_reservation'] = $reservation;

    return $reservation;
}

function clicketHoldReservationSeats(array $seatIds): bool {
    $reservation = clicketReservation();
    if (!$reservation || (int) ($reservation['expires_at'] ?? 0) <= time()) {
        return false;
    }

    $seatIds = array_values(array_unique(array_filter(array_map('strval', $seatIds))));
    $token = (string) ($reservation['token'] ?? '');
    $eventKey = (string) ($reservation['event'] ?? '');
    $performance = (int) ($reservation['performance'] ?? 0);
    $expiresAt = (int) ($reservation['expires_at'] ?? 0);

    return clicketReservationStore(function (array &$holds) use ($seatIds, $token, $eventKey, $performance, $expiresAt): bool {
        foreach ($holds as $hold) {
            if (
                ($hold['event'] ?? '') !== $eventKey
                || (int) ($hold['performance'] ?? -1) !== $performance
                || hash_equals((string) ($hold['token'] ?? ''), $token)
            ) {
                continue;
            }
            if (array_intersect($seatIds, is_array($hold['seat_ids'] ?? null) ? $hold['seat_ids'] : [])) {
                return false;
            }
        }

        $holds = array_values(array_filter(
            $holds,
            fn(array $hold): bool => !hash_equals((string) ($hold['token'] ?? ''), $token)
        ));
        $holds[] = [
            'token' => $token,
            'event' => $eventKey,
            'performance' => $performance,
            'seat_ids' => $seatIds,
            'expires_at' => $expiresAt,
        ];
        return true;
    }) === true;
}

function clicketHeldSeatIds(string $eventKey, int $performance): array {
    $reservation = clicketReservation();
    $ownToken = (string) ($reservation['token'] ?? '');

    return clicketReservationStore(function (array &$holds) use ($eventKey, $performance, $ownToken): array {
        $seatIds = [];
        foreach ($holds as $hold) {
            if (
                ($hold['event'] ?? '') === $eventKey
                && (int) ($hold['performance'] ?? -1) === $performance
                && ($ownToken === '' || !hash_equals((string) ($hold['token'] ?? ''), $ownToken))
            ) {
                $seatIds = array_merge($seatIds, is_array($hold['seat_ids'] ?? null) ? $hold['seat_ids'] : []);
            }
        }
        return array_values(array_unique($seatIds));
    }) ?? [];
}

function clicketReservationIsActive(string $eventKey, int $performance): bool {
    $reservation = clicketReservation();

    return $reservation !== null
        && ($reservation['event'] ?? '') === $eventKey
        && (int) ($reservation['performance'] ?? -1) === max(0, min(3, $performance))
        && (int) ($reservation['expires_at'] ?? 0) > time();
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
