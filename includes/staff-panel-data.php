<?php

require_once __DIR__ . '/order-history-data.php';
require_once __DIR__ . '/news-data.php';
require_once __DIR__ . '/reservation.php';
require_once __DIR__ . '/favorite-data.php';
require_once __DIR__ . '/inventory-sync.php';
require_once __DIR__ . '/ticket-validation.php';
require_once __DIR__ . '/virtual-queue.php';
require_once __DIR__ . '/ticketing.php';

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
            'logo' => 'MOA.png',
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
            'logo' => 'MOA.png',
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
            'logo' => 'PArena.png',
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
            'logo' => 'Smart.png',
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
            'logo' => 'Smart.png',
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
            'venue' => 'Tanghalang Ignacio Jimenez',
            'variant' => 'Theater',
            'category' => 'theater',
            'profileVenue' => 'Tanghalang Ignacio Jimenez',
            'capacity' => 320,
            'svg' => 'Tanghalan.svg',
            'logo' => 'TP.png',
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
            'logo' => 'Newport.png',
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
            'logo' => 'Solaire.png',
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
            'logo' => 'Philsports.png',
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

function clicketStaffAssignmentOptions(): array {
    return array_map(static fn (array $venue): array => [
        'id' => (string) $venue['id'],
        'label' => (string) $venue['name'] . ' — ' . clicketStaffEventCategoryLabel((string) $venue['category']),
    ], clicketDbFetchAll(
        'SELECT id, name, category
         FROM (
             SELECT DISTINCT v.id, v.name, vl.category
             FROM venues v
             INNER JOIN venue_layouts vl ON vl.venue_id = v.id
             WHERE v.status = "active" AND vl.status = "active"
               AND NOT (v.slug = "philippine-arena" AND vl.category = "sports")
               AND NOT (v.slug = "philsports-arena" AND vl.category = "concert")
             UNION
             SELECT v.id, v.name, "sports" AS category
             FROM venues v
             WHERE v.status = "active" AND v.slug = "mall-of-asia-arena"
             UNION
             SELECT v.id, v.name, "concert" AS category
             FROM venues v
             WHERE v.status = "active" AND v.slug = "smart-araneta-coliseum"
             UNION
             SELECT v.id, v.name, "sports" AS category
             FROM venues v
             WHERE v.status = "active" AND v.slug = "smart-araneta-coliseum"
         ) AS assignment_venues
         ORDER BY name, category'
    ));
}

function clicketStaffAssignmentLabel(string $assignment): string {
    foreach (clicketStaffAssignmentOptions() as $option) {
        if ($option['id'] === $assignment) return $option['label'];
    }
    return $assignment;
}

function clicketStaffPeopleRows(array $users, array $staffAccounts): array {
    $people = [];

    foreach ($users as $user) {
        $dbId = (string) ($user['id'] ?? '');
        $status = strtolower((string) ($user['status'] ?? 'active'));
        $user['id'] = 'user-' . $dbId;
        $user['db_id'] = $dbId;
        $user['account_table'] = 'users';
        $user['role'] = 'customer';
        $user['disabled'] = $status !== 'active';
        $people[] = $user;
    }

    foreach ($staffAccounts as $account) {
        $dbId = (string) ($account['session_user_id'] ?? $account['id'] ?? '');
        $status = strtolower((string) ($account['status'] ?? 'active'));
        $account['id'] = 'staff-' . $dbId;
        $account['db_id'] = $dbId;
        $account['account_table'] = 'staff_accounts';
        $account['disabled'] = $status !== 'active';
        $people[] = $account;
    }

    return $people;
}

function clicketStaffVenueAllowed(array $staff, array $venue): bool {
    if (($staff['role'] ?? '') === 'admin') {
        return true;
    }
    $assigned = array_map('strtolower', clicketStaffAssignedVenueNames($staff));
    if (in_array(strtolower((string) ($venue['id'] ?? '')), $assigned, true)) {
        return true;
    }
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

function clicketStaffOrganizerForVenue(string $eventVenue, string $eventCategory = ''): ?array {
    foreach (getStaffAccounts() as $account) {
        if (($account['role'] ?? '') !== 'organizer') {
            continue;
        }
        foreach (clicketStaffVenueDefinitions() as $venue) {
            if (($eventCategory === '' || strtolower((string) ($venue['category'] ?? '')) === strtolower($eventCategory))
                && clicketStaffVenueAllowed($account, $venue)
                && array_filter((array) ($venue['aliases'] ?? []), static fn (string $alias): bool => clicketStaffVenueNamesMatch($eventVenue, $alias))) {
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

function clicketStaffEventSchedules(int $eventId): array {
    return array_map(static function (array $row): array {
        $date = (string) ($row['performance_date'] ?? '');
        $time = (string) ($row['performance_time'] ?? '');

        return [
            'id' => (int) ($row['id'] ?? 0),
            'date' => $date,
            'time' => $time,
            'label' => trim(clicketDbDisplayDate($date) . ' at ' . clicketDbDisplayTime($time)),
            'status' => (string) ($row['status'] ?? 'scheduled'),
        ];
    }, clicketDbFetchAll(
        'SELECT id, performance_date, performance_time, status
         FROM event_performances
         WHERE event_id = :event_id AND status <> "cancelled"
         ORDER BY performance_date, performance_time, id',
        ['event_id' => $eventId]
    ));
}

function clicketStaffLayoutTierRows(int $venueLayoutId, ?int $eventId = null): array {
    if (function_exists('clicketTicketSyncVenueLayoutFromDatabase')) {
        clicketTicketSyncVenueLayoutFromDatabase($venueLayoutId);
    }

    $params = ['venue_layout_id' => $venueLayoutId];
    $eventJoin = '';
    if ($eventId !== null) {
        $eventJoin = 'LEFT JOIN event_tier_settings ets ON ets.tier_id = vt.id AND ets.event_id = :event_id';
        $params['event_id'] = $eventId;
    } else {
        $eventJoin = 'LEFT JOIN event_tier_settings ets ON 1 = 0';
    }

    return array_map(static function (array $row): array {
        $capacity = (int) ($row['event_capacity'] ?? 0);
        if ($capacity <= 0) {
            $capacity = (int) ($row['seat_capacity'] ?? 0);
        }
        $sold = (int) ($row['sold_count'] ?? 0);
        $held = (int) ($row['held_count'] ?? 0);
        $available = (int) ($row['available_count'] ?? 0);
        if (empty($row['setting_id'])) {
            $available = max(0, $capacity - $sold - $held);
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'tier_id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'color' => (string) ($row['color'] ?? '#d8b7ff'),
            'price' => (float) ($row['price'] ?? 0),
            'price_label' => clicketDbFormatPrice($row['price'] ?? 0),
            'capacity' => $capacity,
            'sold' => $sold,
            'held' => $held,
            'available' => $available,
            'status' => (string) ($row['tier_status'] ?? 'active'),
        ];
    }, clicketDbFetchAll(
        'SELECT vt.id,
                vt.name,
                vt.color,
                COALESCE(ets.price, 0) AS price,
                ets.id AS setting_id,
                COALESCE(ets.capacity, 0) AS event_capacity,
                COALESCE(ets.sold_count, 0) AS sold_count,
                COALESCE(ets.held_count, 0) AS held_count,
                COALESCE(ets.available_count, 0) AS available_count,
                COALESCE(ets.status, vt.default_status) AS tier_status,
                COALESCE(SUM(CASE WHEN s.id IS NULL THEN vs.capacity ELSE 1 END), 0) AS seat_capacity
         FROM venue_tiers vt
         LEFT JOIN venue_sections vs ON vs.tier_id = vt.id
         LEFT JOIN seats s ON s.venue_section_id = vs.id
         ' . $eventJoin . '
         WHERE vt.venue_layout_id = :venue_layout_id
           AND vt.default_status = "active"
         GROUP BY vt.id, vt.name, vt.color, ets.id, ets.price, ets.capacity, ets.sold_count, ets.held_count, ets.available_count, ets.status, vt.default_status, vt.sort_order
         ORDER BY vt.sort_order, vt.name',
        $params
    ));
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
        'description' => (string) ($row['description'] ?? ''),
        'cast_performers' => (string) ($row['cast_performers'] ?? ''),
        'cast_logo_url' => (string) ($row['cast_logo_url'] ?? ''),
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
        'running_minutes' => (int) ($row['running_minutes'] ?? 0),
        'age_range' => (string) ($row['age_range'] ?? ''),
        'doors_open_minutes' => (int) ($row['doors_open_minutes'] ?? 0),
        'owner' => clicketStaffEventOwnerLabel($row),
        'artist' => (string) ($row['artist'] ?? ''),
        'company' => (string) ($row['company'] ?? ''),
        'league' => (string) ($row['league'] ?? ''),
        'poster_url' => (string) ($row['poster_url'] ?? ''),
        'banner_url' => (string) ($row['banner_url'] ?? ''),
        'status' => clicketStaffEventStatusLabel($status),
        'status_value' => $status,
        'archived_at' => clicketDbDisplayDateTime((string) ($row['archived_at'] ?? '')),
        'schedules' => clicketStaffEventSchedules((int) $row['id']),
        'tiers' => clicketStaffLayoutTierRows((int) $row['venue_layout_id'], (int) $row['id']),
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
        $sql .= ' WHERE EXISTS (
                    SELECT 1
                    FROM staff_venue_assignments sva
                    WHERE sva.staff_id = :staff_id
                      AND sva.venue_id = e.venue_id
                  )';
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

    if ((string) ($event['organizer_id'] ?? '') === $sessionUserId) return true;

    foreach ($scopedVenues as $venue) {
        if (strtolower((string) ($event['category'] ?? '')) !== strtolower((string) ($venue['category'] ?? ''))) continue;
        foreach ((array) ($venue['aliases'] ?? []) as $alias) {
            if (clicketStaffVenueNamesMatch((string) ($event['venue'] ?? ''), (string) $alias)) return true;
        }
    }
    return false;
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
        'SELECT e.id
         FROM events e
         INNER JOIN staff_venue_assignments sva ON sva.venue_id = e.venue_id
         WHERE e.event_key = :event_key AND sva.staff_id = :staff_id
         LIMIT 1',
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

        if (!$venueIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($venueIds), '?'));
        $where .= ' AND v.id IN (' . $placeholders . ')';
        $params = $venueIds;
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
            'tiers' => clicketStaffLayoutTierRows((int) $row['venue_layout_id'], null),
        ];
    }, $rows);
}

function clicketStaffVenuesForEvents(array $events): array {
    return array_values(array_filter(
        clicketStaffVenueDefinitions(),
        static function (array $venue) use ($events): bool {
            foreach ($events as $event) {
                if (strtolower((string) ($event['category'] ?? '')) !== strtolower((string) ($venue['category'] ?? ''))) {
                    continue;
                }
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

function clicketStaffOrderProofUrl(array $order): string {
    $candidates = [];
    $storedPath = trim((string) ($order['proof_file_path'] ?? ''));
    if ($storedPath !== '') {
        $candidates[] = $storedPath;
    }
    $proof = trim((string) ($order['proof_of_payment'] ?? ''));
    if ($proof !== '') {
        $candidates[] = $proof;
    }

    foreach ($candidates as $candidate) {
        $filename = basename(str_replace('\\', '/', $candidate));
        if ($filename === '') {
            continue;
        }

        foreach (['uploads/payment_proofs', 'storage/payment-proofs'] as $directory) {
            $path = __DIR__ . '/../' . $directory . '/' . $filename;
            if (is_file($path)) {
                return $directory . '/' . rawurlencode($filename);
            }
        }
    }

    return '';
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

function clicketStaffBuildReservationRows(array $reservations, array $orders): array {
    $rows = [];
    foreach ($reservations as $index => $hold) {
        $expiresAt = (int) ($hold['expires_at'] ?? (time() + 600));
        $rawStatus = strtolower((string) ($hold['raw_status'] ?? $hold['status'] ?? ''));
        $status = match ($rawStatus) {
            'active' => $expiresAt > time() ? 'Active' : 'Expired',
            'released' => 'Released',
            'converted' => 'Converted',
            'expired' => 'Expired',
            default => $expiresAt > time() ? 'Active' : 'Expired',
        };

        $rows[] = [
            'id' => (string) ($hold['id'] ?? 'HLD-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)),
            'db_id' => (int) ($hold['db_id'] ?? 0),
            'token' => (string) ($hold['token'] ?? ''),
            'event_key' => (string) ($hold['event'] ?? ''),
            'event_id' => (int) ($hold['event_id'] ?? 0),
            'performance_id' => (int) ($hold['performance_id'] ?? 0),
            'event' => (string) ($hold['event_title'] ?? $hold['event'] ?? 'Seat hold'),
            'venue' => (string) ($hold['venue'] ?? 'Assigned venue'),
            'buyer' => (string) ($hold['buyer_name'] ?? $hold['user_id'] ?? 'Guest checkout'),
            'seats' => clicketStaffTicketCount($hold),
            'status' => $status,
            'expires_at' => $expiresAt,
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

function clicketStaffNewsRows(array $events = []): array {
    $rows = clicketReadNews();
    usort($rows, static fn (array $a, array $b): int => strcmp((string) ($b['updated_at'] ?? $b['created_at'] ?? ''), (string) ($a['updated_at'] ?? $a['created_at'] ?? '')));
    return $rows;
}

function clicketStaffArchiveRows(array $orders, array $events, array $users = []): array {
    $eventById = [];
    foreach ($events as $event) {
        $eventById[(int) ($event['db_id'] ?? 0)] = $event;
    }
    $peopleByType = ['user' => [], 'admin' => [], 'organizer' => []];
    foreach ($users as $person) {
        $role = strtolower((string) ($person['role'] ?? 'customer'));
        $type = $role === 'customer' ? 'user' : $role;
        $dbId = (int) ($person['db_id'] ?? preg_replace('/\D+/', '', (string) ($person['id'] ?? '0')));
        if (isset($peopleByType[$type]) && $dbId > 0) $peopleByType[$type][$dbId] = $person;
    }

    $records = clicketDbFetchAll(
        'SELECT ar.id, ar.entity_type, ar.entity_id, ar.reason, ar.archived_at, ar.restored_at
         FROM archive_records ar
         ORDER BY ar.archived_at DESC, ar.id DESC'
    );
    $rows = [];
    foreach ($records as $record) {
        if ($record['restored_at'] !== null) {
            continue;
        }

        $entityType = strtolower((string) $record['entity_type']);
        $entityId = (int) $record['entity_id'];
        if ($entityType === 'event' && isset($eventById[$entityId])) {
            $event = $eventById[$entityId];
            $rows[] = [
                'archive_id' => (int) $record['id'], 'entity_type' => 'event', 'entity_id' => $entityId,
                'event_key' => (string) ($event['event_key'] ?? $event['key'] ?? ''),
                'type' => 'Archived event', 'title' => (string) ($event['title'] ?? 'Event'),
                'scope' => (string) ($event['venue'] ?? 'Venue'), 'status' => 'Archived',
                'reason' => (string) ($record['reason'] ?? ''), 'archived_at' => (string) $record['archived_at'],
            ];
            continue;
        }
        if (isset($peopleByType[$entityType][$entityId])) {
            $person = $peopleByType[$entityType][$entityId];
            $rows[] = [
                'archive_id' => (int) $record['id'], 'entity_type' => $entityType, 'entity_id' => $entityId,
                'type' => $entityType === 'user' ? 'Archived user' : 'Archived ' . $entityType,
                'title' => (string) ($person['name'] ?? 'Account'),
                'scope' => (string) ($person['email'] ?? ucfirst($entityType)), 'status' => 'Disabled',
                'reason' => (string) ($record['reason'] ?? ''), 'archived_at' => (string) $record['archived_at'],
            ];
        }
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
    $people = clicketStaffPeopleRows($users, $allStaff);

    $paidOrders = array_values(array_filter($orders, static fn (array $order): bool => strtolower((string) ($order['payment_status'] ?? '')) === 'paid'));
    $pendingOrders = array_values(array_filter($orders, static fn (array $order): bool => strtolower((string) ($order['payment_status'] ?? '')) === 'pending'));
    $ticketsSold = array_sum(array_map(static fn (array $order): int => clicketStaffTicketCount($order), $paidOrders));
    $sales = array_sum(array_map(static fn (array $order): int => (int) ($order['total'] ?? 0), $paidOrders));
    $activeReservations = array_values(array_filter(
        $reservations,
        static fn (array $hold): bool => strtolower((string) ($hold['raw_status'] ?? $hold['status'] ?? '')) === 'active'
            && (int) ($hold['expires_at'] ?? 0) > time()
    ));
    $tickets = clicketStaffFlattenTickets($orders);
    $eventKeys = array_values(array_filter(array_map(
        static fn (array $event): string => (string) ($event['event_key'] ?? $event['key'] ?? ''),
        $events
    )));
    $inventoryEventKeys = $isAdmin ? null : $eventKeys;

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
    $reservationRows = clicketStaffBuildReservationRows($reservations, $orders);
    $favoriteRows = clicketStaffBuildFavoriteRows($favorites, $events, $orders);
    $sectionInventory = clicketInventorySectionRows($inventoryEventKeys);
    $tierInventory = clicketInventoryTierRows($inventoryEventKeys);
    $inventorySummary = clicketInventorySummary($inventoryEventKeys);
    $attendance = clicketStaffAttendanceMetrics($staff);
    $virtualQueue = clicketVirtualQueueStatsForEvents($events);
    $queueMetrics = clicketVirtualQueueAggregateMetrics($virtualQueue);

    $audit = clicketDbFetchAll(
        'SELECT al.action_type AS type, COALESCE(sa.name, "System") AS actor, al.entity_type AS scope, al.created_at AS time
         FROM audit_logs al
         LEFT JOIN staff_accounts sa ON sa.id = al.actor_staff_id
         ORDER BY al.created_at DESC, al.id DESC
         LIMIT 20'
    );
    $inventorySeats = clicketDbFetchAll('SELECT seat_code, status FROM seats ORDER BY id LIMIT 144');

    return [
        'venues' => $venueRows,
        'eventVenueOptions' => $eventLayoutOptions,
        'events' => $events,
        'orders' => $orders,
        'payments' => $isAdmin ? $orders : [],
        'tickets' => $tickets,
        'reservations' => $activeReservations,
        'reservationRows' => $reservationRows,
        'favorites' => $isAdmin ? $favorites : [],
        'favoriteRows' => $isAdmin ? $favoriteRows : [],
        'users' => $isAdmin ? $people : [],
        'staff' => $isAdmin ? $allStaff : [],
        'topEvents' => array_slice(array_values($topEvents), 0, 5),
        'topVenues' => array_slice(array_values($topVenues), 0, 5),
        'lowInventory' => $isAdmin ? $lowInventory : [],
        'revenueTrend' => clicketStaffBuildTrend($paidOrders, 'revenue'),
        'ticketTrend' => clicketStaffBuildTrend($paidOrders, 'tickets'),
        'paymentMethods' => $isAdmin ? $paymentMethods : [],
        'sectionInventory' => $isAdmin ? $sectionInventory : [],
        'tierInventory' => $isAdmin ? $tierInventory : [],
        'inventorySummary' => $isAdmin ? $inventorySummary : ['capacity' => 0, 'available' => 0, 'sold' => 0, 'held' => 0, 'blocked' => 0],
        'inventorySeats' => $isAdmin ? $inventorySeats : [],
        'attendance' => $attendance,
        'virtualQueue' => $virtualQueue,
        'news' => clicketStaffNewsRows(),
        'archives' => clicketStaffArchiveRows($orders, $events, $isAdmin ? $people : []),
        'audit' => $isAdmin ? $audit : [],
        'metrics' => [
            'sales' => $sales,
            'ticketsSold' => $ticketsSold,
            'activeEvents' => count(array_filter($events, static fn (array $event): bool => strtolower($event['status']) !== 'archived')),
            'pendingPayments' => $isAdmin ? count($pendingOrders) : 0,
            'activeReservations' => count(array_filter($reservationRows, static fn (array $row): bool => $row['status'] === 'Active')),
            'lowInventory' => $isAdmin ? count(array_filter($venueRows, static fn (array $venue): bool => ($venue['occupancy'] ?? 0) >= 80)) : 0,
            'orders' => count($orders),
            'users' => $isAdmin ? count($users) : 0,
            'serviceFees' => $serviceFees,
            'tickets' => count($tickets),
            'staff' => $isAdmin ? count($allStaff) : 0,
            'checkedIn' => (int) ($attendance['checked_in'] ?? 0),
            'attendanceRate' => (int) ($attendance['attendance_rate'] ?? 0),
            'scanAttempts' => (int) ($attendance['scan_attempts'] ?? 0),
            'duplicateScans' => (int) ($attendance['duplicate_scans'] ?? 0),
            'invalidScans' => (int) ($attendance['invalid_scans'] ?? 0),
            'blockedScans' => (int) ($attendance['blocked_scans'] ?? 0),
            'queueSize' => (int) ($queueMetrics['queueSize'] ?? 0),
            'queueActiveSessions' => (int) ($queueMetrics['queueActiveSessions'] ?? 0),
            'queueAverageWaitSeconds' => (int) ($queueMetrics['queueAverageWaitSeconds'] ?? 0),
        ],
    ];
}
