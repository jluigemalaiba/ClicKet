<?php
// includes/carousel.php — ClicKet Site Carousel
?>

<!-- ===== PARTNERS CAROUSEL ===== -->
<section class="partners-section" id="venues">
  <div class="container-xl px-4">
    <p class="partners-label">Our Trusted Venues &amp; Arenas</p>
  </div>

  <div class="partners-ticker-wrapper">
    <div class="partners-ticker" id="partnersTicker" aria-label="Partner venues and organizers">
      <?php
      $partners = [
        ['file' => 'MOA.png', 'name' => 'Mall of Asia Arena'],
        ['file' => 'Newport.png', 'name' => 'Newport Performing Arts Theater'],
        ['file' => 'PArena.png', 'name' => 'Philippine Arena'],
        ['file' => 'Philsports.png', 'name' => 'PhilSports Arena'],
        ['file' => 'Smart.png', 'name' => 'Smart Araneta Coliseum'],
        ['file' => 'Solaire.png', 'name' => 'The Theatre at Solaire'],
        ['file' => 'TP.png', 'name' => 'Tanghalang Ignacio Jimenez'],
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
