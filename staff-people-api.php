<?php

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';

header('Content-Type: application/json');
$staff = currentStaff();
if (!$staff || ($staff['role'] ?? '') !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Admin access required.']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success' => false, 'message' => 'POST required.']); exit; }

$action = strtolower(trim((string) ($_POST['action'] ?? '')));
$actor = (string) ($staff['email'] ?? 'admin');
$users = getUsers();
$availableVenues = array_map(static fn (array $venue): string => (string) ($venue['id'] ?? ''), clicketStaffVenueDefinitions());

if ($action === 'create') {
    $role = strtolower(trim((string) ($_POST['role'] ?? '')));
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $venue = trim((string) ($_POST['venue'] ?? ''));
    if (!in_array($role, ['admin', 'organizer'], true) || strlen($name) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Add a username, valid email, and password with at least 8 characters.']); exit; }
    if ($role === 'organizer' && !in_array($venue, $availableVenues, true)) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Assign a valid venue to the organizer.']); exit; }
    foreach ($users as $account) if (strtolower((string) ($account['email'] ?? '')) === $email) { http_response_code(409); echo json_encode(['success' => false, 'message' => 'An account already uses that email.']); exit; }
    $account = ['id' => bin2hex(random_bytes(8)), 'name' => $name, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => $role, 'venues' => $role === 'admin' ? ['all'] : [$venue], 'status' => 'Active', 'created_at' => date('c'), 'created_by' => $actor];
    $users[] = $account;
    if (!saveUsers($users)) { http_response_code(500); echo json_encode(['success' => false, 'message' => 'Could not create the account.']); exit; }
    echo json_encode(['success' => true, 'message' => ucfirst($role) . ' account created.', 'account' => $account]); exit;
}

$userId = trim((string) ($_POST['user_id'] ?? ''));
if (!in_array($action, ['archive', 'disable', 'assign'], true) || $userId === '') { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Invalid people action.']); exit; }
foreach ($users as $index => $account) {
    if ((string) ($account['id'] ?? '') !== $userId) continue;
    if ($userId === (string) ($staff['id'] ?? '')) { http_response_code(409); echo json_encode(['success' => false, 'message' => 'You cannot disable your own admin account.']); exit; }
    $role = (string) ($account['role'] ?? 'customer');
    if ($action === 'assign') {
        $venue = trim((string) ($_POST['venue'] ?? ''));
        if ($role !== 'organizer' || !in_array($venue, $availableVenues, true)) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Choose an organizer and assigned venue.']); exit; }
        $account['venues'] = [$venue]; $account['updated_at'] = date('c'); $account['assigned_by'] = $actor;
    } else {
        $account['disabled'] = true; $account['status'] = 'Archived'; $account['archived_at'] = date('c'); $account['archived_by'] = $actor;
    }
    $users[$index] = $account;
    if (!saveUsers($users)) { http_response_code(500); echo json_encode(['success' => false, 'message' => 'Could not update the account.']); exit; }
    echo json_encode(['success' => true, 'message' => $action === 'assign' ? 'Organizer venue assigned.' : 'Account archived and disabled.', 'account' => $account]); exit;
}
http_response_code(404); echo json_encode(['success' => false, 'message' => 'Account not found.']);
