<?php
// theater.php - ClicKet Theater Plays Page
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/log.php';

$categoryPage = [
    'title' => 'Theater Plays',
    'accent' => 'On Stage',
    'eyebrow' => 'ClicKet Theater',
    'description' => 'Find Broadway favorites, Filipino musicals, opera performances, and intimate stage productions.',
    'kicker' => 'Stage Shows',
    'categoryLabel' => 'Theater',
    'idPrefix' => 'theater',
    'posterCategory' => 'theater',
    'timeOffset' => 1,
    'hero' => landscapeUrl('theater', 18),
    'events' => $theater_events,
];

require __DIR__ . '/includes/category-page-template.php';
