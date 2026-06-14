<?php

require_once __DIR__ . '/favorite-data.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$user = currentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sign in to save favorite events.']);
    exit;
}

$eventId = trim((string) ($_POST['event_id'] ?? ''));
$favorite = filter_var($_POST['favorite'] ?? false, FILTER_VALIDATE_BOOL);

if (!preg_match('/^(concerts|theater|sports)-\d+$/', $eventId)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid event.']);
    exit;
}

$result = clicketSetFavorite((string) ($user['id'] ?? ''), $eventId, $favorite);
if (!$result['success']) {
    http_response_code(500);
}

echo json_encode($result, JSON_UNESCAPED_SLASHES);

