<?php

declare(strict_types=1);

require_once __DIR__ . '/log.php';
require_once __DIR__ . '/database.php';

function clicketFavoriteSnapshot(string $eventId): ?array {
    $row = clicketDbFetch(
        'SELECT e.event_key, e.title, e.type, e.category, e.poster_url,
                v.name AS venue_name, ep.performance_date, ep.performance_time
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
         WHERE e.event_key = :event_key
         LIMIT 1',
        ['event_key' => $eventId]
    );
    if (!$row) {
        return null;
    }

    $category = match ((string) $row['category']) {
        'concert' => 'Concert',
        'theater' => 'Theater',
        'sports' => 'Sports',
        default => ucfirst((string) $row['category']),
    };

    return [
        'event_id' => (string) $row['event_key'],
        'event' => (string) $row['event_key'],
        'event_title' => (string) $row['title'],
        'title' => (string) $row['title'],
        'date' => clicketDbDisplayDate((string) ($row['performance_date'] ?? '')),
        'time' => clicketDbDisplayTime((string) ($row['performance_time'] ?? '')),
        'venue' => (string) $row['venue_name'],
        'category' => $category,
        'type' => (string) ($row['type'] ?? $category),
        'poster' => (string) ($row['poster_url'] ?? ''),
        'url' => 'show.php?event=' . rawurlencode((string) $row['event_key']),
    ];
}

function clicketReadFavorites(): array {
    $rows = clicketDbFetchAll(
        'SELECT f.*, u.name AS user_name, u.email AS user_email,
                e.event_key, e.title, e.type, e.category, e.poster_url,
                v.name AS venue_name, ep.performance_date, ep.performance_time
         FROM favorites f
         INNER JOIN users u ON u.id = f.user_id
         INNER JOIN events e ON e.id = f.event_id
         INNER JOIN venues v ON v.id = e.venue_id
         LEFT JOIN event_performances ep
           ON ep.id = (
             SELECT ep2.id
             FROM event_performances ep2
             WHERE ep2.event_id = e.id
             ORDER BY ep2.performance_date, ep2.performance_time, ep2.id
             LIMIT 1
           )
         ORDER BY f.created_at DESC, f.id DESC'
    );

    return array_map(static function (array $row): array {
        $category = match ((string) $row['category']) {
            'concert' => 'Concert',
            'theater' => 'Theater',
            'sports' => 'Sports',
            default => ucfirst((string) $row['category']),
        };

        return [
            'id' => (string) $row['id'],
            'user_id' => (string) $row['user_id'],
            'user_name' => (string) $row['user_name'],
            'user_email' => (string) $row['user_email'],
            'event_id' => (string) $row['event_key'],
            'event' => (string) $row['event_key'],
            'event_title' => (string) $row['title'],
            'title' => (string) $row['title'],
            'date' => clicketDbDisplayDate((string) ($row['performance_date'] ?? '')),
            'time' => clicketDbDisplayTime((string) ($row['performance_time'] ?? '')),
            'venue' => (string) $row['venue_name'],
            'category' => $category,
            'type' => (string) ($row['type'] ?? $category),
            'poster' => (string) ($row['poster_url'] ?? ''),
            'url' => 'show.php?event=' . rawurlencode((string) $row['event_key']),
            'created_at' => clicketDbDisplayDateTime((string) $row['created_at']),
        ];
    }, $rows);
}

function clicketWriteFavorites(array $favorites): bool {
    $pdo = clicketDb();
    $pdo->beginTransaction();

    try {
        foreach ($favorites as $favorite) {
            $userId = clicketDbUserIdFromSession((string) ($favorite['user_id'] ?? ''));
            $event = clicketDbEventByKey((string) ($favorite['event_id'] ?? $favorite['event'] ?? ''));
            if (!$userId || !$event) {
                continue;
            }

            clicketDbExecute(
                'INSERT IGNORE INTO favorites (user_id, event_id, created_at)
                 VALUES (:user_id, :event_id, :created_at)',
                [
                    'user_id' => $userId,
                    'event_id' => (int) $event['id'],
                    'created_at' => clicketDbDateTime((string) ($favorite['created_at'] ?? 'now')),
                ]
            );
        }

        $pdo->commit();
        return true;
    } catch (Throwable) {
        $pdo->rollBack();
        return false;
    }
}

function clicketFavoritesForUser(string $userId): array {
    $dbUserId = clicketDbUserIdFromSession($userId);
    if (!$dbUserId) {
        $current = currentUser();
        $dbUserId = !empty($current['email']) ? clicketDbUserIdByEmail((string) $current['email']) : null;
    }
    if (!$dbUserId) {
        return [];
    }

    return array_values(array_filter(
        clicketReadFavorites(),
        static fn (array $favorite): bool => (string) ($favorite['user_id'] ?? '') === (string) $dbUserId
    ));
}

function clicketFavoriteIdsForUser(string $userId): array {
    return array_values(array_map(
        static fn (array $favorite): string => (string) ($favorite['event_id'] ?? ''),
        clicketFavoritesForUser($userId)
    ));
}

function clicketSetFavorite(string $userId, string $eventId, bool $favorite): array {
    $dbUserId = clicketDbUserIdFromSession($userId);
    if (!$dbUserId) {
        $current = currentUser();
        $dbUserId = !empty($current['email']) ? clicketDbUserIdByEmail((string) $current['email']) : null;
    }

    $event = clicketDbEventByKey($eventId);
    if (!$dbUserId || !$event) {
        return ['success' => false, 'message' => 'Event not found.'];
    }

    if ($favorite) {
        clicketDbExecute(
            'INSERT IGNORE INTO favorites (user_id, event_id) VALUES (:user_id, :event_id)',
            ['user_id' => $dbUserId, 'event_id' => (int) $event['id']]
        );
    } else {
        clicketDbExecute(
            'DELETE FROM favorites WHERE user_id = :user_id AND event_id = :event_id',
            ['user_id' => $dbUserId, 'event_id' => (int) $event['id']]
        );
    }

    return [
        'success' => true,
        'favorite' => $favorite,
        'favorites' => clicketFavoritesForUser((string) $dbUserId),
    ];
}
