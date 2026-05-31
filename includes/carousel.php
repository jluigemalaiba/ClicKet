<?php
// includes/carousel.php — ClicKet Site Carousel
?>

<!-- ===== PARTNERS CAROUSEL ===== -->
<section class="partners-section" id="venues">
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