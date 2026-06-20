<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';
require_once __DIR__ . '/includes/reservation.php';
require_once __DIR__ . '/includes/inventory-sync.php';

function clicketReservationPanelReturnUrl(array $staff): string {
    return (($staff['role'] ?? '') === 'admin' ? 'admin-panel.php' : 'organizer-panel.php') . '#reservations';
}

function clicketReservationRedirect(array $staff, string $type, string $message): never {
    setFlashMessage($type, $message);
    header('Location: ' . clicketReservationPanelReturnUrl($staff));
    exit;
}

function clicketStaffReservationHoldById(int $holdId): ?array {
    return clicketDbFetch(
        'SELECT h.*, e.event_key
         FROM seat_holds h
         INNER JOIN events e ON e.id = h.event_id
         WHERE h.id = :id
         LIMIT 1',
        ['id' => $holdId]
    );
}

function clicketStaffCanManageReservation(array $staff, array $hold): bool {
    return clicketStaffCanAccessEvent($staff, (string) ($hold['event_key'] ?? ''));
}

function clicketStaffExportReservations(array $staff): never {
    $events = clicketStaffScopedEvents($staff, clicketStaffVenueDefinitions());
    $reservations = clicketStaffScopedReservations($staff, clicketReadReservationRows(), $events);
    $rows = clicketStaffBuildReservationRows($reservations, []);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="clicket_reservations_' . gmdate('Ymd_His') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Hold ID', 'Event', 'Venue', 'Buyer', 'Seats', 'Status', 'Expires At']);
    foreach ($rows as $row) {
        fputcsv($output, [
            (string) ($row['id'] ?? ''),
            (string) ($row['event'] ?? ''),
            (string) ($row['venue'] ?? ''),
            (string) ($row['buyer'] ?? ''),
            (int) ($row['seats'] ?? 0),
            (string) ($row['status'] ?? ''),
            date('c', (int) ($row['expires_at'] ?? time())),
        ]);
    }
    fclose($output);
    exit;
}

$auth = clicketRequireStaff();
$staff = currentStaff();
if (!$staff) {
    setFlashMessage('error', 'Please sign in with an admin or organizer account.');
    header('Location: auth.php?mode=admin');
    exit;
}

$action = strtolower(trim((string) ($_POST['action'] ?? $_GET['action'] ?? '')));

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'export') {
    clicketStaffExportReservations($staff);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    clicketReservationRedirect($staff, 'error', 'Reservation action requires a POST request.');
}

$holdId = (int) ($_POST['hold_id'] ?? 0);
if ($holdId <= 0 || !in_array($action, ['release', 'extend'], true)) {
    clicketReservationRedirect($staff, 'error', 'Invalid reservation action.');
}

$hold = clicketStaffReservationHoldById($holdId);
if (!$hold) {
    clicketReservationRedirect($staff, 'error', 'Reservation hold was not found.');
}

if (!clicketStaffCanManageReservation($staff, $hold)) {
    clicketReservationRedirect($staff, 'error', 'You do not have permission to manage that reservation.');
}

if ($action === 'release') {
    clicketDbExecute(
        'UPDATE seat_holds
         SET status = "released"
         WHERE id = :id AND status IN ("active", "expired")',
        ['id' => $holdId]
    );
    clicketInventorySyncEventPerformance((int) $hold['event_id'], (int) $hold['performance_id']);
    clicketReservationRedirect($staff, 'success', 'Reservation hold released.');
}

$minutes = max(1, min(120, (int) ($_POST['minutes'] ?? 15)));
clicketDbExecute(
    'UPDATE seat_holds
     SET status = "active",
         expires_at = DATE_ADD(GREATEST(expires_at, UTC_TIMESTAMP()), INTERVAL ' . $minutes . ' MINUTE)
     WHERE id = :id AND status IN ("active", "expired")',
    ['id' => $holdId]
);
clicketInventorySyncEventPerformance((int) $hold['event_id'], (int) $hold['performance_id']);
clicketReservationRedirect($staff, 'success', 'Reservation hold extended.');
