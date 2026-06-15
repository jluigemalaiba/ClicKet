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
    if (!preg_match('/^(concerts|theater|sports)-(\d+)$/', $eventKey, $matches)) {
        return null;
    }

    $catalogs = clicketTicketCatalogs();
    $categoryKey = $matches[1];
    $eventIndex = (int) $matches[2] - 1;
    $catalog = $catalogs[$categoryKey];
    $event = $catalog['events'][$eventIndex] ?? null;

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

function clicketVenueProfiles(): array {
    return [
        'MOA Arena' => [
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
        'Metropolitan Theater' => clicketTheaterProfile('Metropolitan Theater', 'Orchestra', 'Grand Balcony'),
        'Solaire Resort Entertainment City' => clicketTheaterProfile('The Theatre at Solaire', 'Premium Orchestra', 'Upper Balcony'),
        'Tanghalang Pilipino' => clicketTheaterProfile('Tanghalang Pilipino', 'Center Orchestra', 'Gallery'),
        'Resorts World Manila' => clicketTheaterProfile('Newport World Resorts', 'Stalls', 'Balcony'),
        'Samsung Hall' => clicketHallProfile('Samsung Hall'),
        'Philsports Arena' => clicketCourtProfile('Philsports Arena'),
        'Filoil EcoOil Centre' => clicketCourtProfile('Filoil EcoOil Centre'),
        'Cuneta Astrodome' => clicketCunetaProfile(),
        'Muntinlupa Sports Center' => clicketCourtProfile('Muntinlupa Sports Center'),
        'Ninoy Aquino Stadium and Rizal Memorial' => clicketStadiumProfile(),
        'Nuvali' => clicketOutdoorProfile(),
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

function clicketCunetaProfile(): array {
    $sections = [];
    $number = 101;

    foreach ([
        ['tier' => 'lower', 'count' => 8, 'capacity' => 200, 'category' => 'vip', 'label' => 'Lower Box'],
        ['tier' => 'upper', 'count' => 12, 'capacity' => 200, 'category' => 'platinum', 'label' => 'Upper Box'],
        ['tier' => 'general', 'count' => 16, 'capacity' => 125, 'category' => 'general', 'label' => 'General Admission'],
    ] as $tier) {
        for ($index = 0; $index < $tier['count']; $index++) {
            $sections[] = [
                'id' => 'cuneta-' . $tier['tier'] . '-' . $number,
                'label' => $tier['label'] . ' ' . $number,
                'number' => (string) $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $tier['capacity'],
                'zone' => 'ring-' . $tier['tier'],
            ];
            $number++;
        }
    }

    return [
        'layout' => 'court',
        'stageLabel' => 'Basketball Court',
        'subtitle' => 'Concentric Astrodome arena-bowl layout',
        'capacity' => 6000,
        'sections' => $sections,
    ];
}

function clicketMoaSportsProfile(): array {
    $sections = [];

    foreach ([
        ['tier' => 'lower', 'start' => 101, 'count' => 16, 'capacity' => 400, 'category' => 'vip', 'label' => 'Lower Bowl'],
        ['tier' => 'club', 'start' => 201, 'count' => 16, 'capacity' => 100, 'category' => 'gold', 'label' => 'Club Premium'],
        ['tier' => 'upper', 'start' => 301, 'count' => 32, 'capacity' => 250, 'category' => 'silver', 'label' => 'Upper Bowl'],
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
                'zone' => 'ring-' . $tier['tier'],
            ];
        }
    }

    return [
        'layout' => 'court',
        'stageLabel' => 'Basketball Court',
        'subtitle' => 'MOA Arena 16,000-seat sports bowl',
        'capacity' => 16000,
        'sections' => $sections,
    ];
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
        'MOA Arena' => [
            'concert' => ['mapKey' => 'moa-concert', 'mapType' => 'end-stage', 'stageLabel' => 'Main Stage', 'subtitle' => 'End-stage arena concert layout'],
            'sports' => ['mapKey' => 'moa-sports', 'mapType' => 'court', 'stageLabel' => 'Ring / Court', 'subtitle' => 'Center-floor arena sports layout'],
        ],
        'Philippine Arena' => [
            'concert' => ['mapKey' => 'philippine-concert', 'mapType' => 'end-stage', 'stageLabel' => 'Concert Stage', 'subtitle' => 'Large-capacity concert bowl layout'],
            'sports' => ['mapKey' => 'philippine-sports', 'mapType' => 'court', 'stageLabel' => 'Playing Court', 'subtitle' => 'Large-capacity arena sports layout'],
        ],
        'Smart Araneta Coliseum' => [
            'concert' => ['mapKey' => 'araneta-concert', 'mapType' => 'end-stage', 'stageLabel' => 'Main Stage', 'subtitle' => 'End-stage coliseum concert layout'],
            'sports' => ['mapKey' => 'araneta-sports', 'mapType' => 'court', 'stageLabel' => 'Playing Court', 'subtitle' => 'Center-court coliseum sports layout'],
        ],
        'Newport Performing Arts Theater' => ['default' => ['mapKey' => 'newport', 'mapType' => 'theater']],
        'Metropolitan Theater' => ['default' => ['mapKey' => 'metropolitan', 'mapType' => 'theater-reverse']],
        'Solaire Resort Entertainment City' => ['default' => ['mapKey' => 'solaire', 'mapType' => 'theater-reverse']],
        'Tanghalang Pilipino' => ['default' => ['mapKey' => 'tanghalan', 'mapType' => 'theater-round']],
        'Resorts World Manila' => ['default' => ['mapKey' => 'resorts', 'mapType' => 'end-stage']],
        'Samsung Hall' => ['default' => ['mapKey' => 'samsung', 'mapType' => 'theater-reverse']],
        'Philsports Arena' => ['default' => ['mapKey' => 'philsports', 'mapType' => 'court']],
        'Filoil EcoOil Centre' => ['default' => ['mapKey' => 'filoil', 'mapType' => 'court']],
        'Cuneta Astrodome' => ['default' => ['mapKey' => 'cuneta', 'mapType' => 'cuneta-bowl', 'stageLabel' => 'Basketball Court', 'subtitle' => 'Concentric Astrodome arena-bowl layout', 'capacity' => 6000]],
        'Muntinlupa Sports Center' => ['default' => ['mapKey' => 'muntinlupa', 'mapType' => 'court']],
        'Ninoy Aquino Stadium and Rizal Memorial' => ['default' => ['mapKey' => 'ninoy', 'mapType' => 'court']],
        'Nuvali' => ['default' => ['mapKey' => 'nuvali', 'mapType' => 'tennis', 'stageLabel' => 'Competition Court', 'subtitle' => 'Open-air competition court layout']],
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
    $profile = $venue === 'MOA Arena' && $categoryKey === 'sports'
        ? clicketMoaSportsProfile()
        : ($profiles[$venue] ?? clicketHallProfile($venue));

    return array_merge($profile, clicketVenueMap($venue, $categoryKey));
}
