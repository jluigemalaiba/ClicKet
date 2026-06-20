<?php

declare(strict_types=1);

if (!defined('CLICKET_DB_HOST')) {
    define('CLICKET_DB_HOST', getenv('CLICKET_DB_HOST') ?: '127.0.0.1');
}
if (!defined('CLICKET_DB_NAME')) {
    define('CLICKET_DB_NAME', getenv('CLICKET_DB_NAME') ?: 'clicket');
}
if (!defined('CLICKET_DB_USER')) {
    define('CLICKET_DB_USER', getenv('CLICKET_DB_USER') ?: 'root');
}
if (!defined('CLICKET_DB_PASS')) {
    define('CLICKET_DB_PASS', getenv('CLICKET_DB_PASS') ?: '');
}
if (!defined('CLICKET_DB_CHARSET')) {
    define('CLICKET_DB_CHARSET', 'utf8mb4');
}

function clicketDb(): PDO {
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        CLICKET_DB_HOST,
        CLICKET_DB_NAME,
        CLICKET_DB_CHARSET
    );

    $pdo = new PDO($dsn, CLICKET_DB_USER, CLICKET_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function clicketDbExecute(string $sql, array $params = []): PDOStatement {
    $statement = clicketDb()->prepare($sql);
    $statement->execute($params);

    return $statement;
}

function clicketDbFetch(string $sql, array $params = []): ?array {
    $row = clicketDbExecute($sql, $params)->fetch();

    return is_array($row) ? $row : null;
}

function clicketDbFetchAll(string $sql, array $params = []): array {
    return clicketDbExecute($sql, $params)->fetchAll();
}

function clicketDbScalar(string $sql, array $params = []): mixed {
    $value = clicketDbExecute($sql, $params)->fetchColumn();

    return $value === false ? null : $value;
}

function clicketDbSlug(string $value): string {
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $value) ?? ''));
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'item';
}

function clicketDbMoneyValue(mixed $value): float {
    if (is_numeric($value)) {
        return (float) $value;
    }

    return (float) preg_replace('/[^0-9.]/', '', (string) $value);
}

function clicketDbDateTime(?string $value = null): string {
    try {
        return (new DateTimeImmutable($value ?: 'now'))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d H:i:s');
    } catch (Throwable) {
        return (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    }
}

function clicketDbTimestamp(int $timestamp): string {
    return (new DateTimeImmutable('@' . $timestamp))
        ->setTimezone(new DateTimeZone('UTC'))
        ->format('Y-m-d H:i:s');
}

function clicketDbDisplayDateTime(?string $value): string {
    if (!$value) {
        return '';
    }

    try {
        return (new DateTimeImmutable($value, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Asia/Manila'))
            ->format('c');
    } catch (Throwable) {
        return (string) $value;
    }
}

function clicketDbDisplayDate(?string $value): string {
    if (!$value) {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('l, F j, Y');
    } catch (Throwable) {
        return (string) $value;
    }
}

function clicketDbDisplayTime(?string $value): string {
    if (!$value) {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('g:i A');
    } catch (Throwable) {
        return (string) $value;
    }
}

function clicketDbSqlDateFromLabel(string $label): ?string {
    try {
        return (new DateTimeImmutable($label))->format('Y-m-d');
    } catch (Throwable) {
        return null;
    }
}

function clicketDbSqlTimeFromLabel(string $label): ?string {
    try {
        return (new DateTimeImmutable($label))->format('H:i:s');
    } catch (Throwable) {
        return null;
    }
}

function clicketDbNormalizePaymentStatus(string $status): string {
    $normalized = strtolower(trim($status));

    return match ($normalized) {
        'paid', 'confirmed', 'complete', 'completed', 'approved' => 'approved',
        'failed', 'rejected', 'payment rejected', 'refunded', 'refund', 'cancelled', 'canceled' => 'rejected',
        'processing', 'review', 'under review', 'under_review' => 'under_review',
        default => 'pending',
    };
}

function clicketDbNormalizeOrderStatus(string $status): string {
    $normalized = strtolower(trim($status));

    return match ($normalized) {
        'confirmed', 'complete', 'completed' => 'completed',
        'approved', 'paid' => 'approved',
        'failed', 'payment rejected', 'rejected', 'cancelled', 'canceled', 'refunded', 'void' => 'rejected',
        'archived' => 'archived',
        default => 'pending',
    };
}

function clicketDbDisplayPaymentStatus(string $status): string {
    return match ($status) {
        'approved' => 'Paid',
        'under_review' => 'Pending',
        'rejected' => 'Failed',
        default => 'Pending',
    };
}

function clicketDbDisplayOrderStatus(string $status): string {
    return match ($status) {
        'completed' => 'Confirmed',
        'approved' => 'Confirmed',
        'rejected' => 'Payment Rejected',
        'archived' => 'Archived',
        default => 'Pending',
    };
}

function clicketDbNormalizeTicketStatus(string $status): string {
    $normalized = strtolower(trim($status));

    return match ($normalized) {
        'valid', 'active' => 'active',
        'used' => 'used',
        'cancelled', 'canceled' => 'cancelled',
        'void', 'invalid' => 'void',
        default => 'issued',
    };
}

function clicketDbDisplayTicketStatus(string $status): string {
    return match ($status) {
        'active', 'issued' => 'Valid',
        'used' => 'Used',
        'cancelled' => 'Cancelled',
        'void' => 'Invalid',
        default => 'Pending',
    };
}

function clicketDbUserIdFromSession(string $userId): ?int {
    if ($userId !== '' && ctype_digit($userId)) {
        return (int) $userId;
    }

    return null;
}

function clicketDbUserIdByEmail(string $email): ?int {
    $row = clicketDbFetch(
        'SELECT id FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1',
        ['email' => trim($email)]
    );

    return $row ? (int) $row['id'] : null;
}

function clicketDbStaffIdByEmail(string $email): ?int {
    $row = clicketDbFetch(
        'SELECT id FROM staff_accounts WHERE LOWER(email) = LOWER(:email) LIMIT 1',
        ['email' => trim($email)]
    );

    return $row ? (int) $row['id'] : null;
}

function clicketDbStaffIdBySession(array $staff): ?int {
    $id = (string) ($staff['session_user_id'] ?? $staff['id'] ?? '');
    if ($id !== '' && ctype_digit($id)) {
        return (int) $id;
    }

    $email = (string) ($staff['email'] ?? '');
    return $email !== '' ? clicketDbStaffIdByEmail($email) : null;
}

function clicketDbEventByKey(string $eventKey): ?array {
    return clicketDbFetch(
        'SELECT e.*, v.name AS venue_name, vl.layout_key
         FROM events e
         INNER JOIN venues v ON v.id = e.venue_id
         INNER JOIN venue_layouts vl ON vl.id = e.venue_layout_id
         WHERE e.event_key = :event_key
         LIMIT 1',
        ['event_key' => $eventKey]
    );
}

function clicketDbPerformanceByIndex(string $eventKey, int $performanceIndex): ?array {
    $event = clicketDbEventByKey($eventKey);
    if (!$event) {
        return null;
    }

    $rows = clicketDbFetchAll(
        'SELECT * FROM event_performances WHERE event_id = :event_id ORDER BY performance_date, performance_time, id',
        ['event_id' => (int) $event['id']]
    );

    if (!$rows) {
        return null;
    }

    return $rows[max(0, min($performanceIndex, count($rows) - 1))];
}

function clicketDbPerformanceByLabels(string $eventKey, string $dateLabel, string $timeLabel): ?array {
    $event = clicketDbEventByKey($eventKey);
    $date = clicketDbSqlDateFromLabel($dateLabel);
    $time = clicketDbSqlTimeFromLabel($timeLabel);

    if (!$event || !$date || !$time) {
        return null;
    }

    return clicketDbFetch(
        'SELECT * FROM event_performances
         WHERE event_id = :event_id AND performance_date = :performance_date AND performance_time = :performance_time
         LIMIT 1',
        [
            'event_id' => (int) $event['id'],
            'performance_date' => $date,
            'performance_time' => $time,
        ]
    );
}

function clicketDbEnsurePerformance(string $eventKey, string $dateLabel, string $timeLabel): ?array {
    $existing = clicketDbPerformanceByLabels($eventKey, $dateLabel, $timeLabel);
    if ($existing) {
        return $existing;
    }

    $event = clicketDbEventByKey($eventKey);
    $date = clicketDbSqlDateFromLabel($dateLabel);
    $time = clicketDbSqlTimeFromLabel($timeLabel);
    if (!$event || !$date || !$time) {
        return null;
    }

    clicketDbExecute(
        'INSERT INTO event_performances (event_id, performance_date, performance_time, status)
         VALUES (:event_id, :performance_date, :performance_time, "scheduled")',
        [
            'event_id' => (int) $event['id'],
            'performance_date' => $date,
            'performance_time' => $time,
        ]
    );

    return clicketDbFetch(
        'SELECT * FROM event_performances WHERE id = :id LIMIT 1',
        ['id' => (int) clicketDb()->lastInsertId()]
    );
}

function clicketDbFormatPrice(float|int|string $value): string {
    $amount = clicketDbMoneyValue($value);

    return 'PHP ' . number_format((int) round($amount));
}

function clicketDbEnsureTier(int $venueLayoutId, string $category): int {
    $name = trim($category) !== '' ? trim($category) : 'General Admission';
    $slug = clicketDbSlug($name);
    $existing = clicketDbFetch(
        'SELECT id FROM venue_tiers WHERE venue_layout_id = :layout_id AND slug = :slug LIMIT 1',
        ['layout_id' => $venueLayoutId, 'slug' => $slug]
    );

    if ($existing) {
        return (int) $existing['id'];
    }

    clicketDbExecute(
        'INSERT INTO venue_tiers (venue_layout_id, name, slug, color, sort_order, default_status)
         VALUES (:layout_id, :name, :slug, :color, 0, "active")',
        [
            'layout_id' => $venueLayoutId,
            'name' => $name,
            'slug' => $slug,
            'color' => '#e8162b',
        ]
    );

    return (int) clicketDb()->lastInsertId();
}

function clicketDbEnsureSection(int $venueLayoutId, string $sectionLabel, string $category): int {
    $label = trim($sectionLabel) !== '' ? trim($sectionLabel) : 'General Admission';
    $existing = clicketDbFetch(
        'SELECT id FROM venue_sections WHERE venue_layout_id = :layout_id AND label = :label LIMIT 1',
        ['layout_id' => $venueLayoutId, 'label' => $label]
    );

    if ($existing) {
        return (int) $existing['id'];
    }

    $tierId = clicketDbEnsureTier($venueLayoutId, $category);
    $polygonId = clicketDbSlug($label);

    clicketDbExecute(
        'INSERT INTO venue_sections
           (venue_layout_id, tier_id, svg_polygon_id, section_number, label, category_key, capacity, map_color, zone, is_seating_section)
         VALUES
           (:layout_id, :tier_id, :polygon_id, NULL, :label, :category_key, 0, NULL, NULL, 1)',
        [
            'layout_id' => $venueLayoutId,
            'tier_id' => $tierId,
            'polygon_id' => $polygonId,
            'label' => $label,
            'category_key' => clicketDbSlug($category),
        ]
    );

    return (int) clicketDb()->lastInsertId();
}

function clicketDbEnsureSeat(string $eventKey, string $seatCode, array $seat = []): int {
    $event = clicketDbEventByKey($eventKey);
    if (!$event) {
        throw new RuntimeException('Cannot create seat record for unknown event: ' . $eventKey);
    }

    $sectionLabel = (string) ($seat['section'] ?? '');
    if ($sectionLabel === '') {
        $sectionLabel = preg_replace('/-[A-Z]{1,2}-\d{1,3}$/', '', $seatCode) ?: 'General Admission';
    }

    $venueSectionId = clicketDbEnsureSection(
        (int) $event['venue_layout_id'],
        $sectionLabel,
        (string) ($seat['category'] ?? 'General Admission')
    );

    $existing = clicketDbFetch(
        'SELECT id FROM seats WHERE venue_section_id = :section_id AND seat_code = :seat_code LIMIT 1',
        ['section_id' => $venueSectionId, 'seat_code' => $seatCode]
    );

    if ($existing) {
        return (int) $existing['id'];
    }

    clicketDbExecute(
        'INSERT INTO seats (venue_section_id, seat_code, row_label, seat_number, x, y, status)
         VALUES (:section_id, :seat_code, :row_label, :seat_number, NULL, NULL, "available")',
        [
            'section_id' => $venueSectionId,
            'seat_code' => $seatCode,
            'row_label' => (string) ($seat['row'] ?? $seat['row_label'] ?? ''),
            'seat_number' => (string) ($seat['number'] ?? $seat['seat_number'] ?? ''),
        ]
    );

    return (int) clicketDb()->lastInsertId();
}
