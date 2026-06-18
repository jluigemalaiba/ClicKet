<?php

require_once __DIR__ . '/data.php';
require_once __DIR__ . '/ticketing.php';
require_once __DIR__ . '/order-history-data.php';
require_once __DIR__ . '/reservation.php';
require_once __DIR__ . '/favorite-data.php';

function clicketStaffTier(string $name, string $color): array {
    return [
        'name' => $name,
        'color' => $color,
        'status' => 'Open',
        'priceMode' => 'Event price',
    ];
}

function clicketStaffVenueDefinitions(): array {
    return [
        [
            'id' => 'moa-concert',
            'venue' => 'Mall of Asia Arena',
            'variant' => 'Concert',
            'category' => 'concerts',
            'profileVenue' => 'Mall of Asia Arena',
            'capacity' => 13000,
            'svg' => 'MOA_Concert_final.svg',
            'aliases' => ['MOA Arena', 'Mall of Asia Arena'],
            'tiers' => [
                clicketStaffTier('VIP', '#fff0a8'),
                clicketStaffTier('Lower Box A Premium', '#bfe8c8'),
                clicketStaffTier('Patron', '#afd3ff'),
                clicketStaffTier('Lower Box A Regular', '#ffc58f'),
                clicketStaffTier('Lower Box B', '#f5b6cc'),
                clicketStaffTier('Upper Box', '#d8b7ff'),
                clicketStaffTier('GA', '#f2a0aa'),
            ],
        ],
        [
            'id' => 'moa-sports',
            'venue' => 'Mall of Asia Arena',
            'variant' => 'Sports',
            'category' => 'sports',
            'profileVenue' => 'Mall of Asia Arena',
            'capacity' => 16000,
            'svg' => 'MOA_Sport_final2.svg',
            'aliases' => ['MOA Arena', 'Mall of Asia Arena'],
            'tiers' => [
                clicketStaffTier('Patron', '#bfe8c8'),
                clicketStaffTier('VIP', '#fff0a8'),
                clicketStaffTier('Lower Box', '#ffc58f'),
                clicketStaffTier('Upper Box', '#afd3ff'),
                clicketStaffTier('GA', '#f2a0aa'),
            ],
        ],
        [
            'id' => 'philippine-arena',
            'venue' => 'Philippine Arena',
            'variant' => 'Concert / Sports',
            'category' => 'concerts',
            'profileVenue' => 'Philippine Arena',
            'capacity' => 55000,
            'svg' => 'phil_arena.svg',
            'aliases' => ['Philippine Arena'],
            'tiers' => [
                clicketStaffTier('VIP', '#fff0a8'),
                clicketStaffTier('Lower Box A Premium', '#bfe8c8'),
                clicketStaffTier('Lower Box A Regular', '#afd3ff'),
                clicketStaffTier('Lower Box B Premium', '#ffc58f'),
                clicketStaffTier('Lower Box B Regular', '#f5b6cc'),
                clicketStaffTier('Upper Box A', '#d8b7ff'),
                clicketStaffTier('Upper Box B Premium', '#ffb090'),
                clicketStaffTier('Upper Box B Regular', '#f2a0aa'),
            ],
        ],
        [
            'id' => 'araneta-concert',
            'venue' => 'Smart Araneta Coliseum',
            'variant' => 'Concert',
            'category' => 'concerts',
            'profileVenue' => 'Smart Araneta Coliseum',
            'capacity' => 13000,
            'svg' => 'Araneta_Concert.svg',
            'aliases' => ['Smart Araneta Coliseum', 'Smart Araneta'],
            'tiers' => [
                clicketStaffTier('SVIP', '#fdff00'),
                clicketStaffTier('VIP', '#fff0a8'),
                clicketStaffTier('Patron A', '#5edc1f'),
                clicketStaffTier('Patron B', '#bfe8c8'),
                clicketStaffTier('Lower Box', '#ffc58f'),
                clicketStaffTier('Upper Box', '#d8b7ff'),
                clicketStaffTier('GA', '#f2a0aa'),
            ],
        ],
        [
            'id' => 'araneta-sports',
            'venue' => 'Smart Araneta Coliseum',
            'variant' => 'Sports',
            'category' => 'sports',
            'profileVenue' => 'Smart Araneta Coliseum',
            'capacity' => 18000,
            'svg' => 'Araneta_Sport.svg',
            'aliases' => ['Smart Araneta Coliseum', 'Smart Araneta'],
            'tiers' => [
                clicketStaffTier('VIP', '#fff0a8'),
                clicketStaffTier('Patron', '#bfe8c8'),
                clicketStaffTier('Lower Box', '#ffc58f'),
                clicketStaffTier('Upper Box', '#d8b7ff'),
                clicketStaffTier('GA', '#f2a0aa'),
            ],
        ],
        [
            'id' => 'tanghalan',
            'venue' => 'Tanghalang Pilipino',
            'variant' => 'Theater',
            'category' => 'theater',
            'profileVenue' => 'Tanghalang Ignacio Jimenez',
            'capacity' => 320,
            'svg' => 'Tanghalan.svg',
            'aliases' => ['Tanghalang Pilipino', 'Tanghalang Ignacio Jimenez'],
            'tiers' => [
                clicketStaffTier('SVIP', '#fff0a8'),
                clicketStaffTier('CCP House Seats', '#bfe8c8'),
                clicketStaffTier('VIP', '#d8b7ff'),
                clicketStaffTier('VP House Seats', '#ffc58f'),
                clicketStaffTier('Regular', '#f2a0aa'),
            ],
        ],
        [
            'id' => 'newport',
            'venue' => 'Newport Performing Arts Theater',
            'variant' => 'Theater',
            'category' => 'theater',
            'profileVenue' => 'Newport Performing Arts Theater',
            'capacity' => 1700,
            'svg' => 'Newport_final2.svg',
            'aliases' => ['Newport Performing Arts Theater'],
            'tiers' => [
                clicketStaffTier('SVIP', '#fff86b'),
                clicketStaffTier('VIP', '#fff0a8'),
                clicketStaffTier('Balcony Center', '#afd3ff'),
                clicketStaffTier('Premiere Left', '#ffc58f'),
                clicketStaffTier('Premiere Right', '#ffc58f'),
                clicketStaffTier('Deluxe Left', '#d8b7ff'),
                clicketStaffTier('Deluxe Right', '#d8b7ff'),
                clicketStaffTier('Balcony Left', '#bfe8c8'),
                clicketStaffTier('Balcony Right', '#bfe8c8'),
                clicketStaffTier('Outer Balcony Left', '#f2a0aa'),
                clicketStaffTier('Outer Balcony Right', '#f2a0aa'),
            ],
        ],
        [
            'id' => 'solaire',
            'venue' => 'The Theatre at Solaire',
            'variant' => 'Theater',
            'category' => 'theater',
            'profileVenue' => 'The Theatre at Solaire',
            'capacity' => 1850,
            'svg' => 'Solaire.svg',
            'aliases' => ['The Theatre at Solaire', 'Solaire Resort Entertainment City', 'Solaire'],
            'tiers' => [
                clicketStaffTier('VIP', '#fff0a8'),
                clicketStaffTier('A Reserve', '#bfe8c8'),
                clicketStaffTier('B Reserve', '#afd3ff'),
                clicketStaffTier('C Reserve', '#ffc58f'),
                clicketStaffTier('D Reserve', '#f2a0aa'),
            ],
        ],
        [
            'id' => 'philsports',
            'venue' => 'Philsports Arena',
            'variant' => 'Sports',
            'category' => 'sports',
            'profileVenue' => 'PhilSports Arena',
            'capacity' => 10000,
            'svg' => 'PS_Arena.svg',
            'aliases' => ['Philsports Arena', 'PhilSports Arena'],
            'tiers' => [
                clicketStaffTier('Patron', '#bfe8c8'),
                clicketStaffTier('Lower Box', '#afd3ff'),
                clicketStaffTier('Upper Box', '#ffc58f'),
            ],
        ],
    ];
}

function clicketStaffAssignedVenueNames(array $staff): array {
    $venues = is_array($staff['venues'] ?? null) ? $staff['venues'] : [];
    return array_values(array_filter(array_map('strval', $venues)));
}

function clicketStaffVenueAllowed(array $staff, array $venue): bool {
    if (($staff['role'] ?? '') === 'admin') {
        return true;
    }
    $assigned = array_map('strtolower', clicketStaffAssignedVenueNames($staff));
    foreach ($venue['aliases'] as $alias) {
        if (in_array(strtolower($alias), $assigned, true)) {
            return true;
        }
    }
    return false;
}

function clicketStaffScopedVenues(array $staff): array {
    return array_values(array_filter(
        clicketStaffVenueDefinitions(),
        static fn (array $venue): bool => clicketStaffVenueAllowed($staff, $venue)
    ));
}

function clicketStaffAllEvents(): array {
    global $concert_events, $theater_events, $sports_events;

    $catalogs = [
        'concerts' => ['label' => 'Concert', 'events' => $concert_events ?? []],
        'theater' => ['label' => 'Theater', 'events' => $theater_events ?? []],
        'sports' => ['label' => 'Sports', 'events' => $sports_events ?? []],
    ];
    $events = [];
    foreach ($catalogs as $category => $catalog) {
        foreach ($catalog['events'] as $index => $event) {
            $events[] = [
                'key' => $category . '-' . ($index + 1),
                'category' => $category,
                'category_label' => $catalog['label'],
                'title' => (string) ($event['title'] ?? ''),
                'venue' => (string) ($event['venue'] ?? ''),
                'date' => (string) ($event['date'] ?? ''),
                'type' => (string) ($event['type'] ?? $catalog['label']),
                'price' => (string) ($event['price'] ?? 'PHP 750'),
                'owner' => (string) ($event['artist'] ?? $event['company'] ?? $event['league'] ?? ''),
                'status' => ($index % 7 === 0) ? 'Draft' : (($index % 5 === 0) ? 'Sold Out' : 'Published'),
            ];
        }
    }
    return $events;
}

function clicketStaffEventAllowed(array $staff, array $event, array $scopedVenues): bool {
    if (($staff['role'] ?? '') === 'admin') {
        return true;
    }
    $eventVenue = strtolower((string) ($event['venue'] ?? ''));
    foreach ($scopedVenues as $venue) {
        foreach ($venue['aliases'] as $alias) {
            if ($eventVenue === strtolower($alias)) {
                return true;
            }
        }
    }
    return false;
}

function clicketStaffScopedEvents(array $staff, array $scopedVenues): array {
    return array_values(array_filter(
        clicketStaffAllEvents(),
        static fn (array $event): bool => clicketStaffEventAllowed($staff, $event, $scopedVenues)
    ));
}

function clicketStaffScopedOrders(array $staff, array $scopedVenues): array {
    $orders = clicketReadOrders();
    if (($staff['role'] ?? '') === 'admin') {
        return $orders;
    }
    $aliases = [];
    foreach ($scopedVenues as $venue) {
        foreach ($venue['aliases'] as $alias) {
            $aliases[] = strtolower($alias);
        }
    }
    return array_values(array_filter($orders, static fn (array $order): bool => in_array(strtolower((string) ($order['venue'] ?? '')), $aliases, true)));
}

function clicketStaffCanAccessOrder(array $staff, array $order): bool {
    if (($staff['role'] ?? '') === 'admin') {
        return true;
    }

    $orderVenue = strtolower((string) ($order['venue'] ?? ''));
    foreach (clicketStaffScopedVenues($staff) as $venue) {
        foreach ($venue['aliases'] as $alias) {
            if ($orderVenue === strtolower((string) $alias)) {
                return true;
            }
        }
    }

    return false;
}

function clicketStaffReadJsonFile(string $path): array {
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode(file_get_contents($path) ?: '[]', true);
    return is_array($data) ? $data : [];
}

function clicketStaffPanelPayload(array $staff): array {
    $scopedVenues = clicketStaffScopedVenues($staff);
    $events = clicketStaffScopedEvents($staff, $scopedVenues);
    $orders = clicketStaffScopedOrders($staff, $scopedVenues);
    $reservations = clicketStaffReadJsonFile(__DIR__ . '/../storage/reservations.json');
    $favorites = clicketStaffReadJsonFile(__DIR__ . '/../storage/favorites.json');
    $users = getUsers();
    $allStaff = getStaffAccounts();

    $paidOrders = array_values(array_filter($orders, static fn (array $order): bool => strtolower((string) ($order['payment_status'] ?? '')) === 'paid'));
    $pendingOrders = array_values(array_filter($orders, static fn (array $order): bool => strtolower((string) ($order['payment_status'] ?? '')) === 'pending'));
    $ticketsSold = array_sum(array_map(static fn (array $order): int => count(is_array($order['tickets'] ?? null) ? $order['tickets'] : ($order['seats'] ?? [])), $paidOrders));
    $sales = array_sum(array_map(static fn (array $order): int => (int) ($order['total'] ?? 0), $paidOrders));
    $activeReservations = array_values(array_filter($reservations, static fn (array $hold): bool => (int) ($hold['expires_at'] ?? 0) > time()));

    $topEvents = [];
    foreach ($orders as $order) {
        $title = (string) ($order['event_title'] ?? $order['event'] ?? 'Untitled event');
        if (!isset($topEvents[$title])) {
            $topEvents[$title] = ['title' => $title, 'sales' => 0, 'tickets' => 0];
        }
        $topEvents[$title]['sales'] += (int) ($order['total'] ?? 0);
        $topEvents[$title]['tickets'] += count(is_array($order['tickets'] ?? null) ? $order['tickets'] : ($order['seats'] ?? []));
    }
    usort($topEvents, static fn (array $a, array $b): int => $b['sales'] <=> $a['sales']);

    $topVenues = [];
    foreach ($orders as $order) {
        $venue = (string) ($order['venue'] ?? 'Unknown venue');
        if (!isset($topVenues[$venue])) {
            $topVenues[$venue] = ['venue' => $venue, 'sales' => 0, 'orders' => 0];
        }
        $topVenues[$venue]['sales'] += (int) ($order['total'] ?? 0);
        $topVenues[$venue]['orders']++;
    }
    usort($topVenues, static fn (array $a, array $b): int => $b['sales'] <=> $a['sales']);

    $venueRows = [];
    foreach ($scopedVenues as $venue) {
        $venueOrders = array_values(array_filter($orders, static function (array $order) use ($venue): bool {
            $orderVenue = strtolower((string) ($order['venue'] ?? ''));
            foreach ($venue['aliases'] as $alias) {
                if ($orderVenue === strtolower($alias)) {
                    return true;
                }
            }
            return false;
        }));
        $sold = array_sum(array_map(static fn (array $order): int => count(is_array($order['tickets'] ?? null) ? $order['tickets'] : ($order['seats'] ?? [])), $venueOrders));
        $capacity = max(1, (int) ($venue['capacity'] ?? 0));
        $venueRows[] = $venue + [
            'sold' => $sold,
            'available' => max(0, $capacity - $sold),
            'held' => count($activeReservations),
            'sales' => array_sum(array_map(static fn (array $order): int => (int) ($order['total'] ?? 0), $venueOrders)),
            'occupancy' => min(100, (int) round(($sold / $capacity) * 100)),
        ];
    }

    $audit = [
        ['type' => 'Payment approval', 'actor' => 'ClicKet Admin', 'scope' => 'All venues', 'time' => 'Live audit stream'],
        ['type' => 'Seat block/release', 'actor' => $staff['name'] ?? 'Staff', 'scope' => ($staff['role'] ?? '') === 'admin' ? 'System-wide' : 'Assigned venues', 'time' => 'Tracked per action'],
        ['type' => 'Event archive', 'actor' => 'Organizer / Admin', 'scope' => 'Permission gated', 'time' => 'Requires reason log'],
        ['type' => 'Tier price change', 'actor' => 'Authorized staff', 'scope' => 'Venue tier', 'time' => 'Before/after value saved'],
    ];

    return [
        'venues' => $venueRows,
        'events' => $events,
        'orders' => $orders,
        'payments' => $orders,
        'reservations' => $activeReservations,
        'favorites' => $favorites,
        'users' => $users,
        'staff' => $allStaff,
        'topEvents' => array_slice(array_values($topEvents), 0, 5),
        'topVenues' => array_slice(array_values($topVenues), 0, 5),
        'audit' => $audit,
        'metrics' => [
            'sales' => $sales,
            'ticketsSold' => $ticketsSold,
            'activeEvents' => count(array_filter($events, static fn (array $event): bool => strtolower($event['status']) !== 'archived')),
            'pendingPayments' => count($pendingOrders),
            'activeReservations' => count($activeReservations),
            'lowInventory' => count(array_filter($venueRows, static fn (array $venue): bool => ($venue['occupancy'] ?? 0) >= 80)),
            'orders' => count($orders),
            'users' => count($users),
        ],
    ];
}
