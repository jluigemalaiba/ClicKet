<?php
// sports.php - ClicKet Sports Events Page
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/log.php';

$categoryPage = [
    'title' => 'Sports Events',
    'accent' => 'Action',
    'eyebrow' => 'ClicKet Sports',
    'description' => 'Book seats for basketball finals, boxing nights, football matches, volleyball championships, and more.',
    'kicker' => 'Game Day',
    'categoryLabel' => 'Sports',
    'idPrefix' => 'sports',
    'posterCategory' => 'sports',
    'timeOffset' => 2,
    'hero' => landscapeUrl('sports', 18),
    'events' => $sports_events,
];

require __DIR__ . '/includes/category-page-template.php';
