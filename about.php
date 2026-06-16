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
          <h1>Ticketing built for every live moment.</h1>
          <p>
            Discover events, choose seats, pay securely, and enter venues with a clearer digital ticketing platform made for concerts, theater, and sports.
          </p>
          <div class="about-hero-actions">
            <a href="events.php" class="btn-primary">Browse Events</a>
            <a href="#mission" class="about-link-btn">Our Mission</a>
          </div>
        </div>

        <div class="about-hero-visual" aria-label="ClicKet platform preview">
          <div class="about-hero-stat about-hero-stat--left">
            <span>Tickets issued</span>
            <strong>300k+</strong>
            <small>Verified admissions</small>
          </div>

          <div class="about-laptop">
            <div class="about-laptop-screen">
              <div class="about-laptop-topbar">
                <span></span><span></span><span></span>
                <strong>ClicKet LiveOps</strong>
              </div>
              <div class="about-laptop-grid">
                <div class="about-laptop-panel about-laptop-panel--wide">
                  <span>Featured event</span>
                  <strong>Permission to Dance Manila</strong>
                  <div class="about-ticket-line"></div>
                  <div class="about-ticket-line about-ticket-line--short"></div>
                </div>
                <div class="about-laptop-panel">
                  <span>Queue status</span>
                  <strong>Live</strong>
                  <em>8,421 fans</em>
                </div>
                <div class="about-laptop-panel">
                  <span>Today sales</span>
                  <strong>PHP 1.8M</strong>
                  <em>+14%</em>
                </div>
              </div>
              <div class="about-laptop-chart" aria-hidden="true">
                <span style="height: 36%"></span>
                <span style="height: 58%"></span>
                <span style="height: 46%"></span>
                <span style="height: 72%"></span>
                <span style="height: 64%"></span>
                <span style="height: 82%"></span>
              </div>
            </div>
            <div class="about-laptop-base" aria-hidden="true"></div>
          </div>

          <div class="about-hero-stat about-hero-stat--right">
            <span>Entry checks</span>
            <strong>99.7%</strong>
            <small>Platform uptime</small>
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
          <a href="news.php#modal-seat-maps" class="news-slide">
            <div class="news-slide-img">
              <img src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=700&h=420&fit=crop" alt="Outdoor concert audience" loading="lazy">
            </div>
            <div class="news-slide-body">
              <span>May 2026</span>
              <h3>Expanded support for larger venue seat maps</h3>
              <p>New seat-map improvements help fans review sections faster while giving organizers cleaner inventory control.</p>
            </div>
          </a>

          <a href="news.php#modal-mobile-wallet" class="news-slide">
            <div class="news-slide-img">
              <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=700&h=420&fit=crop" alt="Concert crowd with phones" loading="lazy">
            </div>
            <div class="news-slide-body">
              <span>April 2026</span>
              <h3>Mobile ticket wallet improvements</h3>
              <p>Fans can prepare tickets earlier and move through venue entry with fewer check-in steps.</p>
            </div>
          </a>

          <a href="news.php#modal-analytics" class="news-slide">
            <div class="news-slide-img">
              <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=700&h=420&fit=crop" alt="Analytics dashboard" loading="lazy">
            </div>
            <div class="news-slide-body">
              <span>March 2026</span>
              <h3>Organizer analytics dashboard</h3>
              <p>Partner organizers can now review sales pace, category demand, and attendance trends in one place.</p>
            </div>
          </a>

          <a href="news.php#modal-gate-scanning" class="news-slide">
            <div class="news-slide-img">
              <img src="https://images.unsplash.com/photo-1603739903239-8b6e64c3b185?w=700&h=420&fit=crop" alt="Event gate scanning" loading="lazy">
            </div>
            <div class="news-slide-body">
              <span>February 2026</span>
              <h3>Faster gate scanning for high-capacity venues</h3>
              <p>Updated QR validation cuts average scan time in half, reducing queue bottlenecks at large arenas and stadiums.</p>
            </div>
          </a>

          <a href="news.php#modal-discovery" class="news-slide">
            <div class="news-slide-img">
              <img src="https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=700&h=420&fit=crop" alt="Theater performance" loading="lazy">
            </div>
            <div class="news-slide-body">
              <span>January 2026</span>
              <h3>Redesigned event discovery experience</h3>
              <p>A refreshed browse and filter interface makes it easier to find concerts, theater shows, and sports events by date, city, and category.</p>
            </div>
          </a>
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
