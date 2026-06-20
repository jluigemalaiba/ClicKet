<?php

require_once __DIR__ . '/log.php';
require_once __DIR__ . '/ticketing.php';
require_once __DIR__ . '/reservation.php';
require_once __DIR__ . '/order-history-data.php';
require_once __DIR__ . '/virtual-queue.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '{}', true);
$eventKey = trim((string) ($payload['event'] ?? ''));
$seats = $payload['seats'] ?? [];
$resolved = clicketResolveEvent($eventKey);

if (!$resolved || !is_array($seats)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid ticket selection.']);
    exit;
}

$performance = max(0, min(3, (int) ($payload['performance'] ?? 0)));
clicketVirtualQueueRequireAdmissionJson($eventKey, $performance);
if (!clicketReservationIsActive($eventKey, $performance)) {
    clicketClearReservation();
    http_response_code(410);
    echo json_encode([
        'success' => false,
        'expired' => true,
        'message' => 'Your reservation expired and the selected seats were released.',
        'redirect' => 'ticket.php?event=' . rawurlencode($eventKey)
            . '&performance=' . $performance . '&reservation=expired',
    ]);
    exit;
}

if (count($seats) < 1 || count($seats) > 4) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Select between 1 and 4 seats.']);
    exit;
}

$normalized = [];
$seen = [];
$venueProfile = clicketVenueProfile($resolved['event']['venue'], $resolved['categoryKey']);
$categoryDefinitions = clicketTicketCategories();
foreach ($seats as $seat) {
    $seatId = trim((string) ($seat['id'] ?? ''));
    if ($seatId === '' || isset($seen[$seatId])) {
        continue;
    }

    $matchedSection = null;
    $row = '';
    $number = '';
    foreach ($venueProfile['sections'] as $section) {
        if (preg_match('/^' . preg_quote($section['id'], '/') . '-([A-Z]{1,2})-(\d{1,3})$/', $seatId, $seatMatches)) {
            $matchedSection = $section;
            $row = $seatMatches[1];
            $number = $seatMatches[2];
            break;
        }
    }
    if (!$matchedSection || !isset($categoryDefinitions[$matchedSection['category']])) {
        continue;
    }

    $seen[$seatId] = true;
    $normalized[] = [
        'id' => $seatId,
        'section' => $matchedSection['label'],
        'row' => $row,
        'number' => $number,
        'category' => $categoryDefinitions[$matchedSection['category']]['label'],
    ];
}

if (count($normalized) < 1 || count($normalized) > 4) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'The selected seats could not be saved.']);
    exit;
}

$performanceDate = $resolved['date'];
$performanceTime = $resolved['time'];
if ($resolved['categoryKey'] === 'theater' && $performance > 0) {
    $theaterSlots = [
        [$resolved['date'], $resolved['time']],
        [$resolved['date']->modify('+1 day'), '2:00 PM'],
        [$resolved['date']->modify('+1 day'), '7:30 PM'],
        [$resolved['date']->modify('+2 days'), '3:00 PM'],
    ];
    [$performanceDate, $performanceTime] = $theaterSlots[min($performance, 3)];
}
$performanceDateLabel = $performanceDate->format('l, F j, Y');
$seatIds = array_column($normalized, 'id');
$bookedSeatIds = clicketBookedSeatIds($eventKey, $performanceDateLabel, $performanceTime);
$blockedSeatIds = clicketInventoryBlockedSeatCodes($eventKey, $performance);
if (array_intersect($seatIds, array_merge($bookedSeatIds, $blockedSeatIds)) || !clicketHoldReservationSeats($normalized)) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'One or more selected seats are no longer available. Please choose different seats.',
    ]);
    exit;
}

$_SESSION['clicket_ticket_selection'] = [
    'event' => $eventKey,
    'performance' => $performance,
    'performance_date' => $performanceDateLabel,
    'performance_time' => $performanceTime,
    'seats' => $normalized,
    'non_transferable' => true,
    'saved_at' => date('c'),
    'expires_at' => (int) (clicketReservation()['expires_at'] ?? 0),
];

echo json_encode([
    'success' => true,
    'redirect' => 'checkout.php?event=' . rawurlencode($eventKey)
        . '&performance=' . $performance,
]);
