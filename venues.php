<?php
// venues.php - ClicKet Venues Page
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/log.php';

$venues = [
    ['file' => 'Cuneta.png',     'name' => 'Cuneta Astrodome',                        'location' => 'Pasay City',         'type' => 'Arena and sports venue',       'capacity' => '~12,000'],
    ['file' => 'Filoil.png',     'name' => 'Filoil EcoOil Centre',                    'location' => 'San Juan City',      'type' => 'Indoor sports center',         'capacity' => '~5,000'],
    ['file' => 'Metropolitan.png','name' => 'Metropolitan Theater',                   'location' => 'Manila',             'type' => 'Historic theater',             'capacity' => '~1,600'],
    ['file' => 'MOA.png',        'name' => 'MOA Arena',                               'location' => 'Pasay City',         'type' => 'Concert and sports arena',     'capacity' => '~20,000'],
    ['file' => 'Muntinlupa.png', 'name' => 'Muntinlupa Sports Center',                'location' => 'Muntinlupa City',    'type' => 'Community sports venue',       'capacity' => '~3,000'],
    ['file' => 'Newport.png',    'name' => 'Newport Performing Arts Theater',         'location' => 'Pasay City',         'type' => 'Performing arts theater',      'capacity' => '~1,800'],
    ['file' => 'Ninoy_Rizal.png','name' => 'Ninoy Aquino Stadium and Rizal Memorial', 'location' => 'Manila',             'type' => 'Sports complex',               'capacity' => '~8,000'],
    ['file' => 'Nuvali.png',     'name' => 'Nuvali',                                  'location' => 'Santa Rosa, Laguna', 'type' => 'Outdoor event grounds',        'capacity' => '~30,000+'],
    ['file' => 'PArena.png',     'name' => 'Philippine Arena',                        'location' => 'Bulacan',            'type' => 'Large-scale arena',            'capacity' => '~55,000'],
    ['file' => 'Philsports.png', 'name' => 'Philsports Arena',                        'location' => 'Pasig City',         'type' => 'Indoor arena',                 'capacity' => '~15,000'],
    ['file' => 'RWM.png',        'name' => 'Resorts World Manila',                    'location' => 'Pasay City',         'type' => 'Entertainment venue',          'capacity' => '~1,500'],
    ['file' => 'Samsung.png',    'name' => 'Samsung Hall',                            'location' => 'Taguig City',        'type' => 'Concert hall',                 'capacity' => '~1,800'],
    ['file' => 'Smart.png',      'name' => 'Smart Araneta Coliseum',                  'location' => 'Quezon City',        'type' => 'Coliseum and live events venue','capacity' => '~25,000'],
    ['file' => 'Solaire.png',    'name' => 'Solaire Resort Entertainment City',       'location' => 'Paranaque City',     'type' => 'Resort theater',               'capacity' => '~1,700'],
    ['file' => 'TP.png',         'name' => 'Tanghalang Pilipino',                     'location' => 'Pasay City',         'type' => 'Cultural theater',             'capacity' => '~600'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Explore ClicKet partner venues and arenas for concerts, theater plays, and sports events.">
  <title>ClicKet</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/category-pages.css">
  <link rel="stylesheet" href="css/partners-footer.css">
  <style>
    .venue-arc-stage {
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100vw;
      min-height: 390px;
      margin-top: -22px;
      margin-left: calc(50% - 50vw);
      margin-right: calc(50% - 50vw);
      padding: 0 0 18px;
      overflow: visible;
    }
    .venue-arc-stage::after {
      content: '';
      position: absolute;
      left: -24px;
      right: -24px;
      bottom: 0;
      z-index: 3;
      height: 150px;
      pointer-events: none;
      background: linear-gradient(180deg, rgba(247,247,247,0) 0%, rgba(247,247,247,.92) 46%, var(--light-surface) 100%);
    }
    .venue-arc-wrap {
      position: relative;
      width: min(100vw, 1720px);
      height: clamp(330px, 28vw, 390px);
      flex-shrink: 0;
      overflow: visible;
      isolation: isolate;
    }
    .venue-arc-track {
      display: none;
    }
    .venue-arc-items {
      position: absolute;
      inset: 0;
      z-index: 2;
    }
    .venue-arc-logo {
      position: absolute;
      width: clamp(82px, 9vw, 118px);
      height: clamp(82px, 9vw, 118px);
      padding: 0;
      border: none;
      background: transparent;
      cursor: pointer;
      transform-origin: center center;
      transition: opacity .35s ease, filter .35s ease;
      will-change: left, top, transform;
    }
    .venue-arc-logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      border-radius: 18px;
      border: 1.5px solid rgba(0,0,0,.1);
      background: #fff;
      padding: 10px;
      display: block;
      transition: border-color .2s, transform .2s, box-shadow .2s;
      pointer-events: none;
      box-shadow: 0 12px 34px rgba(17,17,17,.12);
    }
    .venue-arc-logo:hover img,
    .venue-arc-logo.is-active img {
      border-color: rgba(232,22,43,.38);
      transform: scale(1.08);
      box-shadow: 0 18px 44px rgba(232,22,43,.18), 0 0 0 3px rgba(232,22,43,.16);
    }
    .venue-arc-logo.is-active img { box-shadow: 0 20px 48px rgba(232,22,43,.22), 0 0 0 3px var(--red-primary); }
    .venue-arc-info {
      position: absolute;
      left: 50%;
      top: clamp(150px, 20vw, 220px);
      z-index: 4;
      width: min(92vw, 640px);
      transform: translateX(-50%);
      text-align: center;
      padding: 0 18px;
      min-height: 178px;
      animation: arcFadeIn .25s ease;
    }
    @keyframes arcFadeIn {
      from { opacity: 0; transform: translateX(-50%) translateY(8px); }
      to   { opacity: 1; transform: translateX(-50%); }
    }
    .arc-info-name {
      font-family: var(--font-display);
      font-size: clamp(30px, 3.7vw, 46px);
      font-weight: 400;
      line-height: 1;
      letter-spacing: 1px;
      margin: 0 0 8px;
      text-wrap: balance;
      overflow-wrap: anywhere;
    }
    .arc-info-type { font-size: .95rem; color: var(--gray-500); margin: 0 0 16px; }
    .arc-info-meta {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
      font-size: .88rem;
      color: var(--gray-600);
      margin-bottom: 22px;
    }
    .arc-meta-item { display: flex; align-items: center; gap: 5px; }
    .arc-meta-sep  { color: #dcdbdb; }
    .arc-info-hint {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      max-width: 100%;
      margin: 0;
      color: var(--gray-600);
      font-size: .9rem;
      font-weight: 800;
      line-height: 1.45;
    }
    .arc-info-hint::before {
      content: '';
      width: 8px;
      height: 8px;
      flex: 0 0 8px;
      border-radius: 50%;
      background: var(--red-primary);
      box-shadow: 0 0 0 5px rgba(232,22,43,.1);
    }
    .arc-resume-hint { font-size: .78rem; color: var(--gray-400); margin: 10px 0 0; }
    @media (max-width: 767px) {
      .venue-arc-stage {
        min-height: 360px;
        margin-top: 0;
        padding-bottom: 16px;
      }
      .venue-arc-stage::after {
        height: 125px;
      }
      .venue-arc-wrap {
        width: calc(100vw + 80px);
        height: 292px;
        margin-inline: -40px;
      }
      .venue-arc-info {
        margin-top: 0px;
        top: 158px;
        width: min(86vw, 440px);
      }
      .arc-info-name {
        font-size: clamp(28px, 9vw, 38px);
      }
      .arc-info-meta {
        gap: 8px;
        font-size: .8rem;
      }
      .arc-meta-sep { display: none; }
    }
  </style>
</head>
<body class="venues-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>
  <section class="category-hero" aria-label="Venues banner">
    <div class="category-hero-media" style="--hero-bg: url('<?= htmlspecialchars(landscapeUrl('featured', 32)) ?>');" aria-hidden="true"></div>
    <div class="container-xl px-4">
      <div class="category-hero-content">
        <h1 class="category-hero-title">Partner <span>Venues</span></h1>
        <p class="category-hero-copy">
          Explore the arenas, theaters, halls, and event grounds that host ClicKet concerts, stage shows, and game-day moments.
        </p>
        <div class="category-hero-actions">
      <a href="#venueGrid" class="btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        Browse Venues
      </a>
          <a href="events.php" class="btn-outline">All Events</a>
        </div>
      </div>
    </div>
  </section>

  <section class="category-content">
    <div class="container-xl px-4">
      <div class="category-listing-header" style="padding-top:56px;">
        <div>
          <p class="category-kicker">Our Trusted</p>
          <h2 class="category-title">Venues <span>&amp; Arenas</span></h2>
        </div>
      </div>

      <!-- Arc Slider -->
      <div class="venue-arc-stage" id="venueGrid" aria-label="Partner venues arc slider">

        <div class="venue-arc-wrap">
          <div class="venue-arc-track" aria-hidden="true"></div>
          <div class="venue-arc-items" id="arcItems">
            <?php foreach ($venues as $i => $venue): ?>
              <button
                class="venue-arc-logo"
                type="button"
                data-index="<?= $i ?>"
                data-name="<?= htmlspecialchars($venue['name']) ?>"
                data-type="<?= htmlspecialchars($venue['type']) ?>"
                data-location="<?= htmlspecialchars($venue['location']) ?>"
                data-capacity="<?= htmlspecialchars($venue['capacity']) ?>"
                aria-label="<?= htmlspecialchars($venue['name']) ?>"
              >
                <img
                  src="assets/<?= htmlspecialchars($venue['file']) ?>"
                  alt="<?= htmlspecialchars($venue['name']) ?> logo"
                  loading="lazy"
                  draggable="false"
                >
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="venue-arc-info" id="arcInfo" aria-live="polite" hidden>
          <h3 class="arc-info-name" id="arcName"></h3>
          <p  class="arc-info-type" id="arcType"></p>
          <div class="arc-info-meta">
            <span class="arc-meta-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
              <span id="arcLocation"></span>
            </span>
            <span class="arc-meta-sep" aria-hidden="true">&middot;</span>
            <span class="arc-meta-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              <span id="arcCapacity"></span>
            </span>
          </div>
          <p class="arc-info-hint">Select this venue to view matched events</p>
        </div>

      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  const navbar = document.querySelector('.navbar-clicket');

  function handleScroll() {
    if (navbar) {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
    }
  }

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
})();
</script>
<script>
(function () {
  var items     = document.querySelectorAll('.venue-arc-logo');
  var infoPanel = document.getElementById('arcInfo');
  var arcName   = document.getElementById('arcName');
  var arcType   = document.getElementById('arcType');
  var arcLoc    = document.getElementById('arcLocation');
  var arcCap    = document.getElementById('arcCapacity');

  var wrap   = document.querySelector('.venue-arc-wrap');
  var N      = items.length;
  var VISIBLE_SLOTS = 7;
  var ARC_START = Math.PI * 1.08;
  var ARC_SPREAD = Math.PI * 0.84;

  var offset    = 0;
  var speed     = 0.0018;
  var spinning  = true;
  var activeIdx = null;
  var lastTs    = null;

  function wrapSlot(value) {
    return ((value % N) + N) % N;
  }

  function geometry() {
    var rect = wrap.getBoundingClientRect();
    var tile = items[0] ? items[0].offsetWidth : 100;
    var edgeGap = tile * 1.15;
    var rx = Math.max(460, Math.min(rect.width * 0.47, (rect.width / 2) - edgeGap));
    var ry = Math.max(210, Math.min(rect.height * 0.7, 320));
    return {
      cx: rect.width / 2,
      cy: rect.height * 0.98,
      rx: rx,
      ry: ry,
      tile: tile
    };
  }

  function layout() {
    var g = geometry();
    items.forEach(function (el, i) {
      var slot = wrapSlot(i - offset);
      var isInArc = slot <= VISIBLE_SLOTS - 1;
      var t = slot / (VISIBLE_SLOTS - 1);
      var a = ARC_START + ARC_SPREAD * t;
      var x    = g.cx + g.rx * Math.cos(a) - g.tile / 2;
      var y    = g.cy + g.ry * Math.sin(a) - g.tile / 2;
      var edgeFade = Math.min(Math.max(slot, 0), Math.max(VISIBLE_SLOTS - 1 - slot, 0), 1);
      var op = isInArc ? edgeFade : 0;

      if (!isInArc) {
        x = -9999;
        y = -9999;
      }

      el.style.left    = x + 'px';
      el.style.top     = y + 'px';
      el.style.opacity = op;
      el.style.visibility = op > 0.02 ? 'visible' : 'hidden';
      el.style.pointerEvents = isInArc && op > 0.35 ? 'auto' : 'none';
      el.style.zIndex  = Math.round((1 - Math.abs(t - 0.5)) * 20);
      el.style.transform = 'rotate(' + (-16 + 32 * t) + 'deg)';
    });
  }

  function showInfo(idx) {
    var el = items[idx];
    arcName.textContent = el.dataset.name;
    arcType.textContent = el.dataset.type;
    arcLoc.textContent  = el.dataset.location;
    arcCap.textContent  = el.dataset.capacity + ' seats';
    infoPanel.hidden    = false;
    infoPanel.style.animation = 'none';
    requestAnimationFrame(function () { infoPanel.style.animation = ''; });
  }

  function venueEventsUrl(name) {
    return 'events.php?venue=' + encodeURIComponent(name);
  }

  items.forEach(function (el) {
    function pauseOnItem() {
      var idx = parseInt(el.dataset.index, 10);
      spinning  = false;
      activeIdx = idx;
      items.forEach(function (b) { b.classList.remove('is-active'); });
      el.classList.add('is-active');
      showInfo(idx);
    }

    function resumeFromItem() {
      spinning  = true;
      activeIdx = null;
      infoPanel.hidden = true;
      items.forEach(function (b) { b.classList.remove('is-active'); });
    }

    el.addEventListener('mouseenter', pauseOnItem);
    el.addEventListener('mouseleave', resumeFromItem);
    el.addEventListener('focus', pauseOnItem);
    el.addEventListener('blur', resumeFromItem);
    el.addEventListener('click', function () {
      window.location.href = venueEventsUrl(el.dataset.name);
    });
  });

  window.addEventListener('resize', layout, { passive: true });
  function tick(ts) {
    if (lastTs !== null && spinning) {
      offset += speed * (ts - lastTs) / 16.667;
      if (offset >= N) offset -= N;
    }
    lastTs = ts;
    layout();
    requestAnimationFrame(tick);
  }

  layout();
  requestAnimationFrame(tick);
})();
</script>
</body>
</html>