<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';

header('Content-Type: application/json');

function clicketPeopleRespond(array $payload, int $status = 200): never {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function clicketPeopleParseAccountId(string $clientId): ?array {
    if (preg_match('/^(staff|user)-([0-9]+)$/', $clientId, $matches)) {
        return [
            'table' => $matches[1] === 'staff' ? 'staff_accounts' : 'users',
            'id' => (int) $matches[2],
        ];
    }

    return null;
}

function clicketPeopleStaffRow(int $staffId): ?array {
    $row = clicketDbFetch(
        'SELECT sa.*,
                GROUP_CONCAT(v.name ORDER BY v.name SEPARATOR "\n") AS venues
         FROM staff_accounts sa
         LEFT JOIN staff_venue_assignments sva ON sva.staff_id = sa.id
         LEFT JOIN venues v ON v.id = sva.venue_id
         WHERE sa.id = :id
         GROUP BY sa.id
         LIMIT 1',
        ['id' => $staffId]
    );

    if (!$row) {
        return null;
    }

    $account = clicketStaffRowToApp($row);
    $account['id'] = 'staff-' . (string) $staffId;
    $account['db_id'] = (string) $staffId;
    $account['account_table'] = 'staff_accounts';
    $account['disabled'] = strtolower((string) ($account['status'] ?? 'active')) !== 'active';
    unset($account['password']);

    return $account;
}

function clicketPeopleUserRow(int $userId): ?array {
    $row = clicketDbFetch('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $userId]);
    if (!$row) {
        return null;
    }

    $account = clicketUserRowToApp($row);
    $account['id'] = 'user-' . (string) $userId;
    $account['db_id'] = (string) $userId;
    $account['account_table'] = 'users';
    $account['role'] = 'customer';
    $account['disabled'] = strtolower((string) ($account['status'] ?? 'active')) !== 'active';
    unset($account['password']);

    return $account;
}

function clicketPeopleVenueDbId(string $assignmentId): ?int {
    if (!ctype_digit($assignmentId)) {
        return null;
    }

    $row = clicketDbFetch(
        'SELECT id FROM venues WHERE id = :id AND status = "active" LIMIT 1',
        ['id' => (int) $assignmentId]
    );

    return $row ? (int) $row['id'] : null;
}

function clicketPeopleReplaceVenueAssignment(int $staffId, string $assignmentId): bool {
    $venueId = clicketPeopleVenueDbId($assignmentId);
    if (!$venueId) {
        return false;
    }

    clicketDbExecute('DELETE FROM staff_venue_assignments WHERE staff_id = :staff_id', ['staff_id' => $staffId]);
    clicketDbExecute(
        'INSERT INTO staff_venue_assignments
           (staff_id, venue_id, create_events, archive_events, manage_tiers, manage_seats, review_payments, print_tickets)
         VALUES
           (:staff_id, :venue_id, 1, 1, 1, 1, 1, 1)
         ON DUPLICATE KEY UPDATE
           create_events = VALUES(create_events),
           archive_events = VALUES(archive_events),
           manage_tiers = VALUES(manage_tiers),
           manage_seats = VALUES(manage_seats),
           review_payments = VALUES(review_payments),
           print_tickets = VALUES(print_tickets)',
        ['staff_id' => $staffId, 'venue_id' => $venueId]
    );

    return true;
}

$staff = currentStaff();
if (!$staff || ($staff['role'] ?? '') !== 'admin') {
    clicketPeopleRespond(['success' => false, 'message' => 'Admin access required.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    clicketPeopleRespond(['success' => false, 'message' => 'POST required.'], 405);
}

$action = strtolower(trim((string) ($_POST['action'] ?? '')));
$availableVenues = array_map(static fn (array $venue): string => (string) ($venue['id'] ?? ''), clicketStaffAssignmentOptions());

if ($action === 'create') {
    $role = strtolower(trim((string) ($_POST['role'] ?? '')));
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $venue = trim((string) ($_POST['venue'] ?? ''));

    if (!in_array($role, ['admin', 'organizer'], true) || strlen($name) < 3 || !clicketIsSupportedPublicEmail($email) || strlen($password) < 8) {
        clicketPeopleRespond(['success' => false, 'message' => 'Add a username, supported email provider, and password with at least 8 characters.'], 422);
    }

    if ($role === 'organizer' && !in_array($venue, $availableVenues, true)) {
        clicketPeopleRespond(['success' => false, 'message' => 'Assign a valid venue to the organizer.'], 422);
    }

    if (findUserByEmail($email) || findStaffByEmail($email)) {
        clicketPeopleRespond(['success' => false, 'message' => 'An account already uses that email.'], 409);
    }

    $pdo = clicketDb();
    $pdo->beginTransaction();
    try {
        clicketDbExecute(
            'INSERT INTO staff_accounts (name, email, password_hash, role, status, created_at)
             VALUES (:name, :email, :password_hash, :role, "active", :created_at)',
            [
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'created_at' => clicketDbDateTime('now'),
            ]
        );

        $staffId = (int) $pdo->lastInsertId();
        if ($staffId <= 0) {
            $staffId = clicketDbStaffIdByEmail($email) ?? 0;
        }

        if ($staffId <= 0 || ($role === 'organizer' && !clicketPeopleReplaceVenueAssignment($staffId, $venue))) {
            throw new RuntimeException('Unable to create staff account.');
        }

        $pdo->commit();
    } catch (Throwable) {
        $pdo->rollBack();
        clicketPeopleRespond(['success' => false, 'message' => 'Could not create the account.'], 500);
    }

    clicketPeopleRespond([
        'success' => true,
        'message' => ucfirst($role) . ' account created.',
        'account' => clicketPeopleStaffRow($staffId),
    ]);
}

$clientId = trim((string) ($_POST['user_id'] ?? ''));
$target = clicketPeopleParseAccountId($clientId);
if (!in_array($action, ['archive', 'disable', 'assign'], true) || !$target) {
    clicketPeopleRespond(['success' => false, 'message' => 'Invalid people action.'], 422);
}

if ($action === 'assign') {
    if ($target['table'] !== 'staff_accounts') {
        clicketPeopleRespond(['success' => false, 'message' => 'Choose an organizer and assigned venue.'], 422);
    }

    $venue = trim((string) ($_POST['venue'] ?? ''));
    if (!in_array($venue, $availableVenues, true)) {
        clicketPeopleRespond(['success' => false, 'message' => 'Choose an organizer and assigned venue.'], 422);
    }

    $account = clicketDbFetch('SELECT role FROM staff_accounts WHERE id = :id LIMIT 1', ['id' => $target['id']]);
    if (!$account || (string) ($account['role'] ?? '') !== 'organizer') {
        clicketPeopleRespond(['success' => false, 'message' => 'Choose an organizer and assigned venue.'], 422);
    }

    $pdo = clicketDb();
    $pdo->beginTransaction();
    try {
        if (!clicketPeopleReplaceVenueAssignment((int) $target['id'], $venue)) {
            throw new RuntimeException('Unable to assign venue.');
        }
        $pdo->commit();
    } catch (Throwable) {
        $pdo->rollBack();
        clicketPeopleRespond(['success' => false, 'message' => 'Could not update the account.'], 500);
    }

    clicketPeopleRespond([
        'success' => true,
        'message' => 'Organizer venue assigned.',
        'account' => clicketPeopleStaffRow((int) $target['id']),
    ]);
}

if ($target['table'] === 'staff_accounts') {
    $account = clicketDbFetch('SELECT role FROM staff_accounts WHERE id = :id LIMIT 1', ['id' => (int) $target['id']]);
    if (!$account) {
        clicketPeopleRespond(['success' => false, 'message' => 'Account not found.'], 404);
    }

    $currentStaffId = clicketDbStaffIdBySession($staff);
    if ($currentStaffId !== null && $currentStaffId === (int) $target['id']) {
        clicketPeopleRespond(['success' => false, 'message' => 'You cannot disable your own admin account.'], 409);
    }

    clicketDbExecute(
        'UPDATE staff_accounts SET status = "inactive" WHERE id = :id AND role IN ("admin", "organizer")',
        ['id' => (int) $target['id']]
    );

    clicketPeopleRespond([
        'success' => true,
        'message' => 'Staff account disabled.',
        'account' => clicketPeopleStaffRow((int) $target['id']),
    ]);
}

$account = clicketDbFetch('SELECT id FROM users WHERE id = :id LIMIT 1', ['id' => (int) $target['id']]);
if (!$account) {
    clicketPeopleRespond(['success' => false, 'message' => 'Account not found.'], 404);
}

clicketDbExecute('UPDATE users SET status = "inactive" WHERE id = :id', ['id' => (int) $target['id']]);
clicketPeopleRespond([
    'success' => true,
    'message' => 'Customer account archived and disabled.',
    'account' => clicketPeopleUserRow((int) $target['id']),
]);
