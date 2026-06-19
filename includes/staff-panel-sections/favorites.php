<?php
$favoriteMax = max(1, max(array_column($payload['favoriteRows'] ?: [['favorites' => 1]], 'favorites')));
?>

<section class="staff-grid-two" data-subsection="top">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Favorites</p>
        <h2>Most favorited events</h2>
      </div>
      <span><?= sp_count(count($payload['favorites'])) ?> saved records</span>
    </div>
    <div class="staff-ranked-list">
      <?php foreach ($payload['favoriteRows'] as $index => $row): ?>
        <div class="staff-ranked-row" data-search-row>
          <span><?= sp_h(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
          <div>
            <strong><?= sp_h($row['title']) ?></strong>
            <small><?= sp_h($row['venue']) ?> &middot; <?= sp_h($row['category']) ?></small>
          </div>
          <em><?= sp_count($row['favorites']) ?></em>
        </div>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="staff-card" data-subsection="trends">
    <div class="staff-card-heading">
      <div>
        <p>Favorite Trends</p>
        <h2>Popularity movement</h2>
      </div>
    </div>
    <div class="staff-horizontal-bars">
      <?php foreach (array_slice($payload['favoriteRows'], 0, 6) as $row): ?>
        <div class="staff-horizontal-bar" data-search-row>
          <span><?= sp_h($row['title']) ?></span>
          <div><em style="width: <?= sp_percent($row['favorites'], $favoriteMax) ?>%"></em></div>
          <strong>+<?= sp_count($row['trend']) ?>%</strong>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
</section>

<section class="staff-section" data-subsection="analytics">
  <div class="staff-section-heading">
    <div>
      <p>Event Popularity Analytics</p>
      <h2>Marketing and merchandising signals</h2>
    </div>
    <button class="staff-action-btn" type="button">Export Favorites</button>
  </div>
  <div class="staff-report-grid">
    <?php foreach ([
        ['Audience demand', 'Favorites compared against ticket conversion'],
        ['Pre-sale targeting', 'Users who saved but have not ordered'],
        ['Venue popularity', 'Favorite density by venue and category'],
        ['Event heat index', 'Momentum score from saved events and order velocity'],
    ] as $card): ?>
      <article class="staff-module-card">
        <span class="staff-module-icon"><?= sp_h(sp_initials($card[0])) ?></span>
        <strong><?= sp_h($card[0]) ?></strong>
        <small><?= sp_h($card[1]) ?></small>
      </article>
    <?php endforeach; ?>
  </div>
</section>
