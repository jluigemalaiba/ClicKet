<?php
// includes/category-page-template.php - shared ClicKet category listing page

if (!isset($categoryPage) || !is_array($categoryPage)) {
    throw new RuntimeException('Category page configuration is missing.');
}

if (!function_exists('categoryPagePriceValue')) {
    function categoryPagePriceValue(string $price): int {
        preg_match_all('/\d+/', $price, $matches);
        return (int) implode('', $matches[0] ?? []);
    }
}

if (!function_exists('categoryPageStars')) {
    function categoryPageStars(int $rating): string {
        $rating = max(0, min(5, $rating));
        $stars = '';

        for ($i = 1; $i <= 5; $i++) {
            $filled = $i <= $rating;
            $stars .= '<span class="' . ($filled ? 'filled' : 'empty') . '">' . ($filled ? '&#9733;' : '&#9734;') . '</span>';
        }

        return $stars;
    }
}

$pageTitle = $categoryPage['title'] ?? 'Events';
$pageAccent = $categoryPage['accent'] ?? $pageTitle;
$pageEyebrow = $categoryPage['eyebrow'] ?? 'ClicKet Events';
$pageDescription = $categoryPage['description'] ?? '';
$pageKicker = $categoryPage['kicker'] ?? 'Bookable Events';
$pageHero = $categoryPage['hero'] ?? landscapeUrl($categoryPage['posterCategory'] ?? 'featured', 24);
$pageBodyClass = $categoryPage['bodyClass'] ?? 'category-page';
$posterCategory = $categoryPage['posterCategory'] ?? 'featured';
$categoryLabel = $categoryPage['categoryLabel'] ?? $pageTitle;
$idPrefix = $categoryPage['idPrefix'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $pageTitle));
$timeOffset = (int) ($categoryPage['timeOffset'] ?? 0);
$rawEvents = $categoryPage['events'] ?? [];
$times = ['6:00 PM', '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM'];
$events = [];
$types = [];

foreach ($rawEvents as $idx => $event) {
    $type = $event['type'] ?? $categoryLabel;
    $sub = $event['artist'] ?? $event['company'] ?? $event['league'] ?? '';
    $price = (string) ($event['price'] ?? '');

    $types[$type] = true;
    $events[] = [
        'id' => $idPrefix . '-' . ($idx + 1),
        'title' => $event['title'] ?? 'Untitled Event',
        'date' => $event['date'] ?? 'Coming soon',
        'time' => $times[($idx + $timeOffset) % count($times)],
        'venue' => $event['venue'] ?? 'Venue TBA',
        'price' => $price,
        'priceValue' => categoryPagePriceValue($price),
        'rating' => (int) ($event['rating'] ?? 4),
        'type' => $type,
        'sub' => $sub,
        'poster' => posterUrl($posterCategory, $idx + 10),
    ];
}

$typeOptions = array_keys($types);
sort($typeOptions, SORT_NATURAL | SORT_FLAG_CASE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <title><?= htmlspecialchars($pageTitle) ?> | ClicKet</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/category-pages.css">
  <link rel="stylesheet" href="css/partners-footer.css">
</head>
<body class="<?= htmlspecialchars($pageBodyClass) ?>">

<?php require_once __DIR__ . '/navbar.php'; ?>

<main>
  <section class="category-hero" aria-label="<?= htmlspecialchars($pageTitle) ?> banner">
    <div class="category-hero-media" style="--hero-bg: url('<?= htmlspecialchars($pageHero) ?>');" aria-hidden="true"></div>
    <div class="container-xl px-4">
      <div class="category-hero-content">
        <p class="category-eyebrow"><?= htmlspecialchars($pageEyebrow) ?></p>
        <h1 class="category-hero-title"><?= htmlspecialchars($pageTitle) ?> <span><?= htmlspecialchars($pageAccent) ?></span></h1>
        <p class="category-hero-copy"><?= htmlspecialchars($pageDescription) ?></p>
        <div class="category-hero-actions">
          <a href="#categoryGrid" class="btn-primary">Browse <?= htmlspecialchars($pageTitle) ?></a>
          <a href="events.php" class="btn-outline">All Events</a>
        </div>
      </div>
    </div>
  </section>

  <section class="category-content">
    <div class="container-xl px-4">
      <div class="category-controls" aria-label="<?= htmlspecialchars($pageTitle) ?> filters">
        <div class="category-field">
          <label for="typeFilter">Type</label>
          <select class="category-select" id="typeFilter">
            <option value="all">All Types</option>
            <?php foreach ($typeOptions as $type): ?>
              <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="category-field">
          <label for="sortFilter">Sorting</label>
          <select class="category-select" id="sortFilter">
            <option value="rating-asc">Lowest Rating &rarr; Highest Rating</option>
            <option value="price-asc">Lowest Price &rarr; Highest Price</option>
            <option value="title-asc">A &rarr; Z</option>
          </select>
        </div>

        <div class="category-count" id="categoryCount" aria-live="polite">
          <?= count($events) ?> <?= count($events) === 1 ? 'Event' : 'Events' ?>
        </div>
      </div>

      <div class="category-listing-header">
        <div>
          <p class="category-kicker"><?= htmlspecialchars($pageKicker) ?></p>
          <h2 class="category-title"><?= htmlspecialchars($pageTitle) ?> <span>Lineup</span></h2>
        </div>
      </div>

      <div class="category-grid" id="categoryGrid">
        <?php foreach ($events as $event): ?>
          <article
            class="category-card"
            data-type="<?= htmlspecialchars($event['type']) ?>"
            data-rating="<?= (int) $event['rating'] ?>"
            data-price="<?= (int) $event['priceValue'] ?>"
            data-title="<?= htmlspecialchars(strtolower($event['title'])) ?>"
          >
            <div class="category-poster">
              <img src="<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['title']) ?> poster" loading="lazy">
              <div class="category-badges">
                <span class="category-badge"><?= htmlspecialchars($event['type']) ?></span>
                <span class="category-price"><?= htmlspecialchars($event['price']) ?></span>
              </div>
              <div class="category-poster-title">
                <span><?= htmlspecialchars($categoryLabel) ?></span>
                <strong><?= htmlspecialchars($event['title']) ?></strong>
              </div>
            </div>

            <div class="category-card-body">
              <h3 class="category-card-title"><?= htmlspecialchars($event['title']) ?></h3>

              <div class="category-meta">
                <div class="category-meta-row">
                  <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                  <span><strong><?= htmlspecialchars($event['date']) ?></strong> at <?= htmlspecialchars($event['time']) ?></span>
                </div>
                <div class="category-meta-row">
                  <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                  <span><?= htmlspecialchars($event['venue']) ?></span>
                </div>
                <?php if ($event['sub']): ?>
                  <div class="category-meta-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v3"/></svg>
                    <span><?= htmlspecialchars($event['sub']) ?></span>
                  </div>
                <?php endif; ?>
              </div>

              <div class="category-card-bottom">
                <div class="category-stars" aria-label="<?= (int) $event['rating'] ?> out of 5 stars">
                  <?= categoryPageStars((int) $event['rating']) ?>
                </div>
                <a href="auth.php?mode=login&amp;event=<?= urlencode($event['id']) ?>" class="category-book-btn">Book Now</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="category-empty" id="categoryEmpty">No events match the selected filters.</div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  'use strict';

  const navbar = document.querySelector('.navbar-clicket');
  const typeFilter = document.getElementById('typeFilter');
  const sortFilter = document.getElementById('sortFilter');
  const grid = document.getElementById('categoryGrid');
  const count = document.getElementById('categoryCount');
  const empty = document.getElementById('categoryEmpty');

  function handleScroll() {
    if (navbar) {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
    }
  }

  function cards() {
    return Array.from(grid.querySelectorAll('.category-card'));
  }

  function sortCards(mode) {
    const sorted = cards();

    sorted.sort((a, b) => {
      if (mode === 'price-asc') {
        return Number(a.dataset.price) - Number(b.dataset.price);
      }

      if (mode === 'title-asc') {
        return a.dataset.title.localeCompare(b.dataset.title);
      }

      return Number(a.dataset.rating) - Number(b.dataset.rating);
    });

    sorted.forEach(card => grid.appendChild(card));
  }

  function updateCards() {
    const selectedType = typeFilter.value;
    let visible = 0;

    sortCards(sortFilter.value);

    cards().forEach(card => {
      const show = selectedType === 'all' || card.dataset.type === selectedType;
      card.hidden = !show;
      if (show) {
        visible += 1;
      }
    });

    count.textContent = visible + (visible === 1 ? ' Event' : ' Events');
    empty.classList.toggle('is-visible', visible === 0);
  }

  window.addEventListener('scroll', handleScroll, { passive: true });
  typeFilter.addEventListener('change', updateCards);
  sortFilter.addEventListener('change', updateCards);
  handleScroll();
  updateCards();
})();
</script>
</body>
</html>
