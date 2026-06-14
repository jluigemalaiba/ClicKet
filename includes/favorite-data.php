<?php

require_once __DIR__ . '/log.php';
require_once __DIR__ . '/ticketing.php';

if (!defined('CLICKET_FAVORITES_FILE')) {
    define('CLICKET_FAVORITES_FILE', __DIR__ . '/../storage/favorites.json');
}

function clicketEnsureFavoriteStore(): void {
    $directory = dirname(CLICKET_FAVORITES_FILE);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    if (!file_exists(CLICKET_FAVORITES_FILE)) {
        file_put_contents(CLICKET_FAVORITES_FILE, json_encode([], JSON_PRETTY_PRINT));
    }
}

function clicketReadFavorites(): array {
    clicketEnsureFavoriteStore();
    $favorites = json_decode(file_get_contents(CLICKET_FAVORITES_FILE) ?: '[]', true);

    return is_array($favorites) ? $favorites : [];
}

function clicketWriteFavorites(array $favorites): bool {
    clicketEnsureFavoriteStore();

    return file_put_contents(
        CLICKET_FAVORITES_FILE,
        json_encode(array_values($favorites), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    ) !== false;
}

function clicketFavoriteSnapshot(string $eventId): ?array {
    $resolved = clicketResolveEvent($eventId);
    if (!$resolved) {
        return null;
    }

    $event = $resolved['event'];

    return [
        'event_id' => $eventId,
        'title' => (string) ($event['title'] ?? 'ClicKet Event'),
        'date' => $resolved['date']->format('M j, Y'),
        'time' => (string) $resolved['time'],
        'venue' => (string) ($event['venue'] ?? ''),
        'category' => (string) $resolved['categoryLabel'],
        'type' => (string) ($event['type'] ?? $resolved['categoryLabel']),
        'poster' => (string) $resolved['poster'],
        'url' => 'show.php?event=' . rawurlencode($eventId),
    ];
}

function clicketFavoritesForUser(string $userId): array {
    $favorites = array_values(array_filter(
        clicketReadFavorites(),
        fn(array $favorite): bool => hash_equals((string) ($favorite['user_id'] ?? ''), $userId)
    ));

    usort($favorites, fn(array $left, array $right): int => strcmp(
        (string) ($right['created_at'] ?? ''),
        (string) ($left['created_at'] ?? '')
    ));

    return $favorites;
}

function clicketFavoriteIdsForUser(string $userId): array {
    return array_values(array_map(
        fn(array $favorite): string => (string) ($favorite['event_id'] ?? ''),
        clicketFavoritesForUser($userId)
    ));
}

function clicketSetFavorite(string $userId, string $eventId, bool $favorite): array {
    $favorites = clicketReadFavorites();
    $existingIndex = null;

    foreach ($favorites as $index => $item) {
        if (
            hash_equals((string) ($item['user_id'] ?? ''), $userId)
            && hash_equals((string) ($item['event_id'] ?? ''), $eventId)
        ) {
            $existingIndex = $index;
            break;
        }
    }

    if ($favorite && $existingIndex === null) {
        $snapshot = clicketFavoriteSnapshot($eventId);
        if (!$snapshot) {
            return ['success' => false, 'message' => 'Event not found.'];
        }

        $favorites[] = $snapshot + [
            'user_id' => $userId,
            'created_at' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('c'),
        ];
    } elseif (!$favorite && $existingIndex !== null) {
        array_splice($favorites, $existingIndex, 1);
    }

    if (!clicketWriteFavorites($favorites)) {
        return ['success' => false, 'message' => 'Unable to update favorites right now.'];
    }

    return [
        'success' => true,
        'favorite' => $favorite,
        'favorites' => clicketFavoritesForUser($userId),
    ];
}

