<?php
// concerts.php - ClicKet Concerts Page
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/log.php';

$categoryPage = [
    'title' => 'Concerts',
    'accent' => 'Live',
    'eyebrow' => 'ClicKet Concerts',
    'description' => 'Catch arena tours, OPM favorites, festival nights, and global acts performing on Philippine stages.',
    'kicker' => 'Live Music',
    'categoryLabel' => 'Concert',
    'idPrefix' => 'concert',
    'posterCategory' => 'concert',
    'timeOffset' => 0,
    'hero' => landscapeUrl('concert', 18),
    'events' => $concert_events,
];

require __DIR__ . '/includes/category-page-template.php';
