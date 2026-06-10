<?php
// index.php — ClicKet Main Homepage
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/partials.php';
require_once __DIR__ . '/includes/log.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ClicKet — Buy tickets for concerts, theater plays, and sports events online. Interactive seat selection, virtual queuing, and secure payments.">
  <title>ClicKet</title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <!-- ClicKet Stylesheets -->
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/hero.css">
  <link rel="stylesheet" href="css/featured.css">
  <link rel="stylesheet" href="css/events.css">
  <link rel="stylesheet" href="css/partners-footer.css">

  <style>
    /* ── CTA Banner ──────────────────────────── */
    .cta-banner {
      padding: 80px 0;
      background: #fff;
      position: relative;
      overflow: hidden;
      border-top: 1px solid var(--gray-200);
    }
    .cta-banner::before {
      content: '';
      position: absolute; inset: 0;
      background: radial-gradient(ellipse 60% 80% at 50% 50%, rgba(232,22,43,.08) 0%, transparent 70%);
      pointer-events: none;
    }
    .cta-inner { position: relative; z-index: 2; text-align: center; }
    .cta-title {
      font-family: var(--font-display);
      font-size: clamp(32px, 5vw, 60px);
      color: var(--text-primary);
      margin-bottom: 16px;
      letter-spacing: 1px;
    }
    .cta-title span { color: var(--red-primary); }
    .cta-sub {
      font-size: 16px;
      color: var(--gray-500);
      margin-bottom: 36px;
      max-width: 460px;
      margin-left: auto; margin-right: auto;
    }
    .cta-banner .btn-outline {
      border-color: var(--gray-300);
      color: var(--text-primary);
    }
    .cta-banner .btn-outline:hover {
      border-color: var(--red-primary);
      background: rgba(232,22,43,.08);
      color: var(--red-primary);
    }
  </style>
</head>

<body>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- ═══════════════════════════════════
     HERO SECTION
════════════════════════════════════ -->
<section class="hero-section">

  <!-- Mosaic card grid background (Yat.com inspired) -->
  <div class="hero-mosaic-bg" aria-hidden="true">
    <?php
    $mosaic_seeds = [10,20,30,40,50, 60,70,80,90,100, 110,120,130,140,150];
    $mosaic_cats  = ['concert','theater','sports','featured','concert','theater','sports','featured','concert','theater','sports','featured','concert','theater','sports'];
    $mosaic_columns = array_chunk(array_map(null, $mosaic_seeds, $mosaic_cats), 3);
    foreach ($mosaic_columns as $colIndex => $column): ?>
      <div class="mosaic-col mosaic-col--<?= $colIndex + 1 ?>">
        <?php for ($loop = 0; $loop < 2; $loop++): ?>
          <?php foreach ($column as [$seed, $cat]): ?>
            <div class="mosaic-card">
              <img src="<?= posterUrl($cat, $seed) ?>" alt="" loading="lazy">
            </div>
          <?php endforeach; ?>
        <?php endfor; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="hero-content">

    <h1 class="hero-title">
      Experience<br>
      Live Events<br>
      <span class="accent">Like Never Before</span>
    </h1>

    <p class="hero-subtitle">
      Concerts, theater plays, and sports events—all in one place.
      Interactive seat selection, virtual queuing, and secure online payments.
    </p>

    <div class="hero-cta-group">
      <a href="#concerts" class="btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        Browse Events
      </a>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════
     FEATURED SLIDER
════════════════════════════════════ -->
<section class="featured-section">
  <div class="container-fluid">
    <div class="section-header px-0" style="margin-bottom:-30px;">
      <h2 class="section-title">Featured <span>Events</span></h2>
      <a href="events.php" class="see-all-btn" style="margin-bottom:-3px;">See All Events</a>
    </div>
  </div>

  <div class="featured-slider-wrapper" id="featuredSlider">
    <?php
    $total  = count($featured_events);
    $center = 0;
    foreach ($featured_events as $i => $ev):
        $pos = $i - $center;
        if ($pos > floor($total / 2)) $pos -= $total;
        if ($pos < -floor($total / 2)) $pos += $total;
        if ($pos >  3) $pos =  3;
        if ($pos < -3) $pos = -3;
        renderFeaturedCard($ev, $pos);
    endforeach;
    ?>
  </div>

  <div class="featured-controls">
    <button class="feat-ctrl-btn" id="featPrev" aria-label="Previous">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <div class="feat-dots" id="featDots">
      <?php foreach ($featured_events as $i => $ev): ?>
        <span class="feat-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></span>
      <?php endforeach; ?>
    </div>
    <button class="feat-ctrl-btn" id="featNext" aria-label="Next">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
  </div>
</section>

<!-- ═══════════════════════════════════
     CONCERTS — Netflix-style row
════════════════════════════════════ -->
<section class="cat-section" id="concerts">
  <div class="container-xl px-4">

    <div class="section-header" style="margin-bottom:20px;">
      <div style="margin-bottom:-15px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--red-primary);margin-bottom:4px;">Category</p>
        <h2 class="section-title"><span style="width: 46px; height: 46px; display: inline-block; vertical-align: middle;"><img src="assets/concerts.png" alt="ClicKet Concerts"></span> Concerts</h2>
      </div>
      <a href="#" class="see-all-btn" style="margin-bottom:-3px;">See All</a>
    </div>

    <?php renderCategoryShowcase(
      'concertsTrack',
      $concert_events,
      'concert',
      'Concert',
      'Live Music',
      'From K-Pop megastars to Eraserheads reunion shows, catch the biggest acts performing live on Philippine stages.',
      'Events',
      'concerts.php'
    ); ?>

    <div class="netflix-row legacy-event-row">
      <!-- Left meta panel -->
      <div class="netflix-meta-panel">
        <div class="netflix-big-title">Live<br>Music</div>
        <p class="netflix-description">From K-Pop megastars to Eraserheads reunion shows—catch the biggest acts performing live on Philippine stages.</p>
        <a href="concerts.php" class="see-all-btn">See All Concerts</a>
        <div class="netflix-scroll-nav">
          <button class="scroll-nav-btn" onclick="scrollTrack('concertsTrack',-1)" aria-label="Prev">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <button class="scroll-nav-btn" onclick="scrollTrack('concertsTrack',1)" aria-label="Next">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
      </div>

      <!-- Cards -->
      <div class="netflix-cards-wrapper">
        <div class="netflix-cards-track" id="concertsTrack">
          <?php foreach ($concert_events as $idx => $ev): ?>
            <div class="event-col" data-type="<?= htmlspecialchars($ev['type']) ?>">
              <?php renderEventCard($ev, 'concert', $idx); ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</section>

<div class="cat-divider"></div>


<!-- ═══════════════════════════════════
     THEATER PLAYS — Netflix-style row
════════════════════════════════════ -->
<section class="cat-section bg-alt" id="theater">
  <div class="container-xl px-4">

    <div class="section-header" style="margin-bottom:20px;">
      <div style="margin-bottom:-15px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--red-primary);margin-bottom:4px;">Category</p>
        <h2 class="section-title"><span style="width: 54px; height: 54px; display: inline-block; vertical-align: middle;"><img src="assets/theater.png" alt="ClicKet Theater"></span> Theater <span>Plays</span></h2>
      </div>
      <a href="#" class="see-all-btn" style="margin-bottom:-3px;">See All</a>
    </div>

    <?php renderCategoryShowcase(
      'theaterTrack',
      $theater_events,
      'theater',
      'Theater',
      'Stage Magic',
      'Broadway hits, beloved Filipino musicals, and world-class operas gracing the grandest stages in Manila.',
      'Shows',
      'theater.php'
    ); ?>

    <div class="netflix-row legacy-event-row">
      <div class="netflix-meta-panel">
        <div class="netflix-big-title">Stage<br>Magic</div>
        <p class="netflix-description">Broadway hits, beloved Filipino musicals, and world-class operas gracing the grandest stages in Manila.</p>
        <a href="theater.php" class="see-all-btn">See All Theater</a>
        <div class="netflix-scroll-nav">
          <button class="scroll-nav-btn" onclick="scrollTrack('theaterTrack',-1)" aria-label="Prev">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <button class="scroll-nav-btn" onclick="scrollTrack('theaterTrack',1)" aria-label="Next">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
      </div>

      <div class="netflix-cards-wrapper">
        <div class="netflix-cards-track" id="theaterTrack">
          <?php foreach ($theater_events as $idx => $ev): ?>
            <div class="event-col" data-type="<?= htmlspecialchars($ev['type']) ?>">
              <?php renderEventCard($ev, 'theater', $idx); ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</section>

<div class="cat-divider"></div>


<!-- ═══════════════════════════════════
     SPORTS EVENTS — Netflix-style row
════════════════════════════════════ -->
<section class="cat-section" id="sports">
  <div class="container-xl px-4">

    <div class="section-header" style="margin-bottom:20px;">
      <div style="margin-bottom:-15px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--red-primary);margin-bottom:4px;">Category</p>
        <h2 class="section-title"><span style="width: 50px; height: 50px; display: inline-block; vertical-align: middle;"><img src="assets/sport.png" alt="ClicKet Sports"></span> Sports <span>Events</span></h2>
      </div>
      <a href="#" class="see-all-btn" style="margin-bottom:-3px;">See All</a>
    </div>

    <?php renderCategoryShowcase(
      'sportsTrack',
      $sports_events,
      'sports',
      'Sports',
      'Sports Action',
      'Court battles, boxing bouts, football derbies, and volleyball championships, feel the energy live in the arena.',
      'Events',
      'sports.php'
    ); ?>

    <div class="netflix-row legacy-event-row">
      <div class="netflix-meta-panel">
        <div class="netflix-big-title">Sports<br>Action</div>
        <p class="netflix-description">Court battles, boxing bouts, football derbies, and volleyball championships—feel the energy live in the arena.</p>
        <a href="sports.php" class="see-all-btn">See All Sports</a>
        <div class="netflix-scroll-nav">
          <button class="scroll-nav-btn" onclick="scrollTrack('sportsTrack',-1)" aria-label="Prev">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <button class="scroll-nav-btn" onclick="scrollTrack('sportsTrack',1)" aria-label="Next">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
      </div>

      <div class="netflix-cards-wrapper">
        <div class="netflix-cards-track" id="sportsTrack">
          <?php foreach ($sports_events as $idx => $ev): ?>
            <div class="event-col" data-type="<?= htmlspecialchars($ev['type']) ?>">
              <?php renderEventCard($ev, 'sports', $idx); ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════
     CTA BANNER
════════════════════════════════════ -->
<section class="cta-banner">
  <div class="container-xl px-4">
    <div class="cta-inner">
      <h2 class="cta-title">Ready to Get Your <span>Tickets?</span></h2>
      <p class="cta-sub">Join thousands of Filipinos who trust ClicKet for a seamless, fair, and exciting live event experience.</p>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="auth.php?mode=signup" class="btn-primary" style="font-size:15px;padding:14px 36px;">Create Free Account</a>
        <a href="events.php" class="btn-outline" style="font-size:15px;padding:13px 34px;">Browse All Events</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/carousel.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function () {
  'use strict';

  /* ── Navbar scroll → pill ─────────────────────── */
  const navbar = document.querySelector('.navbar-clicket');
  function handleScroll() {
    navbar.classList.toggle('scrolled', window.scrollY > 60);
  }
  window.addEventListener('scroll', handleScroll, { passive: true });

  /* ── Mobile drawer ───────────────────────────── */
  /* ── Featured slider ─────────────────────────── */
  const slider  = document.getElementById('featuredSlider');
  const cards   = Array.from(slider ? slider.querySelectorAll('.feat-card') : []);
  const dots    = Array.from(document.querySelectorAll('.feat-dot'));
  const prevBtn = document.getElementById('featPrev');
  const nextBtn = document.getElementById('featNext');
  const total   = cards.length;
  let current   = 0;

  function goTo(idx) {
    current = ((idx % total) + total) % total;
    cards.forEach((card, i) => {
      let pos = i - current;
      if (pos >  Math.floor(total / 2)) pos -= total;
      if (pos < -Math.floor(total / 2)) pos += total;
      if (pos >  3) pos =  3;
      if (pos < -3) pos = -3;
      card.dataset.pos = pos;
    });
    dots.forEach((d, i) => d.classList.toggle('active', i === current));
  }

  if (prevBtn) prevBtn.addEventListener('click', () => goTo(current - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => goTo(current + 1));
  dots.forEach(d => d.addEventListener('click', () => goTo(+d.dataset.index)));
  setInterval(() => goTo(current + 1), 4000);

  let startX = 0;
  if (slider) {
    slider.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
    slider.addEventListener('touchend',   e => {
      const dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 50) goTo(current + (dx < 0 ? 1 : -1));
    });
  }

  /* ── Netflix filter tabs ──────────────────────── */
  document.querySelectorAll('.cat-tab').forEach(btn => {
    btn.addEventListener('click', function () {
      const gridId = this.dataset.grid;
      const filter = this.dataset.filter;
      if (!gridId) return;

      // Update active tab only within same group
      this.closest('.category-strip').querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      const track = document.getElementById(gridId);
      if (!track) return;
      track.querySelectorAll('.event-col').forEach(col => {
        const type = col.dataset.type || '';
        col.style.display = (filter === 'all' || type === filter) ? '' : 'none';
      });
    });
  });

  /* ── Netflix drag scroll ─────────────────────── */
  document.querySelectorAll('.netflix-cards-track').forEach(track => {
    let isDown = false, startPos = 0, scrollLeft = 0;
    track.addEventListener('mousedown', e => {
      isDown = true;
      startPos = e.pageX - track.offsetLeft;
      scrollLeft = track.scrollLeft;
      track.style.userSelect = 'none';
    });
    document.addEventListener('mouseup', () => { isDown = false; track.style.userSelect = ''; });
    track.addEventListener('mousemove', e => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - track.offsetLeft;
      track.scrollLeft = scrollLeft - (x - startPos) * 1.5;
    });
  });

})();

/* ── Scroll track by button ──────────────────── */
function scrollTrack(id, dir) {
  const track = document.getElementById(id);
  if (track) track.scrollBy({ left: dir * 600, behavior: 'smooth' });
}
</script>

<script>
(function () {
  'use strict';

  function stars(rating) {
    const n = Math.max(0, Math.min(5, Number(rating) || 0));
    return '★'.repeat(n) + '☆'.repeat(5 - n);
  }

  function visibleShowcaseCards(showcase) {
    return Array.from(showcase.querySelectorAll('.showcase-card')).filter(card => !card.hidden);
  }

  function activateShowcaseCard(showcase, card) {
    if (!showcase || !card) return;

    const stage = showcase.querySelector('.showcase-stage');
    const title = showcase.querySelector('.showcase-title');
    const type = showcase.querySelector('.showcase-type');
    const meta = showcase.querySelector('.showcase-meta');
    const rating = showcase.querySelector('.showcase-stars');
    const book = showcase.querySelector('.showcase-book');

    showcase.querySelectorAll('.showcase-card').forEach(item => item.classList.toggle('active', item === card));
    if (stage) {
      stage.classList.add('is-switching');
      window.setTimeout(() => {
        stage.style.setProperty('--stage-bg', `url('${card.dataset.image}')`);
        stage.classList.remove('is-switching');
      }, 90);
    }
    if (title) title.textContent = card.dataset.title || '';
    if (type) type.textContent = card.dataset.type || '';
    if (rating) rating.textContent = stars(card.dataset.rating);
    if (book) book.href = card.dataset.link || '#';
    if (meta) {
      const pieces = [card.dataset.date, card.dataset.venue, card.dataset.sub].filter(Boolean);
      meta.textContent = pieces.join(' • ');
    }

    card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
  }

  document.querySelectorAll('.category-showcase').forEach(showcase => {
    showcase.querySelectorAll('.showcase-card').forEach(card => {
      card.addEventListener('click', () => activateShowcaseCard(showcase, card));
    });

    showcase.querySelectorAll('[data-showcase-nav]').forEach(btn => {
      btn.addEventListener('click', () => {
        const cards = visibleShowcaseCards(showcase);
        if (!cards.length) return;
        const active = showcase.querySelector('.showcase-card.active:not([hidden])') || cards[0];
        const idx = Math.max(0, cards.indexOf(active));
        const next = cards[(idx + Number(btn.dataset.showcaseNav) + cards.length) % cards.length];
        activateShowcaseCard(showcase, next);
      });
    });
  });

  document.querySelectorAll('.cat-tab').forEach(btn => {
    btn.addEventListener('click', function () {
      const showcase = document.querySelector(`[data-showcase="${this.dataset.grid}"]`);
      if (!showcase) return;

      const filter = this.dataset.filter;
      showcase.querySelectorAll('.showcase-card').forEach(card => {
        const type = card.dataset.type || '';
        card.hidden = !(filter === 'all' || type === filter);
      });
      activateShowcaseCard(showcase, visibleShowcaseCards(showcase)[0]);
    });
  });
})();
</script>

</body>
</html>
