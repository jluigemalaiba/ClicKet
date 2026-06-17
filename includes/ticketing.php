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

    foreach ([
        ['tier' => 'floor', 'start' => 101, 'count' => 16, 'capacity' => 100, 'category' => 'vip', 'label' => 'Floor Seating', 'mapColor' => '#166f83'],
        ['tier' => 'lower', 'start' => 201, 'count' => 16, 'capacity' => 200, 'category' => 'gold', 'label' => 'Lower Box', 'mapColor' => '#f2c542'],
        ['tier' => 'upper', 'start' => 301, 'count' => 16, 'capacity' => 300, 'category' => 'platinum', 'label' => 'Upper Box', 'mapColor' => '#178ba0'],
        ['tier' => 'general', 'start' => 401, 'count' => 16, 'capacity' => 150, 'category' => 'general', 'label' => 'General Admission', 'mapColor' => '#b61f36'],
    ] as $tier) {
        for ($index = 0; $index < $tier['count']; $index++) {
            $number = $tier['start'] + $index;
            $sections[] = [
                'id' => 'cuneta-' . $tier['tier'] . '-' . $number,
                'label' => $tier['label'] . ' ' . $number,
                'number' => (string) $number,
                'category' => $tier['category'],
                'tier' => $tier['tier'],
                'capacity' => $tier['capacity'],
                'mapColor' => $tier['mapColor'],
                'zone' => 'ring-' . $tier['tier'],
            ];
        }
    }

    return [
        'layout' => 'court',
        'stageLabel' => 'Basketball Court',
        'subtitle' => 'Cuneta Astrodome four-tier arena bowl',
        'capacity' => 12000,
        'sections' => $sections,
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

    return $points;
}

function clicketPathPoints(string $pathData): array {
    preg_match_all('/[A-Za-z]|-?\d+(?:\.\d+)?/', $pathData, $matches);
    $tokens = $matches[0];
    $points = [];
    $index = 0;
    $command = '';
    $x = 0.0;
    $y = 0.0;

    while ($index < count($tokens)) {
        if (preg_match('/^[A-Za-z]$/', $tokens[$index])) {
            $command = $tokens[$index++];
        }

        if ($command === 'M' || $command === 'L') {
            while ($index + 1 < count($tokens) && !preg_match('/^[A-Za-z]$/', $tokens[$index])) {
                $x = (float) $tokens[$index++];
                $y = (float) $tokens[$index++];
                $points[] = [$x, $y];
                if ($command === 'M') {
                    $command = 'L';
                }
            }
            continue;
        }

        if ($command === 'H') {
            while ($index < count($tokens) && !preg_match('/^[A-Za-z]$/', $tokens[$index])) {
                $x = (float) $tokens[$index++];
                $points[] = [$x, $y];
            }
            continue;
        }

        if ($command === 'V') {
            while ($index < count($tokens) && !preg_match('/^[A-Za-z]$/', $tokens[$index])) {
                $y = (float) $tokens[$index++];
                $points[] = [$x, $y];
            }
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
            'subtitle' => 'MOA Arena 16,000-seat sports bowl',
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
        'subtitle' => 'MOA Arena 16,000-seat sports bowl',
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
            'subtitle' => 'MOA Arena 13,000-seat end-stage concert layout',
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
        'subtitle' => 'MOA Arena 13,000-seat end-stage concert layout',
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
        'Cuneta Astrodome' => ['default' => ['mapKey' => 'cuneta', 'mapType' => 'cuneta-bowl', 'stageLabel' => 'Basketball Court', 'subtitle' => 'Cuneta Astrodome four-tier arena bowl', 'capacity' => 12000]],
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
    if ($venue === 'MOA Arena' && $categoryKey === 'sports') {
        $profile = clicketMoaSportsProfile();
    } elseif ($venue === 'MOA Arena' && $categoryKey === 'concerts') {
        $profile = clicketMoaConcertProfile();
    } elseif ($venue === 'Philippine Arena' && $categoryKey === 'concerts') {
        $profile = clicketPhilippineArenaProfile();
    } else {
        $profile = $profiles[$venue] ?? clicketHallProfile($venue);
    }

    return array_merge($profile, clicketVenueMap($venue, $categoryKey));
}
