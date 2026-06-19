<?php

declare(strict_types=1);

function clicketMailConfig(): array {
    $localPath = dirname(__DIR__) . '/storage/mail.local.php';
    $local = [];
    if (is_file($localPath)) {
        $loaded = require $localPath;
        if (is_array($loaded)) {
            $local = $loaded;
        }
    }

    return [
        'host' => (string) ($local['host'] ?? getenv('CLICKET_MAIL_HOST') ?: 'smtp.gmail.com'),
        'port' => (int) ($local['port'] ?? getenv('CLICKET_MAIL_PORT') ?: 587),
        'username' => (string) ($local['username'] ?? getenv('CLICKET_MAIL_USERNAME') ?: 'clicket.official@gmail.com'),
        'password' => (string) ($local['password'] ?? getenv('CLICKET_MAIL_PASSWORD') ?: 'chhkkxsnweytmcbv'),
        'from_email' => (string) ($local['from_email'] ?? getenv('CLICKET_MAIL_FROM') ?: 'clicket.official@gmail.com'),
        'from_name' => (string) ($local['from_name'] ?? getenv('CLICKET_MAIL_FROM_NAME') ?: 'Clicket'),
        'secure' => (string) ($local['secure'] ?? getenv('CLICKET_MAIL_SECURE') ?: 'tls'),
    ];
}
