<?php
// includes/footer.php — ClicKet Site Footer
?>

<!-- ===== PARTNERS CAROUSEL ===== -->
<section class="partners-section">
  <div class="container-xl px-4">
    <p class="partners-label">Trusted by Leading Venues &amp; Organizers</p>
  </div>

  <div class="partners-ticker-wrapper">
    <div class="partners-ticker" id="partnersTicker" aria-label="Partner venues and organizers">
      <?php
      $partners = [
        ['file' => 'Cuneta.png', 'name' => 'Cuneta Astrodome'],
        ['file' => 'Filoil.png', 'name' => 'Filoil EcoOil Centre'],
        ['file' => 'Metropolitan.png', 'name' => 'Metropolitan Theater'],
        ['file' => 'MOA.png', 'name' => 'MOA Arena'],
        ['file' => 'Muntinlupa.png', 'name' => 'Muntinlupa Sports Center'],
        ['file' => 'Newport.png', 'name' => 'Newport Performing Arts Theater'],
        ['file' => 'Ninoy_Rizal.png', 'name' => 'Ninoy Aquino Stadium and Rizal Memorial'],
        ['file' => 'Nuvali.png', 'name' => 'Nuvali'],
        ['file' => 'PArena.png', 'name' => 'Philippine Arena'],
        ['file' => 'Philsports.png', 'name' => 'Philsports Arena'],
        ['file' => 'RWM.png', 'name' => 'Resorts World Manila'],
        ['file' => 'Samsung.png', 'name' => 'Samsung Hall'],
        ['file' => 'Smart.png', 'name' => 'Smart Araneta Coliseum'],
        ['file' => 'Solaire.png', 'name' => 'Solaire Resort Entertainment City'],
        ['file' => 'TP.png', 'name' => 'Tanghalang Pilipino'],
      ];
      for ($set = 0; $set < 2; $set++): ?>
        <div class="partners-set" aria-hidden="<?= $set === 1 ? 'true' : 'false' ?>">
          <?php foreach ($partners as $partner): ?>
            <div class="partner-item">
              <img
                class="partner-logo"
                src="assets/<?= htmlspecialchars($partner['file']) ?>"
                alt="<?= $set === 0 ? htmlspecialchars($partner['name']) : '' ?>"
                loading="lazy"
              >
            </div>
          <?php endforeach; ?>
        </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- ===== MAIN FOOTER ===== -->
<footer class="site-footer">
  <div class="container-xl px-4">

    <div class="footer-top">

      <!-- Brand -->
      <div class="footer-brand">
        <a href="index.php" class="footer-logo">
          <span class="logo-icon">
            <img src="assets/Icon_Logo.png" alt="" aria-hidden="true">
          </span>
          <span class="logo-name">
            <img src="assets/Name_Logo.png" alt="ClicKet">
          </span>
        </a>
        <p class="footer-tagline">
          Your one-stop web-based ticketing platform for concerts, theater plays, and sports events—with interactive seat selection, virtual queuing, and real-time booking.
        </p>
        <div class="footer-socials">
          <a href="#" class="social-icon" aria-label="Facebook">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
          </a>
          <a href="#" class="social-icon" aria-label="Twitter/X">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          </a>
          <a href="#" class="social-icon" aria-label="Instagram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          </a>
          <a href="#" class="social-icon" aria-label="TikTok">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.74a4.85 4.85 0 01-1.01-.05z"/></svg>
          </a>
        </div>
      </div>

      <!-- Explore -->
      <div>
        <h4 class="footer-col-title">Explore</h4>
        <ul class="footer-links-list">
          <li><a href="about.php">About ClicKet</a></li>
          <li><a href="#">Concerts</a></li>
          <li><a href="#">Theater Plays</a></li>
          <li><a href="#">Sports Events</a></li>
          <li><a href="#">Featured Events</a></li>
          <li><a href="#">Upcoming Shows</a></li>
          <li><a href="#">Venues &amp; Arenas</a></li>
        </ul>
      </div>

      <!-- Support -->
      <div>
        <h4 class="footer-col-title">Support</h4>
        <ul class="footer-links-list">
          <li><a href="#">Help Center</a></li>
          <li><a href="#">Booking Guide</a></li>
          <li><a href="#">Refund Policy</a></li>
          <li><a href="#">Virtual Queue FAQ</a></li>
          <li><a href="#">Contact Us</a></li>
          <li><a href="#">Terms &amp; Conditions</a></li>
        </ul>
      </div>

      <!-- Newsletter -->
      <div>
        <h4 class="footer-col-title">Stay Updated</h4>
        <p style="font-size:13px;color:var(--gray-500);line-height:1.6;margin-bottom:4px;">
          Be the first to know about new events, exclusive pre-sales, and special offers.
        </p>
        <div class="newsletter-form">
          <input type="email" class="newsletter-input" placeholder="your@email.com">
          <button class="newsletter-submit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
        </div>
        <p style="font-size:11px;color:var(--gray-600);margin-top:10px;">
          No spam, unsubscribe anytime.
        </p>
      </div>

    </div><!-- /footer-top -->

    <!-- Footer bottom -->
    <div class="footer-bottom">
      <p class="footer-copyright">
        &copy; <?= date('Y') ?> <strong>ClicKet</strong>. All rights reserved.
      </p>
      <nav class="footer-bottom-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Use</a>
        <a href="#">Cookie Policy</a>
        <a href="#">Accessibility</a>
      </nav>
    </div>

  </div>
</footer>
