<?php

if (!defined('CLICKET_NEWS_FILE')) {
    define('CLICKET_NEWS_FILE', __DIR__ . '/../storage/news.json');
}

function clicketEnsureNewsStore(): void {
    $directory = dirname(CLICKET_NEWS_FILE);
    if (!is_dir($directory)) mkdir($directory, 0775, true);
    if (!is_file(CLICKET_NEWS_FILE)) file_put_contents(CLICKET_NEWS_FILE, "[]\n", LOCK_EX);
}

function clicketReadNews(): array {
    clicketEnsureNewsStore();
    $items = json_decode(file_get_contents(CLICKET_NEWS_FILE) ?: '[]', true);
    return is_array($items) ? array_values(array_filter($items, 'is_array')) : [];
}

function clicketWriteNews(array $items): bool {
    clicketEnsureNewsStore();
    return file_put_contents(CLICKET_NEWS_FILE, json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) !== false;
}

function clicketPublishedNews(): array {
    $items = array_values(array_filter(clicketReadNews(), static fn (array $article): bool => strtolower((string) ($article['status'] ?? '')) === 'published'));
    usort($items, static fn (array $a, array $b): int => strcmp((string) ($b['published_at'] ?? ''), (string) ($a['published_at'] ?? '')));
    return $items;
}

function clicketNewsDate(string $date): string {
    $timestamp = strtotime($date);
    return $timestamp ? date('F j, Y \a\t g:i A', $timestamp) : 'Not published';
}
