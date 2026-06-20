<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';
require_once __DIR__ . '/includes/inventory-sync.php';

header('Content-Type: application/json; charset=utf-8');

clicketRequireRoleJson(['admin', 'organizer'], 'Staff access required.');
$staff = currentStaff();
if (!$staff) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Staff login required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST required.']);
    exit;
}

function clicketInventoryApiJson(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function clicketInventoryApiEvent(string $eventKey): array {
    $event = clicketDbEventByKey($eventKey);
    if (!$event) {
        clicketInventoryApiJson(404, ['success' => false, 'message' => 'Event not found.']);
    }

    return $event;
}

function clicketInventoryApiPerformance(string $eventKey, int $performanceIndex): array {
    $performance = clicketDbPerformanceByIndex($eventKey, max(0, min(3, $performanceIndex)));
    if (!$performance) {
        clicketInventoryApiJson(404, ['success' => false, 'message' => 'Performance not found.']);
    }

    return $performance;
}

function clicketInventoryApiSeatId(int $eventId, string $seatCode): int {
    $row = clicketDbFetch(
        'SELECT s.id
         FROM events e
         INNER JOIN venue_sections vs ON vs.venue_layout_id = e.venue_layout_id
         INNER JOIN seats s ON s.venue_section_id = vs.id
         WHERE e.id = :event_id AND s.seat_code = :seat_code
         LIMIT 1',
        ['event_id' => $eventId, 'seat_code' => $seatCode]
    );

    if (!$row) {
        clicketInventoryApiJson(422, ['success' => false, 'message' => 'Seat code was not found for this event layout.']);
    }

    return (int) $row['id'];
}

function clicketInventoryApiTierId(int $eventId, string $tier): int {
    $row = clicketDbFetch(
        'SELECT vt.id
         FROM events e
         INNER JOIN venue_tiers vt ON vt.venue_layout_id = e.venue_layout_id
         WHERE e.id = ?
           AND (vt.id = ? OR LOWER(vt.slug) = LOWER(?) OR LOWER(vt.name) = LOWER(?))
         LIMIT 1',
        [
            $eventId,
            ctype_digit($tier) ? (int) $tier : 0,
            $tier,
            $tier,
        ]
    );

    if (!$row) {
        clicketInventoryApiJson(422, ['success' => false, 'message' => 'Tier was not found for this event layout.']);
    }

    return (int) $row['id'];
}

$action = strtolower(trim((string) ($_POST['action'] ?? '')));
$eventKey = trim((string) ($_POST['event_key'] ?? $_POST['event'] ?? ''));
$reason = trim((string) ($_POST['reason'] ?? 'Staff inventory control'));
$staffId = clicketDbStaffIdBySession($staff) ?? 0;

if ($action === '' || $eventKey === '' || $staffId <= 0) {
    clicketInventoryApiJson(422, ['success' => false, 'message' => 'Missing inventory action, event, or staff account.']);
}

$event = clicketInventoryApiEvent($eventKey);
if (!clicketStaffCanAccessEvent($staff, $eventKey)) {
    clicketInventoryApiJson(403, ['success' => false, 'message' => 'You do not have permission for this event.']);
}

if (in_array($action, ['block_seat', 'release_seat'], true)) {
    $performance = clicketInventoryApiPerformance($eventKey, (int) ($_POST['performance'] ?? 0));
    $seatCode = trim((string) ($_POST['seat_code'] ?? ''));
    if ($seatCode === '') {
        clicketInventoryApiJson(422, ['success' => false, 'message' => 'Seat code is required.']);
    }
    $seatId = clicketInventoryApiSeatId((int) $event['id'], $seatCode);

    if ($action === 'block_seat') {
        if (clicketInventoryUnavailableSeatIds((int) $event['id'], (int) $performance['id'], [$seatId])) {
            clicketInventoryApiJson(409, ['success' => false, 'message' => 'Seat is already sold, held, or blocked.']);
        }

        clicketDbExecute(
            'INSERT INTO seat_blocks (event_id, performance_id, seat_id, blocked_by_staff_id, reason, status)
             VALUES (:event_id, :performance_id, :seat_id, :staff_id, :reason, "active")',
            [
                'event_id' => (int) $event['id'],
                'performance_id' => (int) $performance['id'],
                'seat_id' => $seatId,
                'staff_id' => $staffId,
                'reason' => $reason,
            ]
        );
        clicketDbExecute('UPDATE seats SET status = "blocked" WHERE id = :seat_id', ['seat_id' => $seatId]);
    } else {
        clicketDbExecute(
            'UPDATE seat_blocks
             SET status = "released", released_at = UTC_TIMESTAMP()
             WHERE event_id = :event_id AND performance_id = :performance_id AND seat_id = :seat_id AND status = "active"',
            [
                'event_id' => (int) $event['id'],
                'performance_id' => (int) $performance['id'],
                'seat_id' => $seatId,
            ]
        );
        $activeSeat = clicketDbFetch(
            'SELECT reservation_status
             FROM order_seats
             WHERE seat_id = :seat_id AND active_reservation_key = "active"
             ORDER BY FIELD(reservation_status, "sold", "held")
             LIMIT 1',
            ['seat_id' => $seatId]
        );
        clicketDbExecute(
            'UPDATE seats
             SET status = :status
             WHERE id = :seat_id AND status = "blocked"',
            [
                'seat_id' => $seatId,
                'status' => $activeSeat ? (string) $activeSeat['reservation_status'] : 'available',
            ]
        );
    }

    clicketInventorySyncEventPerformance((int) $event['id'], (int) $performance['id']);
    echo json_encode(['success' => true, 'message' => $action === 'block_seat' ? 'Seat blocked.' : 'Seat block released.']);
    exit;
}

if (in_array($action, ['block_tier', 'release_tier'], true)) {
    $tier = trim((string) ($_POST['tier_id'] ?? $_POST['tier'] ?? ''));
    if ($tier === '') {
        clicketInventoryApiJson(422, ['success' => false, 'message' => 'Tier is required.']);
    }
    $tierId = clicketInventoryApiTierId((int) $event['id'], $tier);

    if ($action === 'block_tier') {
        clicketDbExecute(
            'INSERT INTO tier_blocks (event_id, tier_id, blocked_by_staff_id, reason, status)
             VALUES (:event_id, :tier_id, :staff_id, :reason, "active")',
            [
                'event_id' => (int) $event['id'],
                'tier_id' => $tierId,
                'staff_id' => $staffId,
                'reason' => $reason,
            ]
        );
    } else {
        clicketDbExecute(
            'UPDATE tier_blocks
             SET status = "released", released_at = UTC_TIMESTAMP()
             WHERE event_id = :event_id AND tier_id = :tier_id AND status = "active"',
            ['event_id' => (int) $event['id'], 'tier_id' => $tierId]
        );
    }

    clicketInventorySyncAll((int) $event['id']);
    echo json_encode(['success' => true, 'message' => $action === 'block_tier' ? 'Tier blocked.' : 'Tier block released.']);
    exit;
}

clicketInventoryApiJson(422, ['success' => false, 'message' => 'Unsupported inventory action.']);
