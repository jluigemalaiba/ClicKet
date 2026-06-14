<?php

require_once __DIR__ . '/log.php';

if (!defined('CLICKET_ORDERS_FILE')) {
    define('CLICKET_ORDERS_FILE', __DIR__ . '/../storage/orders.json');
}

function clicketEnsureOrderStore(): void {
    $directory = dirname(CLICKET_ORDERS_FILE);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    if (!file_exists(CLICKET_ORDERS_FILE)) {
        file_put_contents(CLICKET_ORDERS_FILE, json_encode([], JSON_PRETTY_PRINT));
    }
}

function clicketReadOrders(): array {
    clicketEnsureOrderStore();

    $orders = json_decode(file_get_contents(CLICKET_ORDERS_FILE) ?: '[]', true);

    return is_array($orders) ? $orders : [];
}

function clicketWriteOrders(array $orders): bool {
    clicketEnsureOrderStore();

    return file_put_contents(
        CLICKET_ORDERS_FILE,
        json_encode(array_values($orders), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    ) !== false;
}

function clicketSaveOrder(array $order): bool {
    clicketEnsureOrderStore();

    $handle = fopen(CLICKET_ORDERS_FILE, 'c+');
    if ($handle === false) {
        return false;
    }

    $saved = false;

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }

        rewind($handle);
        $orders = json_decode(stream_get_contents($handle) ?: '[]', true);
        $orders = is_array($orders) ? $orders : [];
        $orderId = (string) ($order['order_id'] ?? '');

        foreach ($orders as $existingOrder) {
            if ($orderId !== '' && ($existingOrder['order_id'] ?? '') === $orderId) {
                return true;
            }
        }

        $orders[] = $order;
        rewind($handle);
        ftruncate($handle, 0);
        $saved = fwrite($handle, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
        fflush($handle);
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    return $saved;
}

function clicketOrdersForUser(string $userId): array {
    $orders = array_values(array_filter(
        clicketReadOrders(),
        fn(array $order): bool => hash_equals((string) ($order['user_id'] ?? ''), $userId)
    ));

    usort($orders, function (array $left, array $right): int {
        return strcmp((string) ($right['booked_at'] ?? ''), (string) ($left['booked_at'] ?? ''));
    });

    return $orders;
}

function clicketOrderDate(string $date, string $format = 'M j, Y, g:i A'): string {
    try {
        return (new DateTimeImmutable($date))
            ->setTimezone(new DateTimeZone('Asia/Manila'))
            ->format($format);
    } catch (Throwable $exception) {
        return $date;
    }
}

function clicketOrderStatusClass(string $status): string {
    $normalized = strtolower(trim($status));

    return in_array($normalized, ['paid', 'confirmed', 'completed'], true)
        ? 'is-success'
        : (in_array($normalized, ['pending', 'processing'], true) ? 'is-pending' : 'is-neutral');
}
