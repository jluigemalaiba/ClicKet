<?php

require_once dirname(__DIR__, 2) . '/includes/log.php';
require_once dirname(__DIR__, 2) . '/includes/staff-panel-data.php';

$auth = currentAuth();
if (!$auth) {
    setFlashMessage('error', 'Please sign in with an organizer account.');
    header('Location: ../auth.php?mode=organizer');
    exit;
}
if (($auth['role'] ?? '') !== 'organizer') {
    setFlashMessage('error', 'Organizer access required.');
    header('Location: ../' . clicketAuthRedirectForRole((string) ($auth['role'] ?? '')));
    exit;
}

$staff = currentStaff();
if (!$staff || ($staff['role'] ?? '') !== 'organizer') {
    logoutStaff();
    setFlashMessage('error', 'Organizer access required.');
    header('Location: ../auth.php?mode=organizer');
    exit;
}

$isAdmin = false;
$payload = clicketStaffPanelPayload($staff);
$metrics = $payload['metrics'];

if (!function_exists('sp_h')) {
    function sp_h(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
    function sp_money(int|float $value): string { return 'PHP ' . number_format((float) $value, 0); }
    function sp_count(mixed $value): string { return number_format((int) $value); }
    function sp_percent(int|float $value, int|float $max): int { return $max <= 0 ? 0 : max(0, min(100, (int) round(($value / $max) * 100))); }
    function sp_initials(string $label): string {
        $initials = '';
        foreach (preg_split('/\s+/', trim($label)) ?: [] as $word) {
            if ($word !== '' && strlen($initials) < 2) $initials .= strtoupper($word[0]);
        }
        return $initials !== '' ? $initials : 'CK';
    }
    function sp_status_class(mixed $status): string {
        return match (strtolower(trim((string) $status))) {
            'paid', 'published', 'confirmed', 'enabled', 'active', 'valid', 'open', 'approved', 'success' => 'is-success',
            'pending', 'draft', 'review', 'held', 'processing', 'warning' => 'is-warning',
            'failed', 'cancelled', 'canceled', 'void', 'blocked', 'expired', 'suspended' => 'is-danger',
            'refunded', 'archived', 'used', 'disabled' => 'is-muted',
            default => 'is-info',
        };
    }
}
