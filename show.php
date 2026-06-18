<?php
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/log.php';

$catalogs = [
    'concerts' => [
        'label' => 'Concert',
        'posterCategory' => 'concert',
        'events' => $concert_events,
        'timeOffset' => 0,
    ],
    'theater' => [
        'label' => 'Theater',
        'posterCategory' => 'theater',
        'events' => $theater_events,
        'timeOffset' => 1,
    ],
    'sports' => [
        'label' => 'Sports',
        'posterCategory' => 'sports',
        'events' => $sports_events,
        'timeOffset' => 2,
    ],
];

$eventKey = trim((string) ($_GET['event'] ?? ''));
if (!preg_match('/^(concerts|theater|sports)-(\d+)$/', $eventKey, $matches)) {
    header('Location: events.php');
    exit;
}

$categoryKey = $matches[1];
$eventIndex = (int) $matches[2] - 1;
$catalog = $catalogs[$categoryKey];
$event = $catalog['events'][$eventIndex] ?? null;

if (!$event) {
    http_response_code(404);
    $event = $catalog['events'][0];
    $eventIndex = 0;
    $eventKey = $categoryKey . '-1';
}

$times = ['6:00 PM', '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM'];
$eventTime = $times[($eventIndex + $catalog['timeOffset']) % count($times)];
$performer = $event['artist'] ?? $event['company'] ?? $event['league'] ?? '';
$poster = posterUrl($catalog['posterCategory'], $eventIndex + 10);
$banner = landscapeUrl($catalog['posterCategory'], $eventIndex + 10);
$eventDate = DateTimeImmutable::createFromFormat('M j, Y', $event['date']) ?: new DateTimeImmutable($event['date']);
$isMultiDay = $categoryKey === 'theater';

$venueDetails = [
    'Mall of Asia Arena' => ['address' => 'Mall of Asia Complex, Pasay City', 'image' => 'assets/moa_place.jpg'],
    'Newport Performing Arts Theater' => ['address' => 'Newport Boulevard, Pasay City', 'image' => 'assets/newport_place.jpg'],
    'Smart Araneta Coliseum' => ['address' => 'General Roxas Avenue, Cubao, Quezon City', 'image' => 'assets/Smart.png'],
    'Philippine Arena' => ['address' => 'Ciudad de Victoria, Bocaue, Bulacan', 'image' => 'assets/phil_place.jpg'],
    'The Theatre at Solaire' => ['address' => 'Entertainment City, Paranaque', 'image' => 'assets/Solaire.png'],
    'Tanghalang Ignacio Jimenez' => ['address' => 'Cultural Center of the Philippines Complex, Pasay City', 'image' => 'assets/tanghalan_place.jpg'],
    'PhilSports Arena' => ['address' => 'Capt. Henry P. Javier, Pasig City', 'image' => 'assets/Philsports.png'],
];
$venue = $venueDetails[$event['venue']] ?? [
    'address' => $event['venue'] . ', Philippines',
    'image' => $banner,
];

$synopsisTemplates = [
    'concerts' => sprintf(
        '%s brings %s to the ClicKet stage for a full-scale live show built around fan favorites, striking production, and the shared energy of a packed arena. Expect a carefully paced set, immersive visuals, and a night designed for singing along.',
        $performer ?: $event['title'],
        $event['title']
    ),
    'theater' => sprintf(
        '%s presents %s, a live theatrical production where music, storytelling, and stagecraft come together. This engagement invites audiences into a richly staged world led by an accomplished company and a memorable ensemble.',
        $performer ?: 'ClicKet',
        $event['title']
    ),
    'sports' => sprintf(
        '%s puts the action at center court for %s. Watch every decisive play, momentum swing, and championship-caliber moment live, surrounded by the atmosphere only an arena crowd can create.',
        $performer ?: 'ClicKet Sports',
        $event['title']
    ),
];
$synopsis = $synopsisTemplates[$categoryKey];

$duration = match ($categoryKey) {
    'concerts' => 'Approximately 2 hours 30 minutes',
    'theater' => 'Approximately 2 hours 20 minutes, including intermission',
    default => 'Approximately 2 to 3 hours',
};
$ageGuidance = $categoryKey === 'theater' ? 'Recommended for ages 8 and above' : 'All ages; minors must follow venue policy';
$organizer = match ($categoryKey) {
    'concerts' => 'ClicKet Live and partner promoters',
    'theater' => $performer ?: 'ClicKet Stage',
    default => $performer ?: 'ClicKet Sports',
};

$ticketPolicies = [
    ['icon' => 'limit', 'title' => 'Maximum of 4 tickets', 'note' => 'Each ClicKet account may purchase up to four tickets per event.'],
    ['icon' => 'shield', 'title' => 'Non-transferable tickets', 'note' => 'Tickets stay linked to the purchasing account to help prevent scalping.'],
    ['icon' => 'live', 'title' => 'Live seat availability', 'note' => 'Available sections and seats may change while other fans complete bookings.'],
];

$schedule = [
    ['date' => $eventDate, 'time' => $eventTime, 'label' => 'Main performance'],
];
if ($isMultiDay) {
    $schedule = [
        ['date' => $eventDate, 'time' => $eventTime, 'label' => 'Opening performance'],
        ['date' => $eventDate->modify('+1 day'), 'time' => '2:00 PM', 'label' => 'Matinee'],
        ['date' => $eventDate->modify('+1 day'), 'time' => '7:30 PM', 'label' => 'Evening performance'],
        ['date' => $eventDate->modify('+2 days'), 'time' => '3:00 PM', 'label' => 'Final performance'],
    ];
}

$relatedEvents = array_values(array_filter(
    $catalog['events'],
    static fn (array $item): bool => $item['title'] !== $event['title']
));
$relatedEvents = array_slice($relatedEvents, 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($event['title']) ?> tickets, schedule, venue, performers, and event information on ClicKet.">
  <title><?= htmlspecialchars($event['title']) ?> | ClicKet</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/partners-footer.css">
  <link rel="stylesheet" href="css/show.css">
</head>
<body class="show-page">
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>
  <section class="show-hero" style="--show-banner:url('<?= htmlspecialchars($banner) ?>')">
    <div class="show-hero-shade"></div>
    <div class="container-xl px-4 show-hero-inner">
      <nav class="show-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Home</a><span>/</span>
        <a href="events.php?category=<?= urlencode($categoryKey) ?>"><?= htmlspecialchars($catalog['label']) ?></a><span>/</span>
        <span><?= htmlspecialchars($event['title']) ?></span>
      </nav>

      <div class="show-hero-grid">
        <div class="show-poster-wrap">
          <img class="show-poster" src="<?= htmlspecialchars($poster) ?>" alt="<?= htmlspecialchars($event['title']) ?> poster">
          <span class="show-poster-tag"><?= htmlspecialchars($event['type']) ?></span>
        </div>

        <div class="show-hero-copy">
          <p class="show-eyebrow"><?= htmlspecialchars($catalog['label']) ?> presentation</p>
          <h1><?= htmlspecialchars($event['title']) ?></h1>
          <p class="show-lead"><?= htmlspecialchars($synopsis) ?></p>
          <div class="show-primary-meta">
            <div><span>Date</span><strong><?= htmlspecialchars($eventDate->format('F j, Y')) ?><?= $isMultiDay ? ' - ' . $eventDate->modify('+2 days')->format('F j, Y') : '' ?></strong></div>
            <div><span>Time</span><strong><?= htmlspecialchars($eventTime) ?></strong></div>
            <div><span>Venue</span><strong><?= htmlspecialchars($event['venue']) ?></strong></div>
          </div>
          <div class="show-hero-actions">
            <a class="show-cta show-ticket-action is-disabled" data-ticket-action aria-disabled="true" role="link">Select Tickets</a>
            <a class="show-secondary" href="#event-information">Event Information</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="show-section" id="event-information">
    <div class="container-xl px-4 show-content-grid">
      <div class="show-main-column">
        <article class="show-panel">
          <div class="show-heading-row">
            <div><p class="show-kicker"><?= $isMultiDay ? 'Performance calendar' : 'Event schedule' ?></p><h2><?= $isMultiDay ? 'Choose a performance' : 'Date and time' ?></h2></div>
            <span class="show-count"><?= count($schedule) ?> <?= count($schedule) === 1 ? 'schedule' : 'schedules' ?></span>
          </div>
          <div class="<?= $isMultiDay ? 'show-calendar' : 'show-single-date' ?>">
            <?php foreach ($schedule as $slotIndex => $slot): ?>
              <button
                type="button"
                class="show-date-card"
                data-performance-url="ticket.php?event=<?= urlencode($eventKey) ?>&amp;performance=<?= (int) $slotIndex ?>"
                aria-pressed="false"
              >
                <span class="show-date-month"><?= $slot['date']->format('M') ?></span>
                <strong class="show-date-day"><?= $slot['date']->format('d') ?></strong>
                <span class="show-date-weekday"><?= $slot['date']->format('l') ?></span>
                <span class="show-date-time"><?= htmlspecialchars($slot['time']) ?></span>
                <small><?= htmlspecialchars($slot['label']) ?></small>
              </button>
            <?php endforeach; ?>
          </div>
        </article>

        <article class="show-panel show-about">
          <p class="show-kicker">About the event</p>
          <h2>The experience</h2>
          <p><?= htmlspecialchars($synopsis) ?></p>
          <div class="show-facts">
            <div><span>Presented by</span><strong><?= htmlspecialchars($organizer) ?></strong></div>
            <div><span>Running time</span><strong><?= htmlspecialchars($duration) ?></strong></div>
            <div><span>Audience</span><strong><?= htmlspecialchars($ageGuidance) ?></strong></div>
            <div><span>Doors open</span><strong>90 minutes before showtime</strong></div>
          </div>
        </article>

        <?php if ($performer): ?>
          <article class="show-panel">
            <p class="show-kicker"><?= $categoryKey === 'sports' ? 'League and participants' : 'Cast and performers' ?></p>
            <h2><?= htmlspecialchars($performer) ?></h2>
            <div class="show-performer">
              <div class="show-performer-avatar"><?= htmlspecialchars(strtoupper(substr($performer, 0, 1))) ?></div>
              <div>
                <strong><?= htmlspecialchars($performer) ?></strong>
                <p><?= $categoryKey === 'theater' ? 'Production company and featured ensemble' : ($categoryKey === 'sports' ? 'Sanctioning league and competing athletes' : 'Headline artist with special guests') ?></p>
              </div>
            </div>
          </article>
        <?php endif; ?>

        <article class="show-panel show-venue-panel">
          <div class="show-venue-image"><img src="<?= htmlspecialchars($venue['image']) ?>" alt="<?= htmlspecialchars($event['venue']) ?>"></div>
          <div class="show-venue-copy">
            <p class="show-kicker">Venue details</p>
            <h2><?= htmlspecialchars($event['venue']) ?></h2>
            <p><?= htmlspecialchars($venue['address']) ?></p>
            <ul>
              <li>Accessible seating and guest assistance available</li>
              <li>Food and beverages subject to venue rules</li>
              <li>Arrive early for security and ticket validation</li>
            </ul>
            <a href="venues.php">View venue guide</a>
          </div>
        </article>
      </div>

      <aside class="show-sidebar">
        <div class="show-panel show-ticket-panel">
          <p class="show-kicker">Ticket information</p>
          <h2>Booking policies</h2>
          <div class="show-ticket-list show-policy-list">
            <?php foreach ($ticketPolicies as $policy): ?>
              <div class="show-ticket-tier show-policy-item">
                <span class="show-policy-icon show-policy-icon--<?= htmlspecialchars($policy['icon']) ?>" aria-hidden="true">
                  <?php if ($policy['icon'] === 'limit'): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.15" stroke-linecap="round" stroke-linejoin="round"><path d="M7 5.5h10a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2Z"/><path d="M9 9h6"/><path d="M9 13h4"/></svg>
                  <?php elseif ($policy['icon'] === 'live'): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.15" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17.5V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v9.5"/><path d="M8 18v-5"/><path d="M12 18V9"/><path d="M16 18v-7"/><path d="M3 18h18"/></svg>
                  <?php else: ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.15" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                  <?php endif; ?>
                </span>
                <div><strong><?= htmlspecialchars($policy['title']) ?></strong><span><?= htmlspecialchars($policy['note']) ?></span></div>
              </div>
            <?php endforeach; ?>
          </div>
          <p class="show-ticket-note">Ticket categories, seat availability, and pricing are shown on the seat selection page.</p>
          <a class="show-cta show-cta-block is-disabled" data-ticket-action aria-disabled="true" role="link">Buy Tickets</a>
        </div>

        <div class="show-panel show-reminders">
          <h3>Before you go</h3>
          <p>Bring a valid ID and your mobile ticket. Re-entry and bag policies vary by venue.</p>
          <a href="FAQ.php">Read ticketing FAQs</a>
        </div>
      </aside>
    </div>
  </section>

  <section class="show-related">
    <div class="container-xl px-4">
      <div class="show-heading-row">
        <div><p class="show-kicker">Keep exploring</p><h2>More in <?= htmlspecialchars($catalog['label']) ?></h2></div>
        <a href="events.php?category=<?= urlencode($categoryKey) ?>">View all</a>
      </div>
      <div class="show-related-grid">
        <?php foreach ($relatedEvents as $relatedIndex => $related):
            $actualIndex = array_search($related, $catalog['events'], true);
        ?>
          <a class="show-related-card" href="<?= htmlspecialchars(eventDetailUrl($categoryKey, (int) $actualIndex)) ?>">
            <img src="<?= htmlspecialchars(posterUrl($catalog['posterCategory'], (int) $actualIndex + 10)) ?>" alt="<?= htmlspecialchars($related['title']) ?> poster" loading="lazy">
            <span><?= htmlspecialchars($related['type']) ?></span>
            <strong><?= htmlspecialchars($related['title']) ?></strong>
            <small><?= htmlspecialchars($related['date']) ?> &middot; <?= htmlspecialchars($related['venue']) ?></small>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<aside class="show-timer" id="bookingTimer" aria-live="polite">
  <span class="show-timer-icon" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 1.5"/><path d="M9 2h6"/><path d="M12 2v3"/></svg>
  </span>
  <span><small>Booking window</small><strong id="bookingTimerValue">15:00</strong></span>
</aside>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  const navbar = document.querySelector('.navbar-clicket');
  const timerValue = document.getElementById('bookingTimerValue');
  const timer = document.getElementById('bookingTimer');
  const dateCards = document.querySelectorAll('.show-date-card[data-performance-url]');
  const ticketActions = document.querySelectorAll('[data-ticket-action]');
  const storageKey = 'clicket_event_timer_<?= htmlspecialchars($eventKey, ENT_QUOTES) ?>';
  const duration = 15 * 60 * 1000;

  const updateNavbar = () => navbar?.classList.toggle('scrolled', window.scrollY > 60);
  window.addEventListener('scroll', updateNavbar, { passive: true });
  updateNavbar();

  const setTicketActions = (url) => {
    ticketActions.forEach((action) => {
      action.href = url;
      action.classList.remove('is-disabled');
      action.setAttribute('aria-disabled', 'false');
    });
  };

  ticketActions.forEach((action) => {
    action.addEventListener('click', (event) => {
      if (action.classList.contains('is-disabled')) {
        event.preventDefault();
      }
    });
  });

  dateCards.forEach((card) => {
    card.addEventListener('click', () => {
      dateCards.forEach((item) => {
        item.classList.remove('is-selected');
        item.setAttribute('aria-pressed', 'false');
      });
      card.classList.add('is-selected');
      card.setAttribute('aria-pressed', 'true');
      setTicketActions(card.dataset.performanceUrl);
    });
  });

  let expiresAt = Number(sessionStorage.getItem(storageKey));
  if (!expiresAt || expiresAt <= Date.now()) {
    expiresAt = Date.now() + duration;
    sessionStorage.setItem(storageKey, String(expiresAt));
  }

  let interval;
  const renderTimer = () => {
    const remaining = Math.max(0, expiresAt - Date.now());
    const totalSeconds = Math.ceil(remaining / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    timerValue.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    timer.classList.toggle('is-urgent', remaining > 0 && remaining <= 2 * 60 * 1000);
    if (remaining <= 0) {
      timer.classList.add('is-expired');
      timer.querySelector('small').textContent = 'Session expired';
      window.clearInterval(interval);
    }
  };

  renderTimer();
  interval = window.setInterval(renderTimer, 1000);
})();
</script>
</body>
</html>
