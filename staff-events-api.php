<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';

clicketRequireStaff();

$staff = currentStaff();
if (!$staff) {
    setFlashMessage('error', 'Please sign in with an admin or organizer account.');
    header('Location: auth.php?mode=admin');
    exit;
}

function clicketStaffEventsRedirect(array $staff): string {
    return ($staff['role'] ?? '') === 'organizer'
        ? 'organizer/events.php'
        : 'admin-panel.php#events:listing';
}

function clicketStaffEventsFail(array $staff, string $message): never {
    setFlashMessage('error', $message);
    header('Location: ' . clicketStaffEventsRedirect($staff));
    exit;
}

function clicketStaffEventsDone(array $staff, string $message): never {
    setFlashMessage('success', $message);
    header('Location: ' . clicketStaffEventsRedirect($staff));
    exit;
}

function clicketStaffNormalizeEventCategory(string $category): string {
    $category = strtolower(trim($category));

    return in_array($category, ['concert', 'sports', 'theater'], true) ? $category : '';
}

function clicketStaffNormalizeEventStatus(string $status): string {
    $status = strtolower(trim($status));

    return in_array($status, ['draft', 'published', 'paused', 'archived'], true) ? $status : '';
}

function clicketStaffEventKeyPrefix(string $category): string {
    return match ($category) {
        'concert' => 'concerts',
        'sports' => 'sports',
        'theater' => 'theater',
        default => 'events',
    };
}

function clicketStaffNextEventKey(string $category): string {
    $prefix = clicketStaffEventKeyPrefix($category);
    $next = (int) clicketDbScalar(
        'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(event_key, "-", -1) AS UNSIGNED)), 0) + 1
         FROM events
         WHERE event_key LIKE :prefix',
        ['prefix' => $prefix . '-%']
    );

    return $prefix . '-' . max(1, $next);
}

function clicketStaffEventLayout(int $venueLayoutId): ?array {
    return clicketDbFetch(
        'SELECT vl.*, v.id AS venue_id, v.name AS venue_name
         FROM venue_layouts vl
         INNER JOIN venues v ON v.id = vl.venue_id
         WHERE vl.id = :layout_id AND vl.status = "active" AND v.status = "active"
         LIMIT 1',
        ['layout_id' => $venueLayoutId]
    );
}

function clicketStaffCanUseEventLayout(array $staff, int $venueLayoutId): bool {
    if (($staff['role'] ?? '') === 'admin') {
        return true;
    }

    $staffId = clicketDbStaffIdBySession($staff);
    if (!$staffId) {
        return false;
    }

    $layout = clicketStaffEventLayout($venueLayoutId);
    if (!$layout) {
        return false;
    }

    $assignmentCount = (int) clicketDbScalar(
        'SELECT COUNT(*) FROM staff_venue_assignments WHERE staff_id = :staff_id',
        ['staff_id' => $staffId]
    );
    if ($assignmentCount === 0) {
        return true;
    }

    return clicketDbFetch(
        'SELECT sva.id
         FROM staff_venue_assignments sva
         WHERE sva.staff_id = :staff_id AND sva.venue_id = :venue_id
         LIMIT 1',
        ['staff_id' => $staffId, 'venue_id' => (int) $layout['venue_id']]
    ) !== null;
}

function clicketStaffEventByKey(string $eventKey): ?array {
    return clicketDbFetch(
        'SELECT * FROM events WHERE event_key = :event_key LIMIT 1',
        ['event_key' => $eventKey]
    );
}

function clicketStaffCanMutateEvent(array $staff, array $event): bool {
    if (($staff['role'] ?? '') === 'admin') {
        return true;
    }

    $staffId = clicketDbStaffIdBySession($staff);

    return $staffId !== null && (int) $event['created_by_staff_id'] === $staffId;
}

function clicketStaffOwnerColumns(string $category, string $ownerName): array {
    return match ($category) {
        'concert' => ['artist' => $ownerName, 'company' => null, 'league' => null],
        'theater' => ['artist' => null, 'company' => $ownerName, 'league' => null],
        'sports' => ['artist' => null, 'company' => null, 'league' => $ownerName],
        default => ['artist' => null, 'company' => null, 'league' => null],
    };
}

function clicketStaffPostDate(string $value): ?string {
    try {
        return (new DateTimeImmutable($value))->format('Y-m-d');
    } catch (Throwable) {
        return null;
    }
}

function clicketStaffPostTime(string $value): ?string {
    try {
        return (new DateTimeImmutable($value))->format('H:i:s');
    } catch (Throwable) {
        return null;
    }
}

function clicketStaffSavePrimaryPerformance(int $eventId, string $date, string $time): void {
    $primary = clicketDbFetch(
        'SELECT id FROM event_performances WHERE event_id = :event_id ORDER BY performance_date, performance_time, id LIMIT 1',
        ['event_id' => $eventId]
    );

    if ($primary) {
        clicketDbExecute(
            'UPDATE event_performances
             SET performance_date = :performance_date, performance_time = :performance_time, status = "scheduled"
             WHERE id = :id',
            [
                'id' => (int) $primary['id'],
                'performance_date' => $date,
                'performance_time' => $time,
            ]
        );

        return;
    }

    clicketDbExecute(
        'INSERT INTO event_performances (event_id, performance_date, performance_time, status)
         VALUES (:event_id, :performance_date, :performance_time, "scheduled")',
        [
            'event_id' => $eventId,
            'performance_date' => $date,
            'performance_time' => $time,
        ]
    );
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    clicketStaffEventsFail($staff, 'Invalid event management request.');
}

$action = strtolower(trim((string) ($_POST['action'] ?? '')));
$staffId = clicketDbStaffIdBySession($staff);
if (!$staffId) {
    clicketStaffEventsFail($staff, 'Your staff session could not be resolved.');
}

if ($action === 'archive') {
    if (($staff['role'] ?? '') !== 'admin') {
        clicketStaffEventsFail($staff, 'Only admins can archive events.');
    }

    $eventKey = trim((string) ($_POST['event_key'] ?? ''));
    $event = $eventKey !== '' ? clicketStaffEventByKey($eventKey) : null;
    if (!$event) {
        clicketStaffEventsFail($staff, 'Event not found.');
    }

    $pdo = clicketDb();
    $pdo->beginTransaction();
    try {
        clicketDbExecute(
            'UPDATE events
             SET status = "archived",
                 archive_requested_by = :staff_id,
                 archive_approved_by = :staff_id,
                 archived_at = UTC_TIMESTAMP()
             WHERE id = :event_id',
            ['staff_id' => $staffId, 'event_id' => (int) $event['id']]
        );
        clicketDbExecute(
            'INSERT INTO archive_records (entity_type, entity_id, archived_by_staff_id, approved_by_staff_id, reason)
             VALUES ("event", :event_id, :staff_id, :staff_id, :reason)',
            [
                'event_id' => (int) $event['id'],
                'staff_id' => $staffId,
                'reason' => 'Archived from Event Management module',
            ]
        );
        $pdo->commit();
    } catch (Throwable) {
        $pdo->rollBack();
        clicketStaffEventsFail($staff, 'The event could not be archived.');
    }

    clicketStaffEventsDone($staff, 'Event archived.');
}

if (!in_array($action, ['create', 'update'], true)) {
    clicketStaffEventsFail($staff, 'Invalid event action.');
}

$title = trim((string) ($_POST['title'] ?? ''));
$category = clicketStaffNormalizeEventCategory((string) ($_POST['category'] ?? ''));
$status = clicketStaffNormalizeEventStatus((string) ($_POST['status'] ?? 'draft'));
$venueLayoutId = (int) ($_POST['venue_layout_id'] ?? 0);
$layout = $venueLayoutId > 0 ? clicketStaffEventLayout($venueLayoutId) : null;
$date = clicketStaffPostDate((string) ($_POST['performance_date'] ?? ''));
$time = clicketStaffPostTime((string) ($_POST['performance_time'] ?? ''));

if ($title === '' || $category === '' || $status === '' || !$layout || !$date || !$time) {
    clicketStaffEventsFail($staff, 'Complete the event title, venue, category, status, and primary performance schedule.');
}

if (($staff['role'] ?? '') !== 'admin' && $status === 'archived') {
    clicketStaffEventsFail($staff, 'Only admins can archive events.');
}

if (!clicketStaffCanUseEventLayout($staff, $venueLayoutId)) {
    clicketStaffEventsFail($staff, 'That venue layout is outside your assigned scope.');
}

$ownerColumns = clicketStaffOwnerColumns($category, trim((string) ($_POST['owner_name'] ?? '')));
$params = [
    'title' => $title,
    'category' => $category,
    'type' => trim((string) ($_POST['type'] ?? '')),
    'artist' => $ownerColumns['artist'],
    'company' => $ownerColumns['company'],
    'league' => $ownerColumns['league'],
    'venue_id' => (int) $layout['venue_id'],
    'venue_layout_id' => $venueLayoutId,
    'poster_url' => trim((string) ($_POST['poster_url'] ?? '')) ?: null,
    'banner_url' => trim((string) ($_POST['banner_url'] ?? '')) ?: null,
    'base_price' => max(0, clicketDbMoneyValue($_POST['base_price'] ?? 0)),
    'status' => $status,
];

if ($action === 'create') {
    $eventKey = clicketStaffNextEventKey($category);
    $pdo = clicketDb();
    $pdo->beginTransaction();
    try {
        clicketDbExecute(
            'INSERT INTO events
               (event_key, title, category, type, artist, company, league, venue_id, venue_layout_id,
                poster_url, banner_url, base_price, rating, status, created_by_staff_id)
             VALUES
               (:event_key, :title, :category, :type, :artist, :company, :league, :venue_id, :venue_layout_id,
                :poster_url, :banner_url, :base_price, NULL, :status, :staff_id)',
            $params + ['event_key' => $eventKey, 'staff_id' => $staffId]
        );
        clicketStaffSavePrimaryPerformance((int) $pdo->lastInsertId(), $date, $time);
        $pdo->commit();
    } catch (Throwable) {
        $pdo->rollBack();
        clicketStaffEventsFail($staff, 'The event could not be created.');
    }

    clicketStaffEventsDone($staff, $status === 'published' ? 'Event published.' : 'Event draft saved.');
}

$eventKey = trim((string) ($_POST['event_key'] ?? ''));
$event = $eventKey !== '' ? clicketStaffEventByKey($eventKey) : null;
if (!$event) {
    clicketStaffEventsFail($staff, 'Event not found.');
}
if (!clicketStaffCanMutateEvent($staff, $event)) {
    clicketStaffEventsFail($staff, 'You can edit only events you own.');
}

$pdo = clicketDb();
$pdo->beginTransaction();
try {
    $archiveFields = [];
    if ($status === 'archived') {
        $archiveFields = [
            'archive_requested_by' => $staffId,
            'archive_approved_by' => $staffId,
        ];
    }

    clicketDbExecute(
        'UPDATE events
         SET title = :title,
             category = :category,
             type = :type,
             artist = :artist,
             company = :company,
             league = :league,
             venue_id = :venue_id,
             venue_layout_id = :venue_layout_id,
             poster_url = :poster_url,
             banner_url = :banner_url,
             base_price = :base_price,
             status = :status,
             archive_requested_by = COALESCE(:archive_requested_by, archive_requested_by),
             archive_approved_by = COALESCE(:archive_approved_by, archive_approved_by),
             archived_at = CASE WHEN :status = "archived" THEN COALESCE(archived_at, UTC_TIMESTAMP()) ELSE archived_at END
         WHERE id = :event_id',
        $params + [
            'event_id' => (int) $event['id'],
            'archive_requested_by' => $archiveFields['archive_requested_by'] ?? null,
            'archive_approved_by' => $archiveFields['archive_approved_by'] ?? null,
        ]
    );
    clicketStaffSavePrimaryPerformance((int) $event['id'], $date, $time);
    $pdo->commit();
} catch (Throwable) {
    $pdo->rollBack();
    clicketStaffEventsFail($staff, 'The event could not be updated.');
}

clicketStaffEventsDone($staff, 'Event updated.');
