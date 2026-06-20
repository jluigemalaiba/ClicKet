<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/ticket-validation.php';

header('Content-Type: application/json; charset=utf-8');

clicketRequireRoleJson(['admin'], 'Administrator access required.');
$staff = currentStaff();
if (!$staff) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Staff login required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST required.']);
    exit;
}

clicketRequireCsrfJson('staff_checkin');

try {
    $result = clicketValidateTicketForEntry(
        $staff,
        (string) ($_POST['validation_code'] ?? ''),
        (string) ($_POST['ticket_id'] ?? ''),
        (string) ($_POST['gate_name'] ?? '')
    );

    http_response_code((int) ($result['http_status'] ?? 200));
    unset($result['http_status']);
    echo json_encode($result);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'scan_result' => 'invalid',
        'message' => 'Ticket validation could not be completed.',
        'ticket' => null,
    ]);
}
