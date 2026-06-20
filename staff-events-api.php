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
    if (!$staffId) {
        return false;
    }

    return clicketDbFetch(
        'SELECT sva.id
         FROM staff_venue_assignments sva
         WHERE sva.staff_id = :staff_id AND sva.venue_id = :venue_id
         LIMIT 1',
        ['staff_id' => $staffId, 'venue_id' => (int) $event['venue_id']]
    ) !== null;
}

function clicketEnsureEventArchiveSchema(): void {
    static $ready = false;
    if ($ready) {
        return;
    }

    clicketDbExecute('ALTER TABLE events ADD COLUMN IF NOT EXISTS archive_requested_by BIGINT UNSIGNED NULL AFTER created_by_staff_id');
    clicketDbExecute('ALTER TABLE events ADD COLUMN IF NOT EXISTS archive_approved_by BIGINT UNSIGNED NULL AFTER archive_requested_by');
    clicketDbExecute('ALTER TABLE events ADD COLUMN IF NOT EXISTS archived_at TIMESTAMP NULL DEFAULT NULL AFTER archive_approved_by');
    clicketDbExecute('ALTER TABLE events ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER title');
    clicketDbExecute('ALTER TABLE events ADD COLUMN IF NOT EXISTS cast_performers TEXT NULL AFTER description');
    clicketDbExecute('ALTER TABLE events ADD COLUMN IF NOT EXISTS cast_logo_url VARCHAR(500) NULL AFTER cast_performers');
    clicketDbExecute('ALTER TABLE events ADD COLUMN IF NOT EXISTS running_minutes SMALLINT UNSIGNED NULL AFTER base_price');
    clicketDbExecute('ALTER TABLE events ADD COLUMN IF NOT EXISTS age_range VARCHAR(80) NULL AFTER running_minutes');
    clicketDbExecute('ALTER TABLE events ADD COLUMN IF NOT EXISTS doors_open_minutes SMALLINT UNSIGNED NULL AFTER age_range');
    $ready = true;
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
    if (trim($value) === '') {
        return null;
    }

    try {
        return (new DateTimeImmutable($value))->format('Y-m-d');
    } catch (Throwable) {
        return null;
    }
}

function clicketStaffPostTime(string $value): ?string {
    if (trim($value) === '') {
        return null;
    }

    try {
        return (new DateTimeImmutable($value))->format('H:i:s');
    } catch (Throwable) {
        return null;
    }
}

function clicketStaffPostArray(string $key): array {
    $value = $_POST[$key] ?? [];
    if (!is_array($value)) {
        $value = [$value];
    }

    return array_values(array_map(static fn ($item): string => trim((string) $item), $value));
}

function clicketStaffPostedPerformances(): array {
    $dates = clicketStaffPostArray('performance_date');
    $times = clicketStaffPostArray('performance_time');
    $performances = [];
    $seen = [];
    $count = max(count($dates), count($times));

    for ($index = 0; $index < $count; $index++) {
        $date = clicketStaffPostDate($dates[$index] ?? '');
        $time = clicketStaffPostTime($times[$index] ?? '');
        if (!$date || !$time) {
            continue;
        }

        $key = $date . ' ' . $time;
        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $performances[] = ['date' => $date, 'time' => $time];
    }

    return $performances;
}

function clicketStaffSavePerformances(int $eventId, array $performances): void {
    if (!$performances) {
        throw new RuntimeException('At least one performance schedule is required.');
    }

    $existing = clicketDbFetchAll(
        'SELECT id, performance_date, performance_time
         FROM event_performances
         WHERE event_id = :event_id
         ORDER BY performance_date, performance_time, id',
        ['event_id' => $eventId]
    );
    $unusedExistingIds = [];
    $existingByDateTime = [];
    foreach ($existing as $row) {
        $id = (int) $row['id'];
        $unusedExistingIds[$id] = $id;
        $existingByDateTime[(string) $row['performance_date'] . ' ' . (string) $row['performance_time']] = $id;
    }

    foreach ($performances as $performance) {
        $key = $performance['date'] . ' ' . $performance['time'];
        $existingId = (int) ($existingByDateTime[$key] ?? 0);
        if ($existingId > 0) {
            clicketDbExecute(
                'UPDATE event_performances
                 SET status = "scheduled"
                 WHERE id = :id',
                ['id' => $existingId]
            );
            unset($unusedExistingIds[$existingId]);
            continue;
        }

        clicketDbExecute(
            'INSERT IGNORE INTO event_performances (event_id, performance_date, performance_time, status)
             VALUES (:event_id, :performance_date, :performance_time, "scheduled")',
            [
                'event_id' => $eventId,
                'performance_date' => $performance['date'],
                'performance_time' => $performance['time'],
            ]
        );
    }

    foreach ($unusedExistingIds as $performanceId) {
        if ($performanceId <= 0) {
            continue;
        }

        $hasOrders = (int) clicketDbScalar(
            'SELECT COUNT(*) FROM orders WHERE performance_id = :performance_id',
            ['performance_id' => $performanceId]
        ) > 0;

        if (!$hasOrders) {
            clicketDbExecute(
                'UPDATE event_performances SET status = "cancelled" WHERE id = :performance_id',
                ['performance_id' => $performanceId]
            );
        }
    }
}

function clicketStaffUploadedMediaUrl(string $field, string $prefix): ?string {
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
        return null;
    }

    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
        throw new RuntimeException('Upload failed.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP, and GIF images are allowed.');
    }

    $directory = __DIR__ . '/storage/event-media';
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Event media directory is not writable.');
    }

    $filename = $prefix . '-' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime];
    $target = $directory . '/' . $filename;
    if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
        throw new RuntimeException('Upload could not be saved.');
    }

    return 'storage/event-media/' . $filename;
}

function clicketStaffMediaValue(string $urlField, string $fileField, string $prefix, ?string $fallback = null): ?string {
    $uploaded = clicketStaffUploadedMediaUrl($fileField, $prefix);
    if ($uploaded !== null) {
        return $uploaded;
    }

    $url = trim((string) ($_POST[$urlField] ?? ''));
    if ($url !== '') {
        return $url;
    }

    return $fallback ?: null;
}

function clicketStaffSaveTierSettings(int $eventId, int $venueLayoutId, int $staffId): void {
    $tierIds = array_values(array_map('intval', clicketStaffPostArray('tier_id')));
    if (!$tierIds) {
        clicketInventorySyncAll($eventId, null);
        return;
    }

    clicketInventorySyncAll($eventId, null);

    $names = clicketStaffPostArray('tier_name');
    $colors = clicketStaffPostArray('tier_color');
    $prices = clicketStaffPostArray('tier_price');

    foreach ($tierIds as $index => $tierId) {
        if ($tierId <= 0) {
            continue;
        }

        $tier = clicketDbFetch(
            'SELECT vt.id, vt.name, vt.color,
                    COALESCE(SUM(CASE WHEN s.id IS NULL THEN vs.capacity ELSE 1 END), 0) AS seat_capacity
             FROM venue_tiers vt
             LEFT JOIN venue_sections vs ON vs.tier_id = vt.id
             LEFT JOIN seats s ON s.venue_section_id = vs.id
             WHERE vt.id = :tier_id AND vt.venue_layout_id = :venue_layout_id
             GROUP BY vt.id, vt.name, vt.color
             LIMIT 1',
            ['tier_id' => $tierId, 'venue_layout_id' => $venueLayoutId]
        );
        if (!$tier) {
            continue;
        }

        $name = trim($names[$index] ?? '');
        $color = trim($colors[$index] ?? '');
        $hasValidColor = preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
        if ($name !== '' || $hasValidColor) {
            clicketDbExecute(
                'UPDATE venue_tiers
                 SET name = CASE WHEN :name_check <> "" THEN :name_value ELSE name END,
                     color = CASE WHEN :color_check REGEXP "^#[0-9A-Fa-f]{6}$" THEN :color_value ELSE color END
                 WHERE id = :tier_id AND venue_layout_id = :venue_layout_id',
                [
                    'name_check' => $name,
                    'name_value' => $name,
                    'color_check' => $color,
                    'color_value' => $color,
                    'tier_id' => $tierId,
                    'venue_layout_id' => $venueLayoutId,
                ]
            );
        }
        if ($hasValidColor) {
            clicketDbExecute(
                'UPDATE venue_sections
                 SET map_color = :map_color
                 WHERE tier_id = :tier_id AND venue_layout_id = :venue_layout_id',
                [
                    'map_color' => $color,
                    'tier_id' => $tierId,
                    'venue_layout_id' => $venueLayoutId,
                ]
            );
        }

        $price = max(0, (int) clicketDbMoneyValue($prices[$index] ?? 0));
        $capacity = (int) ($tier['seat_capacity'] ?? 0);
        clicketDbExecute(
            'INSERT INTO event_tier_settings
               (event_id, tier_id, price, capacity, available_count, status, updated_by_staff_id)
             VALUES
               (:event_id, :tier_id, :price, :capacity_value, :available_value, "active", :staff_id)
             ON DUPLICATE KEY UPDATE
               price = VALUES(price),
               capacity = VALUES(capacity),
               available_count = GREATEST(0, VALUES(capacity) - sold_count - held_count),
               status = "active",
               updated_by_staff_id = VALUES(updated_by_staff_id)',
            [
                'event_id' => $eventId,
                'tier_id' => $tierId,
                'price' => $price,
                'capacity_value' => $capacity,
                'available_value' => $capacity,
                'staff_id' => $staffId,
            ]
        );
    }
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

    clicketEnsureEventArchiveSchema();
    if ((string) ($event['status'] ?? '') === 'archived') {
        clicketStaffEventsFail($staff, 'This event is already archived.');
    }

    $pdo = clicketDb();
    $pdo->beginTransaction();
    try {
        clicketDbExecute(
            'UPDATE events
             SET status = "archived",
                 archive_requested_by = :requested_by_staff_id,
                 archive_approved_by = :approved_by_staff_id,
                 archived_at = UTC_TIMESTAMP()
             WHERE id = :event_id',
            [
                'requested_by_staff_id' => $staffId,
                'approved_by_staff_id' => $staffId,
                'event_id' => (int) $event['id'],
            ]
        );
        clicketDbExecute(
            'INSERT INTO archive_records (entity_type, entity_id, archived_by_staff_id, approved_by_staff_id, reason)
             VALUES ("event", :event_id, :archived_by_staff_id, :approved_by_staff_id, :reason)',
            [
                'event_id' => (int) $event['id'],
                'archived_by_staff_id' => $staffId,
                'approved_by_staff_id' => $staffId,
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
$status = clicketStaffNormalizeEventStatus((string) ($_POST['status'] ?? 'published'));
$venueLayoutId = (int) ($_POST['venue_layout_id'] ?? 0);
$layout = $venueLayoutId > 0 ? clicketStaffEventLayout($venueLayoutId) : null;
$performances = clicketStaffPostedPerformances();
$description = trim((string) ($_POST['description'] ?? ''));
$castPerformers = trim((string) ($_POST['cast_performers'] ?? ''));
$type = trim((string) ($_POST['type'] ?? ''));
$ownerName = trim((string) ($_POST['owner_name'] ?? ''));
$runningMinutes = (int) ($_POST['running_minutes'] ?? 0);
$ageRange = trim((string) ($_POST['age_range'] ?? ''));
$doorsOpenMinutes = (int) ($_POST['doors_open_minutes'] ?? 0);

if (
    $title === ''
    || $description === ''
    || $castPerformers === ''
    || $type === ''
    || $ownerName === ''
    || $category === ''
    || $status === ''
    || !$layout
    || !$performances
    || $runningMinutes <= 0
    || $ageRange === ''
    || $doorsOpenMinutes <= 0
) {
    clicketStaffEventsFail($staff, 'Complete every event detail, venue, category, status, schedule, and audience rule before saving.');
}

if (($staff['role'] ?? '') !== 'admin' && $status === 'archived') {
    clicketStaffEventsFail($staff, 'Only admins can archive events.');
}

if (!clicketStaffCanUseEventLayout($staff, $venueLayoutId)) {
    clicketStaffEventsFail($staff, 'That venue layout is outside your assigned scope.');
}

$ownerColumns = clicketStaffOwnerColumns($category, $ownerName);
clicketEnsureEventArchiveSchema();

$tierIds = array_values(array_map('intval', clicketStaffPostArray('tier_id')));
$tierNames = clicketStaffPostArray('tier_name');
$tierColors = clicketStaffPostArray('tier_color');
$tierPrices = clicketStaffPostArray('tier_price');
if (!$tierIds) {
    clicketStaffEventsFail($staff, 'At least one ticket tier is required.');
}
foreach ($tierIds as $index => $tierId) {
    $tierName = trim($tierNames[$index] ?? '');
    $tierColor = trim($tierColors[$index] ?? '');
    $tierPriceRaw = trim((string) ($tierPrices[$index] ?? ''));
    $tierPrice = clicketDbMoneyValue($tierPriceRaw);
    if ($tierId <= 0 || $tierName === '' || preg_match('/^#[0-9a-fA-F]{6}$/', $tierColor) !== 1 || $tierPrice <= 0) {
        clicketStaffEventsFail($staff, 'Complete every ticket tier title, color, and price before saving.');
    }
    if (preg_match('/^\d+$/', $tierPriceRaw) !== 1) {
        clicketStaffEventsFail($staff, 'Ticket tier prices must be whole numbers only.');
    }
}

try {
    $existingMedia = null;
    if ($action === 'update') {
        $existingKey = trim((string) ($_POST['event_key'] ?? ''));
        $existingMedia = $existingKey !== '' ? clicketStaffEventByKey($existingKey) : null;
    }

    $castLogoUrl = clicketStaffMediaValue('cast_logo_url', 'cast_logo_file', 'cast-logo', $existingMedia['cast_logo_url'] ?? null);
    $posterUrl = clicketStaffMediaValue('poster_url', 'poster_file', 'poster', $existingMedia['poster_url'] ?? null);
    $bannerUrl = clicketStaffMediaValue('banner_url', 'banner_file', 'banner', $existingMedia['banner_url'] ?? null);
} catch (Throwable $error) {
    clicketStaffEventsFail($staff, $error->getMessage());
}

if (!$castLogoUrl || !$posterUrl || !$bannerUrl) {
    clicketStaffEventsFail($staff, 'Cast logo, poster, and banner are required.');
}

$params = [
    'title' => $title,
    'description' => $description,
    'cast_performers' => $castPerformers,
    'cast_logo_url' => $castLogoUrl,
    'category' => $category,
    'type' => $type,
    'artist' => $ownerColumns['artist'],
    'company' => $ownerColumns['company'],
    'league' => $ownerColumns['league'],
    'venue_id' => (int) $layout['venue_id'],
    'venue_layout_id' => $venueLayoutId,
    'poster_url' => $posterUrl,
    'banner_url' => $bannerUrl,
    'base_price' => max(0, clicketDbMoneyValue($_POST['base_price'] ?? 0)),
    'running_minutes' => $runningMinutes,
    'age_range' => $ageRange,
    'doors_open_minutes' => $doorsOpenMinutes,
    'status' => $status,
];

if ($action === 'create') {
    $eventKey = clicketStaffNextEventKey($category);
    $pdo = clicketDb();
    $pdo->beginTransaction();
    try {
        clicketDbExecute(
            'INSERT INTO events
               (event_key, title, description, cast_performers, cast_logo_url, category, type, artist, company, league, venue_id, venue_layout_id,
                poster_url, banner_url, base_price, running_minutes, age_range, doors_open_minutes, rating, status, created_by_staff_id)
             VALUES
               (:event_key, :title, :description, :cast_performers, :cast_logo_url, :category, :type, :artist, :company, :league, :venue_id, :venue_layout_id,
                :poster_url, :banner_url, :base_price, :running_minutes, :age_range, :doors_open_minutes, NULL, :status, :staff_id)',
            $params + ['event_key' => $eventKey, 'staff_id' => $staffId]
        );
        $newEventId = (int) $pdo->lastInsertId();
        clicketStaffSavePerformances($newEventId, $performances);
        clicketStaffSaveTierSettings($newEventId, $venueLayoutId, $staffId);
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
             description = :description,
             cast_performers = :cast_performers,
             cast_logo_url = :cast_logo_url,
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
             running_minutes = :running_minutes,
             age_range = :age_range,
             doors_open_minutes = :doors_open_minutes,
             status = :status,
             archive_requested_by = COALESCE(:archive_requested_by, archive_requested_by),
             archive_approved_by = COALESCE(:archive_approved_by, archive_approved_by),
             archived_at = CASE WHEN :status_for_archive = "archived" THEN COALESCE(archived_at, UTC_TIMESTAMP()) ELSE archived_at END
         WHERE id = :event_id',
        $params + [
            'event_id' => (int) $event['id'],
            'archive_requested_by' => $archiveFields['archive_requested_by'] ?? null,
            'archive_approved_by' => $archiveFields['archive_approved_by'] ?? null,
            'status_for_archive' => $status,
        ]
    );
    clicketStaffSavePerformances((int) $event['id'], $performances);
    clicketStaffSaveTierSettings((int) $event['id'], $venueLayoutId, $staffId);
    $pdo->commit();
} catch (Throwable) {
    $pdo->rollBack();
    clicketStaffEventsFail($staff, 'The event could not be updated.');
}

clicketStaffEventsDone($staff, 'Event updated.');
