<?php

require_once __DIR__ . '/log.php';
require_once __DIR__ . '/ticketing.php';

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

if (count($seats) < 1 || count($seats) > 4) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Select between 1 and 4 seats.']);
    exit;
}

$normalized = [];
$seen = [];
$venueProfile = clicketVenueProfile($resolved['event']['venue']);
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
        if (preg_match('/^' . preg_quote($section['id'], '/') . '-([A-E])-(\d{1,2})$/', $seatId, $seatMatches)) {
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

$_SESSION['clicket_ticket_selection'] = [
    'event' => $eventKey,
    'performance' => max(0, min(3, (int) ($payload['performance'] ?? 0))),
    'performance_date' => substr(trim((string) ($payload['performanceDate'] ?? '')), 0, 80),
    'performance_time' => substr(trim((string) ($payload['performanceTime'] ?? '')), 0, 30),
    'seats' => $normalized,
    'non_transferable' => true,
    'saved_at' => date('c'),
];

echo json_encode([
    'success' => true,
    'redirect' => 'checkout.php?event=' . rawurlencode($eventKey)
        . '&performance=' . max(0, min(3, (int) ($payload['performance'] ?? 0))),
]);
