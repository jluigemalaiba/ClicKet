<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/order-history-data.php';
require_once __DIR__ . '/includes/favorite-data.php';

function clicketMigrationReadJson(string $path): array {
    if (!is_file($path)) {
        return [];
    }

    $data = json_decode(file_get_contents($path) ?: '[]', true);

    return is_array($data) ? $data : [];
}

function clicketMigrationImportCustomer(array $account): ?int {
    $email = strtolower(trim((string) ($account['email'] ?? '')));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }

    clicketDbExecute(
        'INSERT INTO users (name, email, password_hash, status, created_at)
         VALUES (:name, :email, :password_hash, "active", :created_at)
         ON DUPLICATE KEY UPDATE
           name = VALUES(name),
           password_hash = VALUES(password_hash),
           status = "active"',
        [
            'name' => trim((string) ($account['name'] ?? 'ClicKet User')),
            'email' => $email,
            'password_hash' => (string) ($account['password'] ?? $account['password_hash'] ?? password_hash(bin2hex(random_bytes(12)), PASSWORD_DEFAULT)),
            'created_at' => clicketDbDateTime((string) ($account['created_at'] ?? 'now')),
        ]
    );

    return clicketDbUserIdByEmail($email);
}

function clicketMigrationImportStaff(array $account): ?int {
    $email = strtolower(trim((string) ($account['email'] ?? '')));
    $role = (string) ($account['role'] ?? 'organizer');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['admin', 'organizer'], true)) {
        return null;
    }

    clicketDbExecute(
        'INSERT INTO staff_accounts (name, email, password_hash, role, status, created_at)
         VALUES (:name, :email, :password_hash, :role, "active", :created_at)
         ON DUPLICATE KEY UPDATE
           name = VALUES(name),
           password_hash = VALUES(password_hash),
           role = VALUES(role),
           status = "active"',
        [
            'name' => trim((string) ($account['name'] ?? 'ClicKet Staff')),
            'email' => $email,
            'password_hash' => (string) ($account['password'] ?? $account['password_hash'] ?? password_hash(bin2hex(random_bytes(12)), PASSWORD_DEFAULT)),
            'role' => $role,
            'created_at' => clicketDbDateTime((string) ($account['created_at'] ?? 'now')),
        ]
    );

    $staffId = clicketDbStaffIdByEmail($email);
    if (!$staffId || $role === 'admin') {
        return $staffId;
    }

    $venues = is_array($account['venues'] ?? null) ? $account['venues'] : [];
    foreach ($venues as $venueName) {
        $canonical = function_exists('clicketCatalogCanonicalVenueName')
            ? clicketCatalogCanonicalVenueName((string) $venueName)
            : (string) $venueName;
        $venue = clicketDbFetch(
            'SELECT id FROM venues WHERE name = :name OR slug = :slug LIMIT 1',
            ['name' => $canonical, 'slug' => clicketDbSlug($canonical)]
        );
        if (!$venue) {
            continue;
        }

        clicketDbExecute(
            'INSERT IGNORE INTO staff_venue_assignments
               (staff_id, venue_id, create_events, archive_events, manage_tiers, manage_seats, review_payments, print_tickets)
             VALUES
               (:staff_id, :venue_id, 1, 1, 1, 1, 1, 1)',
            ['staff_id' => $staffId, 'venue_id' => (int) $venue['id']]
        );
    }

    return $staffId;
}

$legacyUsers = clicketMigrationReadJson(__DIR__ . '/storage/users.json');
$legacyStaff = clicketMigrationReadJson(__DIR__ . '/storage/staff.json');
$legacyOrders = clicketMigrationReadJson(__DIR__ . '/storage/orders.json');
$legacyFavorites = clicketMigrationReadJson(__DIR__ . '/storage/favorites.json');
$legacyReservations = clicketMigrationReadJson(__DIR__ . '/storage/reservations.json');

$legacyUserMap = [];
$counts = [
    'customers' => 0,
    'staff' => 0,
    'orders' => 0,
    'favorites' => 0,
    'reservations_skipped' => count($legacyReservations),
];

foreach ($legacyUsers as $account) {
    $role = (string) ($account['role'] ?? 'customer');
    if (in_array($role, ['admin', 'organizer'], true)) {
        $staffId = clicketMigrationImportStaff($account);
        if ($staffId) {
            $counts['staff']++;
        }
        continue;
    }

    $userId = clicketMigrationImportCustomer($account);
    if ($userId) {
        $counts['customers']++;
        $legacyId = (string) ($account['id'] ?? '');
        if ($legacyId !== '') {
            $legacyUserMap[$legacyId] = $userId;
        }
    }
}

foreach ($legacyStaff as $account) {
    if (clicketMigrationImportStaff($account)) {
        $counts['staff']++;
    }
}

foreach ($legacyOrders as $order) {
    $legacyUserId = (string) ($order['user_id'] ?? '');
    if ($legacyUserId !== '' && isset($legacyUserMap[$legacyUserId])) {
        $order['user_id'] = (string) $legacyUserMap[$legacyUserId];
    } elseif (!empty($order['buyer_email'])) {
        $dbUserId = clicketDbUserIdByEmail((string) $order['buyer_email']);
        if ($dbUserId) {
            $order['user_id'] = (string) $dbUserId;
        }
    }

    if (clicketSaveOrder($order, true)) {
        $counts['orders']++;
    }
}

foreach ($legacyFavorites as $favorite) {
    $legacyUserId = (string) ($favorite['user_id'] ?? '');
    $dbUserId = $legacyUserMap[$legacyUserId] ?? clicketDbUserIdFromSession($legacyUserId);
    $eventId = (string) ($favorite['event_id'] ?? $favorite['event'] ?? '');
    if ($dbUserId && $eventId !== '') {
        clicketSetFavorite((string) $dbUserId, $eventId, true);
        $counts['favorites']++;
    }
}

echo "CLICKET JSON to MySQL migration complete\n";
foreach ($counts as $label => $count) {
    echo $label . ': ' . $count . "\n";
}
