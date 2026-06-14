<?php

$favorites = $favorites ?? [];
?>
<section class="favorites-list" id="favoritesList" aria-live="polite">
  <?php if (!$favorites): ?>
    <div class="favorites-empty" data-favorites-empty>
      <span>
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
      </span>
      <h3>No favorites yet</h3>
      <p>Tap the heart on an event card to save it here.</p>
      <a href="events.php">Explore events</a>
    </div>
  <?php else: ?>
    <?php foreach ($favorites as $favorite): ?>
      <article class="favorite-item" data-favorite-item="<?= htmlspecialchars((string) ($favorite['event_id'] ?? '')) ?>">
        <a class="favorite-item__poster" href="<?= htmlspecialchars((string) ($favorite['url'] ?? '#')) ?>">
          <img src="<?= htmlspecialchars((string) ($favorite['poster'] ?? 'assets/Icon_Logo.png')) ?>" alt="<?= htmlspecialchars((string) ($favorite['title'] ?? 'Event')) ?> poster">
        </a>
        <div class="favorite-item__content">
          <span><?= htmlspecialchars((string) ($favorite['category'] ?? 'Event')) ?></span>
          <h3><a href="<?= htmlspecialchars((string) ($favorite['url'] ?? '#')) ?>"><?= htmlspecialchars((string) ($favorite['title'] ?? 'ClicKet Event')) ?></a></h3>
          <p><?= htmlspecialchars(trim((string) ($favorite['date'] ?? '') . ' at ' . (string) ($favorite['time'] ?? ''))) ?></p>
          <p><?= htmlspecialchars((string) ($favorite['venue'] ?? '')) ?></p>
          <div>
            <a href="<?= htmlspecialchars((string) ($favorite['url'] ?? '#')) ?>">View event</a>
            <button type="button" data-favorite-remove="<?= htmlspecialchars((string) ($favorite['event_id'] ?? '')) ?>">
              <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
              Unfavorite
            </button>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

