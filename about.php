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
    <img
      src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=1800&h=1100&fit=crop"
      alt="Fans enjoying a live concert"
      class="about-hero-bg"
      loading="eager"
    >
    <div class="about-hero-shade" aria-hidden="true"></div>

    <div class="container-xl px-4">
      <div class="about-hero-layout">
        <div class="about-hero-copy">
          <p class="about-kicker">ClicKet Company Profile</p>
          <h1>Your trusted partner in live event ticketing.</h1>
          <p>
            We help fans discover memorable concerts, theater shows, and sports events while giving organizers a reliable platform for selling, scanning, and managing tickets.
          </p>
          <div class="about-hero-actions">
            <a href="events.php" class="btn-primary">Browse Events</a>
            <a href="#mission" class="about-link-btn">Our Mission</a>
          </div>
        </div>

        <div class="about-hero-panel" aria-label="ClicKet platform highlights">
          <div>
            <span>Active categories</span>
            <strong>Concerts, Theater, Sports</strong>
          </div>
          <div>
            <span>Core promise</span>
            <strong>Secure tickets, clearer choices, faster entry</strong>
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
        <a href="events.php" class="about-link-btn">See current events</a>
      </div>

      <div class="news-grid">
        <article class="news-story news-story-large">
          <img src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=900&h=700&fit=crop" alt="Outdoor concert audience" loading="lazy">
          <div>
            <span>May 2026</span>
            <h3>Expanded support for larger venue seat maps</h3>
            <p>New seat-map improvements help fans review sections faster while giving organizers cleaner inventory control.</p>
          </div>
        </article>

        <article class="news-story">
          <span>April 2026</span>
          <h3>Mobile ticket wallet improvements</h3>
          <p>Fans can prepare tickets earlier and move through venue entry with fewer check-in steps.</p>
        </article>

        <article class="news-story">
          <span>March 2026</span>
          <h3>Organizer analytics dashboard</h3>
          <p>Partner organizers can now review sales pace, category demand, and attendance trends in one place.</p>
        </article>
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
