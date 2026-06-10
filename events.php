<?php
// events.php - ClicKet Events Listing Page
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/log.php';

function eventPageStars(int $rating): string {
    $rating = max(0, min(5, $rating));
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= '<span class="' . ($i <= $rating ? 'filled' : 'empty') . '">' . ($i <= $rating ? '&#9733;' : '&#9734;') . '</span>';
    }
    return $stars;
}

function buildEventPageRows(array $events, string $categoryKey, string $categoryLabel, string $posterCategory, int $timeOffset): array {
    $times = ['6:00 PM', '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM'];
    $rows = [];

    foreach ($events as $idx => $event) {
        $sub = $event['artist'] ?? $event['company'] ?? $event['league'] ?? '';
        $rows[] = [
            'id' => $categoryKey . '-' . ($idx + 1),
            'title' => $event['title'],
            'date' => $event['date'],
            'dateValue' => strtotime($event['date']) ?: 0,
            'time' => $times[($idx + $timeOffset) % count($times)],
            'venue' => $event['venue'],
            'rating' => (int) ($event['rating'] ?? 4),
            'type' => $event['type'] ?? $categoryLabel,
            'sub' => $sub,
            'categoryKey' => $categoryKey,
            'categoryLabel' => $categoryLabel,
            'poster' => posterUrl($posterCategory, $idx + 10),
        ];
    }

    return $rows;
}

$events = array_merge(
    buildEventPageRows($concert_events, 'concerts', 'Concerts', 'concert', 0),
    buildEventPageRows($theater_events, 'theater', 'Theater', 'theater', 1),
    buildEventPageRows($sports_events, 'sports', 'Sports', 'sports', 2)
);

$venueOptions = array_values(array_unique(array_map(static fn ($event) => $event['venue'], $events)));
sort($venueOptions, SORT_NATURAL | SORT_FLAG_CASE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Discover and book upcoming concerts, theater shows, and sports events on ClicKet.">
  <title>ClicKet</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/partners-footer.css">

  <style>
    body.events-page {
      background: var(--light-bg);
      color: var(--text-primary);
    }

    .events-hero {
      position: relative;
      min-height: 620px;
      padding: 168px 0 92px;
      overflow: hidden;
      background: #111;
      color: #fff;
      isolation: isolate;
    }

    .events-hero-video,
    .events-hero-fallback {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: -3;
    }

    .events-hero-fallback {
      background-image: url('https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=1800&h=1000&fit=crop');
      background-size: cover;
      background-position: center;
    }

    .events-hero-video {
      opacity: .88;
    }

    .events-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        linear-gradient(90deg, rgba(17,17,17,.9) 0%, rgba(17,17,17,.68) 38%, rgba(232,22,43,.5) 100%),
        linear-gradient(180deg, rgba(0,0,0,.18) 0%, rgba(0,0,0,.62) 100%);
      z-index: -2;
    }

    .events-hero::after {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      height: 8px;
      background: var(--red-primary);
      z-index: 1;
    }

    .events-hero-content {
      max-width: 720px;
    }

    .events-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 18px;
      padding: 7px 14px;
      border-radius: var(--btn-radius);
      background: rgba(232,22,43,.8);
      border: 1px solid rgba(255,255,255,.22);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .events-eyebrow::before {
      content: '';
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #fff;
      box-shadow: 0 0 0 5px rgba(255,255,255,.15);
    }

    .events-hero-title {
      font-family: var(--font-display);
      font-size: 78px;
      line-height: .92;
      letter-spacing: 1px;
      margin: 0 0 22px;
      max-width: 700px;
      text-wrap: balance;
    }

    .events-hero-title span {
      color: var(--red-light);
    }

    .events-hero-copy {
      max-width: 570px;
      margin: 0 0 34px;
      color: rgba(255,255,255,.78);
      font-size: 16px;
      line-height: 1.75;
    }

    .events-hero-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
    }

    .events-hero .btn-outline {
      border-color: rgba(255,255,255,.55);
      color: #fff;
    }

    .events-hero .btn-outline:hover {
      background: rgba(255,255,255,.14);
      border-color: #fff;
      color: #fff;
    }

    .events-filter-section {
      position: relative;
      padding: 34px 0 22px;
      background: var(--light-bg);
      border-bottom: 1px solid var(--gray-200);
    }

    .events-filter-panel {
      display: grid;
      grid-template-columns: minmax(170px, 280px) minmax(230px, 330px) minmax(260px, 280px);
      gap: 14px;
      align-items: end;
      width: fit-content;
      max-width: 100%;
      margin: 0 auto;
      padding: 20px;
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-sm);
    }

    .events-filter-field {
      position: relative;
      display: grid;
      gap: 8px;
    }

    .events-filter-field label {
      margin: 0;
      color: var(--gray-600);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 1.6px;
      text-transform: uppercase;
    }

    .events-select {
      width: 100%;
      min-height: 46px;
      padding: 0 42px 0 14px;
      border: 1.5px solid var(--gray-200);
      border-radius: 10px;
      background: var(--gray-100);
      color: var(--text-primary);
      font-family: var(--font-body);
      font-size: 14px;
      font-weight: 600;
      outline: none;
      transition: border-color var(--dur-fast), background var(--dur-fast), box-shadow var(--dur-fast);
    }

    .events-select:focus {
      border-color: var(--red-primary);
      background: #fff;
      box-shadow: 0 0 0 4px rgba(232,22,43,.1);
    }

    .events-select.is-enhanced {
      position: absolute;
      width: 1px;
      height: 1px;
      opacity: 0;
      pointer-events: none;
    }

    .events-custom-select {
      position: relative;
      z-index: 20;
    }

    .events-custom-select.is-open {
      z-index: 70;
    }

    .events-custom-trigger {
      width: 100%;
      min-height: 44px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 0 14px 0 16px;
      border: 1.5px solid var(--gray-200);
      border-radius: 14px;
      background: #fff;
      color: var(--text-primary);
      font-family: var(--font-body);
      font-size: 14px;
      font-weight: 650;
      text-align: left;
      box-shadow: 0 1px 0 rgba(0,0,0,.03);
      transition: border-color var(--dur-fast), box-shadow var(--dur-fast), background var(--dur-fast);
    }

    .events-custom-trigger:hover,
    .events-custom-trigger[aria-expanded="true"] {
      border-color: rgba(232,22,43,.5);
      box-shadow: 0 0 0 4px rgba(232,22,43,.11);
    }

    .events-custom-trigger svg {
      width: 16px;
      height: 16px;
      flex: 0 0 16px;
      stroke: var(--gray-500);
      transition: transform var(--dur-fast), stroke var(--dur-fast);
    }

    .events-custom-trigger[aria-expanded="true"] svg {
      transform: rotate(180deg);
      stroke: var(--red-primary);
    }

    .events-custom-menu {
      position: absolute;
      left: 0;
      top: calc(100% + 8px);
      z-index: 50;
      display: none;
      min-width: min(360px, 92vw);
      padding: 6px;
      border: 1px solid rgba(0,0,0,.1);
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 18px 46px rgba(17,17,17,.16);
    }

    .events-custom-select.is-open .events-custom-menu {
      display: grid;
      gap: 4px;
    }

    .events-custom-select--venue .events-custom-menu {
      width: min(300px, calc(100vw - 48px));
      max-height: 240px;
      overflow-y: auto;
      overscroll-behavior: contain;
      grid-template-columns: 1fr;
      gap: 1px;
      padding: 6px;
    }

    .events-custom-menu::-webkit-scrollbar {
      width: 8px;
    }

    .events-custom-menu::-webkit-scrollbar-track {
      background: transparent;
    }

    .events-custom-menu::-webkit-scrollbar-thumb {
      border: 2px solid #fff;
      border-radius: 999px;
      background: rgba(232,22,43,.28);
    }

    .events-custom-select--sort .events-custom-menu {
      left: auto;
      right: 0;
      min-width: min(340px, 92vw);
    }

    .events-custom-select--sort .events-custom-option {
      white-space: nowrap;
    }

    .events-custom-option {
      display: flex;
      align-items: center;
      min-height: 30px;
      padding: 5px 10px;
      border-radius: 8px;
      color: var(--gray-600);
      font-size: 12px;
      font-weight: 600;
      line-height: 1.2;
      text-align: left;
      transition: background var(--dur-fast), color var(--dur-fast);
    }

    .events-custom-option:hover,
    .events-custom-option[aria-selected="true"] {
      background: rgba(232,22,43,.08);
      color: var(--text-primary);
    }

    .events-custom-option[aria-selected="true"] {
      color: var(--red-primary);
    }

    .events-custom-select--venue .events-custom-option {
      min-height: 30px;
      padding: 5px 10px;
      font-size: 12px;
      line-height: 1.2;
    }

    .events-listing-section {
      padding: 56px 0 82px;
      background:
        linear-gradient(180deg, var(--light-bg) 0%, var(--light-surface) 100%);
    }

    .events-listing-header {
      display: flex;
      justify-content: space-between;
      align-items: end;
      gap: 20px;
      margin-bottom: 28px;
      flex-wrap: wrap;
    }

    .events-section-kicker {
      margin: 0 0 6px;
      color: var(--red-primary);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 2.2px;
      text-transform: uppercase;
    }

    .events-section-title {
      margin: 0;
      font-family: var(--font-display);
      font-size: 48px;
      line-height: 1;
      letter-spacing: 1px;
      color: var(--text-primary);
    }

    .events-section-title span {
      color: var(--red-primary);
    }

    .events-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 24px;
      align-items: stretch;
    }

    .events-card {
      display: flex;
      flex-direction: column;
      min-width: 0;
      overflow: hidden;
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: var(--light-card);
      box-shadow: var(--shadow-card);
      transition: transform var(--dur-mid) var(--ease), box-shadow var(--dur-mid), border-color var(--dur-mid);
    }

    .events-card:hover {
      transform: translateY(-7px) scale(1.015);
      border-color: rgba(232,22,43,.26);
      box-shadow: 0 18px 44px rgba(0,0,0,.14);
    }

    .events-card[hidden] {
      display: none;
    }

    .events-card-poster {
      position: relative;
      aspect-ratio: 2 / 3;
      overflow: hidden;
      background: var(--gray-200);
    }

    .events-card-poster img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .48s var(--ease);
    }

    .events-card:hover .events-card-poster img {
      transform: scale(1.07);
    }

    .events-card-poster::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(0,0,0,0) 44%, rgba(0,0,0,.82) 100%);
    }

    .events-card-badges {
      position: absolute;
      top: 12px;
      left: 12px;
      right: 12px;
      z-index: 2;
      display: flex;
      justify-content: flex-start;
      gap: 8px;
    }

    .events-category-badge {
      display: inline-flex;
      align-items: center;
      min-height: 26px;
      padding: 4px 10px;
      border-radius: 999px;
      color: #fff;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .7px;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .events-category-badge {
      background: rgba(17,17,17,.68);
      backdrop-filter: blur(8px);
    }

    .events-card-overlay-title {
      position: absolute;
      left: 16px;
      right: 16px;
      bottom: 15px;
      z-index: 2;
      color: #fff;
    }

    .events-card-overlay-title span {
      display: block;
      margin-bottom: 4px;
      color: rgba(255,255,255,.72);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.2px;
      text-transform: uppercase;
    }

    .events-card-overlay-title strong {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      font-family: var(--font-display);
      font-size: 25px;
      line-height: 1;
      letter-spacing: .7px;
      overflow-wrap: anywhere;
    }

    .events-card-body {
      display: flex;
      flex: 1;
      flex-direction: column;
      padding: 16px;
    }

    .events-card-title {
      display: -webkit-box;
      min-height: 42px;
      margin: 0 0 11px;
      color: var(--text-primary);
      font-size: 16px;
      font-weight: 800;
      line-height: 1.3;
      -webkit-line-clamp: 2;
      line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      overflow-wrap: anywhere;
    }

    .events-meta {
      display: grid;
      gap: 8px;
      margin-bottom: 15px;
    }

    .events-meta-row {
      display: flex;
      gap: 8px;
      min-width: 0;
      color: var(--gray-500);
      font-size: 13px;
      line-height: 1.45;
    }

    .events-meta-row svg {
      width: 15px;
      height: 15px;
      flex: 0 0 15px;
      margin-top: 2px;
      stroke: var(--red-primary);
    }

    .events-meta-row strong {
      color: var(--gray-600);
      font-weight: 700;
    }

    .events-card-bottom {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-top: auto;
      padding-top: 14px;
      border-top: 1px solid var(--gray-200);
    }

    .events-stars {
      display: inline-flex;
      gap: 1px;
      color: #F59E0B;
      font-size: 13px;
      letter-spacing: .8px;
      white-space: nowrap;
    }

    .events-stars .empty {
      color: #D8D8D8;
    }

    .events-book-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 36px;
      padding: 0 16px;
      border-radius: var(--btn-radius);
      background: var(--red-primary);
      color: #fff;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .5px;
      white-space: nowrap;
      transition: background var(--dur-fast), transform var(--dur-fast), box-shadow var(--dur-fast);
    }

    .events-book-btn:hover {
      background: var(--red-light);
      color: #fff;
      transform: translateY(-2px);
      box-shadow: var(--glow-red);
    }

    .events-empty {
      display: none;
      padding: 42px 24px;
      border: 1px dashed var(--gray-300);
      border-radius: var(--card-radius);
      background: #fff;
      color: var(--gray-500);
      text-align: center;
      font-weight: 700;
    }

    .events-empty.is-visible {
      display: block;
    }

    @media (max-width: 1199px) {
      .events-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
    }

    @media (max-width: 991px) {
      .events-hero {
        min-height: 560px;
        padding: 142px 0 72px;
      }

      .events-hero-title {
        font-size: 58px;
      }

      .events-filter-panel {
        width: 100%;
        grid-template-columns: 1fr 1fr;
      }

    }

    @media (max-width: 767px) {
      .events-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
      }

      .events-filter-panel {
        width: 100%;
        grid-template-columns: 1fr;
        padding: 16px;
      }

      .events-custom-select--venue .events-custom-menu {
        width: 100%;
        grid-template-columns: 1fr;
      }

      .events-section-title {
        font-size: 38px;
      }

      .events-card-bottom {
        align-items: stretch;
        flex-direction: column;
      }

      .events-book-btn {
        width: 100%;
      }
    }

    @media (max-width: 520px) {
      .events-hero {
        min-height: 530px;
        padding: 132px 0 62px;
      }

      .events-hero-title {
        font-size: 44px;
      }

      .events-hero-copy {
        font-size: 14px;
      }

      .events-grid {
        grid-template-columns: 1fr;
      }

      .events-card-poster {
        aspect-ratio: 16 / 11;
      }
    }
  </style>
</head>
<body class="events-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>
  <section class="events-hero" aria-label="Events banner">
    <div class="events-hero-fallback" aria-hidden="true"></div>
    <video class="events-hero-video" autoplay muted loop playsinline poster="https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=1800&h=1000&fit=crop" aria-hidden="true">
      <source src="https://videos.pexels.com/video-files/13641377/13641377-hd_1920_1080_24fps.mp4" type="video/mp4">
    </video>

    <div class="container-xl px-4">
      <div class="events-hero-content">
        <p class="events-eyebrow">ClicKet Events</p>
        <h1 class="events-hero-title">Discover <span>Upcoming</span> Events</h1>
        <p class="events-hero-copy">
          Find the nights worth dressing up for, from arena tours and opening acts
          to final whistles and standing ovations.
        </p>
        <div class="events-hero-actions">
          <a href="#eventsGrid" class="btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
            Browse Events
          </a>
          <a href="auth.php?mode=login" class="btn-outline">Log In to Book</a>
        </div>
      </div>
    </div>
  </section>

  <section class="events-filter-section" aria-label="Event filters">
    <div class="container-xl px-4">
      <div class="events-filter-panel">
        <div class="events-filter-field">
          <label for="categoryFilter">Category</label>
          <select class="events-select" id="categoryFilter">
            <option value="all">All Categories</option>
            <option value="concerts">Concerts</option>
            <option value="theater">Theater</option>
            <option value="sports">Sports</option>
          </select>
        </div>

        <div class="events-filter-field">
          <label for="venueFilter">Venue</label>
          <select class="events-select" id="venueFilter">
            <option value="all">All Venues</option>
            <?php foreach ($venueOptions as $venue): ?>
              <option value="<?= htmlspecialchars($venue) ?>"><?= htmlspecialchars($venue) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="events-filter-field">
          <label for="sortFilter">Sorting</label>
          <select class="events-select" id="sortFilter">
            <option value="rating-desc">Highest Rating &rarr; Lowest Rating</option>
            <option value="rating-asc">Lowest Rating &rarr; Highest Rating</option>
            <option value="title-asc">A &rarr; Z</option>
            <option value="title-desc">Z &rarr; A</option>
            <option value="date-asc">Earliest Date &rarr; Latest Date</option>
            <option value="date-desc">Latest Date &rarr; Earliest Date</option>
          </select>
        </div>
      </div>
    </div>
  </section>

  <section class="events-listing-section">
    <div class="container-xl px-4">
      <div class="events-listing-header">
        <div>
          <p class="events-section-kicker">Bookable Events</p>
          <h2 class="events-section-title">All <span>Events</span></h2>
        </div>
      </div>

      <div class="events-grid" id="eventsGrid">
        <?php foreach ($events as $event): ?>
          <article
            class="events-card"
            data-category="<?= htmlspecialchars($event['categoryKey']) ?>"
            data-rating="<?= (int) $event['rating'] ?>"
            data-date="<?= (int) $event['dateValue'] ?>"
            data-title="<?= htmlspecialchars(strtolower($event['title'])) ?>"
            data-venue="<?= htmlspecialchars($event['venue']) ?>"
            data-search="<?= htmlspecialchars(strtolower($event['title'] . ' ' . $event['categoryLabel'] . ' ' . $event['type'] . ' ' . $event['venue'] . ' ' . $event['sub'])) ?>"
          >
            <div class="events-card-poster">
              <img src="<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['title']) ?> poster" loading="lazy">
              <div class="events-card-badges">
                <span class="events-category-badge"><?= htmlspecialchars($event['categoryLabel']) ?></span>
              </div>
              <div class="events-card-overlay-title">
                <span><?= htmlspecialchars($event['type']) ?></span>
                <strong><?= htmlspecialchars($event['title']) ?></strong>
              </div>
            </div>

            <div class="events-card-body">
              <h3 class="events-card-title"><?= htmlspecialchars($event['title']) ?></h3>

              <div class="events-meta">
                <div class="events-meta-row">
                  <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                  <span><strong><?= htmlspecialchars($event['date']) ?></strong> at <?= htmlspecialchars($event['time']) ?></span>
                </div>
                <div class="events-meta-row">
                  <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                  <span><?= htmlspecialchars($event['venue']) ?></span>
                </div>
                <?php if ($event['sub']): ?>
                  <div class="events-meta-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v3"/></svg>
                    <span><?= htmlspecialchars($event['sub']) ?></span>
                  </div>
                <?php endif; ?>
              </div>

              <div class="events-card-bottom">
                <div class="events-stars" aria-label="<?= (int) $event['rating'] ?> out of 5 stars">
                  <?= eventPageStars((int) $event['rating']) ?>
                </div>
                <a href="auth.php?mode=login&amp;event=<?= urlencode($event['id']) ?>" class="events-book-btn">Book Now</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="events-empty" id="eventsEmpty">No events match the selected filters.</div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  'use strict';

  const navbar = document.querySelector('.navbar-clicket');
  const categoryFilter = document.getElementById('categoryFilter');
  const venueFilter = document.getElementById('venueFilter');
  const sortFilter = document.getElementById('sortFilter');
  const grid = document.getElementById('eventsGrid');
  const navEventsLabel = document.getElementById('navEventsLabel');
  const empty = document.getElementById('eventsEmpty');
  const searchQuery = (new URLSearchParams(window.location.search).get('search') || '').trim().toLowerCase();
  const categoryLabels = {
    all: 'Events',
    concerts: 'Concerts',
    theater: 'Theater Plays',
    sports: 'Sports Events'
  };

  function handleScroll() {
    if (navbar) {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
    }
  }

  function getCards() {
    return Array.from(grid.querySelectorAll('.events-card'));
  }

  function sortCards(cards, mode) {
    const sorted = cards.slice();

    sorted.sort((a, b) => {
      if (mode === 'title-asc') {
        return a.dataset.title.localeCompare(b.dataset.title);
      }

      if (mode === 'title-desc') {
        return b.dataset.title.localeCompare(a.dataset.title);
      }

      if (mode === 'date-asc') {
        return Number(a.dataset.date) - Number(b.dataset.date);
      }

      if (mode === 'date-desc') {
        return Number(b.dataset.date) - Number(a.dataset.date);
      }

      if (mode === 'rating-asc') {
        return Number(a.dataset.rating) - Number(b.dataset.rating);
      }

      return Number(b.dataset.rating) - Number(a.dataset.rating);
    });

    sorted.forEach(card => grid.appendChild(card));
  }

  function closeCustomSelects(except) {
    document.querySelectorAll('.events-custom-select.is-open').forEach(select => {
      if (select !== except) {
        select.classList.remove('is-open');
        const trigger = select.querySelector('.events-custom-trigger');
        if (trigger) {
          trigger.setAttribute('aria-expanded', 'false');
        }
      }
    });
  }

  function enhanceSelect(select, modifier) {
    if (!select || select.dataset.enhanced === 'true') {
      return;
    }

    select.dataset.enhanced = 'true';
    select.classList.add('is-enhanced');

    const wrapper = document.createElement('div');
    wrapper.className = 'events-custom-select events-custom-select--' + modifier;

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'events-custom-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');

    const triggerText = document.createElement('span');
    const triggerIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    triggerIcon.setAttribute('viewBox', '0 0 24 24');
    triggerIcon.setAttribute('fill', 'none');
    triggerIcon.setAttribute('stroke-width', '2.4');
    triggerIcon.setAttribute('stroke-linecap', 'round');
    triggerIcon.setAttribute('stroke-linejoin', 'round');
    triggerIcon.setAttribute('aria-hidden', 'true');
    const iconPath = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
    iconPath.setAttribute('points', '6 9 12 15 18 9');
    triggerIcon.appendChild(iconPath);

    trigger.append(triggerText, triggerIcon);

    const menu = document.createElement('div');
    menu.className = 'events-custom-menu';
    menu.setAttribute('role', 'listbox');

    function sync() {
      const selected = select.options[select.selectedIndex] || select.options[0];
      triggerText.textContent = selected ? selected.textContent : '';
      menu.querySelectorAll('.events-custom-option').forEach(option => {
        option.setAttribute('aria-selected', String(option.dataset.value === select.value));
      });
    }

    Array.from(select.options).forEach(option => {
      const item = document.createElement('button');
      item.type = 'button';
      item.className = 'events-custom-option';
      item.dataset.value = option.value;
      item.textContent = option.textContent;
      item.setAttribute('role', 'option');

      item.addEventListener('click', () => {
        select.value = option.value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        sync();
        closeCustomSelects();
        trigger.focus();
      });

      item.addEventListener('keydown', event => {
        const items = Array.from(menu.querySelectorAll('.events-custom-option'));
        const index = items.indexOf(item);

        if (event.key === 'Escape') {
          closeCustomSelects();
          trigger.focus();
        }

        if (event.key === 'ArrowDown') {
          event.preventDefault();
          items[Math.min(index + 1, items.length - 1)].focus();
        }

        if (event.key === 'ArrowUp') {
          event.preventDefault();
          items[Math.max(index - 1, 0)].focus();
        }
      });

      menu.appendChild(item);
    });

    trigger.addEventListener('click', event => {
      event.stopPropagation();
      const willOpen = !wrapper.classList.contains('is-open');
      closeCustomSelects(wrapper);
      wrapper.classList.toggle('is-open', willOpen);
      trigger.setAttribute('aria-expanded', String(willOpen));
    });

    trigger.addEventListener('keydown', event => {
      if (event.key === 'Escape') {
        closeCustomSelects();
      }

      if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        closeCustomSelects(wrapper);
        wrapper.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        const selected = menu.querySelector('[aria-selected="true"]') || menu.querySelector('.events-custom-option');
        if (selected) {
          selected.focus();
        }
      }
    });

    select.addEventListener('change', sync);
    wrapper.append(trigger, menu);
    select.insertAdjacentElement('afterend', wrapper);
    sync();
  }

  function updateEvents() {
    const selectedCategory = categoryFilter.value;
    const selectedVenue = venueFilter.value;
    const cards = getCards();
    let visible = 0;

    sortCards(cards, sortFilter.value);

    getCards().forEach(card => {
      const matchesCategory = selectedCategory === 'all' || card.dataset.category === selectedCategory;
      const matchesVenue = selectedVenue === 'all' || card.dataset.venue === selectedVenue;
      const matchesSearch = !searchQuery || card.dataset.search.includes(searchQuery);
      const shouldShow = matchesCategory && matchesVenue && matchesSearch;
      card.hidden = !shouldShow;
      if (shouldShow) {
        visible += 1;
      }
    });

    if (navEventsLabel) {
      navEventsLabel.textContent = categoryLabels[selectedCategory] || 'Events';
    }

    empty.classList.toggle('is-visible', visible === 0);
  }

  function applyCategoryFromUrl() {
    const category = new URLSearchParams(window.location.search).get('category');
    if (!category || !categoryFilter.querySelector('option[value="' + category + '"]')) {
      return;
    }

    categoryFilter.value = category;
  }

  function applyVenueFromUrl() {
    const venue = new URLSearchParams(window.location.search).get('venue');
    if (!venue) {
      return;
    }

    const matchingOption = Array.from(venueFilter.options).find(option => option.value.toLowerCase() === venue.toLowerCase());
    if (matchingOption) {
      venueFilter.value = matchingOption.value;
    }
  }

  window.addEventListener('scroll', handleScroll, { passive: true });
  document.addEventListener('click', () => closeCustomSelects());
  categoryFilter.addEventListener('change', updateEvents);
  venueFilter.addEventListener('change', updateEvents);
  sortFilter.addEventListener('change', updateEvents);

  applyCategoryFromUrl();
  applyVenueFromUrl();
  enhanceSelect(categoryFilter, 'category');
  enhanceSelect(venueFilter, 'venue');
  enhanceSelect(sortFilter, 'sort');
  handleScroll();
  updateEvents();
})();
</script>
</body>
</html>
