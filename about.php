<?php
// about.php - ClicKet About Page
require_once __DIR__ . '/includes/log.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Learn about ClicKet, a trusted ticketing platform for concerts, theater, sports, and live events.">
  <title>ClicKet</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/about.css">
  <link rel="stylesheet" href="css/partners-footer.css">
</head>
<body class="about-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>
  <section class="about-hero" aria-label="About ClicKet">
    <div class="container-xl px-4">
      <div class="about-hero-layout">
        <div class="about-hero-copy">
          <p class="about-kicker">ClicKet Company Profile</p>
          <h1>ONLINE TICKETING <span>PLATFORM</span></h1>
          <p>
            ClicKet helps fans discover verified events, choose seats, purchase securely, and enter venues with mobile-ready digital tickets.
          </p>
          <div class="about-hero-actions">
            <a href="events.php" class="btn-primary">Browse Events</a>
            <a href="#mission" class="about-link-btn">Learn More</a>
          </div>
        </div>

        <div class="about-hero-visual" aria-hidden="true">
          <div class="about-hero-glow about-hero-glow--one"></div>
          <div class="about-hero-glow about-hero-glow--two"></div>
          <div class="about-tag-cloud">
            <span class="about-ticket-tag tag-red tag-xl" style="--x: 18%; --y: 3%; --r: -1.5deg; --d: 0s;">Concerts</span>
            <span class="about-ticket-tag tag-white" style="--x: 58%; --y: 2%; --r: 1.8deg; --d: .35s;">Sports</span>
            <span class="about-ticket-tag tag-soft" style="--x: 38%; --y: 17.5%; --r: -2.4deg; --d: .7s;">Theater</span>
            <span class="about-ticket-tag tag-white tag-lg" style="--x: 64%; --y: 23%; --r: 2deg; --d: .15s;">Secure Checkout</span>
            <span class="about-ticket-tag tag-outline" style="--x: 8%; --y: 31%; --r: 1.6deg; --d: .95s;">E-Tickets</span>
            <span class="about-ticket-tag tag-red tag-lg" style="--x: 35%; --y: 39%; --r: -2deg; --d: .45s;">Seat Selection</span>
            <span class="about-ticket-tag tag-soft" style="--x: 73%; --y: 45%; --r: 1.3deg; --d: 1.05s;">Real-Time Booking</span>
            <span class="about-ticket-tag tag-soft" style="--x: 15%; --y: 57%; --r: -1.4deg; --d: .2s;">QR Validation</span>
            <span class="about-ticket-tag tag-outline" style="--x: 52%; --y: 64%; --r: 2.4deg; --d: .8s;">Mobile Friendly</span>
            <span class="about-ticket-tag tag-red" style="--x: 31%; --y: 76%; --r: 1.2deg; --d: 1.2s;">Fast Purchase</span>
            <span class="about-ticket-tag tag-red" style="--x: 67%; --y: 81%; --r: -1.8deg; --d: .55s;">Verified Events</span>
            <span class="about-ticket-tag tag-soft tag-lg" style="--x: 5%; --y: 85%; --r: 2deg; --d: 1.45s;">Ticket Protection</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="about-intro-section">
    <div class="container-xl px-4">
      <div class="about-intro-grid">
        <div class="about-intro-media">
          <img
            src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1000&h=850&fit=crop"
            alt="Crowd at a live music event"
            loading="lazy"
          >
        </div>

        <div class="about-intro-copy">
          <p class="about-kicker">Who We Are</p>
          <h2>Built for the moment before the lights go down.</h2>
          <p>
            ClicKet is a digital ticketing service made for Philippine event fans, venue teams, and organizers. Our platform combines event discovery, seat selection, payment, mobile tickets, and gate validation in one clear booking flow.
          </p>
          <div class="about-proof-row" aria-label="ClicKet company statistics">
            <div>
              <strong>1,000+</strong>
              <span>Events supported</span>
            </div>
            <div>
              <strong>300k+</strong>
              <span>Tickets delivered</span>
            </div>
            <div>
              <strong>99.7%</strong>
              <span>Platform uptime</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="mission-section" id="mission">
    <div class="container-xl px-4">
      <div class="mission-heading">
        <p class="about-kicker">Mission &amp; Vision</p>
        <h2>We make ticketing easier to trust, manage, and enjoy.</h2>
      </div>

      <div class="mission-layout">
        <article class="mission-feature">
          <span>01</span>
          <h3>Our Mission</h3>
          <p>
            To make live events easy to find, book, enter, and remember by removing friction from ticket purchase, payment, and venue admission.
          </p>
        </article>

        <article class="mission-feature mission-feature-dark">
          <span>02</span>
          <h3>Our Vision</h3>
          <p>
            To become the ticketing partner people choose first for concerts, theater, sports, campus events, and local productions across the Philippines.
          </p>
        </article>

        <article class="mission-feature">
          <span>03</span>
          <h3>Our Standard</h3>
          <p>
            Clear pricing, secure checkout, verified tickets, responsive support, and tools that help organizers understand demand before doors open.
          </p>
        </article>
      </div>
    </div>
  </section>

  <section class="owners-section">
    <div class="container-xl px-4">
      <div class="owners-layout">
        <div class="owners-copy">
          <p class="about-kicker">Owners</p>
          <h2>Guided by people who understand event operations.</h2>
          <p>
            ClicKet is led by a small ownership group focused on reliable technology, practical venue workflows, and better fan support.
          </p>
        </div>

        <div class="owners-list">
          <article class="owner-row">
            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=300&h=300&fit=crop" alt="Alex Herrera" loading="lazy">
            <div>
              <h3>Alex Herrera</h3>
              <p>Founder &amp; Managing Owner</p>
            </div>
          </article>
          <article class="owner-row">
            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&h=300&fit=crop" alt="Camille Reyes" loading="lazy">
            <div>
              <h3>Camille Reyes</h3>
              <p>Operations &amp; Venue Partnerships Owner</p>
            </div>
          </article>
          <article class="owner-row">
            <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=300&h=300&fit=crop" alt="Rafael Ong" loading="lazy">
            <div>
              <h3>Rafael Ong</h3>
              <p>Technology &amp; Product Owner</p>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>

  <section class="process-section">
    <div class="container-xl px-4">
      <div class="process-heading">
        <p class="about-kicker">Our Strategy</p>
        <h2>Four working principles behind every ClicKet release.</h2>
      </div>

      <div class="process-grid">
        <article>
          <span>01</span>
          <h3>Protect the ticket</h3>
          <p>Use account checks, secure payment handling, and verified mobile tickets to reduce fraud and resale confusion.</p>
        </article>
        <article>
          <span>02</span>
          <h3>Shorten the line</h3>
          <p>Design faster purchase paths and gate scanning tools so fans spend less time waiting and more time inside.</p>
        </article>
        <article>
          <span>03</span>
          <h3>Show useful data</h3>
          <p>Give organizers sales, attendance, and customer insights that support better event planning.</p>
        </article>
        <article>
          <span>04</span>
          <h3>Support the scene</h3>
          <p>Make room for major venues, school programs, independent theater, sports leagues, and local productions.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="news-section">
    <div class="container-xl px-4">
      <div class="news-heading">
        <div>
          <p class="about-kicker">News</p>
          <h2>Latest updates from ClicKet</h2>
        </div>
        <a href="news.php" class="about-link-btn">See All News</a>
      </div>

      <div class="news-slider-wrapper">
        <div class="news-slider" id="newsSlider">
          <article class="news-slide news-preview-trigger" data-news="seatMaps" role="button" tabindex="0" aria-label="Read Expanded support for larger venue seat maps">
            <div class="news-slide-img">
              <img src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=700&h=420&fit=crop" alt="Outdoor concert audience" loading="lazy">
            </div>
            <div class="news-slide-body">
              <span>May 2026</span>
              <h3>Expanded support for larger venue seat maps</h3>
              <p>New seat-map improvements help fans review sections faster while giving organizers cleaner inventory control.</p>
            </div>
          </article>

          <article class="news-slide news-preview-trigger" data-news="mobileWallet" role="button" tabindex="0" aria-label="Read Mobile ticket wallet improvements">
            <div class="news-slide-img">
              <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=700&h=420&fit=crop" alt="Concert crowd with phones" loading="lazy">
            </div>
            <div class="news-slide-body">
              <span>April 2026</span>
              <h3>Mobile ticket wallet improvements</h3>
              <p>Fans can prepare tickets earlier and move through venue entry with fewer check-in steps.</p>
            </div>
          </article>

          <article class="news-slide news-preview-trigger" data-news="analytics" role="button" tabindex="0" aria-label="Read Organizer analytics dashboard">
            <div class="news-slide-img">
              <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=700&h=420&fit=crop" alt="Analytics dashboard" loading="lazy">
            </div>
            <div class="news-slide-body">
              <span>March 2026</span>
              <h3>Organizer analytics dashboard</h3>
              <p>Partner organizers can now review sales pace, category demand, and attendance trends in one place.</p>
            </div>
          </article>

          <article class="news-slide news-preview-trigger" data-news="gateScanning" role="button" tabindex="0" aria-label="Read Faster gate scanning for high-capacity venues">
            <div class="news-slide-img">
              <img src="https://images.unsplash.com/photo-1603739903239-8b6e64c3b185?w=700&h=420&fit=crop" alt="Event gate scanning" loading="lazy">
            </div>
            <div class="news-slide-body">
              <span>February 2026</span>
              <h3>Faster gate scanning for high-capacity venues</h3>
              <p>Updated QR validation cuts average scan time in half, reducing queue bottlenecks at large arenas and stadiums.</p>
            </div>
          </article>

          <article class="news-slide news-preview-trigger" data-news="discovery" role="button" tabindex="0" aria-label="Read Redesigned event discovery experience">
            <div class="news-slide-img">
              <img src="https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=700&h=420&fit=crop" alt="Theater performance" loading="lazy">
            </div>
            <div class="news-slide-body">
              <span>January 2026</span>
              <h3>Redesigned event discovery experience</h3>
              <p>A refreshed browse and filter interface makes it easier to find concerts, theater shows, and sports events by date, city, and category.</p>
            </div>
          </article>
        </div>

        <div class="news-slider-controls">
          <button class="news-slider-btn" id="newsPrev" aria-label="Previous news">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
          </button>
          <div class="news-slider-dots" id="newsDots">
            <button class="news-dot active" aria-label="News 1"></button>
            <button class="news-dot" aria-label="News 2"></button>
            <button class="news-dot" aria-label="News 3"></button>
            <button class="news-dot" aria-label="News 4"></button>
            <button class="news-dot" aria-label="News 5"></button>
          </div>
          <button class="news-slider-btn" id="newsNext" aria-label="Next news">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </section>

  <div class="about-news-modal" id="aboutNewsModal" role="dialog" aria-modal="true" aria-labelledby="aboutNewsModalTitle" aria-hidden="true">
    <div class="about-news-modal-backdrop" data-news-close></div>
    <div class="about-news-modal-panel">
      <button class="about-news-modal-close" type="button" aria-label="Close news details" data-news-close>
        <span aria-hidden="true">&times;</span>
      </button>
      <div class="about-news-modal-hero">
        <img id="aboutNewsModalImage" src="" alt="">
        <span id="aboutNewsModalCategory" class="about-news-category"></span>
      </div>
      <div class="about-news-modal-body">
        <div class="about-news-modal-meta">
          <time id="aboutNewsModalDate"></time>
          <span id="aboutNewsModalReadTime"></span>
        </div>
        <h2 id="aboutNewsModalTitle"></h2>
        <p id="aboutNewsModalLead" class="about-news-modal-lead"></p>
        <div id="aboutNewsModalContent" class="about-news-modal-content"></div>
      </div>
    </div>
  </div>

  <section class="faq-section">
    <div class="container-xl px-4">
      <div class="faq-layout">
        <div>
          <p class="about-kicker">FAQ</p>
          <h2>Frequently asked questions</h2>
        </div>

        <div class="faq-list">
          <details open>
            <summary>What events does ClicKet support?</summary>
            <p>ClicKet supports concerts, theater productions, sports events, campus programs, and organizer-created live experiences.</p>
          </details>
          <details>
            <summary>How does ClicKet help prevent ticket issues?</summary>
            <p>We use secure checkout, verified ticket records, mobile delivery, and account-based access to make tickets easier to validate.</p>
          </details>
          <details>
            <summary>Can organizers use ClicKet for event data?</summary>
            <p>Yes. Partner organizers can monitor sales performance, attendance signals, and customer trends for smarter planning.</p>
          </details>
          <details>
            <summary>Do fans need printed tickets?</summary>
            <p>No. ClicKet is designed around mobile tickets that can be scanned at the gate.</p>
          </details>
        </div>
      </div>
    </div>
  </section>

  <section class="about-cta-banner">
    <img
      src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1800&h=800&fit=crop"
      alt="Live performance stage lights"
      class="cta-background"
      loading="lazy"
    >
    <div class="cta-overlay" aria-hidden="true"></div>
    <div class="container-xl px-4">
      <div class="cta-content">
        <p class="about-kicker">Ready to book?</p>
        <h2>Find the next show worth showing up for.</h2>
        <a href="events.php" class="btn-primary">Browse Events</a>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // News Slider
  (function () {
    const slider = document.getElementById('newsSlider');
    const dots = document.querySelectorAll('.news-dot');
    const slides = document.querySelectorAll('.news-slide');
    if (!slider || !slides.length) return;

    let current = 0;

    function goTo(index) {
      current = (index + slides.length) % slides.length;
      slider.style.transform = `translateX(-${current * 100}%)`;
      dots.forEach((d, i) => d.classList.toggle('active', i === current));
    }

    document.getElementById('newsPrev')?.addEventListener('click', () => goTo(current - 1));
    document.getElementById('newsNext')?.addEventListener('click', () => goTo(current + 1));
    dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));

    // Auto-play
    let timer = setInterval(() => goTo(current + 1), 5000);
    slider.closest('.news-slider-wrapper')?.addEventListener('mouseenter', () => clearInterval(timer));
    slider.closest('.news-slider-wrapper')?.addEventListener('mouseleave', () => {
      timer = setInterval(() => goTo(current + 1), 5000);
    });
  })();
</script>

<script>
  (function () {
    const articles = {
      seatMaps: {
        category: 'Platform Update',
        date: 'May 2026',
        readTime: '4 min read',
        title: 'Expanded support for larger venue seat maps',
        image: 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=1400&h=700&fit=crop',
        imageAlt: 'Concert crowd with confetti',
        lead: "We've rolled out major improvements to how fans and organizers interact with venue seat maps, making it faster to browse, easier to compare sections, and cleaner to manage inventory at scale.",
        sections: [
          ['What changed', 'The updated seat map engine now supports venues with up to 50,000 seats without performance degradation. Zone-level pricing overlays let fans see exact tier costs before diving into individual seat selection, reducing drop-offs during the browsing stage.'],
          ['Live availability', 'Organizers benefit from a new live availability layer that highlights which sections are selling quickly. This lets event teams make real-time decisions about dynamic pricing and promotional holds without leaving the dashboard.'],
          ['Faster on mobile', 'The map renderer has been rewritten to load quickly on mid-range Android and iOS devices. Pinch-to-zoom, tap-to-select, and section tooltip previews all work smoothly at any zoom level.'],
          ["What's next", 'Accessible seat filtering is next, including options for wheelchair-accessible rows, companion seating, and low-obstruction sections directly from the map view.']
        ]
      },
      mobileWallet: {
        category: 'For Fans',
        date: 'April 2026',
        readTime: '3 min read',
        title: 'Mobile ticket wallet improvements',
        image: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1400&h=700&fit=crop',
        imageAlt: 'Concert crowd with phone lights',
        lead: "Getting into a show should be the easy part. We've overhauled the ClicKet mobile wallet so fans can prepare, access, and present tickets in fewer steps, even with limited connectivity.",
        sections: [
          ['Tickets ready before you arrive', 'The wallet now pre-loads QR codes up to 24 hours before an event. Fans can open their tickets faster at the gate without digging through account screens.'],
          ['Offline support', "For venues with spotty signal near entry points, tickets now cache locally as soon as they're downloaded. The QR validator at the gate works entirely offline for cached tickets."],
          ['Multi-ticket grouping', 'If you bought tickets for a group, the wallet now stacks them in a swipeable view so scanning staff can move through your party quickly.'],
          ['Wallet export', 'Fans can export ClicKet tickets to Apple Wallet or Google Wallet as a backup, with pass details updating automatically if event information changes.']
        ]
      },
      analytics: {
        category: 'For Organizers',
        date: 'March 2026',
        readTime: '5 min read',
        title: 'Organizer analytics dashboard',
        image: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1400&h=700&fit=crop',
        imageAlt: 'Analytics dashboard screens',
        lead: 'Partner organizers now have a dedicated analytics view that consolidates sales data, attendance signals, and customer behavior into one place.',
        sections: [
          ['Sales pace tracking', 'The dashboard shows a rolling sales curve compared against similar past events, helping organizers see whether a show is tracking ahead, on pace, or behind.'],
          ['Category and section demand', 'Teams can review which ticket tiers are selling fastest, where checkout drop-offs happen, and which sections fans browse most without converting.'],
          ['Attendance and gate data', 'Once doors open, the dashboard switches to a live gate view with scans per minute, cumulative entry count, and breakdowns by ticket tier.'],
          ['Access levels', 'Organizers can share dashboard access with venue staff, co-promoters, or sponsors at configurable permission levels.']
        ]
      },
      gateScanning: {
        category: 'Platform Update',
        date: 'February 2026',
        readTime: '3 min read',
        title: 'Faster gate scanning for high-capacity venues',
        image: 'https://images.unsplash.com/photo-1603739903239-8b6e64c3b185?w=1400&h=700&fit=crop',
        imageAlt: 'Event entrance gate',
        lead: "Long entry queues cost fans their pre-show energy and cost organizers goodwill. We've rebuilt the QR validation stack to cut scan time at high-volume gates.",
        sections: [
          ['What we improved', 'The validator app camera pipeline recognizes QR codes faster across a wider range of angles and lighting conditions, including outdoor daylight gates and dark indoor corridors.'],
          ['Bulk scan mode', "For venues with dedicated entry staff, bulk scan mode keeps the camera live between scans. Valid scans show a green flash, while invalid tickets trigger clear haptic and audio cues."],
          ['Offline validation', 'Gate devices sync the valid-ticket manifest before doors open and validate locally, so network outages at entry points no longer cause avoidable delays.']
        ]
      },
      discovery: {
        category: 'For Fans',
        date: 'January 2026',
        readTime: '4 min read',
        title: 'Redesigned event discovery experience',
        image: 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=1400&h=700&fit=crop',
        imageAlt: 'Theater performance on stage',
        lead: "Finding the right event to go to shouldn't take longer than buying the ticket. The redesigned browse experience gives fans faster paths to what they want and better ways to discover something new.",
        sections: [
          ['Smarter filters', 'Filters now include date range, city, price bracket, category, and venue type, and they update results instantly without a page reload.'],
          ['Category collections', 'The homepage surfaces curated collections like This Weekend, New Announcements, and Live in Your City based on available inventory.'],
          ['Event detail improvements', 'Event pages now show clearer ticket tier breakdowns, remaining availability, and seating chart access before checkout.'],
          ['Search upgrades', 'Search now matches artist names, venue names, event descriptions, and categories simultaneously, with typo tolerance and partial matching.']
        ]
      }
    };

    const modal = document.getElementById('aboutNewsModal');
    const triggers = document.querySelectorAll('.news-preview-trigger');
    if (!modal || !triggers.length) return;

    const image = document.getElementById('aboutNewsModalImage');
    const category = document.getElementById('aboutNewsModalCategory');
    const date = document.getElementById('aboutNewsModalDate');
    const readTime = document.getElementById('aboutNewsModalReadTime');
    const title = document.getElementById('aboutNewsModalTitle');
    const lead = document.getElementById('aboutNewsModalLead');
    const content = document.getElementById('aboutNewsModalContent');
    let activeTrigger = null;

    function renderArticle(article) {
      image.src = article.image;
      image.alt = article.imageAlt;
      category.textContent = article.category;
      date.textContent = article.date;
      readTime.textContent = article.readTime;
      title.textContent = article.title;
      lead.textContent = article.lead;
      content.replaceChildren();

      article.sections.forEach(function (section) {
        const heading = document.createElement('h3');
        const paragraph = document.createElement('p');
        heading.textContent = section[0];
        paragraph.textContent = section[1];
        content.append(heading, paragraph);
      });
    }

    function openModal(key, trigger) {
      const article = articles[key];
      if (!article) return;
      activeTrigger = trigger;
      renderArticle(article);
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      modal.querySelector('.about-news-modal-close')?.focus();
    }

    function closeModal() {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      activeTrigger?.focus();
    }

    triggers.forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        openModal(trigger.dataset.news, trigger);
      });

      trigger.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          openModal(trigger.dataset.news, trigger);
        }
      });
    });

    modal.querySelectorAll('[data-news-close]').forEach(function (closeControl) {
      closeControl.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && modal.classList.contains('is-open')) {
        closeModal();
      }
    });
  })();
</script>

<script>
  (function () {
    const visual = document.querySelector('.about-hero-visual');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    if (!visual || reduceMotion.matches) {
      return;
    }

    function moveTags(event) {
      const rect = visual.getBoundingClientRect();
      const x = ((event.clientX - rect.left) / rect.width - 0.5) * 16;
      const y = ((event.clientY - rect.top) / rect.height - 0.5) * 16;

      visual.style.setProperty('--mx', `${x.toFixed(2)}px`);
      visual.style.setProperty('--my', `${y.toFixed(2)}px`);
    }

    function resetTags() {
      visual.style.setProperty('--mx', '0px');
      visual.style.setProperty('--my', '0px');
    }

    visual.addEventListener('pointermove', moveTags);
    visual.addEventListener('pointerleave', resetTags);
  })();
</script>

<script>
  (function () {
    const navbar = document.querySelector('.navbar-clicket');
    if (!navbar) {
      return;
    }

    function handleScroll() {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
    }

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  })();
</script>
</body>
</html>
