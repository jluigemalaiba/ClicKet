<?php
// news.php - ClicKet News Page
require_once __DIR__ . '/includes/log.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Latest news, updates, and announcements from ClicKet — your trusted ticketing platform.">
  <title>News — ClicKet</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/news.css">
  <link rel="stylesheet" href="css/partners-footer.css">
</head>
<body class="news-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- ===================== MODALS ===================== -->

<!-- Modal: Seat Maps -->
<div class="news-modal" id="modal-seat-maps" role="dialog" aria-modal="true" aria-label="Expanded support for larger venue seat maps">
  <div class="news-modal-backdrop"></div>
  <div class="news-modal-panel">
    <button class="news-modal-close" aria-label="Close article">&#10005;</button>
    <div class="news-modal-hero">
      <img src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=1400&h=700&fit=crop" alt="Concert crowd with confetti">
      <span class="news-category-badge">Platform Update</span>
    </div>
    <div class="news-modal-body">
      <div class="news-meta"><time>May 2026</time><span class="news-readtime">4 min read</span></div>
      <h2>Expanded support for larger venue seat maps</h2>
      <p class="news-modal-lead">We've rolled out major improvements to how fans and organizers interact with venue seat maps — making it faster to browse, easier to compare sections, and cleaner to manage inventory at scale.</p>
      <h3>What changed</h3>
      <p>The updated seat map engine now supports venues with up to 50,000 seats without performance degradation. Zone-level pricing overlays let fans see exact tier costs before diving into individual seat selection, reducing drop-offs during the browsing stage.</p>
      <p>Organizers benefit from a new live availability layer that highlights which sections are selling quickly. This lets event teams make real-time decisions about dynamic pricing and promotional holds without leaving the dashboard.</p>
      <h3>Faster on mobile</h3>
      <p>The map renderer has been rewritten to load in under 1.2 seconds on mid-range Android and iOS devices. Pinch-to-zoom, tap-to-select, and section tooltip previews all work smoothly at any zoom level — no more accidental seat selections when navigating large arenas.</p>
      <h3>What's next</h3>
      <p>We're working on accessible seat filtering — fans will be able to filter for wheelchair-accessible rows, companion seating, and low-obstruction sections directly from the map view. Expected in Q3 2026.</p>
    </div>
  </div>
</div>

<!-- Modal: Mobile Wallet -->
<div class="news-modal" id="modal-mobile-wallet" role="dialog" aria-modal="true" aria-label="Mobile ticket wallet improvements">
  <div class="news-modal-backdrop"></div>
  <div class="news-modal-panel">
    <button class="news-modal-close" aria-label="Close article">&#10005;</button>
    <div class="news-modal-hero">
      <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1400&h=700&fit=crop" alt="Concert crowd with phone lights">
      <span class="news-category-badge">For Fans</span>
    </div>
    <div class="news-modal-body">
      <div class="news-meta"><time>April 2026</time><span class="news-readtime">3 min read</span></div>
      <h2>Mobile ticket wallet improvements</h2>
      <p class="news-modal-lead">Getting into a show should be the easy part. We've overhauled the ClicKet mobile wallet so fans can prepare, access, and present tickets in fewer steps — even with limited connectivity.</p>
      <h3>Tickets ready before you arrive</h3>
      <p>The wallet now pre-loads QR codes up to 24 hours before an event. Fans no longer need to open the app, log in, and navigate to their ticket at the gate — a single tap from the lock screen notification opens the QR directly.</p>
      <h3>Offline support</h3>
      <p>For venues with spotty signal near entry points, tickets now cache locally as soon as they're downloaded. The QR validator at the gate works entirely offline for cached tickets, removing the most common cause of delayed entry.</p>
      <h3>Multi-ticket grouping</h3>
      <p>If you bought tickets for a group, the wallet now stacks them in a swipeable view. Scanning staff can move through your party quickly without asking you to switch screens between each ticket.</p>
      <h3>Apple Wallet & Google Wallet export</h3>
      <p>You can now export any ClicKet ticket directly to Apple Wallet or Google Wallet as a backup. The exported pass updates automatically if the event time changes.</p>
    </div>
  </div>
</div>

<!-- Modal: Analytics -->
<div class="news-modal" id="modal-analytics" role="dialog" aria-modal="true" aria-label="Organizer analytics dashboard">
  <div class="news-modal-backdrop"></div>
  <div class="news-modal-panel">
    <button class="news-modal-close" aria-label="Close article">&#10005;</button>
    <div class="news-modal-hero">
      <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1400&h=700&fit=crop" alt="Analytics dashboard screens">
      <span class="news-category-badge">For Organizers</span>
    </div>
    <div class="news-modal-body">
      <div class="news-meta"><time>March 2026</time><span class="news-readtime">5 min read</span></div>
      <h2>Organizer analytics dashboard</h2>
      <p class="news-modal-lead">Partner organizers now have a dedicated analytics view that consolidates sales data, attendance signals, and customer behavior into one place — built around the decisions event teams actually make.</p>
      <h3>Sales pace tracking</h3>
      <p>The dashboard shows a rolling sales curve compared against similar past events, so organizers can tell early whether a show is tracking ahead, on pace, or behind. Alerts are configurable — get notified when daily sales drop below a target threshold or when a tier sells out.</p>
      <h3>Category and section demand</h3>
      <p>See which ticket tiers are selling fastest, where drop-offs happen in the checkout flow, and which sections fans are browsing most without converting. These signals help teams decide when to open more inventory, adjust pricing, or push a targeted promotion.</p>
      <h3>Attendance and gate data</h3>
      <p>Once doors open, the dashboard switches to a live gate view — scans per minute, cumulative entry count, and a breakdown by ticket tier. Post-event, full attendance reports are exportable as CSV or PDF.</p>
      <h3>Access levels</h3>
      <p>Organizers can share dashboard access with venue staff, co-promoters, or sponsors at configurable permission levels. Viewer access shows charts only; editor access allows tier and inventory changes.</p>
    </div>
  </div>
</div>

<!-- Modal: Gate Scanning -->
<div class="news-modal" id="modal-gate-scanning" role="dialog" aria-modal="true" aria-label="Faster gate scanning for high-capacity venues">
  <div class="news-modal-backdrop"></div>
  <div class="news-modal-panel">
    <button class="news-modal-close" aria-label="Close article">&#10005;</button>
    <div class="news-modal-hero">
      <img src="https://images.unsplash.com/photo-1603739903239-8b6e64c3b185?w=1400&h=700&fit=crop" alt="Event entrance gate">
      <span class="news-category-badge">Platform Update</span>
    </div>
    <div class="news-modal-body">
      <div class="news-meta"><time>February 2026</time><span class="news-readtime">3 min read</span></div>
      <h2>Faster gate scanning for high-capacity venues</h2>
      <p class="news-modal-lead">Long entry queues cost fans their pre-show energy and cost organizers goodwill. We've rebuilt the QR validation stack to cut average scan time significantly at high-volume gates.</p>
      <h3>What we improved</h3>
      <p>The validator app's camera pipeline now processes QR codes in under 300ms on any device released after 2021. Recognition works at a wider range of angles and lighting conditions — including the harsh contrast of outdoor daylight gates and dark indoor corridors.</p>
      <h3>Bulk scan mode</h3>
      <p>For venues with dedicated entry staff, bulk scan mode keeps the camera live between scans with no tap required. Valid scans show a green flash; invalid or already-used tickets trigger a distinct haptic and audio cue so staff don't have to look at the screen between each person.</p>
      <h3>Offline validation</h3>
      <p>Gate devices sync the full valid-ticket manifest before doors open and validate locally. Network outages at the gate no longer cause delays. The manifest updates every 30 seconds when connected, and any invalidations from re-sales or refunds propagate automatically.</p>
    </div>
  </div>
</div>

<!-- Modal: Discovery -->
<div class="news-modal" id="modal-discovery" role="dialog" aria-modal="true" aria-label="Redesigned event discovery experience">
  <div class="news-modal-backdrop"></div>
  <div class="news-modal-panel">
    <button class="news-modal-close" aria-label="Close article">&#10005;</button>
    <div class="news-modal-hero">
      <img src="https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=1400&h=700&fit=crop" alt="Theater performance on stage">
      <span class="news-category-badge">For Fans</span>
    </div>
    <div class="news-modal-body">
      <div class="news-meta"><time>January 2026</time><span class="news-readtime">4 min read</span></div>
      <h2>Redesigned event discovery experience</h2>
      <p class="news-modal-lead">Finding the right event to go to shouldn't take longer than buying the ticket. The redesigned browse experience gives fans faster paths to what they're looking for — and better ways to stumble onto something new.</p>
      <h3>Smarter filters</h3>
      <p>Filters now include date range, city, price bracket, category, and venue type — and they update results instantly without a page reload. Saved filter presets let returning fans jump straight to their usual view.</p>
      <h3>Category collections</h3>
      <p>The homepage now surfaces curated collections — This Weekend, New Announcements, Under ₱500, and Live in Your City — based on inventory rather than manual editorial curation. Collections update automatically as new events go on sale.</p>
      <h3>Event detail improvements</h3>
      <p>Event pages now show a clear breakdown of ticket tiers, what each includes, how many remain, and whether resale is enabled. The seating chart is available inline before checkout, not behind a modal — reducing the number of steps fans take before committing to purchase.</p>
      <h3>Search upgrades</h3>
      <p>Search now matches against artist names, venue names, event descriptions, and categories simultaneously. Typo tolerance and partial matching mean fans find the right event even when they're not sure of the exact name.</p>
    </div>
  </div>
</div>

<!-- Modal: Launch -->
<div class="news-modal" id="modal-launch" role="dialog" aria-modal="true" aria-label="ClicKet officially launches in the Philippines">
  <div class="news-modal-backdrop"></div>
  <div class="news-modal-panel">
    <button class="news-modal-close" aria-label="Close article">&#10005;</button>
    <div class="news-modal-hero">
      <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1400&h=700&fit=crop" alt="Live music event crowd">
      <span class="news-category-badge">Company</span>
    </div>
    <div class="news-modal-body">
      <div class="news-meta"><time>December 2025</time><span class="news-readtime">6 min read</span></div>
      <h2>ClicKet officially launches in the Philippines</h2>
      <p class="news-modal-lead">After eight months of beta testing with a select group of venues, organizers, and fans, ClicKet is now open to everyone — fans, creators, and venues across the Philippines.</p>
      <h3>How we got here</h3>
      <p>ClicKet started as an internal tool for a small group of Manila-based event organizers who needed a better way to manage ticket distribution, gate validation, and post-event reporting in one place. What began as a logistics fix became the foundation for a full-featured ticketing platform.</p>
      <h3>Beta program results</h3>
      <p>During the beta period, ClicKet processed over 38,000 tickets across 120 events — covering concerts at mid-size Manila venues, theater runs in BGC and Makati, university events in Quezon City and Cebu, and regional sports tournaments. Gate scan success rate across all events was 99.4%.</p>
      <h3>What's available at launch</h3>
      <p>The public launch includes the full fan experience — browse, seat selection, secure checkout, mobile tickets, and gate entry — alongside the organizer dashboard with sales tracking, inventory management, and attendance reporting. The API for venue integration is available to partners under a separate application.</p>
      <h3>What comes next</h3>
      <p>Q1 2026 priorities include expanded regional coverage outside Metro Manila, installment payment options for higher-priced events, and a dedicated student verification tier for campus events. We'll share updates here as features ship.</p>
    </div>
  </div>
</div>

<!-- ===================== MAIN PAGE ===================== -->
<main class="news-main">
  <div class="container-xl px-4">

    <!-- Hero Feature Post -->
    <section class="news-hero-section" aria-label="Featured news article">
      <div class="news-hero-grid">
        <button class="news-hero-card news-trigger" data-modal="modal-seat-maps" type="button">
          <div class="news-hero-img">
            <img src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=1200&h=760&fit=crop" alt="Outdoor concert crowd with confetti" loading="eager">
            <span class="news-category-badge">Platform Update</span>
          </div>
          <div class="news-hero-body">
            <div class="news-meta">
              <time>May 2026</time>
              <span class="news-readtime">4 min read</span>
            </div>
            <h1>Expanded support for larger venue seat maps</h1>
            <p>New seat-map improvements help fans review sections faster while giving organizers cleaner inventory control. The update includes zone-level pricing overlays and live availability highlights.</p>
          </div>
        </button>

        <aside class="news-latest-list" aria-label="Latest posts">
          <h2 class="news-latest-heading">Latest posts</h2>

          <button class="news-latest-item news-trigger" data-modal="modal-mobile-wallet" type="button">
            <div class="news-latest-thumb">
              <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=300&h=220&fit=crop" alt="Concert crowd with phone lights" loading="lazy">
            </div>
            <div class="news-latest-copy">
              <h3>Mobile ticket wallet improvements</h3>
              <div class="news-meta"><time>April 2026</time><span class="news-readtime">3 min read</span></div>
            </div>
          </button>

          <button class="news-latest-item news-trigger" data-modal="modal-analytics" type="button">
            <div class="news-latest-thumb">
              <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=300&h=220&fit=crop" alt="Analytics charts" loading="lazy">
            </div>
            <div class="news-latest-copy">
              <h3>Organizer analytics dashboard</h3>
              <div class="news-meta"><time>March 2026</time><span class="news-readtime">5 min read</span></div>
            </div>
          </button>

          <button class="news-latest-item news-trigger" data-modal="modal-gate-scanning" type="button">
            <div class="news-latest-thumb">
              <img src="https://images.unsplash.com/photo-1603739903239-8b6e64c3b185?w=300&h=220&fit=crop" alt="Event entrance scanning" loading="lazy">
            </div>
            <div class="news-latest-copy">
              <h3>Faster gate scanning for high-capacity venues</h3>
              <div class="news-meta"><time>February 2026</time><span class="news-readtime">3 min read</span></div>
            </div>
          </button>

          <button class="news-latest-item news-trigger" data-modal="modal-discovery" type="button">
            <div class="news-latest-thumb">
              <img src="https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=300&h=220&fit=crop" alt="Theater performance" loading="lazy">
            </div>
            <div class="news-latest-copy">
              <h3>Redesigned event discovery experience</h3>
              <div class="news-meta"><time>January 2026</time><span class="news-readtime">4 min read</span></div>
            </div>
          </button>
        </aside>
      </div>
    </section>

    <!-- Category strip -->
    <div class="news-category-strip">
      <button class="news-cat-btn active" data-category="all">All</button>
      <button class="news-cat-btn" data-category="platform">Platform Updates</button>
      <button class="news-cat-btn" data-category="organizer">For Organizers</button>
      <button class="news-cat-btn" data-category="fans">For Fans</button>
      <button class="news-cat-btn" data-category="company">Company</button>
    </div>

    <!-- All Posts Grid -->
    <section class="news-all-section" aria-label="All news articles">
      <h2 class="news-section-label">All updates</h2>

      <div class="news-cards-grid" id="newsCardsGrid">

        <button class="news-card news-trigger" data-modal="modal-seat-maps" data-category="platform" type="button">
          <div class="news-card-img">
            <img src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=700&h=440&fit=crop" alt="Concert crowd" loading="lazy">
            <span class="news-category-badge">Platform Update</span>
          </div>
          <div class="news-card-body">
            <div class="news-meta"><time>May 2026</time><span class="news-readtime">4 min read</span></div>
            <h3>Expanded support for larger venue seat maps</h3>
            <p>New seat-map improvements help fans review sections faster while giving organizers cleaner inventory control.</p>
          </div>
        </button>

        <button class="news-card news-trigger" data-modal="modal-mobile-wallet" data-category="fans" type="button">
          <div class="news-card-img">
            <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=700&h=440&fit=crop" alt="Concert with phones" loading="lazy">
            <span class="news-category-badge">For Fans</span>
          </div>
          <div class="news-card-body">
            <div class="news-meta"><time>April 2026</time><span class="news-readtime">3 min read</span></div>
            <h3>Mobile ticket wallet improvements</h3>
            <p>Fans can prepare tickets earlier and move through venue entry with fewer check-in steps.</p>
          </div>
        </button>

        <button class="news-card news-trigger" data-modal="modal-analytics" data-category="organizer" type="button">
          <div class="news-card-img">
            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=700&h=440&fit=crop" alt="Dashboard analytics" loading="lazy">
            <span class="news-category-badge">For Organizers</span>
          </div>
          <div class="news-card-body">
            <div class="news-meta"><time>March 2026</time><span class="news-readtime">5 min read</span></div>
            <h3>Organizer analytics dashboard</h3>
            <p>Partner organizers can now review sales pace, category demand, and attendance trends in one place.</p>
          </div>
        </button>

        <button class="news-card news-trigger" data-modal="modal-gate-scanning" data-category="platform" type="button">
          <div class="news-card-img">
            <img src="https://images.unsplash.com/photo-1603739903239-8b6e64c3b185?w=700&h=440&fit=crop" alt="Gate scanning" loading="lazy">
            <span class="news-category-badge">Platform Update</span>
          </div>
          <div class="news-card-body">
            <div class="news-meta"><time>February 2026</time><span class="news-readtime">3 min read</span></div>
            <h3>Faster gate scanning for high-capacity venues</h3>
            <p>Updated QR validation cuts average scan time in half, reducing queue bottlenecks at large arenas.</p>
          </div>
        </button>

        <button class="news-card news-trigger" data-modal="modal-discovery" data-category="fans" type="button">
          <div class="news-card-img">
            <img src="https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=700&h=440&fit=crop" alt="Theater lights" loading="lazy">
            <span class="news-category-badge">For Fans</span>
          </div>
          <div class="news-card-body">
            <div class="news-meta"><time>January 2026</time><span class="news-readtime">4 min read</span></div>
            <h3>Redesigned event discovery experience</h3>
            <p>A refreshed browse and filter interface makes it easier to find concerts, theater shows, and sports events.</p>
          </div>
        </button>

        <button class="news-card news-trigger" data-modal="modal-launch" data-category="company" type="button">
          <div class="news-card-img">
            <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=700&h=440&fit=crop" alt="Live music event" loading="lazy">
            <span class="news-category-badge">Company</span>
          </div>
          <div class="news-card-body">
            <div class="news-meta"><time>December 2025</time><span class="news-readtime">6 min read</span></div>
            <h3>ClicKet officially launches in the Philippines</h3>
            <p>After months of beta testing with select venues and organizers, ClicKet opens full access to fans and event creators nationwide.</p>
          </div>
        </button>

      </div>

      <!-- Pagination -->
      <div class="news-pagination" aria-label="News pagination">
        <button class="news-page-arrow" aria-label="Previous page" disabled>&#8592;</button>
        <div class="news-page-numbers">
          <button class="news-page-btn active">1</button>
          <button class="news-page-btn">2</button>
          <button class="news-page-btn">3</button>
          <button class="news-page-btn">4</button>
          <button class="news-page-btn">5</button>
        </div>
        <button class="news-page-arrow" aria-label="Next page">&#8594;</button>
      </div>
    </section>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Navbar scroll
  (function () {
    const navbar = document.querySelector('.navbar-clicket');
    if (!navbar) return;
    function onScroll() { navbar.classList.toggle('scrolled', window.scrollY > 60); }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  })();

  // Modal system
  (function () {
    let openModal = null;

    function open(id) {
      const modal = document.getElementById(id);
      if (!modal) return;
      if (openModal) close(openModal);
      openModal = modal;
      modal.classList.add('is-open');
      modal.querySelector('.news-modal-panel').scrollTop = 0;
      document.body.classList.add('modal-open-noscroll');
      modal.querySelector('.news-modal-close').focus();
    }

    function close(modal) {
      if (!modal) return;
      modal.classList.remove('is-open');
      document.body.classList.remove('modal-open-noscroll');
      openModal = null;
    }

    // Triggers
    document.querySelectorAll('.news-trigger').forEach(el => {
      el.addEventListener('click', () => open(el.dataset.modal));
    });

    // Close button
    document.querySelectorAll('.news-modal-close').forEach(btn => {
      btn.addEventListener('click', () => close(btn.closest('.news-modal')));
    });

    // Backdrop click
    document.querySelectorAll('.news-modal-backdrop').forEach(bd => {
      bd.addEventListener('click', () => close(bd.closest('.news-modal')));
    });

    // Escape key
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && openModal) close(openModal);
    });

    // Auto-open from URL hash (e.g. news.php#modal-seat-maps)
    const hash = window.location.hash.slice(1);
    if (hash && hash.startsWith('modal-')) {
      setTimeout(() => open(hash), 150);
    }
  })();

  // Category filter
  (function () {
    const btns = document.querySelectorAll('.news-cat-btn');
    const cards = document.querySelectorAll('.news-card');
    btns.forEach(btn => {
      btn.addEventListener('click', () => {
        btns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const cat = btn.dataset.category;
        cards.forEach(card => {
          card.style.display = (cat === 'all' || card.dataset.category === cat) ? '' : 'none';
        });
      });
    });
  })();

  // Pagination (visual)
  (function () {
    const pageBtns = document.querySelectorAll('.news-page-btn');
    const prev = document.querySelector('.news-page-arrow:first-child');
    const next = document.querySelector('.news-page-arrow:last-child');
    let cur = 0;
    function setPage(i) {
      cur = Math.max(0, Math.min(pageBtns.length - 1, i));
      pageBtns.forEach((b, j) => b.classList.toggle('active', j === cur));
      prev.disabled = cur === 0;
      next.disabled = cur === pageBtns.length - 1;
    }
    pageBtns.forEach((b, i) => b.addEventListener('click', () => setPage(i)));
    prev?.addEventListener('click', () => setPage(cur - 1));
    next?.addEventListener('click', () => setPage(cur + 1));
  })();
</script>
</body>
</html>
