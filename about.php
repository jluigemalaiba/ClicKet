<?php
// about.php — ClicKet About Us Page (Refined Edition)
require_once __DIR__ . '/includes/log.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Learn about ClicKet — Your trusted ticketing platform for concerts, theater plays, and sports events with innovative seat selection technology.">
  <title>ClicKet</title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <!-- ClicKet Stylesheets -->
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/about.css">
  <link rel="stylesheet" href="css/partners-footer.css">
</head>
<body>

  <!-- ===== NAVBAR ===== -->
  <?php require_once __DIR__ . '/includes/navbar.php'; ?>

  <!-- ===== HERO SECTION ===== -->
  <section class="about-hero">
    <!-- Unsplash Dark Concert/Stadium Background Image URL: https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=1600&h=800&fit=crop&blend=https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=1600&h=800&fit=crop&blend_mode=multiply -->
    <img
      src="https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=1600&h=800&fit=crop"
      alt="Concert crowd with dynamic lighting"
      class="about-hero-bg"
      loading="lazy"
    >
    <div class="about-hero-overlay"></div>
    <div class="about-hero-content">
      <h1 class="about-hero-title">About Us</h1>
      <nav class="about-breadcrumbs" aria-label="Breadcrumb">
        <a href="index.php">Home</a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">About Us</span>
      </nav>
    </div>
  </section>

  <!-- ===== COMPANY INTRO SECTION ===== -->
  <section class="company-intro-section py-5 py-lg-6">
    <div class="container-xxl">
      <div class="row g-4 g-lg-5 align-items-center">

        <!-- Left: Image -->
        <div class="col-lg-6 mb-4 mb-lg-0">
          <div class="intro-image-container">
            <!-- Unsplash Concert/Event Image URL: https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop -->
            <img
              src="https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop"
              alt="Live concert crowd with dynamic lighting and energy"
              class="intro-image img-fluid rounded"
              loading="lazy"
            >
          </div>
        </div>

        <!-- Right: Content -->
        <div class="col-lg-6">
          <div class="intro-content">
            <span class="intro-label">About Us</span>
            <h2 class="intro-title mb-3">We Always Make The Best Event Experiences</h2>
            <p class="intro-description mb-4">
              At ClicKet, we believe every event deserves seamless access. Since day one, we've revolutionized the ticketing experience with innovative technology, transparent pricing, and customer-first design. From intimate theater productions to massive outdoor festivals, we're committed to connecting fans with the moments that matter most.
            </p>
            <div class="intro-highlights">
              <div class="highlight-item mb-3">
                <span class="highlight-icon">✓</span>
                <p class="mb-0">Real-time seat selection with interactive venue maps</p>
              </div>
              <div class="highlight-item mb-3">
                <span class="highlight-icon">✓</span>
                <p class="mb-0">Virtual queue system preventing ticket fraud and scalping</p>
              </div>
              <div class="highlight-item mb-0">
                <span class="highlight-icon">✓</span>
                <p class="mb-0">Secure payments and instant mobile ticket delivery</p>
              </div>
            </div>
            <a href="auth.php?mode=signup" class="btn btn-primary mt-4">Get Started</a>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ===== STATS & SKILLS SECTION ===== -->
  <section class="stats-skills-section py-5 py-lg-6">
    <div class="container-xxl">
      <div class="row g-4 g-lg-5">

        <!-- Left: Progress Bars (Skills) -->
        <div class="col-lg-6 mb-4 mb-lg-0">
          <div class="skills-container">
            <h3 class="skills-title mb-2">Our Operations</h3>
            <p class="skills-description mb-4">
              Trusted by leading venues and organizers across the Philippines, we maintain industry-leading standards in security, reliability, and customer satisfaction.
            </p>

            <div class="progress-bar-group">
              <div class="progress-item mb-4">
                <div class="progress-header mb-2">
                  <span class="progress-label">Ticket Availability</span>
                  <span class="progress-value">95%</span>
                </div>
                <div class="progress progress-bar">
                  <div class="progress-fill" style="width: 95%"></div>
                </div>
              </div>

              <div class="progress-item mb-4">
                <div class="progress-header mb-2">
                  <span class="progress-label">Network Security</span>
                  <span class="progress-value">98%</span>
                </div>
                <div class="progress progress-bar">
                  <div class="progress-fill" style="width: 98%"></div>
                </div>
              </div>

              <div class="progress-item mb-4">
                <div class="progress-header mb-2">
                  <span class="progress-label">Customer Satisfaction</span>
                  <span class="progress-value">94%</span>
                </div>
                <div class="progress progress-bar">
                  <div class="progress-fill" style="width: 94%"></div>
                </div>
              </div>

              <div class="progress-item">
                <div class="progress-header mb-2">
                  <span class="progress-label">Uptime Guarantee</span>
                  <span class="progress-value">99.7%</span>
                </div>
                <div class="progress progress-bar">
                  <div class="progress-fill" style="width: 99.7%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Stats Grid -->
        <div class="col-lg-6">
          <div class="row g-3">
            <div class="col-sm-6">
              <div class="stat-card h-100">
                <div class="stat-number">20+</div>
                <div class="stat-label">Partner Venues</div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="stat-card h-100">
                <div class="stat-number">1,000+</div>
                <div class="stat-label">Events Hosted</div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="stat-card h-100">
                <div class="stat-number">300k+</div>
                <div class="stat-label">Satisfied Customers</div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="stat-card h-100">
                <div class="stat-number">₱2.5B+</div>
                <div class="stat-label">Ticket Revenue Processed</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ===== THE CLICKET EXPERIENCE SECTION (WOW FACTOR) ===== -->
  <section class="experience-section py-5 py-lg-6">
    <div class="container-xxl">
      <div class="row mb-5 text-center">
        <div class="col-12">
          <h2 class="section-title mb-3">The ClicKet Experience</h2>
          <p class="section-subtitle">Three simple steps to secure your perfect seats</p>
        </div>
      </div>

      <div class="row g-4 g-lg-5">
        <!-- Step 1: Discover -->
        <div class="col-md-6 col-lg-4">
          <div class="experience-card">
            <div class="experience-icon-wrap">
              <div class="experience-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="11" cy="11" r="8"/>
                  <path d="m21 21-4.35-4.35"/>
                </svg>
              </div>
            </div>
            <h3 class="experience-step-number">01</h3>
            <h4 class="experience-title">Discover</h4>
            <p class="experience-description">
              Browse thousands of events from concerts to sports. Use our intuitive filters to find the perfect show that matches your interests and schedule.
            </p>
            <div class="experience-cta">Explore Events →</div>
          </div>
        </div>

        <!-- Step 2: Secure -->
        <div class="col-md-6 col-lg-4">
          <div class="experience-card">
            <div class="experience-icon-wrap">
              <div class="experience-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
              </div>
            </div>
            <h3 class="experience-step-number">02</h3>
            <h4 class="experience-title">Secure</h4>
            <p class="experience-description">
              Select your seats with our interactive venue maps. Lock in your choice with encrypted payment processing and instant confirmation.
            </p>
            <div class="experience-cta">Select Seats →</div>
          </div>
        </div>

        <!-- Step 3: Scan -->
        <div class="col-md-6 col-lg-4">
          <div class="experience-card">
            <div class="experience-icon-wrap">
              <div class="experience-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 4h18v7H3z"/>
                  <path d="M3 13h18v7H3z"/>
                  <path d="M6 6v1m6-1v1m6-1v1M6 15v1m6-1v1m6-1v1"/>
                </svg>
              </div>
            </div>
            <h3 class="experience-step-number">03</h3>
            <h4 class="experience-title">Scan</h4>
            <p class="experience-description">
              Receive your mobile ticket instantly. Scan at the gate with a single tap—no printing needed. Enjoy the show!
            </p>
            <div class="experience-cta">Get Tickets →</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== CTA BANNER SECTION ===== -->
  <section class="about-cta-banner">
    <!-- Unsplash Event/Crowd Image URL: https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=1600&h=600&fit=crop -->
    <img
      src="https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=1600&h=600&fit=crop"
      alt="Dynamic event atmosphere with crowd"
      class="cta-background"
      loading="lazy"
    >
    <div class="cta-overlay"></div>
    <div class="container-xxl">
      <div class="cta-content">
        <span class="cta-label">Ready to Book?</span>
        <h2 class="cta-title">We Are Always Ready To Secure Your Seats</h2>
        <a href="index.php" class="btn btn-primary">Browse Events</a>
      </div>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
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
