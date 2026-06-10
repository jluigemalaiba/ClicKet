<?php
// includes/footer.php — ClicKet Site Footer
?>

<!-- ===== MAIN FOOTER ===== -->
<footer class="site-footer">
  <div class="container-xl px-4">

    <div class="footer-top">

      <!-- Brand -->
      <div class="footer-brand">
        <a href="#top" class="footer-logo" data-scroll-top>
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
          <li><a href="index.php">Home</a></li>
          <li><a href="events.php">Events</a></li>
          <li><a href="venues.php">Venues &amp; Arenas</a></li>
          <li><a href="about.php">About Us</a></li>
        </ul>
      </div>

      <!-- Support -->
      <div>
        <h4 class="footer-col-title">Support</h4>
        <ul class="footer-links-list">
          <li><a href="help.php">Help Center</a></li>
          <li><a href="help.php#queue">Virtual Queue FAQ</a></li>
          <li><a href="contact.php">Contact Us</a></li>
          <li><a href="terms.php">Terms &amp; Conditions</a></li>
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
        <a href="terms.php">Terms of Use</a>
        <a href="#">Cookie Policy</a>
        <a href="#">Accessibility</a>
      </nav>
    </div>

  </div>
</footer>
