<?php

declare(strict_types=1);

require_once __DIR__ . '/log.php';

function clicketVirtualQueueDefaults(): array {
    return [
        'enabled' => false,
        'max_active' => 25,
        'timeout_seconds' => 300,
        'throughput_per_minute' => 12,
        'updated_at' => null,
    ];
}

function clicketVirtualQueueStorageDir(): string {
    $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'virtual-queue';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    return $dir;
}

function clicketVirtualQueueConfigPath(): string {
    return clicketVirtualQueueStorageDir() . DIRECTORY_SEPARATOR . 'config.json';
}

function clicketVirtualQueueSafeKey(string $eventKey): string {
    $safe = strtolower((string) preg_replace('/[^a-z0-9_-]+/i', '-', $eventKey));
    $safe = trim($safe, '-_');

    return $safe !== '' ? $safe : 'event';
}

function clicketVirtualQueueStateKey(string $eventKey, int $performance): string {
    return clicketVirtualQueueSafeKey($eventKey);
}

function clicketVirtualQueueStatePath(string $eventKey, int $performance): string {
    return clicketVirtualQueueStorageDir() . DIRECTORY_SEPARATOR . clicketVirtualQueueStateKey($eventKey, $performance) . '.json';
}

function clicketVirtualQueueLockedJson(string $path, array $default, ?callable $mutator = null): array {
    if ($mutator === null && !is_file($path)) {
        return $default;
    }

    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $handle = @fopen($path, $mutator ? 'c+' : 'r');
    if (!$handle) {
        return $default;
    }

    try {
        flock($handle, $mutator ? LOCK_EX : LOCK_SH);
        rewind($handle);
        $raw = stream_get_contents($handle);
        $data = json_decode((string) $raw, true);
        if (!is_array($data)) {
            $data = $default;
        }

        if ($mutator) {
            $next = $mutator($data);
            $data = is_array($next) ? $next : $data;
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            fflush($handle);
        }

        flock($handle, LOCK_UN);
        fclose($handle);

        return $data;
    } catch (Throwable $error) {
        flock($handle, LOCK_UN);
        fclose($handle);

        return $default;
    }
}

function clicketVirtualQueueConfigStore(?callable $mutator = null): array {
    return clicketVirtualQueueLockedJson(clicketVirtualQueueConfigPath(), [], $mutator);
}

function clicketVirtualQueueConfig(string $eventKey): array {
    $store = clicketVirtualQueueConfigStore();
    $key = clicketVirtualQueueSafeKey($eventKey);
    $config = is_array($store[$key] ?? null) ? $store[$key] : [];

    return array_merge(clicketVirtualQueueDefaults(), $config);
}

function clicketVirtualQueueSaveConfig(string $eventKey, array $input): array {
    $key = clicketVirtualQueueSafeKey($eventKey);
    $nextConfig = [
        'enabled' => !empty($input['enabled']),
        'max_active' => max(1, min(500, (int) ($input['max_active'] ?? 25))),
        'timeout_seconds' => max(60, min(3600, (int) ($input['timeout_seconds'] ?? 300))),
        'throughput_per_minute' => max(1, min(300, (int) ($input['throughput_per_minute'] ?? 12))),
        'updated_at' => gmdate('c'),
    ];

    clicketVirtualQueueConfigStore(static function (array $store) use ($key, $nextConfig): array {
        $store[$key] = $nextConfig;
        ksort($store);

        return $store;
    });

    return array_merge(clicketVirtualQueueDefaults(), $nextConfig);
}

function clicketVirtualQueueIsEnabled(string $eventKey): bool {
    return !empty(clicketVirtualQueueConfig($eventKey)['enabled']);
}

function clicketVirtualQueueDefaultState(string $eventKey, int $performance): array {
    return [
        'event_key' => $eventKey,
        'performance' => 0,
        'queued' => [],
        'active' => [],
        'updated_at' => gmdate('c'),
    ];
}

function clicketVirtualQueueNormalizeEntry(mixed $entry): ?array {
    if (!is_array($entry)) {
        return null;
    }

    $token = (string) ($entry['token'] ?? '');
    if ($token === '') {
        return null;
    }

    return [
        'token' => $token,
        'session_id' => (string) ($entry['session_id'] ?? ''),
        'user_id' => (string) ($entry['user_id'] ?? ''),
        'created_at' => (int) ($entry['created_at'] ?? time()),
        'last_seen' => (int) ($entry['last_seen'] ?? time()),
        'admitted_at' => isset($entry['admitted_at']) ? (int) $entry['admitted_at'] : null,
        'expires_at' => isset($entry['expires_at']) ? (int) $entry['expires_at'] : null,
    ];
}

function clicketVirtualQueueNormalizeState(array $state, string $eventKey, int $performance): array {
    $normalized = clicketVirtualQueueDefaultState($eventKey, $performance);
    $normalized['queued'] = array_values(array_filter(array_map(
        'clicketVirtualQueueNormalizeEntry',
        is_array($state['queued'] ?? null) ? $state['queued'] : []
    )));
    $normalized['active'] = array_values(array_filter(array_map(
        'clicketVirtualQueueNormalizeEntry',
        is_array($state['active'] ?? null) ? $state['active'] : []
    )));
    $normalized['updated_at'] = (string) ($state['updated_at'] ?? gmdate('c'));

    return $normalized;
}

function clicketVirtualQueueCleanupState(array $state, array $config, int $now): array {
    $timeout = max(60, (int) ($config['timeout_seconds'] ?? 300));
    $staleQueueAfter = max(600, $timeout * 2);
    $maxActive = max(1, (int) ($config['max_active'] ?? 25));

    $state['queued'] = array_values(array_filter(
        $state['queued'],
        static fn (array $entry): bool => (int) ($entry['last_seen'] ?? 0) >= ($now - $staleQueueAfter)
    ));
    $state['active'] = array_values(array_filter(
        $state['active'],
        static fn (array $entry): bool => (int) ($entry['expires_at'] ?? 0) > $now
    ));

    $activeTokens = [];
    foreach ($state['active'] as $entry) {
        $activeTokens[(string) $entry['token']] = true;
    }
    $state['queued'] = array_values(array_filter(
        $state['queued'],
        static fn (array $entry): bool => empty($activeTokens[(string) $entry['token']])
    ));

    while (!empty($config['enabled']) && count($state['active']) < $maxActive && count($state['queued']) > 0) {
        $entry = array_shift($state['queued']);
        if (!is_array($entry) || (string) ($entry['token'] ?? '') === '') {
            continue;
        }

        $entry['last_seen'] = $now;
        $entry['admitted_at'] = $now;
        $entry['expires_at'] = $now + $timeout;
        $state['active'][] = $entry;
    }

    $state['updated_at'] = gmdate('c', $now);

    return $state;
}

function clicketVirtualQueueState(string $eventKey, int $performance, ?callable $mutator = null): array {
    $default = clicketVirtualQueueDefaultState($eventKey, $performance);
    $path = clicketVirtualQueueStatePath($eventKey, $performance);

    return clicketVirtualQueueLockedJson($path, $default, function (array $state) use ($eventKey, $performance, $mutator): array {
        $state = clicketVirtualQueueNormalizeState($state, $eventKey, $performance);
        if ($mutator) {
            $state = $mutator($state);
        }

        return clicketVirtualQueueNormalizeState($state, $eventKey, $performance);
    });
}

function clicketVirtualQueueSessionBucket(): array {
    startSessionIfNeeded();
    if (!is_array($_SESSION['clicket_virtual_queue'] ?? null)) {
        $_SESSION['clicket_virtual_queue'] = [];
    }

    return $_SESSION['clicket_virtual_queue'];
}

function clicketVirtualQueueSessionToken(string $eventKey, int $performance): string {
    startSessionIfNeeded();
    $key = clicketVirtualQueueStateKey($eventKey, $performance);
    if (!is_array($_SESSION['clicket_virtual_queue'] ?? null)) {
        $_SESSION['clicket_virtual_queue'] = [];
    }
    if (empty($_SESSION['clicket_virtual_queue'][$key]['token'])) {
        $_SESSION['clicket_virtual_queue'][$key] = [
            'token' => bin2hex(random_bytes(18)),
            'created_at' => time(),
        ];
    }

    return (string) $_SESSION['clicket_virtual_queue'][$key]['token'];
}

function clicketVirtualQueueStartedUntil(string $eventKey, int $performance): int {
    startSessionIfNeeded();
    $key = clicketVirtualQueueStateKey($eventKey, $performance);

    return (int) ($_SESSION['clicket_virtual_queue_started'][$key]['expires_at'] ?? 0);
}

function clicketVirtualQueueActorId(): string {
    $auth = function_exists('currentAuth') ? currentAuth() : null;
    if (is_array($auth)) {
        $role = (string) ($auth['role'] ?? 'user');
        $id = (string) ($auth['user_id'] ?? $auth['id'] ?? $auth['email'] ?? '');
        if ($id !== '') {
            return $role . ':' . $id;
        }
    }

    return 'session:' . session_id();
}

function clicketVirtualQueueBuildEntry(string $token, int $now): array {
    return [
        'token' => $token,
        'session_id' => session_id(),
        'user_id' => clicketVirtualQueueActorId(),
        'created_at' => $now,
        'last_seen' => $now,
    ];
}

function clicketVirtualQueueFindEntry(array $entries, string $token): ?array {
    foreach ($entries as $index => $entry) {
        if ((string) ($entry['token'] ?? '') === $token) {
            return ['index' => $index, 'entry' => $entry];
        }
    }

    return null;
}

function clicketVirtualQueueStatusFromState(array $state, array $config, string $token, int $now, string $eventKey, int $performance): array {
    $active = clicketVirtualQueueFindEntry($state['active'], $token);
    $queued = clicketVirtualQueueFindEntry($state['queued'], $token);
    $startedUntil = clicketVirtualQueueStartedUntil($eventKey, $performance);
    $queueSize = count($state['queued']);
    $activeCount = count($state['active']);
    $throughput = max(1, (int) ($config['throughput_per_minute'] ?? 12));

    $status = 'queued';
    $position = $queued ? ((int) $queued['index'] + 1) : null;
    $usersAhead = $queued ? (int) $queued['index'] : 0;
    $estimatedWait = $position ? (int) ceil(($position / $throughput) * 60) : 0;
    $admittedUntil = null;

    if ($active) {
        $status = 'admitted';
        $position = 0;
        $usersAhead = 0;
        $admittedUntil = (int) ($active['entry']['expires_at'] ?? 0);
    } elseif ($startedUntil > $now) {
        $status = 'booking';
        $position = 0;
        $usersAhead = 0;
        $admittedUntil = $startedUntil;
    } elseif (!$queued) {
        $status = 'waiting';
    }

    return [
        'enabled' => !empty($config['enabled']),
        'status' => $status,
        'admitted' => in_array($status, ['admitted', 'booking'], true),
        'token' => $token,
        'position' => $position,
        'users_ahead' => $usersAhead,
        'queue_size' => $queueSize,
        'active_count' => $activeCount,
        'max_active' => max(1, (int) ($config['max_active'] ?? 25)),
        'estimated_wait_seconds' => $estimatedWait,
        'average_wait_seconds' => $queueSize > 0 ? (int) ceil(($queueSize / $throughput) * 60) : 0,
        'admitted_until' => $admittedUntil,
        'redirect' => 'ticket.php?event=' . rawurlencode($eventKey) . '&performance=' . max(0, $performance),
        'updated_at' => $state['updated_at'] ?? gmdate('c', $now),
    ];
}

function clicketVirtualQueueEnter(string $eventKey, int $performance): array {
    $config = clicketVirtualQueueConfig($eventKey);
    if (empty($config['enabled'])) {
        return [
            'enabled' => false,
            'status' => 'disabled',
            'admitted' => true,
            'position' => 0,
            'users_ahead' => 0,
            'queue_size' => 0,
            'active_count' => 0,
            'max_active' => max(1, (int) $config['max_active']),
            'estimated_wait_seconds' => 0,
            'average_wait_seconds' => 0,
            'admitted_until' => null,
            'redirect' => 'ticket.php?event=' . rawurlencode($eventKey) . '&performance=' . max(0, $performance),
            'updated_at' => gmdate('c'),
        ];
    }

    $now = time();
    if (clicketVirtualQueueStartedUntil($eventKey, $performance) > $now) {
        $state = clicketVirtualQueueState($eventKey, $performance, static fn (array $state): array => clicketVirtualQueueCleanupState($state, $config, $now));

        return clicketVirtualQueueStatusFromState($state, $config, '', $now, $eventKey, $performance);
    }

    $token = clicketVirtualQueueSessionToken($eventKey, $performance);
    $state = clicketVirtualQueueState($eventKey, $performance, function (array $state) use ($config, $token, $now): array {
        $state = clicketVirtualQueueCleanupState($state, $config, $now);
        $active = clicketVirtualQueueFindEntry($state['active'], $token);
        $queued = clicketVirtualQueueFindEntry($state['queued'], $token);

        if ($active) {
            $state['active'][$active['index']]['last_seen'] = $now;
        } elseif ($queued) {
            $state['queued'][$queued['index']]['last_seen'] = $now;
        } else {
            $state['queued'][] = clicketVirtualQueueBuildEntry($token, $now);
        }

        return clicketVirtualQueueCleanupState($state, $config, $now);
    });

    return clicketVirtualQueueStatusFromState($state, $config, $token, $now, $eventKey, $performance);
}

function clicketVirtualQueueHasAdmission(string $eventKey, int $performance): bool {
    $config = clicketVirtualQueueConfig($eventKey);
    if (empty($config['enabled'])) {
        return true;
    }

    $now = time();
    if (clicketVirtualQueueStartedUntil($eventKey, $performance) > $now) {
        return true;
    }

    startSessionIfNeeded();
    $sessionKey = clicketVirtualQueueStateKey($eventKey, $performance);
    $token = (string) ($_SESSION['clicket_virtual_queue'][$sessionKey]['token'] ?? '');
    if ($token === '') {
        return false;
    }

    $state = clicketVirtualQueueState($eventKey, $performance, static fn (array $state): array => clicketVirtualQueueCleanupState($state, $config, $now));
    $active = clicketVirtualQueueFindEntry($state['active'], $token);

    return $active !== null && (int) ($active['entry']['expires_at'] ?? 0) > $now;
}

function clicketVirtualQueueEntryUrl(string $eventKey, int $performance, string $next = ''): string {
    $target = $next !== '' ? $next : 'ticket.php?event=' . rawurlencode($eventKey) . '&performance=' . max(0, $performance);
    if (!clicketVirtualQueueIsEnabled($eventKey)) {
        return $target;
    }

    return 'virtual-queue.php?event=' . rawurlencode($eventKey)
        . '&performance=' . max(0, $performance)
        . '&next=' . rawurlencode($target);
}

function clicketVirtualQueueRequireAdmission(string $eventKey, int $performance): void {
    if (clicketVirtualQueueHasAdmission($eventKey, $performance)) {
        return;
    }

    header('Location: ' . clicketVirtualQueueEntryUrl($eventKey, $performance));
    exit;
}

function clicketVirtualQueueRequireAdmissionJson(string $eventKey, int $performance): void {
    if (clicketVirtualQueueHasAdmission($eventKey, $performance)) {
        return;
    }

    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Please wait for queue admission before selecting seats.',
        'redirect' => clicketVirtualQueueEntryUrl($eventKey, $performance),
    ]);
    exit;
}

function clicketVirtualQueueMarkBookingStarted(string $eventKey, int $performance, int $ttlSeconds = 900): void {
    $config = clicketVirtualQueueConfig($eventKey);
    if (empty($config['enabled'])) {
        return;
    }

    startSessionIfNeeded();
    $now = time();
    $sessionKey = clicketVirtualQueueStateKey($eventKey, $performance);
    $token = (string) ($_SESSION['clicket_virtual_queue'][$sessionKey]['token'] ?? '');
    $_SESSION['clicket_virtual_queue_started'][$sessionKey] = [
        'expires_at' => $now + max(60, $ttlSeconds),
        'started_at' => $now,
    ];
    unset($_SESSION['clicket_virtual_queue'][$sessionKey]);

    if ($token === '') {
        return;
    }

    clicketVirtualQueueState($eventKey, $performance, static function (array $state) use ($config, $token, $now): array {
        $state['active'] = array_values(array_filter(
            $state['active'],
            static fn (array $entry): bool => (string) ($entry['token'] ?? '') !== $token
        ));
        $state['queued'] = array_values(array_filter(
            $state['queued'],
            static fn (array $entry): bool => (string) ($entry['token'] ?? '') !== $token
        ));

        return clicketVirtualQueueCleanupState($state, $config, $now);
    });
}

function clicketVirtualQueueStats(string $eventKey, int $performance = 0): array {
    $config = clicketVirtualQueueConfig($eventKey);
    $now = time();
    if (!is_file(clicketVirtualQueueStatePath($eventKey, $performance))) {
        $throughput = max(1, (int) ($config['throughput_per_minute'] ?? 12));

        return [
            'enabled' => !empty($config['enabled']),
            'queue_size' => 0,
            'active_count' => 0,
            'max_active' => max(1, (int) ($config['max_active'] ?? 25)),
            'timeout_seconds' => max(60, (int) ($config['timeout_seconds'] ?? 300)),
            'throughput_per_minute' => $throughput,
            'average_wait_seconds' => 0,
            'updated_at' => null,
        ];
    }

    $state = clicketVirtualQueueState($eventKey, $performance, static fn (array $state): array => clicketVirtualQueueCleanupState($state, $config, $now));
    $throughput = max(1, (int) ($config['throughput_per_minute'] ?? 12));
    $queueSize = count($state['queued']);

    return [
        'enabled' => !empty($config['enabled']),
        'queue_size' => $queueSize,
        'active_count' => count($state['active']),
        'max_active' => max(1, (int) ($config['max_active'] ?? 25)),
        'timeout_seconds' => max(60, (int) ($config['timeout_seconds'] ?? 300)),
        'throughput_per_minute' => $throughput,
        'average_wait_seconds' => $queueSize > 0 ? (int) ceil(($queueSize / $throughput) * 60) : 0,
        'updated_at' => $state['updated_at'] ?? gmdate('c', $now),
    ];
}

function clicketVirtualQueueStatsForEvents(array $events): array {
    $rows = [];
    foreach ($events as $event) {
        $eventKey = (string) ($event['event_key'] ?? $event['key'] ?? '');
        if ($eventKey === '') {
            continue;
        }

        $config = clicketVirtualQueueConfig($eventKey);
        $stats = clicketVirtualQueueStats($eventKey, 0);
        $queueSize = (int) $stats['queue_size'];
        $activeCount = (int) $stats['active_count'];
        $updatedAt = (string) $stats['updated_at'];

        $throughput = max(1, (int) ($config['throughput_per_minute'] ?? 12));
        $rows[$eventKey] = [
            'event_key' => $eventKey,
            'title' => (string) ($event['title'] ?? 'Untitled event'),
            'venue' => (string) ($event['venue'] ?? ''),
            'enabled' => !empty($config['enabled']),
            'max_active' => max(1, (int) ($config['max_active'] ?? 25)),
            'timeout_seconds' => max(60, (int) ($config['timeout_seconds'] ?? 300)),
            'throughput_per_minute' => $throughput,
            'queue_size' => $queueSize,
            'active_count' => $activeCount,
            'average_wait_seconds' => $queueSize > 0 ? (int) ceil(($queueSize / $throughput) * 60) : 0,
            'updated_at' => $updatedAt,
        ];
    }

    return $rows;
}

function clicketVirtualQueueAggregateMetrics(array $queueRows): array {
    $queueSize = 0;
    $activeCount = 0;
    $waitTotal = 0;
    $waitWeight = 0;

    foreach ($queueRows as $row) {
        $rowQueue = (int) ($row['queue_size'] ?? 0);
        $queueSize += $rowQueue;
        $activeCount += (int) ($row['active_count'] ?? 0);
        if ($rowQueue > 0) {
            $waitTotal += (int) ($row['average_wait_seconds'] ?? 0) * $rowQueue;
            $waitWeight += $rowQueue;
        }
    }

    return [
        'queueSize' => $queueSize,
        'queueActiveSessions' => $activeCount,
        'queueAverageWaitSeconds' => $waitWeight > 0 ? (int) round($waitTotal / $waitWeight) : 0,
    ];
}

function clicketVirtualQueueFormatDuration(int $seconds): string {
    if ($seconds <= 0) {
        return '0 min';
    }
    if ($seconds < 60) {
        return $seconds . ' sec';
    }

    $minutes = (int) ceil($seconds / 60);
    if ($minutes < 60) {
        return $minutes . ' min';
    }

    $hours = intdiv($minutes, 60);
    $remaining = $minutes % 60;

    return $remaining > 0 ? $hours . ' hr ' . $remaining . ' min' : $hours . ' hr';
}
