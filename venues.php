<?php
// venues.php - ClicKet Venues Page
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/log.php';

$venues = [
    ['file' => 'Cuneta.png', 'name' => 'Cuneta Astrodome', 'location' => 'Pasay City', 'type' => 'Arena and sports venue'],
    ['file' => 'Filoil.png', 'name' => 'Filoil EcoOil Centre', 'location' => 'San Juan City', 'type' => 'Indoor sports center'],
    ['file' => 'Metropolitan.png', 'name' => 'Metropolitan Theater', 'location' => 'Manila', 'type' => 'Historic theater'],
    ['file' => 'MOA.png', 'name' => 'MOA Arena', 'location' => 'Pasay City', 'type' => 'Concert and sports arena'],
    ['file' => 'Muntinlupa.png', 'name' => 'Muntinlupa Sports Center', 'location' => 'Muntinlupa City', 'type' => 'Community sports venue'],
    ['file' => 'Newport.png', 'name' => 'Newport Performing Arts Theater', 'location' => 'Pasay City', 'type' => 'Performing arts theater'],
    ['file' => 'Ninoy_Rizal.png', 'name' => 'Ninoy Aquino Stadium and Rizal Memorial', 'location' => 'Manila', 'type' => 'Sports complex'],
    ['file' => 'Nuvali.png', 'name' => 'Nuvali', 'location' => 'Santa Rosa, Laguna', 'type' => 'Outdoor event grounds'],
    ['file' => 'PArena.png', 'name' => 'Philippine Arena', 'location' => 'Bulacan', 'type' => 'Large-scale arena'],
    ['file' => 'Philsports.png', 'name' => 'Philsports Arena', 'location' => 'Pasig City', 'type' => 'Indoor arena'],
    ['file' => 'RWM.png', 'name' => 'Resorts World Manila', 'location' => 'Pasay City', 'type' => 'Entertainment venue'],
    ['file' => 'Samsung.png', 'name' => 'Samsung Hall', 'location' => 'Taguig City', 'type' => 'Concert hall'],
    ['file' => 'Smart.png', 'name' => 'Smart Araneta Coliseum', 'location' => 'Quezon City', 'type' => 'Coliseum and live events venue'],
    ['file' => 'Solaire.png', 'name' => 'Solaire Resort Entertainment City', 'location' => 'Paranaque City', 'type' => 'Resort theater'],
    ['file' => 'TP.png', 'name' => 'Tanghalang Pilipino', 'location' => 'Pasay City', 'type' => 'Cultural theater'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Explore ClicKet partner venues and arenas for concerts, theater plays, and sports events.">
  <title>Venues | ClicKet</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/category-pages.css">
  <link rel="stylesheet" href="css/partners-footer.css">
</head>
<body class="venues-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>
  <section class="category-hero" aria-label="Venues banner">
    <div class="category-hero-media" style="--hero-bg: url('<?= htmlspecialchars(landscapeUrl('featured', 32)) ?>');" aria-hidden="true"></div>
    <div class="container-xl px-4">
      <div class="category-hero-content">
        <p class="category-eyebrow">ClicKet Venues</p>
        <h1 class="category-hero-title">Partner <span>Venues</span></h1>
        <p class="category-hero-copy">
          Explore the arenas, theaters, halls, and event grounds that host ClicKet concerts, stage shows, and game-day moments.
        </p>
        <div class="category-hero-actions">
          <a href="#venueGrid" class="btn-primary">Browse Venues</a>
          <a href="events.php" class="btn-outline">All Events</a>
        </div>
      </div>
    </div>
  </section>

  <section class="category-content">
    <div class="container-xl px-4">
      <div class="category-listing-header" style="padding-top:56px;">
        <div>
          <p class="category-kicker">Trusted by Organizers</p>
          <h2 class="category-title">Venues <span>&amp; Arenas</span></h2>
        </div>
      </div>

      <div class="venue-grid" id="venueGrid">
        <?php foreach ($venues as $venue): ?>
          <article class="venue-card">
            <div class="venue-logo-wrap">
              <img src="assets/<?= htmlspecialchars($venue['file']) ?>" alt="<?= htmlspecialchars($venue['name']) ?> logo" loading="lazy">
            </div>
            <h3><?= htmlspecialchars($venue['name']) ?></h3>
            <p><?= htmlspecialchars($venue['type']) ?> in <?= htmlspecialchars($venue['location']) ?>.</p>
            <a href="events.php" class="venue-card-link">View Events</a>
          </article>
        <?php endforeach; ?>
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
</body>
</html>
