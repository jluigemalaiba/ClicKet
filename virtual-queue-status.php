<?php

require_once __DIR__ . '/includes/ticketing.php';
require_once __DIR__ . '/includes/virtual-queue.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$eventKey = trim((string) ($_GET['event'] ?? ''));
$performance = max(0, (int) ($_GET['performance'] ?? 0));
$resolved = clicketResolveEvent($eventKey);

if (!$resolved) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Event not found.']);
    exit;
}

$status = clicketVirtualQueueEnter($eventKey, $performance);
$redirect = (string) ($_GET['next'] ?? '');
if ($redirect === '' || preg_match('/^[a-z][a-z0-9+.-]*:/i', $redirect) || str_starts_with($redirect, '//')) {
    $redirect = (string) ($status['redirect'] ?? ('ticket.php?event=' . rawurlencode($eventKey) . '&performance=' . $performance));
}

echo json_encode([
    'success' => true,
    'enabled' => (bool) ($status['enabled'] ?? false),
    'status' => (string) ($status['status'] ?? 'waiting'),
    'admitted' => (bool) ($status['admitted'] ?? false),
    'position' => $status['position'] ?? null,
    'users_ahead' => (int) ($status['users_ahead'] ?? 0),
    'queue_size' => (int) ($status['queue_size'] ?? 0),
    'active_count' => (int) ($status['active_count'] ?? 0),
    'max_active' => (int) ($status['max_active'] ?? 0),
    'estimated_wait_seconds' => (int) ($status['estimated_wait_seconds'] ?? 0),
    'average_wait_seconds' => (int) ($status['average_wait_seconds'] ?? 0),
    'admitted_until' => $status['admitted_until'] ?? null,
    'redirect' => $redirect,
]);
