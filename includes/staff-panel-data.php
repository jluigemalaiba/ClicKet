<?php

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
            'venue' => 'MOA Arena',
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
                clicketStaffTier('General Admission', '#f2a0aa'),
            ],
        ],
        [
            'id' => 'moa-sports',
            'venue' => 'MOA Arena',
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
                clicketStaffTier('General Admission', '#f2a0aa'),
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
                clicketStaffTier('General Admission', '#f2a0aa'),
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
                clicketStaffTier('General Admission', '#f2a0aa'),
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

function clicketStaffVenueNamesMatch(string $left, string $right): bool {
    $left = strtolower(trim($left));
    $right = strtolower(trim($right));
    if ($left === '' || $right === '') {
        return false;
    }
    if ($left === $right) {
        return true;
    }

    foreach (clicketStaffVenueDefinitions() as $venue) {
        $aliases = array_map('strtolower', array_map('strval', $venue['aliases'] ?? []));
        if (in_array($left, $aliases, true) && in_array($right, $aliases, true)) {
            return true;
        }
    }

    return false;
}

function clicketStaffOrganizerForVenue(string $eventVenue): ?array {
    foreach (getStaffAccounts() as $account) {
        if (($account['role'] ?? '') !== 'organizer') {
            continue;
        }
        foreach (clicketStaffAssignedVenueNames($account) as $assignedVenue) {
            if (clicketStaffVenueNamesMatch($eventVenue, $assignedVenue)) {
                return $account;
            }
        }
    }

    return null;
}

function clicketStaffEventCategoryLabel(string $category): string {
    return match ($category) {
        'concert' => 'Concert',
        'theater' => 'Theater',
        'sports' => 'Sports',
        default => ucfirst($category),
    };
}

function clicketStaffEventCategoryKey(string $category): string {
    return match ($category) {
        'concert' => 'concerts',
        'theater' => 'theater',
        'sports' => 'sports',
        default => $category,
    };
}

function clicketStaffEventStatusLabel(string $status): string {
    return ucwords(str_replace('_', ' ', strtolower(trim($status))));
}

function clicketStaffEventOwnerLabel(array $row): string {
    $owner = (string) ($row['artist'] ?: ($row['company'] ?: ($row['league'] ?: '')));

    return $owner !== '' ? $owner : (string) ($row['organizer_name'] ?? 'Organizer');
}

function clicketStaffEventRowToPanel(array $row): array {
    $primaryDate = (string) ($row['primary_performance_date'] ?? '');
    $primaryTime = (string) ($row['primary_performance_time'] ?? '');
    $dateLabel = $primaryDate !== '' ? clicketDbDisplayDate($primaryDate) : 'No performance scheduled';
    $timeLabel = $primaryTime !== '' ? clicketDbDisplayTime($primaryTime) : '';
    $category = (string) ($row['category'] ?? '');
    $status = (string) ($row['status'] ?? 'draft');

    return [
        'db_id' => (int) $row['id'],
        'key' => (string) $row['event_key'],
        'event_key' => (string) $row['event_key'],
        'organizer_id' => (string) $row['created_by_staff_id'],
        'organizer_name' => (string) ($row['organizer_name'] ?? 'Organizer'),
        'organizer_email' => (string) ($row['organizer_email'] ?? ''),
        'category' => clicketStaffEventCategoryKey($category),
        'category_db' => $category,
        'category_label' => clicketStaffEventCategoryLabel($category),
        'title' => (string) $row['title'],
        'venue' => (string) $row['venue_name'],
        'venue_id' => (int) $row['venue_id'],
        'venue_layout_id' => (int) $row['venue_layout_id'],
        'layout_key' => (string) ($row['layout_key'] ?? ''),
        'layout_variant' => (string) ($row['layout_variant'] ?? ''),
        'date' => trim($dateLabel . ($timeLabel !== '' ? ' at ' . $timeLabel : '')),
        'performance_date' => $primaryDate,
        'performance_time' => $primaryTime,
        'performance_status' => clicketStaffEventStatusLabel((string) ($row['primary_performance_status'] ?? 'scheduled')),
        'performance_count' => (int) ($row['performance_count'] ?? 0),
        'type' => (string) ($row['type'] ?? clicketStaffEventCategoryLabel($category)),
        'price' => clicketDbFormatPrice($row['base_price'] ?? 0),
        'base_price' => (float) ($row['base_price'] ?? 0),
        'owner' => clicketStaffEventOwnerLabel($row),
        'artist' => (string) ($row['artist'] ?? ''),
        'company' => (string) ($row['company'] ?? ''),
        'league' => (string) ($row['league'] ?? ''),
        'poster_url' => (string) ($row['poster_url'] ?? ''),
        'banner_url' => (string) ($row['banner_url'] ?? ''),
        'status' => clicketStaffEventStatusLabel($status),
        'status_value' => $status,
        'archived_at' => clicketDbDisplayDateTime((string) ($row['archived_at'] ?? '')),
    ];
}

function clicketStaffAllEvents(?array $staff = null): array {
    $isAdmin = !$staff || ($staff['role'] ?? '') === 'admin';
    $staffId = $staff ? (clicketDbStaffIdBySession($staff) ?? 0) : 0;

    $sql = 'SELECT e.*,
                   v.name AS venue_name,
                   vl.layout_key,
                   vl.variant AS layout_variant,
                   staff.name AS organizer_name,
                   staff.email AS organizer_email,
                   primary_ep.performance_date AS primary_performance_date,
                   primary_ep.performance_time AS primary_performance_time,
                   primary_ep.status AS primary_performance_status,
                   COALESCE(performance_counts.performance_count, 0) AS performance_count
            FROM events e
            INNER JOIN venues v ON v.id = e.venue_id
            INNER JOIN venue_layouts vl ON vl.id = e.venue_layout_id
            INNER JOIN staff_accounts staff ON staff.id = e.created_by_staff_id
            LEFT JOIN event_performances primary_ep
              ON primary_ep.id = (
                SELECT ep2.id
                FROM event_performances ep2
                WHERE ep2.event_id = e.id
                ORDER BY ep2.performance_date, ep2.performance_time, ep2.id
                LIMIT 1
              )
            LEFT JOIN (
                SELECT event_id, COUNT(*) AS performance_count
                FROM event_performances
                GROUP BY event_id
            ) performance_counts ON performance_counts.event_id = e.id';
    $params = [];

    if (!$isAdmin) {
        $sql .= ' WHERE e.created_by_staff_id = :staff_id';
        $params['staff_id'] = $staffId;
    }

    $sql .= ' ORDER BY e.updated_at DESC, e.created_at DESC, e.id DESC';

    return array_map('clicketStaffEventRowToPanel', clicketDbFetchAll($sql, $params));
}

function clicketStaffEventAllowed(array $staff, array $event, array $scopedVenues): bool {
    if (($staff['role'] ?? '') === 'admin') {
        return true;
    }

    $sessionUserId = (string) ($staff['session_user_id'] ?? $staff['id'] ?? '');

    return (string) ($event['organizer_id'] ?? '') !== ''
        && (string) ($event['organizer_id'] ?? '') === $sessionUserId;
}

function clicketStaffScopedEvents(array $staff, array $scopedVenues): array {
    return clicketStaffAllEvents($staff);
}

function clicketStaffCanAccessEvent(array $staff, string $eventKey): bool {
    if (($staff['role'] ?? '') === 'admin') {
        return true;
    }

    $staffId = clicketDbStaffIdBySession($staff);
    if (!$staffId) {
        return false;
    }

    return clicketDbFetch(
        'SELECT id FROM events WHERE event_key = :event_key AND created_by_staff_id = :staff_id LIMIT 1',
        ['event_key' => $eventKey, 'staff_id' => $staffId]
    ) !== null;
}

function clicketStaffEventLayoutOptions(array $staff): array {
    $params = [];
    $where = 'WHERE v.status = "active" AND vl.status = "active"';

    if (($staff['role'] ?? '') !== 'admin') {
        $staffId = clicketDbStaffIdBySession($staff) ?? 0;
        $assignedVenueIds = clicketDbFetchAll(
            'SELECT venue_id FROM staff_venue_assignments WHERE staff_id = :staff_id ORDER BY venue_id',
            ['staff_id' => $staffId]
        );
        $venueIds = array_values(array_map(static fn (array $row): int => (int) $row['venue_id'], $assignedVenueIds));

        if ($venueIds) {
            $placeholders = implode(',', array_fill(0, count($venueIds), '?'));
            $where .= ' AND v.id IN (' . $placeholders . ')';
            $params = $venueIds;
        }
    }

    $rows = clicketDbExecute(
        'SELECT vl.id AS venue_layout_id,
                vl.layout_key,
                vl.variant,
                vl.category,
                vl.capacity,
                v.id AS venue_id,
                v.name AS venue_name
         FROM venue_layouts vl
         INNER JOIN venues v ON v.id = vl.venue_id
         ' . $where . '
         ORDER BY v.name, vl.category, vl.variant',
        $params
    )->fetchAll();

    return array_map(static function (array $row): array {
        $category = (string) ($row['category'] ?? '');
        $variant = (string) ($row['variant'] ?? '');
        $venue = (string) ($row['venue_name'] ?? '');

        return [
            'venue_layout_id' => (int) $row['venue_layout_id'],
            'venue_id' => (int) $row['venue_id'],
            'venue' => $venue,
            'variant' => $variant,
            'category' => $category,
            'category_label' => clicketStaffEventCategoryLabel($category),
            'capacity' => (int) ($row['capacity'] ?? 0),
            'label' => trim($venue . ' - ' . clicketStaffEventCategoryLabel($category) . ($variant !== '' ? ' / ' . ucfirst($variant) : '')),
        ];
    }, $rows);
}

function clicketStaffVenuesForEvents(array $events): array {
    return array_values(array_filter(
        clicketStaffVenueDefinitions(),
        static function (array $venue) use ($events): bool {
            foreach ($events as $event) {
                foreach ($venue['aliases'] as $alias) {
                    if (clicketStaffVenueNamesMatch((string) ($event['venue'] ?? ''), (string) $alias)) {
                        return true;
                    }
                }
            }

            return false;
        }
    ));
}

function clicketStaffScopedOrders(array $staff, array $scopedEvents): array {
    $orders = clicketReadOrders();
    if (($staff['role'] ?? '') === 'admin') {
        return $orders;
    }

    $eventKeys = array_map(static fn (array $event): string => strtolower((string) ($event['key'] ?? '')), $scopedEvents);
    $eventTitles = array_map(static fn (array $event): string => strtolower((string) ($event['title'] ?? '')), $scopedEvents);

    return array_values(array_filter($orders, static function (array $order) use ($eventKeys, $eventTitles): bool {
        $orderEventKey = strtolower((string) ($order['event'] ?? ''));
        $orderEventTitle = strtolower((string) ($order['event_title'] ?? ''));

        return ($orderEventKey !== '' && in_array($orderEventKey, $eventKeys, true))
            || ($orderEventTitle !== '' && in_array($orderEventTitle, $eventTitles, true));
    }));
}

function clicketStaffCanAccessOrder(array $staff, array $order): bool {
    if (($staff['role'] ?? '') === 'admin') {
        return true;
    }

    $events = clicketStaffScopedEvents($staff, clicketStaffScopedVenues($staff));
    $eventKeys = array_map(static fn (array $event): string => strtolower((string) ($event['key'] ?? '')), $events);
    $eventTitles = array_map(static fn (array $event): string => strtolower((string) ($event['title'] ?? '')), $events);
    $orderEventKey = strtolower((string) ($order['event'] ?? ''));
    $orderEventTitle = strtolower((string) ($order['event_title'] ?? ''));

    return ($orderEventKey !== '' && in_array($orderEventKey, $eventKeys, true))
        || ($orderEventTitle !== '' && in_array($orderEventTitle, $eventTitles, true));
}

function clicketDenyStaffResource(string $message = 'This resource is outside your assigned scope.'): never {
    http_response_code(403);
    exit($message);
}

function clicketRequireStaffCanAccessEventKey(string $eventKey): array {
    clicketRequireStaff();
    $staff = currentStaff();
    if (!$staff || !clicketStaffCanAccessEvent($staff, $eventKey)) {
        clicketDenyStaffResource('Event is outside your assigned scope.');
    }

    return $staff;
}

function clicketRequireStaffCanAccessOrderRecord(array $order): array {
    clicketRequireStaff();
    $staff = currentStaff();
    if (!$staff || !clicketStaffCanAccessOrder($staff, $order)) {
        clicketDenyStaffResource('Order is outside your assigned scope.');
    }

    return $staff;
}

function clicketRequireStaffCanAccessOrderJson(array $staff, array $order): void {
    if (clicketStaffCanAccessOrder($staff, $order)) {
        return;
    }

    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Order is outside your assigned scope.']);
    exit;
}

function clicketStaffEventLookup(array $events): array {
    $lookup = ['keys' => [], 'titles' => []];
    foreach ($events as $event) {
        $key = strtolower((string) ($event['key'] ?? ''));
        $title = strtolower((string) ($event['title'] ?? ''));
        if ($key !== '') {
            $lookup['keys'][$key] = true;
        }
        if ($title !== '') {
            $lookup['titles'][$title] = true;
        }
    }

    return $lookup;
}

function clicketStaffRecordBelongsToEvents(array $record, array $events): bool {
    $lookup = clicketStaffEventLookup($events);
    $eventKey = strtolower((string) ($record['event'] ?? $record['event_key'] ?? ''));
    $eventTitle = strtolower((string) ($record['event_title'] ?? $record['title'] ?? ''));

    return ($eventKey !== '' && isset($lookup['keys'][$eventKey]))
        || ($eventTitle !== '' && isset($lookup['titles'][$eventTitle]));
}

function clicketStaffScopedReservations(array $staff, array $reservations, array $events): array {
    if (($staff['role'] ?? '') === 'admin') {
        return $reservations;
    }

    return array_values(array_filter(
        $reservations,
        static fn (array $reservation): bool => clicketStaffRecordBelongsToEvents($reservation, $events)
    ));
}

function clicketStaffTicketCount(array $order): int {
    if (is_array($order['tickets'] ?? null)) {
        return count($order['tickets']);
    }

    return count(is_array($order['seats'] ?? null) ? $order['seats'] : []);
}

function clicketStaffMoneyValue(mixed $value): int {
    if (is_numeric($value)) {
        return (int) $value;
    }

    return (int) preg_replace('/[^0-9]/', '', (string) $value);
}

function clicketStaffDateKey(array $order): string {
    $date = (string) ($order['booked_at'] ?? $order['created_at'] ?? '');
    $timestamp = strtotime($date) ?: time();

    return date('M d', $timestamp);
}

function clicketStaffBuildTrend(array $orders, string $mode): array {
    $bucket = [];
    foreach ($orders as $order) {
        $key = clicketStaffDateKey($order);
        if (!isset($bucket[$key])) {
            $bucket[$key] = 0;
        }

        $bucket[$key] += $mode === 'tickets'
            ? clicketStaffTicketCount($order)
            : (int) ($order['total'] ?? 0);
    }

    if (!$bucket) {
        return [
            ['label' => 'Mon', 'value' => $mode === 'tickets' ? 82 : 68000],
            ['label' => 'Tue', 'value' => $mode === 'tickets' ? 116 : 93000],
            ['label' => 'Wed', 'value' => $mode === 'tickets' ? 94 : 76000],
            ['label' => 'Thu', 'value' => $mode === 'tickets' ? 148 : 132000],
            ['label' => 'Fri', 'value' => $mode === 'tickets' ? 176 : 158000],
            ['label' => 'Sat', 'value' => $mode === 'tickets' ? 213 : 192000],
            ['label' => 'Sun', 'value' => $mode === 'tickets' ? 165 : 141000],
        ];
    }

    return array_slice(array_map(
        static fn (string $label, int $value): array => ['label' => $label, 'value' => $value],
        array_keys($bucket),
        array_values($bucket)
    ), -7);
}

function clicketStaffFlattenTickets(array $orders): array {
    $tickets = [];
    foreach ($orders as $order) {
        $orderTickets = is_array($order['tickets'] ?? null) ? $order['tickets'] : [];
        foreach ($orderTickets as $ticket) {
            $tickets[] = [
                'ticket_id' => (string) ($ticket['ticket_id'] ?? $ticket['barcode_value'] ?? ''),
                'voucher_id' => (string) ($ticket['voucher_id'] ?? ($order['voucher']['voucher_id'] ?? '')),
                'validation_code' => (string) ($ticket['validation_code'] ?? ''),
                'status' => (string) ($ticket['status'] ?? 'Valid'),
                'category' => (string) ($ticket['category'] ?? ''),
                'section' => (string) ($ticket['section'] ?? ''),
                'row' => (string) ($ticket['row'] ?? ''),
                'number' => (string) ($ticket['number'] ?? ''),
                'price' => (int) ($ticket['price'] ?? 0),
                'order_id' => (string) ($order['order_id'] ?? ''),
                'event_title' => (string) ($order['event_title'] ?? $order['event'] ?? ''),
                'venue' => (string) ($order['venue'] ?? ''),
                'buyer_name' => (string) ($order['buyer_name'] ?? ''),
            ];
        }
    }

    return $tickets;
}

function clicketStaffPaymentMethodSummary(array $orders): array {
    $summary = [];
    foreach ($orders as $order) {
        $method = (string) ($order['payment_method_label'] ?? $order['payment_method'] ?? 'Manual Review');
        if (!isset($summary[$method])) {
            $summary[$method] = ['method' => $method, 'orders' => 0, 'sales' => 0, 'fees' => 0];
        }

        $summary[$method]['orders']++;
        $summary[$method]['sales'] += (int) ($order['total'] ?? 0);
        $summary[$method]['fees'] += (int) ($order['service_fee'] ?? 0);
    }

    usort($summary, static fn (array $a, array $b): int => $b['sales'] <=> $a['sales']);

    return array_values($summary);
}

function clicketStaffSectionInventory(array $orders, array $venues): array {
    $sections = [];
    foreach ($orders as $order) {
        $venueName = (string) ($order['venue'] ?? 'Unassigned venue');
        foreach ((array) ($order['seats'] ?? []) as $seat) {
            $section = (string) ($seat['section'] ?? 'General');
            $key = strtolower($venueName . '|' . $section);
            if (!isset($sections[$key])) {
                $sections[$key] = [
                    'venue' => $venueName,
                    'section' => $section,
                    'sold' => 0,
                    'held' => 0,
                    'blocked' => 0,
                    'accessible' => 0,
                    'complimentary' => 0,
                    'available' => 0,
                ];
            }
            $sections[$key]['sold']++;
        }
    }

    if (!$sections) {
        foreach (array_slice($venues, 0, 6) as $index => $venue) {
            $sections[$venue['id'] . '-sample'] = [
                'venue' => (string) $venue['venue'],
                'section' => ['Floor A', 'Lower Box 101', 'Upper Box 401', 'Balcony Center', 'Patron A', 'VIP Deck'][$index] ?? 'Main',
                'sold' => 24 + ($index * 7),
                'held' => $index % 4,
                'blocked' => $index % 3,
                'accessible' => 8 + $index,
                'complimentary' => $index % 2,
                'available' => 160 - ($index * 11),
            ];
        }
    } else {
        foreach ($sections as $key => $row) {
            $sold = (int) $row['sold'];
            $sections[$key]['held'] = max(1, $sold % 5);
            $sections[$key]['blocked'] = $sold % 3;
            $sections[$key]['accessible'] = 6 + ($sold % 4);
            $sections[$key]['complimentary'] = $sold % 2;
            $sections[$key]['available'] = max(0, 180 - $sold - $sections[$key]['held'] - $sections[$key]['blocked']);
        }
    }

    return array_values($sections);
}

function clicketStaffBuildReservationRows(array $reservations, array $orders): array {
    $rows = [];
    foreach ($reservations as $index => $hold) {
        $expiresAt = (int) ($hold['expires_at'] ?? (time() + 600));
        $rows[] = [
            'id' => (string) ($hold['id'] ?? 'HLD-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)),
            'event' => (string) ($hold['event_title'] ?? $hold['event'] ?? 'Seat hold'),
            'venue' => (string) ($hold['venue'] ?? 'Assigned venue'),
            'buyer' => (string) ($hold['buyer_name'] ?? $hold['user_id'] ?? 'Guest checkout'),
            'seats' => clicketStaffTicketCount($hold),
            'status' => $expiresAt > time() ? 'Active' : 'Expired',
            'expires_at' => $expiresAt,
            'expires_label' => date('H:i:s', $expiresAt),
        ];
    }

    if ($rows) {
        return $rows;
    }

    foreach (array_slice($orders, 0, 4) as $index => $order) {
        $expiresAt = time() + (($index + 2) * 180);
        $rows[] = [
            'id' => 'HLD-' . strtoupper(substr(md5((string) ($order['order_id'] ?? $index)), 0, 8)),
            'event' => (string) ($order['event_title'] ?? 'Checkout hold'),
            'venue' => (string) ($order['venue'] ?? 'Assigned venue'),
            'buyer' => (string) ($order['buyer_name'] ?? 'Guest checkout'),
            'seats' => clicketStaffTicketCount($order),
            'status' => $index === 3 ? 'Expired' : 'Active',
            'expires_at' => $index === 3 ? time() - 90 : $expiresAt,
            'expires_label' => date('H:i:s', $expiresAt),
        ];
    }

    return $rows;
}

function clicketStaffBuildFavoriteRows(array $favorites, array $events, array $orders): array {
    $counts = [];
    foreach ($favorites as $favorite) {
        $title = (string) ($favorite['event_title'] ?? $favorite['title'] ?? $favorite['event'] ?? '');
        if ($title === '') {
            continue;
        }
        $counts[$title] = ($counts[$title] ?? 0) + 1;
    }

    foreach ($orders as $order) {
        $title = (string) ($order['event_title'] ?? '');
        if ($title !== '') {
            $counts[$title] = ($counts[$title] ?? 0) + max(2, clicketStaffTicketCount($order) * 3);
        }
    }

    foreach ($events as $index => $event) {
        $title = (string) ($event['title'] ?? 'Untitled event');
        $counts[$title] = ($counts[$title] ?? 0) + (12 + (($index * 17) % 64));
    }

    $rows = [];
    foreach ($counts as $title => $count) {
        $event = current(array_filter($events, static fn (array $item): bool => (string) ($item['title'] ?? '') === $title)) ?: [];
        $rows[] = [
            'title' => $title,
            'venue' => (string) ($event['venue'] ?? 'Multiple venues'),
            'category' => (string) ($event['category_label'] ?? 'Event'),
            'favorites' => $count,
            'trend' => min(99, 8 + ($count % 45)),
        ];
    }

    usort($rows, static fn (array $a, array $b): int => $b['favorites'] <=> $a['favorites']);

    return array_slice($rows, 0, 8);
}

function clicketStaffNewsRows(array $events): array {
    $rows = [];
    $statuses = ['Draft', 'Published', 'Draft', 'Archived'];
    foreach (array_slice($events, 0, 8) as $index => $event) {
        $status = $statuses[$index % count($statuses)];
        $rows[] = [
            'event_key' => (string) ($event['key'] ?? ''),
            'organizer_id' => (string) ($event['organizer_id'] ?? ''),
            'title' => (string) ($event['title'] ?? 'Event') . ' update',
            'event_title' => (string) ($event['title'] ?? ''),
            'author' => (string) ($event['organizer_name'] ?? 'Organizer'),
            'status' => $status,
            'featured' => $status === 'Published' && $index % 3 === 0 ? 'Yes' : 'No',
            'updated' => $index === 0 ? 'Today' : 'Jun ' . (18 - min(10, $index)),
        ];
    }

    return $rows;
}

function clicketStaffArchiveRows(array $orders, array $events): array {
    $rows = [];
    foreach (array_slice(array_reverse($events), 0, 4) as $event) {
        $rows[] = [
            'type' => 'Archived event',
            'title' => (string) ($event['title'] ?? 'Event'),
            'scope' => (string) ($event['venue'] ?? 'Venue'),
            'status' => (string) ($event['status'] ?? 'Archived'),
            'archived_at' => 'Retention ready',
        ];
    }

    foreach (array_slice($orders, 0, 4) as $order) {
        $rows[] = [
            'type' => 'Archived order',
            'title' => (string) ($order['order_id'] ?? 'Order'),
            'scope' => (string) ($order['event_title'] ?? 'Event'),
            'status' => (string) ($order['order_status'] ?? 'Confirmed'),
            'archived_at' => (string) ($order['booked_at'] ?? 'Order history'),
        ];
    }

    return $rows;
}

function clicketStaffPanelPayload(array $staff): array {
    $isAdmin = ($staff['role'] ?? '') === 'admin';
    $events = clicketStaffScopedEvents($staff, clicketStaffVenueDefinitions());
    $eventLayoutOptions = clicketStaffEventLayoutOptions($staff);
    $scopedVenues = $isAdmin ? clicketStaffVenueDefinitions() : clicketStaffVenuesForEvents($events);
    $orders = clicketStaffScopedOrders($staff, $events);
    $reservations = clicketStaffScopedReservations(
        $staff,
        clicketReadReservationRows(),
        $events
    );
    $favorites = clicketReadFavorites();
    $users = getUsers();
    $allStaff = getStaffAccounts();

    $paidOrders = array_values(array_filter($orders, static fn (array $order): bool => strtolower((string) ($order['payment_status'] ?? '')) === 'paid'));
    $pendingOrders = array_values(array_filter($orders, static fn (array $order): bool => strtolower((string) ($order['payment_status'] ?? '')) === 'pending'));
    $ticketsSold = array_sum(array_map(static fn (array $order): int => clicketStaffTicketCount($order), $paidOrders));
    $sales = array_sum(array_map(static fn (array $order): int => (int) ($order['total'] ?? 0), $paidOrders));
    $activeReservations = array_values(array_filter($reservations, static fn (array $hold): bool => (int) ($hold['expires_at'] ?? 0) > time()));
    $tickets = clicketStaffFlattenTickets($orders);

    $topEvents = [];
    foreach ($orders as $order) {
        $title = (string) ($order['event_title'] ?? $order['event'] ?? 'Untitled event');
        if (!isset($topEvents[$title])) {
            $topEvents[$title] = ['title' => $title, 'sales' => 0, 'tickets' => 0];
        }
        $topEvents[$title]['sales'] += (int) ($order['total'] ?? 0);
        $topEvents[$title]['tickets'] += clicketStaffTicketCount($order);
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
        $sold = array_sum(array_map(static fn (array $order): int => clicketStaffTicketCount($order), $venueOrders));
        $capacity = max(1, (int) ($venue['capacity'] ?? 0));
        $venueRows[] = $venue + [
            'sold' => $sold,
            'available' => max(0, $capacity - $sold),
            'held' => count($activeReservations),
            'sales' => array_sum(array_map(static fn (array $order): int => (int) ($order['total'] ?? 0), $venueOrders)),
            'occupancy' => min(100, (int) round(($sold / $capacity) * 100)),
        ];
    }

    $paymentMethods = clicketStaffPaymentMethodSummary($orders);
    $serviceFees = array_sum(array_map(static fn (array $order): int => (int) ($order['service_fee'] ?? 0), $orders));
    $lowInventory = array_values(array_filter($venueRows, static fn (array $venue): bool => ($venue['occupancy'] ?? 0) >= 70));
    $reservationRows = clicketStaffBuildReservationRows($activeReservations, $orders);
    $favoriteRows = clicketStaffBuildFavoriteRows($favorites, $events, $orders);
    $sectionInventory = clicketStaffSectionInventory($orders, $venueRows);
    $tierInventory = [];
    foreach ($venueRows as $venue) {
        foreach ($venue['tiers'] as $tierIndex => $tier) {
            $tierCapacity = max(1, (int) floor($venue['capacity'] / max(1, count($venue['tiers']))));
            $sold = min($tierCapacity, max(0, (int) floor(($venue['sold'] + $tierIndex * 3) / max(1, count($venue['tiers'])))));
            $held = $tierIndex % 3;
            $tierInventory[] = [
                'venue' => (string) $venue['venue'],
                'variant' => (string) $venue['variant'],
                'tier' => (string) $tier['name'],
                'capacity' => $tierCapacity,
                'sold' => $sold,
                'held' => $held,
                'available' => max(0, $tierCapacity - $sold - $held),
                'revenue' => $sold * max(650, 1800 - ($tierIndex * 100)),
            ];
        }
    }

    $audit = [
        ['type' => 'Payment approval', 'actor' => 'ClicKet Admin', 'scope' => 'All venues', 'time' => 'Live audit stream'],
        ['type' => 'Seat block/release', 'actor' => $staff['name'] ?? 'Authorized user', 'scope' => ($staff['role'] ?? '') === 'admin' ? 'System-wide' : 'Assigned venues', 'time' => 'Tracked per action'],
        ['type' => 'Event archive', 'actor' => 'Organizer / Admin', 'scope' => 'Permission controlled', 'time' => 'Requires reason log'],
        ['type' => 'Tier price change', 'actor' => 'Authorized staff', 'scope' => 'Venue tier', 'time' => 'Before/after value saved'],
    ];

    return [
        'venues' => $venueRows,
        'eventVenueOptions' => $eventLayoutOptions,
        'events' => $events,
        'orders' => $isAdmin ? $orders : [],
        'payments' => $isAdmin ? $orders : [],
        'tickets' => $isAdmin ? $tickets : [],
        'reservations' => $activeReservations,
        'reservationRows' => $reservationRows,
        'favorites' => $isAdmin ? $favorites : [],
        'favoriteRows' => $isAdmin ? $favoriteRows : [],
        'users' => $isAdmin ? $users : [],
        'staff' => $isAdmin ? $allStaff : [],
        'topEvents' => $isAdmin ? array_slice(array_values($topEvents), 0, 5) : [],
        'topVenues' => $isAdmin ? array_slice(array_values($topVenues), 0, 5) : [],
        'lowInventory' => $isAdmin ? $lowInventory : [],
        'revenueTrend' => $isAdmin ? clicketStaffBuildTrend($paidOrders, 'revenue') : [],
        'ticketTrend' => $isAdmin ? clicketStaffBuildTrend($paidOrders, 'tickets') : [],
        'paymentMethods' => $isAdmin ? $paymentMethods : [],
        'sectionInventory' => $isAdmin ? $sectionInventory : [],
        'tierInventory' => $isAdmin ? $tierInventory : [],
        'news' => clicketStaffNewsRows($events),
        'archives' => $isAdmin ? clicketStaffArchiveRows($orders, $events) : [],
        'audit' => $isAdmin ? $audit : [],
        'metrics' => [
            'sales' => $isAdmin ? $sales : 0,
            'ticketsSold' => $isAdmin ? $ticketsSold : 0,
            'activeEvents' => count(array_filter($events, static fn (array $event): bool => strtolower($event['status']) !== 'archived')),
            'pendingPayments' => $isAdmin ? count($pendingOrders) : 0,
            'activeReservations' => count(array_filter($reservationRows, static fn (array $row): bool => $row['status'] === 'Active')),
            'lowInventory' => $isAdmin ? count(array_filter($venueRows, static fn (array $venue): bool => ($venue['occupancy'] ?? 0) >= 80)) : 0,
            'orders' => $isAdmin ? count($orders) : 0,
            'users' => $isAdmin ? count($users) : 0,
            'serviceFees' => $isAdmin ? $serviceFees : 0,
            'tickets' => $isAdmin ? count($tickets) : 0,
            'staff' => $isAdmin ? count($allStaff) : 0,
        ],
    ];
}
