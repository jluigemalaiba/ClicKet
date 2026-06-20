<?php
// includes/partials.php — ClicKet Reusable Component Helpers

/**
 * Renders a single event card (for concert / theater / sports grids).
 */
function renderEventCard(array $event, string $category, int $idx): void {
    $poster = (string) ($event['poster'] ?? '') !== '' ? (string) $event['poster'] : posterUrl($category, $idx + 10);
    $sub    = $event['artist'] ?? $event['company'] ?? $event['league'] ?? '';
    $type   = htmlspecialchars($event['type'] ?? '');
    $title  = htmlspecialchars($event['title']);
    $venue  = htmlspecialchars($event['venue']);
    $date   = htmlspecialchars($event['date']);
    $price  = htmlspecialchars($event['price'] ?? 'PHP 500');
    $detailUrl = htmlspecialchars(clicketEventDetailUrl($event, $category, $idx));
?>
    <div class="event-card" role="group" aria-label="<?= $title ?>">
      <!-- Poster -->
      <a class="event-poster" href="<?= $detailUrl ?>">
        <img src="<?= $poster ?>" alt="<?= $title ?>" loading="lazy">
        <div class="event-poster-overlay">
          <div class="event-poster-top">
            <span class="event-type-badge"><?= $type ?></span>
          </div>
          <div class="event-poster-bottom">
            <span class="event-quick-title"><?= $title ?></span>
          </div>
        </div>
      </a>

      <!-- Info -->
      <div class="event-info">
        <h3 class="event-title"><a href="<?= $detailUrl ?>"><?= $title ?></a></h3>
        <div class="event-meta">
          <div class="event-meta-row">
            <!-- Calendar icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <strong><?= $date ?></strong>
          </div>
          <div class="event-meta-row">
            <!-- Location pin icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            <span><?= $venue ?></span>
          </div>
          <?php if ($sub): ?>
          <div class="event-meta-row">
            <!-- Mic icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/><path d="M19 10v2a7 7 0 01-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/>
            </svg>
            <span><?= htmlspecialchars($sub) ?></span>
          </div>
          <?php endif; ?>
        </div>

        <div class="event-footer">
          <a class="event-book-btn" href="<?= $detailUrl ?>">View Event</a>
        </div>
      </div>
    </div>
<?php }

/**
 * Renders a featured slider card.
 */
function renderFeaturedCard(array $event, int $pos): void {
    $poster   = htmlspecialchars($event['poster']);
    $title    = htmlspecialchars($event['title']);
    $sub      = htmlspecialchars($event['sub']);
    $category = htmlspecialchars($event['category']);
    $detailUrl = htmlspecialchars(clicketEventDetailUrl($event, strtolower($event['category'] ?? 'events'), 0));
?>
    <a class="feat-card" data-pos="<?= $pos ?>" href="<?= $detailUrl ?>" aria-label="View <?= $title ?>">
      <img src="<?= $poster ?>" alt="<?= $title ?>" loading="lazy">
      <div class="feat-card-overlay">
        <span class="feat-card-category"><?= $category ?></span>
        <div class="feat-card-title"><?= $title ?></div>
        <div class="feat-card-sub"><?= $sub ?></div>
      </div>
    </a>
<?php }

/**
 * Renders a Netflix-style category showcase with a large landscape preview
 * and a horizontal poster rail.
 */
function renderCategoryShowcase(
    string $sectionId,
    array $events,
    string $category,
    string $label,
    string $bigTitle,
    string $description,
    string $countLabel,
    string $seeAllUrl
): void {
    $count = count($events);
    $first = $events[0] ?? null;
    if (!$first) return;

    $firstSub = $first['artist'] ?? $first['company'] ?? $first['league'] ?? '';
    $firstType = $first['type'] ?? $label;
    $firstBanner = (string) ($first['banner'] ?? '') !== '' ? (string) $first['banner'] : landscapeUrl($category, 10);
    $firstUrl = clicketEventDetailUrl($first, $category, 0);
?>
    <div class="category-showcase" data-showcase="<?= htmlspecialchars($sectionId) ?>">
      <div class="showcase-stage" style="--stage-bg: url('<?= htmlspecialchars($firstBanner) ?>');">
        <div class="showcase-copy">
          <div class="showcase-pills">
            <span><?= htmlspecialchars($label) ?></span>
            <span class="showcase-type"><?= htmlspecialchars($firstType) ?></span>
          </div>
          <h3 class="showcase-title"><?= htmlspecialchars($first['title']) ?></h3>
          <p class="showcase-meta">
            <?= htmlspecialchars($first['date']) ?> &bull; <?= htmlspecialchars($first['venue']) ?><?= $firstSub ? ' &bull; ' . htmlspecialchars($firstSub) : '' ?>
          </p>
          <p class="showcase-description"><?= htmlspecialchars($description) ?></p>
          <div class="showcase-actions">
            <a href="<?= htmlspecialchars($firstUrl) ?>" class="btn-primary showcase-book">
              View Event
            </a>
          </div>
        </div>

        <div class="showcase-bottom">
          <div class="showcase-summary">
            <div>
              <p class="netflix-category-label"><?= htmlspecialchars($label) ?></p>
              <div class="netflix-big-title"><?= htmlspecialchars($bigTitle) ?></div>
            </div>
            <p class="netflix-description"><?= htmlspecialchars($description) ?></p>
          </div>

          <div class="showcase-rail-wrap">
            <button class="showcase-nav prev" type="button" aria-label="Previous" data-showcase-nav="-1">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>

            <div class="showcase-rail">
              <?php foreach ($events as $idx => $event):
                  $sub = $event['artist'] ?? $event['company'] ?? $event['league'] ?? '';
                  $type = $event['type'] ?? $label;
                  $poster = (string) ($event['poster'] ?? '') !== '' ? (string) $event['poster'] : posterUrl($category, $idx + 10);
                  $banner = (string) ($event['banner'] ?? '') !== '' ? (string) $event['banner'] : landscapeUrl($category, $idx + 10);
                  $detailUrl = clicketEventDetailUrl($event, $category, $idx);
              ?>
                <a
                  class="showcase-card <?= $idx === 0 ? 'active' : '' ?>"
                  href="<?= htmlspecialchars($detailUrl) ?>"
                  data-type="<?= htmlspecialchars($type) ?>"
                  data-title="<?= htmlspecialchars($event['title']) ?>"
                  data-date="<?= htmlspecialchars($event['date']) ?>"
                  data-venue="<?= htmlspecialchars($event['venue']) ?>"
                  data-sub="<?= htmlspecialchars($sub) ?>"
                  data-category="<?= htmlspecialchars($label) ?>"
                  data-image="<?= htmlspecialchars($banner) ?>"
                  data-link="<?= htmlspecialchars($detailUrl) ?>"
                >
                  <img src="<?= htmlspecialchars($poster) ?>" alt="<?= htmlspecialchars($event['title']) ?>" loading="lazy">
                  <span class="showcase-card-overlay">
                    <span class="showcase-card-type"><?= htmlspecialchars($type) ?></span>
                    <strong><?= htmlspecialchars($event['title']) ?></strong>
                  </span>
                </a>
              <?php endforeach; ?>
            </div>

            <button class="showcase-nav next" type="button" aria-label="Next" data-showcase-nav="1">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
<?php }
