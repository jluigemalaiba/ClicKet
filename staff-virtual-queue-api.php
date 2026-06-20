<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';
require_once __DIR__ . '/includes/virtual-queue.php';

function clicketVirtualQueueAdminReturn(string $type, string $message): never {
    setFlashMessage($type, $message);
    header('Location: admin-panel.php#virtual_queue');
    exit;
}

$auth = clicketRequireStaff('admin');
$staff = currentStaff();
if (!$staff || ($staff['role'] ?? '') !== 'admin') {
    clicketVirtualQueueAdminReturn('error', 'Admin access required.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    clicketVirtualQueueAdminReturn('error', 'Virtual queue updates require POST.');
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (!clicketVerifyCsrfToken($csrfToken, 'staff_queue')) {
    clicketVirtualQueueAdminReturn('error', 'Security token expired. Refresh the page and try again.');
}

$eventKey = trim((string) ($_POST['event_key'] ?? ''));
if ($eventKey === '') {
    clicketVirtualQueueAdminReturn('error', 'Event key is required.');
}

$events = clicketStaffAllEvents($staff);
$allowedKeys = array_flip(array_map(
    static fn (array $event): string => (string) ($event['event_key'] ?? $event['key'] ?? ''),
    $events
));

if (!isset($allowedKeys[$eventKey])) {
    clicketVirtualQueueAdminReturn('error', 'Event was not found in admin scope.');
}

clicketVirtualQueueSaveConfig($eventKey, [
    'enabled' => !empty($_POST['enabled']),
    'max_active' => (int) ($_POST['max_active'] ?? 25),
    'timeout_seconds' => (int) ($_POST['timeout_seconds'] ?? 300),
    'throughput_per_minute' => (int) ($_POST['throughput_per_minute'] ?? 12),
]);

clicketVirtualQueueAdminReturn('success', 'Virtual queue settings updated.');
