<?php

require_once __DIR__ . '/data.php';

function clicketTicketCatalogs(): array {
    global $concert_events, $theater_events, $sports_events;

    return [
        'concerts' => [
            'label' => 'Concert',
            'posterCategory' => 'concert',
            'events' => $concert_events,
            'timeOffset' => 0,
        ],
        'theater' => [
            'label' => 'Theater',
            'posterCategory' => 'theater',
            'events' => $theater_events,
            'timeOffset' => 1,
        ],
        'sports' => [
            'label' => 'Sports',
            'posterCategory' => 'sports',
            'events' => $sports_events,
            'timeOffset' => 2,
        ],
    ];
}

function clicketResolveEvent(string $eventKey): ?array {
    if (!preg_match('/^(concerts|theater|sports)-(.+)$/', $eventKey, $matches)) {
        return null;
    }

    $catalogs = clicketTicketCatalogs();
    $categoryKey = $matches[1];
    $catalog = $catalogs[$categoryKey];
    $event = null;
    $eventIndex = 0;

    foreach ($catalog['events'] as $index => $candidate) {
        if ((string) ($candidate['event_key'] ?? $candidate['id'] ?? '') === $eventKey) {
            $event = $candidate;
            $eventIndex = (int) $index;
            break;
        }
    }

    if (!$event && ctype_digit($matches[2])) {
        $eventIndex = (int) $matches[2] - 1;
        $event = $catalog['events'][$eventIndex] ?? null;
    }

    if (!$event) {
        return null;
    }

    $times = ['6:00 PM', '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM'];
    $eventDate = DateTimeImmutable::createFromFormat('M j, Y', $event['date'])
        ?: new DateTimeImmutable($event['date']);

    return [
        'key' => $eventKey,
        'categoryKey' => $categoryKey,
        'categoryLabel' => $catalog['label'],
        'index' => $eventIndex,
        'event' => $event,
        'date' => $eventDate,
        'time' => $times[($eventIndex + $catalog['timeOffset']) % count($times)],
        'poster' => posterUrl($catalog['posterCategory'], $eventIndex + 10),
        'banner' => landscapeUrl($catalog['posterCategory'], $eventIndex + 10),
    ];
}

function clicketTicketCategories(): array {
    return [
        'vip' => ['label' => 'VIP', 'color' => '#e8162b', 'rank' => 1],
        'platinum' => ['label' => 'Platinum', 'color' => '#7c5ce4', 'rank' => 2],
        'gold' => ['label' => 'Gold', 'color' => '#f4a62a', 'rank' => 3],
        'silver' => ['label' => 'Silver', 'color' => '#7aa7ff', 'rank' => 4],
        'bronze' => ['label' => 'Bronze', 'color' => '#a87316', 'rank' => 5],
        'general' => ['label' => 'General Admission', 'color' => '#30a46c', 'rank' => 6],
    ];
}

function clicketTicketDbTierPayload(string $eventKey, array $profile): ?array {
    if (!function_exists('clicketDbFetch')) {
        return null;
    }

    $event = clicketDbFetch(
        'SELECT id, venue_layout_id, base_price
         FROM events
         WHERE event_key = :event_key
         LIMIT 1',
        ['event_key' => $eventKey]
    );
    if (!$event) {
        return null;
    }
    clicketTicketSyncVenueLayoutProfile((int) $event['venue_layout_id'], $profile);

    $rows = clicketDbFetchAll(
        'SELECT vs.svg_polygon_id,
                vs.capacity AS section_capacity,
                vt.id AS tier_id,
                vt.name AS tier_name,
                vt.color AS tier_color,
                vt.sort_order,
                COALESCE(ets.price, e.base_price, 0) AS tier_price
         FROM venue_sections vs
         INNER JOIN venue_tiers vt ON vt.id = vs.tier_id
         INNER JOIN events e ON e.id = :event_id
         LEFT JOIN event_tier_settings ets ON ets.event_id = e.id AND ets.tier_id = vt.id
         WHERE vs.venue_layout_id = :venue_layout_id
           AND vt.default_status = "active"
         ORDER BY vt.sort_order, vt.name, vs.label',
        [
            'event_id' => (int) $event['id'],
            'venue_layout_id' => (int) $event['venue_layout_id'],
        ]
    );
    if (!$rows) {
        return null;
    }

    $bySection = [];
    $categories = [];
    foreach ($rows as $row) {
        $tierId = (int) $row['tier_id'];
        $key = 'tier_' . $tierId;
        $bySection[(string) $row['svg_polygon_id']] = [
            'category' => $key,
            'tier_id' => $tierId,
            'tier' => $key,
            'tierName' => (string) $row['tier_name'],
            'mapColor' => (string) $row['tier_color'],
            'capacity' => (int) ($row['section_capacity'] ?? 0),
        ];
        if (!isset($categories[$key])) {
            $categories[$key] = [
                'label' => (string) $row['tier_name'],
                'color' => (string) $row['tier_color'],
                'rank' => max(1, (int) ($row['sort_order'] ?? 0) + 1),
                'price' => max(0, (int) round((float) ($row['tier_price'] ?? 0))),
            ];
        }
    }

    $profile['sections'] = array_values(array_map(static function (array $section) use ($bySection): array {
        $sectionId = (string) ($section['id'] ?? '');
        if (!isset($bySection[$sectionId])) {
            return $section;
        }

        return array_merge($section, array_filter(
            $bySection[$sectionId],
            static fn ($value): bool => $value !== null && $value !== ''
        ));
    }, $profile['sections'] ?? []));

    $missingCategory = false;
    foreach ($profile['sections'] ?? [] as $section) {
        if (!isset($categories[(string) ($section['category'] ?? '')])) {
            $missingCategory = true;
            break;
        }
    }
    if ($missingCategory) {
        $categories += clicketTicketCategories();
    }

    return ['profile' => $profile, 'categories' => $categories];
}

function clicketTicketFallbackPrice(array $event, string $categoryLabel): int {
    $basePrice = (int) preg_replace('/\D/', '', (string) ($event['price'] ?? '2500'));
    if ($basePrice < 500) {
        $basePrice = 2500;
    }

    $factors = [
        'vip' => 1,
        'platinum' => .82,
        'gold' => .64,
        'silver' => .46,
        'bronze' => .3,
        'general admission' => .24,
        'general' => .24,
    ];
    $key = strtolower(trim($categoryLabel));
    $factor = $factors[$key] ?? .5;

    return (int) (round(($basePrice * $factor) / 50) * 50);
}

function clicketTicketPricingContext(string $eventKey, ?array $resolved = null): array {
    $resolved ??= clicketResolveEvent($eventKey);
    if (!$resolved) {
        return ['profile' => ['sections' => []], 'categories' => [], 'event' => []];
    }

    $profile = clicketVenueProfile((string) ($resolved['event']['venue'] ?? ''), (string) ($resolved['categoryKey'] ?? ''));
    $categories = clicketTicketCategories();
    $dbTierPayload = clicketTicketDbTierPayload($eventKey, $profile);
    if ($dbTierPayload) {
        $profile = $dbTierPayload['profile'];
        $categories = $dbTierPayload['categories'] ?: $categories;
    }

    return [
        'profile' => $profile,
        'categories' => $categories,
        'event' => $resolved['event'],
    ];
}

function clicketTicketSectionForSeatId(string $seatId, array $profile): ?array {
    foreach (($profile['sections'] ?? []) as $section) {
        $sectionId = (string) ($section['id'] ?? '');
        if ($sectionId !== '' && preg_match('/^' . preg_quote($sectionId, '/') . '-([A-Z]{1,2})-(\d{1,3})$/', $seatId)) {
            return $section;
        }
    }

    return null;
}

function clicketTicketCategoryByLabel(array $categories, string $label): ?array {
    $normalized = strtolower(trim($label));
    foreach ($categories as $category) {
        if (strtolower(trim((string) ($category['label'] ?? ''))) === $normalized) {
            return $category;
        }
    }

    return null;
}

function clicketTicketPriceForSeat(string $eventKey, array $seat, ?array $context = null): int {
    $context ??= clicketTicketPricingContext($eventKey);
    $seatId = (string) ($seat['id'] ?? $seat['seat_code'] ?? '');
    $section = $seatId !== '' ? clicketTicketSectionForSeatId($seatId, $context['profile'] ?? []) : null;
    $categoryKey = (string) ($section['category'] ?? '');
    $category = $categoryKey !== '' ? (($context['categories'][$categoryKey] ?? null)) : null;
    if (!$category) {
        $category = clicketTicketCategoryByLabel($context['categories'] ?? [], (string) ($seat['category'] ?? ''));
    }

    if ($category && isset($category['price']) && (int) round((float) $category['price']) > 0) {
        return (int) round((float) $category['price']);
    }

    return clicketTicketFallbackPrice($context['event'] ?? [], (string) ($seat['category'] ?? ($category['label'] ?? 'General Admission')));
}

function clicketTicketPricedSeatRows(string $eventKey, array $seats, ?array $resolved = null): array {
    $context = clicketTicketPricingContext($eventKey, $resolved);

    return array_values(array_map(static function (array $seat) use ($eventKey, $context): array {
        $price = clicketTicketPriceForSeat($eventKey, $seat, $context);
        return $seat + ['price' => $price];
    }, $seats));
}

function clicketTicketBaseTierName(array $section): string {
    $name = trim((string) ($section['tierName'] ?? ''));
    if ($name !== '') {
        return $name;
    }

    $label = trim((string) ($section['label'] ?? 'General Admission'));
    $label = preg_replace('/\s+\d+$/', '', $label) ?? $label;
    return trim($label) !== '' ? trim($label) : 'General Admission';
}

function clicketTicketSyncVenueLayoutProfile(int $venueLayoutId, array $profile): void {
    if ($venueLayoutId <= 0 || empty($profile['sections']) || !function_exists('clicketDbExecute')) {
        return;
    }

    static $synced = [];
    if (isset($synced[$venueLayoutId])) {
        return;
    }
    $synced[$venueLayoutId] = true;

    $tierIds = [];
    $tierSlugs = [];
    $sort = 0;

    foreach ($profile['sections'] as $section) {
        $tierKey = trim((string) ($section['tier'] ?? $section['category'] ?? 'general'));
        $tierName = clicketTicketBaseTierName($section);
        $tierSlug = clicketDbSlug($tierKey !== '' ? $tierKey : $tierName);
        if (isset($tierIds[$tierSlug])) {
            continue;
        }

        clicketDbExecute(
            'INSERT INTO venue_tiers (venue_layout_id, name, slug, color, sort_order, default_status)
             VALUES (:layout_id, :name, :slug, :color, :sort_order, "active")
             ON DUPLICATE KEY UPDATE
               sort_order = VALUES(sort_order),
               default_status = "active"',
            [
                'layout_id' => $venueLayoutId,
                'name' => $tierName,
                'slug' => $tierSlug,
                'color' => (string) ($section['mapColor'] ?? '#d8b7ff'),
                'sort_order' => $sort++,
            ]
        );
        $row = clicketDbFetch(
            'SELECT id FROM venue_tiers WHERE venue_layout_id = :layout_id AND slug = :slug LIMIT 1',
            ['layout_id' => $venueLayoutId, 'slug' => $tierSlug]
        );
        if ($row) {
            $tierIds[$tierSlug] = (int) $row['id'];
            $tierSlugs[] = $tierSlug;
        }
    }

    if (!$tierIds) {
        return;
    }

    $sectionIds = [];
    foreach ($profile['sections'] as $section) {
        $polygonId = trim((string) ($section['id'] ?? ''));
        if ($polygonId === '') {
            continue;
        }

        $tierKey = trim((string) ($section['tier'] ?? $section['category'] ?? 'general'));
        $tierSlug = clicketDbSlug($tierKey !== '' ? $tierKey : clicketTicketBaseTierName($section));
        $tierId = $tierIds[$tierSlug] ?? null;
        if (!$tierId) {
            continue;
        }

        $sectionIds[] = $polygonId;
        clicketDbExecute(
            'INSERT INTO venue_sections
               (venue_layout_id, tier_id, svg_polygon_id, section_number, label, category_key, capacity, map_color, zone, is_seating_section)
             VALUES
               (:layout_id, :tier_id, :polygon_id, :section_number, :label, :category_key, :capacity, :map_color, :zone, 1)
             ON DUPLICATE KEY UPDATE
               tier_id = VALUES(tier_id),
               section_number = VALUES(section_number),
               label = VALUES(label),
               category_key = VALUES(category_key),
               capacity = VALUES(capacity),
               zone = VALUES(zone),
               is_seating_section = 1',
            [
                'layout_id' => $venueLayoutId,
                'tier_id' => $tierId,
                'polygon_id' => $polygonId,
                'section_number' => (string) ($section['number'] ?? ''),
                'label' => (string) ($section['label'] ?? $polygonId),
                'category_key' => (string) ($section['category'] ?? $tierSlug),
                'capacity' => max(0, (int) ($section['capacity'] ?? 0)),
                'map_color' => (string) ($section['mapColor'] ?? ''),
                'zone' => (string) ($section['zone'] ?? ''),
            ]
        );
    }

    $slugPlaceholders = implode(',', array_fill(0, count($tierSlugs), '?'));
    clicketDbExecute(
        'UPDATE venue_tiers SET default_status = "inactive"
         WHERE venue_layout_id = ? AND slug NOT IN (' . $slugPlaceholders . ')',
        array_merge([$venueLayoutId], $tierSlugs)
    );
}

function clicketTicketSyncVenueLayoutFromDatabase(int $venueLayoutId): void {
    $layout = clicketDbFetch(
        'SELECT vl.id, vl.category, v.name AS venue_name
         FROM venue_layouts vl
         INNER JOIN venues v ON v.id = vl.venue_id
         WHERE vl.id = :layout_id
         LIMIT 1',
        ['layout_id' => $venueLayoutId]
    );
    if (!$layout) {
        return;
    }

    $categoryKey = match ((string) $layout['category']) {
        'concert' => 'concerts',
        'sports' => 'sports',
        'theater' => 'theater',
        default => (string) $layout['category'],
    };
    clicketTicketSyncVenueLayoutProfile($venueLayoutId, clicketVenueProfile((string) $layout['venue_name'], $categoryKey));
}

function clicketVenueProfiles(): array {
    return [
        'Mall of Asia Arena' => [
            'layout' => 'arena',
            'stageLabel' => 'Main Stage',
            'subtitle' => 'End-stage arena layout',
            'sections' => [
                ['id' => 'floor-a', 'label' => 'Floor A', 'category' => 'vip', 'zone' => 'floor-left'],
                ['id' => 'floor-b', 'label' => 'Floor B', 'category' => 'vip', 'zone' => 'floor-right'],
                ['id' => 'lower-101', 'label' => 'Lower 101', 'category' => 'platinum', 'zone' => 'lower-left'],
                ['id' => 'lower-102', 'label' => 'Lower 102', 'category' => 'platinum', 'zone' => 'lower-right'],
                ['id' => 'lower-103', 'label' => 'Lower 103', 'category' => 'gold', 'zone' => 'side-left'],
                ['id' => 'lower-104', 'label' => 'Lower 104', 'category' => 'gold', 'zone' => 'side-right'],
                ['id' => 'upper-201', 'label' => 'Upper 201', 'category' => 'silver', 'zone' => 'upper-left'],
                ['id' => 'upper-202', 'label' => 'Upper 202', 'category' => 'silver', 'zone' => 'upper-right'],
                ['id' => 'upper-203', 'label' => 'Upper 203', 'category' => 'bronze', 'zone' => 'rear'],
            ],
        ],
        'Philippine Arena' => [
            'layout' => 'stadium',
            'stageLabel' => 'Concert Stage',
            'subtitle' => 'Large-capacity bowl layout',
            'sections' => [
                ['id' => 'floor-center', 'label' => 'Floor Center', 'category' => 'vip', 'zone' => 'floor-center'],
                ['id' => 'floor-left', 'label' => 'Floor Left', 'category' => 'vip', 'zone' => 'floor-left'],
                ['id' => 'floor-right', 'label' => 'Floor Right', 'category' => 'vip', 'zone' => 'floor-right'],
                ['id' => 'lower-a', 'label' => 'Lower A', 'category' => 'platinum', 'zone' => 'lower-left'],
                ['id' => 'lower-b', 'label' => 'Lower B', 'category' => 'platinum', 'zone' => 'lower-right'],
                ['id' => 'mid-a', 'label' => 'Mid A', 'category' => 'gold', 'zone' => 'side-left'],
                ['id' => 'mid-b', 'label' => 'Mid B', 'category' => 'gold', 'zone' => 'side-right'],
                ['id' => 'upper-a', 'label' => 'Upper A', 'category' => 'silver', 'zone' => 'upper-left'],
                ['id' => 'upper-b', 'label' => 'Upper B', 'category' => 'silver', 'zone' => 'upper-right'],
                ['id' => 'upper-c', 'label' => 'Upper C', 'category' => 'bronze', 'zone' => 'rear'],
            ],
        ],
        'Smart Araneta Coliseum' => [
            'layout' => 'arena',
            'stageLabel' => 'Stage / Court',
            'subtitle' => 'Classic coliseum bowl',
            'sections' => [
                ['id' => 'patron-a', 'label' => 'Patron A', 'category' => 'vip', 'zone' => 'floor-left'],
                ['id' => 'patron-b', 'label' => 'Patron B', 'category' => 'vip', 'zone' => 'floor-right'],
                ['id' => 'lower-box-a', 'label' => 'Lower Box A', 'category' => 'platinum', 'zone' => 'lower-left'],
                ['id' => 'lower-box-b', 'label' => 'Lower Box B', 'category' => 'platinum', 'zone' => 'lower-right'],
                ['id' => 'upper-box-a', 'label' => 'Upper Box A', 'category' => 'gold', 'zone' => 'side-left'],
                ['id' => 'upper-box-b', 'label' => 'Upper Box B', 'category' => 'gold', 'zone' => 'side-right'],
                ['id' => 'general-a', 'label' => 'General A', 'category' => 'silver', 'zone' => 'upper-left'],
                ['id' => 'general-b', 'label' => 'General B', 'category' => 'silver', 'zone' => 'upper-right'],
                ['id' => 'general-c', 'label' => 'General C', 'category' => 'bronze', 'zone' => 'rear'],
            ],
        ],
        'Newport Performing Arts Theater' => clicketTheaterProfile('Newport Performing Arts Theater', 'Orchestra', 'Balcony'),
        'The Theatre at Solaire' => clicketTheaterProfile('The Theatre at Solaire', 'Premium Orchestra', 'Upper Balcony'),
        'Tanghalang Ignacio Jimenez' => clicketTheaterProfile('Tanghalang Ignacio Jimenez', 'Center Orchestra', 'Gallery'),
        'PhilSports Arena' => clicketCourtProfile('PhilSports Arena'),
    ];
}

function clicketTheaterProfile(string $name, string $mainLabel, string $upperLabel): array {
    return [
        'layout' => 'theater',
        'stageLabel' => 'Stage',
        'subtitle' => $name . ' proscenium layout',
        'sections' => [
            ['id' => 'orchestra-center', 'label' => $mainLabel . ' Center', 'category' => 'vip', 'zone' => 'orchestra-center'],
            ['id' => 'orchestra-left', 'label' => $mainLabel . ' Left', 'category' => 'platinum', 'zone' => 'orchestra-left'],
            ['id' => 'orchestra-right', 'label' => $mainLabel . ' Right', 'category' => 'platinum', 'zone' => 'orchestra-right'],
            ['id' => 'loge-left', 'label' => 'Loge Left', 'category' => 'gold', 'zone' => 'loge-left'],
            ['id' => 'loge-center', 'label' => 'Loge Center', 'category' => 'gold', 'zone' => 'loge-center'],
            ['id' => 'loge-right', 'label' => 'Loge Right', 'category' => 'gold', 'zone' => 'loge-right'],
            ['id' => 'balcony-left', 'label' => $upperLabel . ' Left', 'category' => 'silver', 'zone' => 'balcony-left'],
            ['id' => 'balcony-center', 'label' => $upperLabel . ' Center', 'category' => 'silver', 'zone' => 'balcony-center'],
            ['id' => 'balcony-right', 'label' => $upperLabel . ' Right', 'category' => 'bronze', 'zone' => 'balcony-right'],
        ],
    ];
}

function clicketHallProfile(string $name): array {
    return [
        'layout' => 'hall',
        'stageLabel' => 'Stage',
        'subtitle' => $name . ' flexible hall layout',
        'sections' => [
            ['id' => 'standing-a', 'label' => 'Standing A', 'category' => 'vip', 'zone' => 'floor-left'],
            ['id' => 'standing-b', 'label' => 'Standing B', 'category' => 'vip', 'zone' => 'floor-right'],
            ['id' => 'reserved-a', 'label' => 'Reserved A', 'category' => 'platinum', 'zone' => 'lower-left'],
            ['id' => 'reserved-b', 'label' => 'Reserved B', 'category' => 'platinum', 'zone' => 'lower-right'],
            ['id' => 'rear-left', 'label' => 'Rear Left', 'category' => 'gold', 'zone' => 'upper-left'],
            ['id' => 'rear-right', 'label' => 'Rear Right', 'category' => 'gold', 'zone' => 'upper-right'],
            ['id' => 'rear-center', 'label' => 'Rear Center', 'category' => 'silver', 'zone' => 'rear'],
        ],
    ];
}

function clicketCourtProfile(string $name): array {
    return [
        'layout' => 'court',
        'stageLabel' => 'Playing Court',
        'subtitle' => $name . ' court-side layout',
        'sections' => [
            ['id' => 'courtside-west', 'label' => 'Courtside West', 'category' => 'vip', 'zone' => 'court-left'],
            ['id' => 'courtside-east', 'label' => 'Courtside East', 'category' => 'vip', 'zone' => 'court-right'],
            ['id' => 'lower-west', 'label' => 'Lower West', 'category' => 'platinum', 'zone' => 'lower-left'],
            ['id' => 'lower-east', 'label' => 'Lower East', 'category' => 'platinum', 'zone' => 'lower-right'],
            ['id' => 'baseline-north', 'label' => 'Baseline North', 'category' => 'gold', 'zone' => 'upper-left'],
            ['id' => 'baseline-south', 'label' => 'Baseline South', 'category' => 'gold', 'zone' => 'upper-right'],
            ['id' => 'upper-west', 'label' => 'Upper West', 'category' => 'silver', 'zone' => 'side-left'],
            ['id' => 'upper-east', 'label' => 'Upper East', 'category' => 'silver', 'zone' => 'side-right'],
            ['id' => 'general', 'label' => 'General Admission', 'category' => 'bronze', 'zone' => 'rear'],
        ],
    ];
}

function clicketMoaSportsTierForSvgId(string $id): ?array {
    $normalized = strtolower($id);
    $tiers = [
        'patron_' => ['tier' => 'patron', 'category' => 'platinum', 'label' => 'Patron', 'color' => '#bfe8c8'],
        'vip_' => ['tier' => 'vip', 'category' => 'vip', 'label' => 'VIP', 'color' => '#fff0a8'],
        'lowerb_' => ['tier' => 'lowerb', 'category' => 'gold', 'label' => 'Lower Box', 'color' => '#ffc58f'],
        'uppeb_' => ['tier' => 'uppeb', 'category' => 'silver', 'label' => 'Upper Box', 'color' => '#afd3ff'],
        'gen_ad' => ['tier' => 'genad', 'category' => 'general', 'label' => 'General Admission', 'color' => '#f2a0aa'],
        'genad_' => ['tier' => 'genad', 'category' => 'general', 'label' => 'General Admission', 'color' => '#f2a0aa'],
    ];

    foreach ($tiers as $prefix => $tier) {
        if (strpos($normalized, $prefix) === 0) {
            return $tier;
        }
    }

    return null;
}

function clicketMoaConcertTierForSvgId(string $id): ?array {
    $normalized = strtolower($id);
    $tiers = [
        'vip_' => ['tier' => 'vip', 'category' => 'vip', 'label' => 'VIP', 'color' => '#fff0a8'],
        'lbprema_' => ['tier' => 'lbprema', 'category' => 'general', 'label' => 'Lower Box A Premium', 'color' => '#bfe8c8'],
        'patron_' => ['tier' => 'patron', 'category' => 'platinum', 'label' => 'Patron', 'color' => '#afd3ff'],
        'lbrega_' => ['tier' => 'lbrega', 'category' => 'gold', 'label' => 'Lower Box A Regular', 'color' => '#ffc58f'],
        'lowerb_' => ['tier' => 'lowerb', 'category' => 'silver', 'label' => 'Lower Box B', 'color' => '#f5b6cc'],
        'upperb_' => ['tier' => 'upperb', 'category' => 'bronze', 'label' => 'Upper Box', 'color' => '#d8b7ff'],
        'genad' => ['tier' => 'genad', 'category' => 'general', 'label' => 'General Admission', 'color' => '#f2a0aa'],
    ];

    foreach ($tiers as $prefix => $tier) {
        if (strpos($normalized, $prefix) === 0) {
            return $tier;
        }
    }

    return null;
}

function clicketPhilippineArenaTierForSvgId(string $id): ?array {
    $normalized = strtolower($id);
    $tiers = [
        'vip_' => ['tier' => 'vip', 'category' => 'vip', 'label' => 'VIP', 'color' => '#fff0a8'],
        'lbaprem_' => ['tier' => 'lbaprem', 'category' => 'platinum', 'label' => 'Lower Box A Premium', 'color' => '#bfe8c8'],
        'lbareg_' => ['tier' => 'lbareg', 'category' => 'gold', 'label' => 'Lower Box A Regular', 'color' => '#afd3ff'],
        'lbbprem_' => ['tier' => 'lbbprem', 'category' => 'silver', 'label' => 'Lower Box B Premium', 'color' => '#ffc58f'],
        'lbbreg_' => ['tier' => 'lbbreg', 'category' => 'bronze', 'label' => 'Lower Box B Regular', 'color' => '#f5b6cc'],
        'uba_' => ['tier' => 'uba', 'category' => 'silver', 'label' => 'Upper Box A', 'color' => '#d8b7ff'],
        'ubbprem_' => ['tier' => 'ubbprem', 'category' => 'bronze', 'label' => 'Upper Box B Premium', 'color' => '#ffb090'],
        'ubbreg_' => ['tier' => 'ubbreg', 'category' => 'general', 'label' => 'Upper Box B Reg', 'color' => '#f2a0aa'],
    ];

    foreach ($tiers as $prefix => $tier) {
        if (strpos($normalized, $prefix) === 0) {
            return $tier;
        }
    }

    return null;
}

function clicketAranetaConcertTierForSvgId(string $id): ?array {
    $normalized = strtolower($id);
    $tiers = [
        'svip_' => ['tier' => 'svip', 'category' => 'vip', 'label' => 'SVIP', 'color' => '#fdff00'],
        'vip_' => ['tier' => 'vip', 'category' => 'vip', 'label' => 'VIP', 'color' => '#fff0a8'],
        'patrona_' => ['tier' => 'patrona', 'category' => 'platinum', 'label' => 'Patron A', 'color' => '#5edc1f'],
        'patronb_' => ['tier' => 'patronb', 'category' => 'gold', 'label' => 'Patron B', 'color' => '#bfe8c8'],
        'lb_' => ['tier' => 'lb', 'category' => 'silver', 'label' => 'Lower Box', 'color' => '#ffc58f'],
        'ub_' => ['tier' => 'ub', 'category' => 'bronze', 'label' => 'Upper Box', 'color' => '#d8b7ff'],
        'genad_' => ['tier' => 'genad', 'category' => 'general', 'label' => 'General Admission', 'color' => '#f2a0aa'],
    ];

    foreach ($tiers as $prefix => $tier) {
        if (strpos($normalized, $prefix) === 0) {
            return $tier;
        }
    }

    return null;
}

function clicketAranetaSportsTierForSvgId(string $id): ?array {
    $normalized = strtolower($id);
    $tiers = [
        'vip_' => ['tier' => 'vip', 'category' => 'vip', 'label' => 'VIP', 'color' => '#fff0a8'],
        'patron_' => ['tier' => 'patron', 'category' => 'platinum', 'label' => 'Patron', 'color' => '#bfe8c8'],
        'lb_' => ['tier' => 'lower', 'category' => 'gold', 'label' => 'Lower Box', 'color' => '#ffc58f'],
        'ub_' => ['tier' => 'upper', 'category' => 'silver', 'label' => 'Upper Box', 'color' => '#d8b7ff'],
        'genad_' => ['tier' => 'general', 'category' => 'general', 'label' => 'General Admission', 'color' => '#f2a0aa'],
    ];

    foreach ($tiers as $prefix => $tier) {
        if (strpos($normalized, $prefix) === 0) {
            return $tier;
        }
    }

    return null;
}

function clicketPhilsportsTierForSvgId(string $id): ?array {
    $normalized = strtolower($id);
    $tiers = [
        'patron_' => ['tier' => 'patron', 'category' => 'platinum', 'label' => 'Patron', 'color' => '#bfe8c8'],
        'lb_' => ['tier' => 'lower', 'category' => 'gold', 'label' => 'Lower Box', 'color' => '#afd3ff'],
        'upperb_' => ['tier' => 'upper', 'category' => 'silver', 'label' => 'Upper Box', 'color' => '#ffc58f'],
    ];

    foreach ($tiers as $prefix => $tier) {
        if (strpos($normalized, $prefix) === 0) {
            return $tier;
        }
    }

    return null;
}

function clicketTanghalanTierForSvgId(string $id): ?array {
    $normalized = strtolower($id);
    $tiers = [
        'svip_' => ['tier' => 'svip', 'category' => 'vip', 'label' => 'SVIP', 'color' => '#fff0a8'],
        'ccp_' => ['tier' => 'ccp', 'category' => 'platinum', 'label' => 'CCP House Seats', 'color' => '#bfe8c8'],
        'vip_' => ['tier' => 'vip', 'category' => 'silver', 'label' => 'VIP', 'color' => '#d8b7ff'],
        'vp_' => ['tier' => 'vp', 'category' => 'gold', 'label' => 'VP House Seats', 'color' => '#ffc58f'],
        'reg_' => ['tier' => 'regular', 'category' => 'general', 'label' => 'Regular', 'color' => '#f2a0aa'],
    ];

    foreach ($tiers as $prefix => $tier) {
        if (strpos($normalized, $prefix) === 0) {
            return $tier;
        }
    }

    return null;
}

function clicketNewportTierForSvgId(string $id): ?array {
    $normalized = strtolower($id);
    $tiers = [
        'svip' => ['tier' => 'svip', 'category' => 'vip', 'label' => 'SVIP', 'color' => '#fff86b'],
        'vip' => ['tier' => 'vip', 'category' => 'platinum', 'label' => 'VIP', 'color' => '#fff0a8'],
        'balconyc' => ['tier' => 'balcony-center', 'category' => 'silver', 'label' => 'Balcony Center', 'color' => '#afd3ff'],
        'premierel' => ['tier' => 'premiere-left', 'category' => 'gold', 'label' => 'Premiere Left', 'color' => '#bfe8c8'],
        'premierer' => ['tier' => 'premiere-right', 'category' => 'gold', 'label' => 'Premiere Right', 'color' => '#bfe8c8'],
        'deluxel' => ['tier' => 'deluxe-left', 'category' => 'bronze', 'label' => 'Deluxe Left', 'color' => '#d8b7ff'],
        'deluxer' => ['tier' => 'deluxe-right', 'category' => 'bronze', 'label' => 'Deluxe Right', 'color' => '#d8b7ff'],
        'balconyl' => ['tier' => 'balcony-left', 'category' => 'general', 'label' => 'Balcony Left', 'color' => '#ffc58f'],
        'balconyr' => ['tier' => 'balcony-right', 'category' => 'general', 'label' => 'Balcony Right', 'color' => '#ffc58f'],
        'outerbalcl' => ['tier' => 'outer-balcony-left', 'category' => 'general', 'label' => 'Outer Balcony Left', 'color' => '#f2a0aa'],
        'outerbalcr' => ['tier' => 'outer-balcony-right', 'category' => 'general', 'label' => 'Outer Balcony Right', 'color' => '#f2a0aa'],
    ];

    foreach ($tiers as $prefix => $tier) {
        if (strpos($normalized, $prefix) === 0) {
            return $tier;
        }
    }

    return null;
}

function clicketSolaireTierForSvgId(string $id): ?array {
    $normalized = strtolower($id);
    $tiers = [
        'vip_' => ['tier' => 'vip', 'category' => 'vip', 'label' => 'VIP', 'color' => '#fff0a8'],
        'ares_' => ['tier' => 'a-reserve', 'category' => 'platinum', 'label' => 'A Reserve', 'color' => '#bfe8c8'],
        'bres_' => ['tier' => 'b-reserve', 'category' => 'gold', 'label' => 'B Reserve', 'color' => '#afd3ff'],
        'cres_' => ['tier' => 'c-reserve', 'category' => 'silver', 'label' => 'C Reserve', 'color' => '#ffc58f'],
        'dres_' => ['tier' => 'd-reserve', 'category' => 'bronze', 'label' => 'D Reserve', 'color' => '#f2a0aa'],
    ];

    foreach ($tiers as $prefix => $tier) {
        if (strpos($normalized, $prefix) === 0) {
            return $tier;
        }
    }

    return null;
}

function clicketSvgAttributes(string $tag): array {
    preg_match_all('/([A-Za-z_:][A-Za-z0-9_.:-]*)="([^"]*)"/', $tag, $matches, PREG_SET_ORDER);
    $attributes = [];
    foreach ($matches as $match) {
        $attributes[$match[1]] = $match[2];
    }
    return $attributes;
}

function clicketRotatePoint(array $point, float $angle, float $cx, float $cy): array {
    $radians = deg2rad($angle);
    $cos = cos($radians);
    $sin = sin($radians);
    $dx = $point[0] - $cx;
    $dy = $point[1] - $cy;

    return [
        $cx + $dx * $cos - $dy * $sin,
        $cy + $dx * $sin + $dy * $cos,
    ];
}

function clicketRectPoints(array $attributes): array {
    $x = (float) ($attributes['x'] ?? 0);
    $y = (float) ($attributes['y'] ?? 0);
    $width = (float) ($attributes['width'] ?? 0);
    $height = (float) ($attributes['height'] ?? 0);
    $points = [
        [$x, $y],
        [$x + $width, $y],
        [$x + $width, $y + $height],
        [$x, $y + $height],
    ];

    if (!empty($attributes['transform']) && preg_match('/rotate\((-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\)/', $attributes['transform'], $match)) {
        $angle = (float) $match[1];
        $cx = (float) $match[2];
        $cy = (float) $match[3];
        $points = array_map(static fn (array $point): array => clicketRotatePoint($point, $angle, $cx, $cy), $points);
    }
    if (!empty($attributes['transform']) && preg_match('/matrix\(([^)]+)\)/', $attributes['transform'], $match)) {
        $matrix = array_map('floatval', preg_split('/[\s,]+/', trim($match[1])));
        if (count($matrix) === 6) {
            [$a, $b, $c, $d, $e, $f] = $matrix;
            $points = array_map(static fn (array $point): array => [
                $a * $point[0] + $c * $point[1] + $e,
                $b * $point[0] + $d * $point[1] + $f,
            ], $points);
        }
    }

    return $points;
}

function clicketPathPoints(string $pathData): array {
    preg_match_all('/[A-Za-z]|[-+]?(?:(?:\d*\.\d+)|(?:\d+\.?))(?:[eE][-+]?\d+)?/', $pathData, $matches);
    $tokens = $matches[0];
    $points = [];
    $index = 0;
    $command = '';
    $x = 0.0;
    $y = 0.0;
    $startX = 0.0;
    $startY = 0.0;
    $curveSteps = 10;
    $isCommand = static fn (string $token): bool => (bool) preg_match('/^[A-Za-z]$/', $token);
    $readPoint = static function (bool $relative) use (&$tokens, &$index, &$x, &$y): array {
        $nextX = (float) $tokens[$index++];
        $nextY = (float) $tokens[$index++];
        if ($relative) {
            $nextX += $x;
            $nextY += $y;
        }
        return [$nextX, $nextY];
    };

    while ($index < count($tokens)) {
        if ($isCommand($tokens[$index])) {
            $command = $tokens[$index++];
        }

        $upperCommand = strtoupper($command);
        $relative = $command !== $upperCommand;

        if ($upperCommand === 'M' || $upperCommand === 'L') {
            while ($index + 1 < count($tokens) && !$isCommand($tokens[$index])) {
                [$x, $y] = $readPoint($relative);
                $points[] = [$x, $y];
                if ($upperCommand === 'M') {
                    $startX = $x;
                    $startY = $y;
                    $upperCommand = 'L';
                    $command = $relative ? 'l' : 'L';
                }
            }
            continue;
        }

        if ($upperCommand === 'H') {
            while ($index < count($tokens) && !$isCommand($tokens[$index])) {
                $nextX = (float) $tokens[$index++];
                $x = $relative ? $x + $nextX : $nextX;
                $points[] = [$x, $y];
            }
            continue;
        }

        if ($upperCommand === 'V') {
            while ($index < count($tokens) && !$isCommand($tokens[$index])) {
                $nextY = (float) $tokens[$index++];
                $y = $relative ? $y + $nextY : $nextY;
                $points[] = [$x, $y];
            }
            continue;
        }

        if ($upperCommand === 'C') {
            while ($index + 5 < count($tokens) && !$isCommand($tokens[$index])) {
                [$x1, $y1] = $readPoint($relative);
                [$x2, $y2] = $readPoint($relative);
                [$x3, $y3] = $readPoint($relative);
                $originX = $x;
                $originY = $y;
                for ($step = 1; $step <= $curveSteps; $step++) {
                    $t = $step / $curveSteps;
                    $mt = 1 - $t;
                    $points[] = [
                        ($mt ** 3) * $originX + 3 * ($mt ** 2) * $t * $x1 + 3 * $mt * ($t ** 2) * $x2 + ($t ** 3) * $x3,
                        ($mt ** 3) * $originY + 3 * ($mt ** 2) * $t * $y1 + 3 * $mt * ($t ** 2) * $y2 + ($t ** 3) * $y3,
                    ];
                }
                $x = $x3;
                $y = $y3;
            }
            continue;
        }

        if ($upperCommand === 'Q') {
            while ($index + 3 < count($tokens) && !$isCommand($tokens[$index])) {
                [$x1, $y1] = $readPoint($relative);
                [$x2, $y2] = $readPoint($relative);
                $originX = $x;
                $originY = $y;
                for ($step = 1; $step <= $curveSteps; $step++) {
                    $t = $step / $curveSteps;
                    $mt = 1 - $t;
                    $points[] = [
                        ($mt ** 2) * $originX + 2 * $mt * $t * $x1 + ($t ** 2) * $x2,
                        ($mt ** 2) * $originY + 2 * $mt * $t * $y1 + ($t ** 2) * $y2,
                    ];
                }
                $x = $x2;
                $y = $y2;
            }
            continue;
        }

        if ($upperCommand === 'Z') {
            $x = $startX;
            $y = $startY;
            continue;
        }

        $index++;
    }

    return $points;
}

function clicketPolygonArea(array $points): float {
    $area = 0.0;
    $count = count($points);
    for ($index = 0; $index < $count; $index++) {
        $next = ($index + 1) % $count;
        $area += $points[$index][0] * $points[$next][1] - $points[$next][0] * $points[$index][1];
    }

    return abs($area / 2);
}

function clicketMoaSportsShapeFromElement(string $element): ?array {
    if (!preg_match('/^<(rect|path)\b/i', trim($element), $tagMatch)) {
        return null;
    }

    $attributes = clicketSvgAttributes($element);
    $points = strtolower($tagMatch[1]) === 'rect'
        ? clicketRectPoints($attributes)
        : clicketPathPoints($attributes['d'] ?? '');

    return count($points) >= 3 ? ['points' => $points] : null;
}

function clicketSvgShapeElementsFromMarkup(string $markup): array {
    preg_match_all('/<(rect|path)\b[^>]*>/i', $markup, $matches);
    return $matches[0] ?? [];
}

function clicketSvgShapeFromGroup(string $markup, bool $combine = false): ?array {
    $shapes = [];
    foreach (clicketSvgShapeElementsFromMarkup($markup) as $element) {
        $shape = clicketMoaSportsShapeFromElement($element);
        if ($shape) {
            if (!$combine) {
                return $shape;
            }
            $shapes[] = $shape;
        }
    }

    if (!$shapes) {
        return null;
    }

    return [
        'points' => array_merge(...array_column($shapes, 'points')),
        'shapes' => $shapes,
    ];
}

function clicketArenaSvgLayout(string $assetName, array $options): array {
    $svgPath = __DIR__ . '/../assets/' . $assetName;
    if (!is_file($svgPath)) {
        return ['sections' => [], 'nonSeats' => []];
    }

    $svg = file_get_contents($svgPath) ?: '';
    $viewBox = $options['viewBox'];
    if (preg_match('/<svg\b[^>]*\bviewBox="([^"]+)"/i', $svg, $viewBoxMatch)) {
        $parts = preg_split('/[\s,]+/', trim($viewBoxMatch[1]));
        if (count($parts) === 4) {
            $viewBox = array_map('floatval', $parts);
        }
    }
    $sections = [];
    $seen = [];
    $seatPattern = $options['seatPattern'];
    $seatGroupPattern = $options['seatGroupPattern'] ?? '/<g\b[^>]*\bid="([^"]+)"[^>]*>(.*?)<\/g>/is';

    preg_match_all($seatGroupPattern, $svg, $groups, PREG_SET_ORDER);
    foreach ($groups as $group) {
        if (isset($seen[$group[1]]) || !preg_match($seatPattern, $group[1])) {
            continue;
        }
        $shape = clicketSvgShapeFromGroup($group[2]);
        if (!$shape) {
            continue;
        }
        $sections[] = ['id' => $group[1], 'shape' => $shape, 'area' => clicketPolygonArea($shape['points'])];
        $seen[$group[1]] = true;
    }

    preg_match_all('/<(rect|path)\b[^>]*\bid="([^"]+)"[^>]*>/i', $svg, $elements, PREG_SET_ORDER);
    foreach ($elements as $element) {
        if (isset($seen[$element[2]]) || !preg_match($seatPattern, $element[2])) {
            continue;
        }
        $shape = clicketMoaSportsShapeFromElement($element[0]);
        if (!$shape) {
            continue;
        }
        $sections[] = ['id' => $element[2], 'shape' => $shape, 'area' => clicketPolygonArea($shape['points'])];
        $seen[$element[2]] = true;
    }

    $totalArea = array_sum(array_column($sections, 'area'));
    $allocated = 0;
    $targetCapacity = (int) ($options['capacity'] ?? 16000);
    foreach ($sections as $index => $section) {
        $exact = $totalArea > 0 ? ($section['area'] / $totalArea) * $targetCapacity : 0;
        $capacity = max(12, (int) floor($exact));
        $sections[$index]['capacity'] = $capacity;
        $sections[$index]['remainder'] = $exact - floor($exact);
        $allocated += $capacity;
    }

    usort($sections, static fn (array $a, array $b): int => $b['remainder'] <=> $a['remainder']);
    $difference = $targetCapacity - $allocated;
    for ($index = 0; $difference > 0 && $index < count($sections); $index = ($index + 1) % max(1, count($sections))) {
        $sections[$index]['capacity']++;
        $difference--;
    }
    for ($index = 0; $difference < 0 && $index < count($sections); $index = ($index + 1) % max(1, count($sections))) {
        if ($sections[$index]['capacity'] > 12) {
            $sections[$index]['capacity']--;
            $difference++;
        }
    }
    usort($sections, static fn (array $a, array $b): int => strcmp($a['id'], $b['id']));

    $nonSeats = [];
    $blockedIds = $options['blockedIds'];
    foreach ($blockedIds as $blockedId => $label) {
        if (!preg_match('/<g\b[^>]*\bid="' . preg_quote($blockedId, '/') . '"[^>]*>(.*?)<\/g>/is', $svg, $groupMatch)) {
            continue;
        }
        $shape = clicketSvgShapeFromGroup($groupMatch[1], true);
        if ($shape) {
            $nonSeats[] = ['id' => $blockedId, 'shape' => $shape, 'label' => $label];
            $seen[$blockedId] = true;
        }
    }

    preg_match_all('/<g\b[^>]*\bid="([^"]+)"[^>]*>(.*?)<\/g>/is', $svg, $blockedGroups, PREG_SET_ORDER);
    foreach ($blockedGroups as $group) {
        if (!isset($blockedIds[$group[1]]) || isset($seen[$group[1]])) {
            continue;
        }
        $shape = clicketSvgShapeFromGroup($group[2], true);
        if ($shape) {
            $nonSeats[] = ['id' => $group[1], 'shape' => $shape, 'label' => $blockedIds[$group[1]]];
            $seen[$group[1]] = true;
        }
    }

    preg_match_all('/<(rect|path)\b[^>]*\bid="([^"]+)"[^>]*>/i', $svg, $blocked, PREG_SET_ORDER);
    foreach ($blocked as $element) {
        if (!isset($blockedIds[$element[2]]) || isset($seen[$element[2]])) {
            continue;
        }
        $shape = clicketMoaSportsShapeFromElement($element[0]);
        if ($shape) {
            $nonSeats[] = ['id' => $element[2], 'shape' => $shape, 'label' => $blockedIds[$element[2]]];
        }
    }

    return [
        'viewBox' => $viewBox,
        'sections' => $sections,
        'nonSeats' => $nonSeats,
    ];
}

function clicketMoaSportsSvgLayout(): array {
    return clicketArenaSvgLayout('MOA_Sport_final2.svg', [
        'seatPattern' => '/^(?:patron_|vip_|lowerb_|uppeb_|gen_?ad)/i',
        'seatGroupPattern' => '/<g\b[^>]*\bid="((?:patron_|vip_|lowerb_|uppeb_|gen_?ad)[^"]+)"[^>]*>(.*?)<\/g>/is',
        'blockedIds' => ['Court' => 'Court', 'Technical' => 'Technical', 'Commentary' => 'Commentary'],
        'capacity' => 16000,
        'viewBox' => [0, 0, 730, 645],
    ]);
}

function clicketMoaConcertSvgLayout(): array {
    return clicketArenaSvgLayout('MOA_Concert_final.svg', [
        'seatPattern' => '/^(?:vip_|lbpremA_|patron_|lbregA_|lowerB_|upperb_|genad)/i',
        'seatGroupPattern' => '/<g\b[^>]*\bid="((?:vip_|lbpremA_|patron_|lbregA_|lowerB_|upperb_|genad)[^"]+)"[^>]*>(.*?)<\/g>/is',
        'blockedIds' => [
            'Stage' => 'Stage',
            'Tech' => 'Tech',
            'Con1' => '',
            'Con2' => '',
            'Con3' => '',
            'Con4' => '',
        ],
        'capacity' => 13000,
        'viewBox' => [0, 0, 699, 666],
    ]);
}

function clicketPhilippineArenaSvgLayout(): array {
    return clicketArenaSvgLayout('phil_arena.svg', [
        'seatPattern' => '/^(?:vip_|lbAprem_|lbAreg_|lbBprem_|lbBreg_|ubA_|ubBprem_|ubBreg_)/i',
        'seatGroupPattern' => '/<g\b[^>]*\bid="((?:vip_|lbAprem_|lbAreg_|lbBprem_|lbBreg_|ubA_|ubBprem_|ubBreg_)[^"]+)"[^>]*>(.*?)<\/g>/is',
        'blockedIds' => ['Stage' => 'Stage'],
        'capacity' => 55000,
        'viewBox' => [0, 0, 1134, 713],
    ]);
}

function clicketAranetaConcertSvgLayout(): array {
    return clicketArenaSvgLayout('Araneta_Concert.svg', [
        'seatPattern' => '/^(?:svip_|vip_|patronA_|patronB_|lb_|ub_|genad_)/i',
        'seatGroupPattern' => '/<g\b[^>]*\bid="((?:svip_|vip_|patronA_|patronB_|lb_|ub_|genad_)[^"]+)"[^>]*>(.*?)<\/g>/is',
        'blockedIds' => ['Stage' => 'Stage', 'Booth' => 'Booth'],
        'capacity' => 13000,
        'viewBox' => [0, 0, 794, 583],
    ]);
}

function clicketAranetaSportsSvgLayout(): array {
    return clicketArenaSvgLayout('Araneta_Sport.svg', [
        'seatPattern' => '/^(?:vip_|patron_|lb_|ub_|genad_)/i',
        'seatGroupPattern' => '/<g\b[^>]*\bid="((?:vip_|patron_|lb_|ub_|genad_)[^"]+)"[^>]*>(.*?)<\/g>/is',
        'blockedIds' => ['Court' => 'Court'],
        'capacity' => 18000,
        'viewBox' => [0, 0, 794, 803],
    ]);
}

function clicketPhilsportsSvgLayout(): array {
    return clicketArenaSvgLayout('PS_Arena.svg', [
        'seatPattern' => '/^(?:patron_|lb_|upperb_)/i',
        'seatGroupPattern' => '/<g\b[^>]*\bid="((?:patron_|lb_|upperb_)[^"]+)"[^>]*>(.*?)<\/g>/is',
        'blockedIds' => ['Court' => 'Court'],
        'capacity' => 10000,
        'viewBox' => [0, 0, 1006, 787],
    ]);
}

function clicketTanghalanSvgLayout(): array {
    return clicketArenaSvgLayout('Tanghalan.svg', [
        'seatPattern' => '/^(?:svip_|ccp_|vip_|vp_|reg_)/i',
        'seatGroupPattern' => '/<g\b[^>]*\bid="((?:svip_|ccp_|vip_|vp_|reg_)[^"]+)"[^>]*>(.*?)<\/g>/is',
        'blockedIds' => ['Stage' => 'Stage'],
        'capacity' => 320,
        'viewBox' => [0, 0, 405, 435],
    ]);
}

function clicketNewportSvgLayout(): array {
    return clicketArenaSvgLayout('Newport_final2.svg', [
        'seatPattern' => '/^(?:svip|vip|balconyC|premiereL|premiereR|deluxeL|deluxeR|balconyL|balconyR|outerbalcL|outerbalcR)$/i',
        'seatGroupPattern' => '/<g\b[^>]*\bid="((?:svip|vip|balconyC|premiereL|premiereR|deluxeL|deluxeR|balconyL|balconyR|outerbalcL|outerbalcR)[^"]*)"[^>]*>(.*?)<\/g>/is',
        'blockedIds' => ['Stage' => 'Stage'],
        'capacity' => 1700,
        'viewBox' => [0, 0, 666, 559],
    ]);
}

function clicketSolaireSvgLayout(): array {
    return clicketArenaSvgLayout('Solaire.svg', [
        'seatPattern' => '/^(?:vip_|ARes_|BRes_|CRes_|DRes_)sec_\d+$/i',
        'seatGroupPattern' => '/<g\b[^>]*\bid="((?:vip_|ARes_|BRes_|CRes_|DRes_)sec_\d+)"[^>]*>(.*?)<\/g>/is',
        'blockedIds' => ['Stage' => 'Stage', 'Line' => ''],
        'capacity' => 1850,
        'viewBox' => [0, 0, 697, 804],
    ]);
}

function clicketMoaSportsProfile(): array {
    $svgLayout = clicketMoaSportsSvgLayout();
    if (!empty($svgLayout['sections'])) {
        $sections = [];
        foreach ($svgLayout['sections'] as $section) {
            $tier = clicketMoaSportsTierForSvgId($section['id']);
            if (!$tier || !preg_match('/sec_(\d+)/i', $section['id'], $numberMatch)) {
                continue;
            }
            $number = $numberMatch[1];
            $sections[] = [
                'id' => $section['id'],
                'label' => $tier['label'] . ' ' . $number,
                'number' => $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $section['capacity'],
                'mapColor' => $tier['color'],
                'zone' => 'svg-' . $tier['tier'],
                'svgShape' => $section['shape'],
            ];
        }

        return [
            'layout' => 'court',
            'stageLabel' => 'Basketball Court',
            'subtitle' => 'Mall of Asia Arena 16,000-seat sports bowl',
            'capacity' => 16000,
            'sections' => $sections,
            'svgLayout' => [
                'viewBox' => $svgLayout['viewBox'],
                'nonSeats' => $svgLayout['nonSeats'],
            ],
        ];
    }

    $sections = [];

    foreach ([
        [
            'tier' => 'lower',
            'start' => 101,
            'count' => 16,
            'capacity' => 300,
            'category' => 'vip',
            'label' => 'Lower Bowl',
            'colors' => ['#607d2f', '#9b236f', '#173b70', '#9ca1a8', '#8f1d2c', '#28a6a0', '#9ca424', '#5a5bb5'],
        ],
        [
            'tier' => 'suite',
            'start' => 201,
            'count' => 16,
            'capacity' => 100,
            'category' => 'platinum',
            'label' => 'Lower Concourse Suite',
            'colors' => ['#a76c91', '#d2b62f', '#1680c5', '#bd2638'],
        ],
        [
            'tier' => 'club',
            'start' => 221,
            'count' => 16,
            'capacity' => 200,
            'category' => 'gold',
            'label' => 'Club Level',
            'colors' => ['#f3cc28', '#29a568', '#f0a45f', '#b64055'],
        ],
        [
            'tier' => 'upper',
            'start' => 301,
            'count' => 32,
            'capacity' => 200,
            'category' => 'silver',
            'label' => 'Upper Bowl',
            'colors' => ['#123c75', '#17758b', '#efc0ad', '#343537'],
        ],
    ] as $tier) {
        for ($index = 0; $index < $tier['count']; $index++) {
            $number = $tier['start'] + $index;
            $sections[] = [
                'id' => 'moa-' . $tier['tier'] . '-' . $number,
                'label' => $tier['label'] . ' ' . $number,
                'number' => (string) $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $tier['capacity'],
                'mapColor' => $tier['colors'][$index % count($tier['colors'])],
                'zone' => 'ring-' . $tier['tier'],
            ];
        }
    }

    return [
        'layout' => 'court',
        'stageLabel' => 'Basketball Court',
        'subtitle' => 'Mall of Asia Arena 16,000-seat sports bowl',
        'capacity' => 16000,
        'sections' => $sections,
    ];
}

function clicketMoaConcertProfile(): array {
    $svgLayout = clicketMoaConcertSvgLayout();
    if (!empty($svgLayout['sections'])) {
        $sections = [];
        foreach ($svgLayout['sections'] as $section) {
            $tier = clicketMoaConcertTierForSvgId($section['id']);
            if (!$tier || !preg_match('/sec_(\d+)/i', $section['id'], $numberMatch)) {
                continue;
            }
            $number = $numberMatch[1];
            $sections[] = [
                'id' => $section['id'],
                'label' => $tier['label'] . ' ' . $number,
                'number' => $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $section['capacity'],
                'mapColor' => $tier['color'],
                'zone' => 'svg-' . $tier['tier'],
                'svgShape' => $section['shape'],
            ];
        }

        return [
            'layout' => 'arena',
            'stageLabel' => 'Stage',
            'subtitle' => 'Mall of Asia Arena 13,000-seat end-stage concert layout',
            'capacity' => 13000,
            'sections' => $sections,
            'svgLayout' => [
                'viewBox' => $svgLayout['viewBox'],
                'nonSeats' => $svgLayout['nonSeats'],
            ],
        ];
    }

    $sections = [];

    foreach ([
        ['tier' => 'standing', 'start' => 1, 'count' => 2, 'capacity' => 780, 'category' => 'vip', 'label' => 'VIP Standing', 'numbers' => ['LEFT', 'RIGHT'], 'colors' => ['#f6a6bd', '#f6a6bd']],
        ['tier' => 'patron', 'start' => 1, 'count' => 1, 'capacity' => 1450, 'category' => 'platinum', 'label' => 'Patron Standing', 'numbers' => ['PATRON'], 'colors' => ['#9fe7eb']],
        ['tier' => 'lower', 'start' => 1, 'count' => 10, 'capacity' => 520, 'category' => 'gold', 'label' => 'Lower Box', 'numbers' => ['A1', 'A2', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7', 'B8'], 'colors' => ['#2456dc', '#31bd40', '#8f159f', '#2456dc', '#31bd40', '#8f159f', '#2456dc', '#31bd40', '#8f159f', '#2456dc']],
        ['tier' => 'upper', 'start' => 1, 'count' => 12, 'capacity' => 340, 'category' => 'silver', 'label' => 'Upper Box', 'numbers' => ['U1', 'U2', 'U3', 'U4', 'U5', 'U6', 'U7', 'U8', 'U9', 'U10', 'U11', 'U12'], 'colors' => ['#fff173', '#ff8128', '#fff173', '#ff8128', '#fff173', '#ff8128', '#fff173', '#ff8128', '#fff173', '#ff8128', '#fff173', '#ff8128']],
        ['tier' => 'general', 'start' => 1, 'count' => 8, 'capacity' => 310, 'category' => 'general', 'label' => 'General Admission', 'numbers' => ['GA1', 'GA2', 'GA3', 'GA4', 'GA5', 'GA6', 'GA7', 'GA8'], 'colors' => ['#d51e4f']],
    ] as $tier) {
        for ($index = 0; $index < $tier['count']; $index++) {
            $number = $tier['start'] + $index;
            $sections[] = [
                'id' => 'moa-concert-' . $tier['tier'] . '-' . $number,
                'label' => $tier['count'] === 1 ? $tier['label'] : $tier['label'] . ' ' . ($tier['numbers'][$index] ?? $number),
                'number' => $tier['numbers'][$index] ?? (string) $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $tier['capacity'],
                'mapColor' => $tier['colors'][$index % count($tier['colors'])],
                'zone' => 'concert-' . $tier['tier'],
            ];
        }
    }

    return [
        'layout' => 'arena',
        'stageLabel' => 'Stage',
        'subtitle' => 'Mall of Asia Arena 13,000-seat end-stage concert layout',
        'capacity' => 13000,
        'sections' => $sections,
    ];
}

function clicketPhilippineArenaProfile(): array {
    $svgLayout = clicketPhilippineArenaSvgLayout();
    if (!empty($svgLayout['sections'])) {
        $sections = [];
        foreach ($svgLayout['sections'] as $section) {
            $tier = clicketPhilippineArenaTierForSvgId($section['id']);
            if (!$tier || !preg_match('/sec_(\d+)/i', $section['id'], $numberMatch)) {
                continue;
            }
            $number = $numberMatch[1];
            $sections[] = [
                'id' => $section['id'],
                'label' => $tier['label'] . ' ' . $number,
                'number' => $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $section['capacity'],
                'mapColor' => $tier['color'],
                'zone' => 'svg-' . $tier['tier'],
                'svgShape' => $section['shape'],
            ];
        }

        return [
            'layout' => 'arena',
            'stageLabel' => 'Stage',
            'subtitle' => 'Philippine Arena 55,000-seat concert layout',
            'capacity' => 55000,
            'sections' => $sections,
            'svgLayout' => [
                'viewBox' => $svgLayout['viewBox'],
                'nonSeats' => $svgLayout['nonSeats'],
            ],
        ];
    }

    return clicketVenueProfiles()['Philippine Arena'];
}

function clicketAranetaConcertProfile(): array {
    $svgLayout = clicketAranetaConcertSvgLayout();
    if (!empty($svgLayout['sections'])) {
        $sections = [];
        foreach ($svgLayout['sections'] as $section) {
            $tier = clicketAranetaConcertTierForSvgId($section['id']);
            if (!$tier || !preg_match('/sec_(\d+)/i', $section['id'], $numberMatch)) {
                continue;
            }
            $number = $numberMatch[1];
            $sections[] = [
                'id' => $section['id'],
                'label' => $tier['label'] . ' ' . $number,
                'number' => $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $section['capacity'],
                'mapColor' => $tier['color'],
                'zone' => 'svg-' . $tier['tier'],
                'svgShape' => $section['shape'],
            ];
        }

        return [
            'layout' => 'arena',
            'stageLabel' => 'Stage',
            'subtitle' => 'Smart Araneta Coliseum 13,000-seat concert layout',
            'capacity' => 13000,
            'sections' => $sections,
            'svgLayout' => [
                'viewBox' => $svgLayout['viewBox'],
                'nonSeats' => $svgLayout['nonSeats'],
            ],
        ];
    }

    return clicketVenueProfiles()['Smart Araneta Coliseum'];
}

function clicketAranetaSportsProfile(): array {
    $svgLayout = clicketAranetaSportsSvgLayout();
    if (!empty($svgLayout['sections'])) {
        $sections = [];
        foreach ($svgLayout['sections'] as $section) {
            $tier = clicketAranetaSportsTierForSvgId($section['id']);
            if (!$tier || !preg_match('/sec_(\d+)/i', $section['id'], $numberMatch)) {
                continue;
            }
            $number = $numberMatch[1];
            $sections[] = [
                'id' => $section['id'],
                'label' => $tier['label'] . ' ' . $number,
                'number' => $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $section['capacity'],
                'mapColor' => $tier['color'],
                'zone' => 'svg-' . $tier['tier'],
                'svgShape' => $section['shape'],
            ];
        }

        return [
            'layout' => 'court',
            'stageLabel' => 'Court',
            'subtitle' => 'Smart Araneta Coliseum 18,000-seat sports layout',
            'capacity' => 18000,
            'sections' => $sections,
            'svgLayout' => [
                'viewBox' => $svgLayout['viewBox'],
                'nonSeats' => $svgLayout['nonSeats'],
            ],
        ];
    }

    return clicketVenueProfiles()['Smart Araneta Coliseum'];
}

function clicketPhilsportsProfile(): array {
    $svgLayout = clicketPhilsportsSvgLayout();
    if (!empty($svgLayout['sections'])) {
        $sections = [];
        foreach ($svgLayout['sections'] as $section) {
            $tier = clicketPhilsportsTierForSvgId($section['id']);
            if (!$tier || !preg_match('/sec_(\d+)/i', $section['id'], $numberMatch)) {
                continue;
            }
            $number = $numberMatch[1];
            $sections[] = [
                'id' => $section['id'],
                'label' => $tier['label'] . ' ' . $number,
                'number' => $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $section['capacity'],
                'mapColor' => $tier['color'],
                'zone' => 'svg-' . $tier['tier'],
                'svgShape' => $section['shape'],
            ];
        }

        return [
            'layout' => 'court',
            'stageLabel' => 'Court',
            'subtitle' => 'PhilSports Arena 10,000-seat sports layout',
            'capacity' => 10000,
            'sections' => $sections,
            'svgLayout' => [
                'viewBox' => $svgLayout['viewBox'],
                'nonSeats' => $svgLayout['nonSeats'],
            ],
        ];
    }

    return clicketCourtProfile('PhilSports Arena');
}

function clicketTanghalanProfile(): array {
    $svgLayout = clicketTanghalanSvgLayout();
    if (!empty($svgLayout['sections'])) {
        $sections = [];
        $fixedCapacity = 0;
        $flexArea = 0.0;

        foreach ($svgLayout['sections'] as $section) {
            $tier = clicketTanghalanTierForSvgId($section['id']);
            if (!$tier || !preg_match('/sec_(\d+)/i', $section['id'], $numberMatch)) {
                continue;
            }
            $capacity = $tier['tier'] === 'svip' ? 8 : 0;
            $fixedCapacity += $capacity;
            if ($tier['tier'] !== 'svip') {
                $flexArea += (float) ($section['area'] ?? 0);
            }
            $number = $numberMatch[1];
            $sections[] = [
                'id' => $section['id'],
                'label' => $tier['label'] . ' ' . $number,
                'number' => $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $capacity,
                'mapColor' => $tier['color'],
                'zone' => 'svg-' . $tier['tier'],
                'svgShape' => $section['shape'],
                'area' => $section['area'] ?? 0,
            ];
        }

        $targetCapacity = 320;
        $remainingCapacity = max(0, $targetCapacity - $fixedCapacity);
        $allocated = 0;
        foreach ($sections as $index => $section) {
            if ($section['tier'] === 'svip') {
                $sections[$index]['remainder'] = 0;
                continue;
            }
            $exact = $flexArea > 0 ? (((float) $section['area']) / $flexArea) * $remainingCapacity : 0;
            $capacity = max(1, (int) floor($exact));
            $sections[$index]['capacity'] = $capacity;
            $sections[$index]['remainder'] = $exact - floor($exact);
            $allocated += $capacity;
        }

        $difference = $remainingCapacity - $allocated;
        $flexIndexes = array_keys(array_filter($sections, static fn (array $section): bool => $section['tier'] !== 'svip'));
        usort($flexIndexes, static fn (int $a, int $b): int => ($sections[$b]['remainder'] ?? 0) <=> ($sections[$a]['remainder'] ?? 0));
        for ($cursor = 0; $difference > 0 && !empty($flexIndexes); $cursor = ($cursor + 1) % count($flexIndexes)) {
            $sections[$flexIndexes[$cursor]]['capacity']++;
            $difference--;
        }
        for ($cursor = 0; $difference < 0 && !empty($flexIndexes); $cursor = ($cursor + 1) % count($flexIndexes)) {
            $index = $flexIndexes[$cursor];
            if ($sections[$index]['capacity'] > 1) {
                $sections[$index]['capacity']--;
                $difference++;
            }
        }

        foreach ($sections as $index => $section) {
            unset($sections[$index]['area'], $sections[$index]['remainder']);
        }

        return [
            'layout' => 'theater',
            'stageLabel' => 'Stage',
            'subtitle' => 'Tanghalang Ignacio Jimenez 320-seat theater layout',
            'capacity' => $targetCapacity,
            'sections' => $sections,
            'svgLayout' => [
                'viewBox' => $svgLayout['viewBox'],
                'nonSeats' => $svgLayout['nonSeats'],
            ],
        ];
    }

    return clicketTheaterProfile('Tanghalang Ignacio Jimenez', 'Center Orchestra', 'Gallery');
}

function clicketNewportProfile(): array {
    $svgLayout = clicketNewportSvgLayout();
    if (!empty($svgLayout['sections'])) {
        $sections = [];
        foreach ($svgLayout['sections'] as $section) {
            $tier = clicketNewportTierForSvgId($section['id']);
            if (!$tier) {
                continue;
            }
            $sections[] = [
                'id' => $section['id'],
                'label' => $tier['label'],
                'number' => $tier['label'],
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $section['capacity'],
                'mapColor' => $tier['color'],
                'zone' => 'svg-' . $tier['tier'],
                'svgShape' => $section['shape'],
            ];
        }

        return [
            'layout' => 'theater',
            'stageLabel' => 'Stage',
            'subtitle' => 'Newport Performing Arts Theater 1,700-seat layout',
            'capacity' => 1700,
            'sections' => $sections,
            'svgLayout' => [
                'viewBox' => $svgLayout['viewBox'],
                'nonSeats' => $svgLayout['nonSeats'],
            ],
        ];
    }

    return clicketTheaterProfile('Newport Performing Arts Theater', 'Orchestra', 'Balcony');
}

function clicketSolaireProfile(): array {
    $svgLayout = clicketSolaireSvgLayout();
    if (!empty($svgLayout['sections'])) {
        $sections = [];
        foreach ($svgLayout['sections'] as $section) {
            $tier = clicketSolaireTierForSvgId($section['id']);
            if (!$tier || !preg_match('/sec_(\d+)/i', $section['id'], $numberMatch)) {
                continue;
            }
            $number = $numberMatch[1];
            $sections[] = [
                'id' => $section['id'],
                'label' => $tier['label'] . ' ' . $number,
                'number' => $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $section['capacity'],
                'mapColor' => $tier['color'],
                'zone' => 'svg-' . $tier['tier'],
                'svgShape' => $section['shape'],
            ];
        }

        return [
            'layout' => 'theater',
            'stageLabel' => 'Stage',
            'subtitle' => 'The Theatre at Solaire 1,850-seat layout',
            'capacity' => 1850,
            'sections' => $sections,
            'svgLayout' => [
                'viewBox' => $svgLayout['viewBox'],
                'nonSeats' => $svgLayout['nonSeats'],
            ],
        ];
    }

    return clicketTheaterProfile('The Theatre at Solaire', 'Premium Orchestra', 'Upper Balcony');
}

function clicketStadiumProfile(): array {
    return [
        'layout' => 'stadium',
        'stageLabel' => 'Field',
        'subtitle' => 'Rizal Memorial stadium bowl',
        'sections' => [
            ['id' => 'grandstand-center', 'label' => 'Grandstand Center', 'category' => 'vip', 'zone' => 'floor-center'],
            ['id' => 'grandstand-left', 'label' => 'Grandstand Left', 'category' => 'platinum', 'zone' => 'lower-left'],
            ['id' => 'grandstand-right', 'label' => 'Grandstand Right', 'category' => 'platinum', 'zone' => 'lower-right'],
            ['id' => 'bleachers-left', 'label' => 'Bleachers Left', 'category' => 'gold', 'zone' => 'side-left'],
            ['id' => 'bleachers-right', 'label' => 'Bleachers Right', 'category' => 'gold', 'zone' => 'side-right'],
            ['id' => 'upper-left', 'label' => 'Upper Left', 'category' => 'silver', 'zone' => 'upper-left'],
            ['id' => 'upper-right', 'label' => 'Upper Right', 'category' => 'silver', 'zone' => 'upper-right'],
            ['id' => 'general', 'label' => 'General Admission', 'category' => 'bronze', 'zone' => 'rear'],
        ],
    ];
}

function clicketOutdoorProfile(): array {
    return [
        'layout' => 'outdoor',
        'stageLabel' => 'Festival Stage',
        'subtitle' => 'Open-air festival grounds',
        'sections' => [
            ['id' => 'pit-left', 'label' => 'VIP Pit Left', 'category' => 'vip', 'zone' => 'floor-left'],
            ['id' => 'pit-right', 'label' => 'VIP Pit Right', 'category' => 'vip', 'zone' => 'floor-right'],
            ['id' => 'premium-left', 'label' => 'Premium Lawn Left', 'category' => 'platinum', 'zone' => 'lower-left'],
            ['id' => 'premium-right', 'label' => 'Premium Lawn Right', 'category' => 'platinum', 'zone' => 'lower-right'],
            ['id' => 'gold-left', 'label' => 'Gold Lawn Left', 'category' => 'gold', 'zone' => 'side-left'],
            ['id' => 'gold-right', 'label' => 'Gold Lawn Right', 'category' => 'gold', 'zone' => 'side-right'],
            ['id' => 'general-left', 'label' => 'General Left', 'category' => 'general', 'zone' => 'upper-left'],
            ['id' => 'general-right', 'label' => 'General Right', 'category' => 'general', 'zone' => 'upper-right'],
            ['id' => 'general-rear', 'label' => 'General Rear', 'category' => 'bronze', 'zone' => 'rear'],
        ],
    ];
}

function clicketVenueMap(string $venue, string $categoryKey): array {
    $variant = $categoryKey === 'sports' ? 'sports' : 'concert';
    $maps = [
        'Mall of Asia Arena' => [
            'concert' => ['mapKey' => 'moa-concert', 'mapType' => 'end-stage', 'stageLabel' => 'Main Stage', 'subtitle' => 'End-stage arena concert layout'],
            'sports' => ['mapKey' => 'moa-sports', 'mapType' => 'court', 'stageLabel' => 'Ring / Court', 'subtitle' => 'Center-floor arena sports layout'],
        ],
        'Philippine Arena' => [
            'concert' => ['mapKey' => 'philippine-concert', 'mapType' => 'end-stage', 'stageLabel' => 'Concert Stage', 'subtitle' => 'Large-capacity concert bowl layout'],
            'sports' => ['mapKey' => 'philippine-concert', 'mapType' => 'end-stage', 'stageLabel' => 'Concert Stage', 'subtitle' => 'Large-capacity arena SVG layout'],
        ],
        'Smart Araneta Coliseum' => [
            'concert' => ['mapKey' => 'araneta-concert', 'mapType' => 'end-stage', 'stageLabel' => 'Main Stage', 'subtitle' => 'End-stage coliseum concert layout'],
            'sports' => ['mapKey' => 'araneta-sports', 'mapType' => 'court', 'stageLabel' => 'Playing Court', 'subtitle' => 'Center-court coliseum sports layout'],
        ],
        'Newport Performing Arts Theater' => ['default' => ['mapKey' => 'newport-svg', 'mapType' => 'theater', 'stageLabel' => 'Stage', 'subtitle' => 'Newport Performing Arts Theater 1,700-seat layout']],
        'The Theatre at Solaire' => ['default' => ['mapKey' => 'solaire-svg', 'mapType' => 'theater-reverse', 'stageLabel' => 'Stage', 'subtitle' => 'The Theatre at Solaire 1,850-seat layout']],
        'Tanghalang Ignacio Jimenez' => ['default' => ['mapKey' => 'tanghalan-svg', 'mapType' => 'theater-round', 'stageLabel' => 'Stage', 'subtitle' => 'Tanghalang Ignacio Jimenez theater layout']],
        'PhilSports Arena' => ['default' => ['mapKey' => 'philsports-svg', 'mapType' => 'court', 'stageLabel' => 'Court', 'subtitle' => 'PhilSports Arena sports layout']],
    ];

    $venueMaps = $maps[$venue] ?? [];
    $map = $venueMaps[$variant] ?? $venueMaps['default'] ?? [
        'mapKey' => 'generic-' . ($categoryKey ?: 'event'),
        'mapType' => $categoryKey === 'sports' ? 'court' : 'end-stage',
    ];

    return $map + ['mapVariant' => isset($venueMaps[$variant]) ? $variant : 'venue'];
}

function clicketVenueProfile(string $venue, string $categoryKey = ''): array {
    $profiles = clicketVenueProfiles();
    if ($venue === 'Mall of Asia Arena' && $categoryKey === 'sports') {
        $profile = clicketMoaSportsProfile();
    } elseif ($venue === 'Mall of Asia Arena' && $categoryKey === 'concerts') {
        $profile = clicketMoaConcertProfile();
    } elseif ($venue === 'Philippine Arena') {
        $profile = clicketPhilippineArenaProfile();
    } elseif ($venue === 'Smart Araneta Coliseum' && $categoryKey === 'concerts') {
        $profile = clicketAranetaConcertProfile();
    } elseif ($venue === 'Smart Araneta Coliseum' && $categoryKey === 'sports') {
        $profile = clicketAranetaSportsProfile();
    } elseif ($venue === 'Newport Performing Arts Theater') {
        $profile = clicketNewportProfile();
    } elseif ($venue === 'The Theatre at Solaire') {
        $profile = clicketSolaireProfile();
    } elseif ($venue === 'PhilSports Arena') {
        $profile = clicketPhilsportsProfile();
    } elseif ($venue === 'Tanghalang Ignacio Jimenez') {
        $profile = clicketTanghalanProfile();
    } else {
        $profile = $profiles[$venue] ?? clicketHallProfile($venue);
    }

    return array_merge($profile, clicketVenueMap($venue, $categoryKey));
}
