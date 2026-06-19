<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function clicketCatalogCategoryDbName(string $categoryKey): string {
    return match ($categoryKey) {
        'concerts' => 'concert',
        'theater' => 'theater',
        'sports' => 'sports',
        default => $categoryKey,
    };
}

function clicketCatalogCategoryLabel(string $categoryKey): string {
    return match ($categoryKey) {
        'concerts' => 'Concert',
        'theater' => 'Theater',
        'sports' => 'Sports',
        default => ucfirst($categoryKey),
    };
}

function clicketCatalogTimeFor(string $categoryKey, int $index): string {
    $times = ['6:00 PM', '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM'];
    $offset = match ($categoryKey) {
        'theater' => 1,
        'sports' => 2,
        default => 0,
    };

    return $times[($index + $offset) % count($times)];
}

function clicketCatalogPosterCategory(string $categoryKey): string {
    return $categoryKey === 'concerts' ? 'concert' : $categoryKey;
}

function clicketCatalogVenueSlug(string $venueName): string {
    $normalized = strtolower(trim($venueName));
    return match ($normalized) {
        'moa arena', 'mall of asia arena' => 'mall-of-asia-arena',
        'philsports arena' => 'philsports-arena',
        'tanghalang pilipino', 'tanghalang ignacio jimenez' => 'tanghalang-ignacio-jimenez',
        default => clicketDbSlug($venueName),
    };
}

function clicketCatalogCanonicalVenueName(string $venueName): string {
    $normalized = strtolower(trim($venueName));
    return match ($normalized) {
        'moa arena' => 'Mall of Asia Arena',
        'philsports arena' => 'PhilSports Arena',
        'tanghalang pilipino' => 'Tanghalang Ignacio Jimenez',
        default => trim($venueName),
    };
}

function clicketCatalogEnsureVenue(string $venueName): int {
    $name = clicketCatalogCanonicalVenueName($venueName);
    $slug = clicketCatalogVenueSlug($name);
    $existing = clicketDbFetch(
        'SELECT id FROM venues WHERE slug = :slug LIMIT 1',
        ['slug' => $slug]
    );

    if ($existing) {
        return (int) $existing['id'];
    }

    clicketDbExecute(
        'INSERT INTO venues (name, slug, status) VALUES (:name, :slug, "active")',
        ['name' => $name, 'slug' => $slug]
    );

    $venueId = (int) clicketDb()->lastInsertId();
    $aliases = array_unique(array_filter([$venueName, $name]));
    foreach ($aliases as $alias) {
        clicketDbExecute(
            'INSERT IGNORE INTO venue_aliases (venue_id, alias) VALUES (:venue_id, :alias)',
            ['venue_id' => $venueId, 'alias' => $alias]
        );
    }

    return $venueId;
}

function clicketCatalogEnsureVenueLayout(int $venueId, string $venueName, string $categoryKey): int {
    $variant = $categoryKey === 'sports' ? 'sports' : ($categoryKey === 'theater' ? 'theater' : 'concert');
    $layoutKey = clicketDbSlug(clicketCatalogCanonicalVenueName($venueName) . '-' . $variant);
    $existing = clicketDbFetch(
        'SELECT id FROM venue_layouts WHERE layout_key = :layout_key LIMIT 1',
        ['layout_key' => $layoutKey]
    );

    if ($existing) {
        return (int) $existing['id'];
    }

    $capacity = match (clicketCatalogCanonicalVenueName($venueName)) {
        'Philippine Arena' => 55000,
        'Smart Araneta Coliseum' => $categoryKey === 'sports' ? 18000 : 13000,
        'Mall of Asia Arena' => $categoryKey === 'sports' ? 16000 : 13000,
        'Newport Performing Arts Theater' => 1700,
        'The Theatre at Solaire' => 1850,
        'Tanghalang Ignacio Jimenez' => 320,
        'PhilSports Arena' => 10000,
        default => 1000,
    };

    clicketDbExecute(
        'INSERT INTO venue_layouts
           (venue_id, layout_key, variant, category, capacity, svg_file, map_type, stage_label, subtitle, status)
         VALUES
           (:venue_id, :layout_key, :variant, :category, :capacity, :svg_file, :map_type, :stage_label, :subtitle, "active")',
        [
            'venue_id' => $venueId,
            'layout_key' => $layoutKey,
            'variant' => $variant,
            'category' => clicketCatalogCategoryDbName($categoryKey),
            'capacity' => $capacity,
            'svg_file' => $layoutKey . '.svg',
            'map_type' => $categoryKey === 'sports' ? 'court' : ($categoryKey === 'theater' ? 'theater' : 'end-stage'),
            'stage_label' => $categoryKey === 'sports' ? 'Playing Court' : 'Stage',
            'subtitle' => clicketCatalogCanonicalVenueName($venueName) . ' ' . clicketCatalogCategoryLabel($categoryKey) . ' layout',
        ]
    );

    return (int) clicketDb()->lastInsertId();
}

function clicketCatalogDefaultStaffId(): int {
    $staffId = clicketDbStaffIdByEmail('organizer@clicket.local')
        ?? clicketDbStaffIdByEmail('admin@clicket.local');
    if ($staffId) {
        return $staffId;
    }

    clicketDbExecute(
        'INSERT INTO staff_accounts (name, email, password_hash, role, status)
         VALUES ("CLICKET Admin", "admin@clicket.local", :password_hash, "admin", "active")',
        ['password_hash' => password_hash('admin123', PASSWORD_DEFAULT)]
    );

    return (int) clicketDb()->lastInsertId();
}

function clicketCatalogSeedEvent(string $categoryKey, int $index, array $event): void {
    $eventKey = $categoryKey . '-' . ($index + 1);
    $venueName = clicketCatalogCanonicalVenueName((string) ($event['venue'] ?? 'ClicKet Venue'));
    $venueId = clicketCatalogEnsureVenue($venueName);
    $layoutId = clicketCatalogEnsureVenueLayout($venueId, $venueName, $categoryKey);
    $posterCategory = clicketCatalogPosterCategory($categoryKey);
    $baseDate = (string) ($event['date'] ?? 'today');
    $baseTime = clicketCatalogTimeFor($categoryKey, $index);
    $price = clicketDbMoneyValue($event['price'] ?? 2500);
    if ($price <= 0) {
        $price = 2500;
    }

    clicketDbExecute(
        'INSERT INTO events
           (event_key, title, category, type, artist, company, league, venue_id, venue_layout_id,
            poster_url, banner_url, base_price, rating, status, created_by_staff_id)
         VALUES
           (:event_key, :title, :category, :type, :artist, :company, :league, :venue_id, :layout_id,
            :poster_url, :banner_url, :base_price, :rating, "published", :staff_id)
         ON DUPLICATE KEY UPDATE
           title = VALUES(title),
           category = VALUES(category),
           type = VALUES(type),
           artist = VALUES(artist),
           company = VALUES(company),
           league = VALUES(league),
           venue_id = VALUES(venue_id),
           venue_layout_id = VALUES(venue_layout_id),
           poster_url = VALUES(poster_url),
           banner_url = VALUES(banner_url),
           base_price = VALUES(base_price),
           rating = VALUES(rating),
           status = VALUES(status)',
        [
            'event_key' => $eventKey,
            'title' => (string) ($event['title'] ?? 'ClicKet Event'),
            'category' => clicketCatalogCategoryDbName($categoryKey),
            'type' => (string) ($event['type'] ?? clicketCatalogCategoryLabel($categoryKey)),
            'artist' => $event['artist'] ?? null,
            'company' => $event['company'] ?? null,
            'league' => $event['league'] ?? null,
            'venue_id' => $venueId,
            'layout_id' => $layoutId,
            'poster_url' => posterUrl($posterCategory, $index + 10),
            'banner_url' => landscapeUrl($posterCategory, $index + 10),
            'base_price' => $price,
            'rating' => (float) ($event['rating'] ?? 5),
            'staff_id' => clicketCatalogDefaultStaffId(),
        ]
    );

    $eventRow = clicketDbEventByKey($eventKey);
    if (!$eventRow) {
        return;
    }

    $baseDateObject = DateTimeImmutable::createFromFormat('M j, Y', $baseDate)
        ?: new DateTimeImmutable($baseDate);
    $performances = [[$baseDateObject, $baseTime]];
    if ($categoryKey === 'theater') {
        $performances = [
            [$baseDateObject, $baseTime],
            [$baseDateObject->modify('+1 day'), '2:00 PM'],
            [$baseDateObject->modify('+1 day'), '7:30 PM'],
            [$baseDateObject->modify('+2 days'), '3:00 PM'],
        ];
    }

    foreach ($performances as [$date, $time]) {
        clicketDbExecute(
            'INSERT IGNORE INTO event_performances (event_id, performance_date, performance_time, status)
             VALUES (:event_id, :performance_date, :performance_time, "scheduled")',
            [
                'event_id' => (int) $eventRow['id'],
                'performance_date' => $date->format('Y-m-d'),
                'performance_time' => clicketDbSqlTimeFromLabel($time),
            ]
        );
    }
}

function clicketCatalogSeed(array $concertEvents, array $theaterEvents, array $sportsEvents): void {
    foreach ([
        'concerts' => $concertEvents,
        'theater' => $theaterEvents,
        'sports' => $sportsEvents,
    ] as $categoryKey => $events) {
        foreach ($events as $index => $event) {
            clicketCatalogSeedEvent($categoryKey, (int) $index, $event);
        }
    }
}

function clicketCatalogRows(string $categoryKey): array {
    $rows = clicketDbFetchAll(
        'SELECT e.*, v.name AS venue_name,
                ep.performance_date, ep.performance_time,
                assigned.staff_id AS organizer_id
         FROM events e
         INNER JOIN venues v ON v.id = e.venue_id
         LEFT JOIN event_performances ep
           ON ep.id = (
             SELECT ep2.id
             FROM event_performances ep2
             WHERE ep2.event_id = e.id
             ORDER BY ep2.performance_date, ep2.performance_time, ep2.id
             LIMIT 1
           )
         LEFT JOIN staff_venue_assignments assigned
           ON assigned.id = (
             SELECT sva.id
             FROM staff_venue_assignments sva
             INNER JOIN staff_accounts sa ON sa.id = sva.staff_id
             WHERE sva.venue_id = v.id AND sa.role = "organizer"
             ORDER BY sva.id
             LIMIT 1
           )
         WHERE e.category = :category
         ORDER BY CAST(SUBSTRING_INDEX(e.event_key, "-", -1) AS UNSIGNED), e.id',
        ['category' => clicketCatalogCategoryDbName($categoryKey)]
    );

    return array_map(static function (array $row) use ($categoryKey): array {
        $owner = $row['artist'] ?: ($row['company'] ?: ($row['league'] ?: ''));
        $event = [
            'event_key' => (string) $row['event_key'],
            'id' => (string) $row['event_key'],
            'db_id' => (int) $row['id'],
            'organizer_id' => (string) ($row['organizer_id'] ?? $row['created_by_staff_id'] ?? ''),
            'title' => (string) $row['title'],
            'venue' => (string) $row['venue_name'],
            'date' => clicketDbDisplayDate((string) ($row['performance_date'] ?? '')),
            'price' => clicketDbFormatPrice($row['base_price'] ?? 0),
            'rating' => (float) ($row['rating'] ?? 5),
            'type' => (string) ($row['type'] ?? clicketCatalogCategoryLabel($categoryKey)),
            'poster' => (string) ($row['poster_url'] ?? ''),
            'banner' => (string) ($row['banner_url'] ?? ''),
            'status' => (string) ($row['status'] ?? 'published'),
        ];

        if ($categoryKey === 'concerts') {
            $event['artist'] = $owner;
        } elseif ($categoryKey === 'theater') {
            $event['company'] = $owner;
        } else {
            $event['league'] = $owner;
        }

        return $event;
    }, $rows);
}

function clicketCatalogFeaturedRows(): array {
    $rows = clicketDbFetchAll(
        'SELECT e.*, v.name AS venue_name, ep.performance_date
         FROM events e
         INNER JOIN venues v ON v.id = e.venue_id
         LEFT JOIN event_performances ep
           ON ep.id = (
             SELECT ep2.id
             FROM event_performances ep2
             WHERE ep2.event_id = e.id
             ORDER BY ep2.performance_date, ep2.performance_time, ep2.id
             LIMIT 1
           )
         WHERE e.status = "published"
         ORDER BY FIELD(e.event_key, "concerts-1", "theater-1", "sports-1", "concerts-2", "theater-2", "sports-2", "concerts-5"), e.id
         LIMIT 7'
    );

    return array_map(static function (array $row): array {
        $categoryLabel = match ((string) $row['category']) {
            'concert' => 'Concert',
            'theater' => 'Theater',
            'sports' => 'Sports',
            default => ucfirst((string) $row['category']),
        };

        return [
            'title' => (string) $row['title'],
            'sub' => (string) ($row['type'] ?? $categoryLabel),
            'category' => $categoryLabel,
            'venue' => (string) $row['venue_name'],
            'date' => clicketDbDisplayDate((string) ($row['performance_date'] ?? '')),
            'price' => clicketDbFormatPrice($row['base_price'] ?? 0),
            'poster' => (string) ($row['poster_url'] ?? ''),
            'event_key' => (string) $row['event_key'],
        ];
    }, $rows);
}

function clicketLoadCatalogFromDatabase(array &$concertEvents, array &$theaterEvents, array &$sportsEvents, array &$featuredEvents): void {
    static $loaded = false;
    static $cache = null;

    if (!$loaded) {
        clicketCatalogSeed($concertEvents, $theaterEvents, $sportsEvents);
        $cache = [
            'concerts' => clicketCatalogRows('concerts'),
            'theater' => clicketCatalogRows('theater'),
            'sports' => clicketCatalogRows('sports'),
            'featured' => clicketCatalogFeaturedRows(),
        ];
        $loaded = true;
    }

    $concertEvents = $cache['concerts'];
    $theaterEvents = $cache['theater'];
    $sportsEvents = $cache['sports'];
    $featuredEvents = $cache['featured'];
}
