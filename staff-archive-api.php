<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/staff-panel-data.php';

function clicketArchiveRedirect(string $type, string $message): never {
    setFlashMessage($type, $message);
    header('Location: admin-panel.php#archives');
    exit;
}

$staff = clicketRequireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'export') {
    $rows = clicketStaffArchiveRows(clicketStaffScopedOrders($staff, clicketStaffAllEvents($staff)), clicketStaffAllEvents($staff), getUsers());
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="clicket_archives_' . gmdate('Ymd_His') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Archive ID', 'Type', 'Record', 'Scope', 'Reason', 'Archived At']);
    foreach ($rows as $row) {
        fputcsv($output, [$row['archive_id'], $row['type'], $row['title'], $row['scope'], $row['reason'], $row['archived_at']]);
    }
    fclose($output);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'restore') {
    clicketArchiveRedirect('error', 'Invalid archive action.');
}

$archiveId = (int) ($_POST['archive_id'] ?? 0);
if ($archiveId <= 0) {
    clicketArchiveRedirect('error', 'Archive record was not found.');
}

$record = clicketDbFetch('SELECT * FROM archive_records WHERE id = :id AND restored_at IS NULL LIMIT 1', ['id' => $archiveId]);
if (!$record || !in_array((string) $record['entity_type'], ['event', 'user', 'admin', 'organizer'], true)) {
    clicketArchiveRedirect('error', 'This archived record cannot be restored from this panel.');
}

$pdo = clicketDb();
$pdo->beginTransaction();
try {
    $entityType = (string) $record['entity_type'];
    if ($entityType === 'event') {
        clicketDbExecute('UPDATE events SET status = "published", archived_at = NULL WHERE id = :id AND status = "archived"', ['id' => (int) $record['entity_id']]);
    } elseif ($entityType === 'user') {
        clicketDbExecute('UPDATE users SET status = "active" WHERE id = :id', ['id' => (int) $record['entity_id']]);
    } else {
        clicketDbExecute('UPDATE staff_accounts SET status = "active" WHERE id = :id AND role = :role', ['id' => (int) $record['entity_id'], 'role' => $entityType]);
    }
    clicketDbExecute('UPDATE archive_records SET restored_at = UTC_TIMESTAMP() WHERE id = :id', ['id' => $archiveId]);
    $pdo->commit();
} catch (Throwable) {
    $pdo->rollBack();
    clicketArchiveRedirect('error', 'The archived event could not be restored.');
}

clicketArchiveRedirect('success', 'Archived record restored successfully.');
