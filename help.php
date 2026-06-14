<?php
// help.php - ClicKet Help Center
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/log.php';

$categories = [
    [
        'id' => 'event-info',
        'label' => 'Info',
        'title' => 'Event Information',
        'desc' => 'Details about shows, schedules, and venues.',
        'faqs' => [
            ['q' => 'Where can I find event details?', 'a' => 'Open the Events page and select an event to review the venue, date, time, lineup, and other availability details.'],
            ['q' => 'How do I know if an event is sold out?', 'a' => 'Sold-out events will be clearly marked on the event listing. Ticket actions will be unavailable once all seats are taken.'],
            ['q' => 'Can I see events by venue?', 'a' => 'Yes. Go to Venues, select a partner venue, and ClicKet will show matching events for that location.'],
        ],
    ],
    [
        'id' => 'booking',
        'label' => 'Book',
        'title' => 'Booking Guide',
        'desc' => 'How to reserve, buy, and manage your tickets.',
        'faqs' => [
            ['q' => 'How do I buy a ticket?', 'a' => 'Choose an event, sign in to your ClicKet account, select your ticket details, and continue to checkout.'],
            ['q' => 'Can I buy tickets without an account?', 'a' => 'No. An account is required so purchased tickets can be stored securely and recovered when needed.'],
            ['q' => 'How many tickets can I buy per event?', 'a' => 'Most events allow up to 10 tickets per transaction. High-demand events may use a lower purchase limit.'],
            ['q' => 'Will I receive confirmation after buying?', 'a' => 'Yes. Your confirmation and ticket information will be available in your account after successful checkout.'],
        ],
    ],
    [
        'id' => 'refunds',
        'label' => 'Pay',
        'title' => 'Payment and Refunds',
        'desc' => 'Accepted payment methods and refund timelines.',
        'faqs' => [
            ['q' => 'What payment methods are accepted?', 'a' => 'ClicKet supports common online payment methods such as cards, e-wallets, and bank transfer options when available.'],
            ['q' => 'Is my payment information secure?', 'a' => 'Payment details are handled through secure checkout flows. ClicKet does not display sensitive payment credentials.'],
            ['q' => 'Can I get a refund for my ticket?', 'a' => 'Refund eligibility depends on the event policy. Cancelled events are handled according to organizer and payment-provider timelines.'],
            ['q' => 'How long does a refund take?', 'a' => 'Approved refunds usually return to the original payment channel within several business days, depending on the provider.'],
        ],
    ],
    [
        'id' => 'queue',
        'label' => 'Queue',
        'title' => 'Virtual Queue FAQ',
        'desc' => 'What to expect during high-demand releases.',
        'faqs' => [
            ['q' => 'Why do some events use a queue?', 'a' => 'Queues help keep ticket releases stable and fair when many users are trying to book at the same time.'],
            ['q' => 'Does refreshing improve my queue position?', 'a' => 'No. Refreshing can interrupt your session. Stay on the queue page until it is your turn.'],
            ['q' => 'Will queued events appear on this Help Center?', 'a' => 'Queue instructions will be shown on the event page when that feature is enabled for a release.'],
        ],
    ],
    [
        'id' => 'account',
        'label' => 'Acct',
        'title' => 'Fan Account',
        'desc' => 'Managing your profile and ticket history.',
        'faqs' => [
            ['q' => 'How do I create an account?', 'a' => 'Use Sign Up in the navigation bar, enter your details, and complete the account flow.'],
            ['q' => 'I forgot my password. What should I do?', 'a' => 'Use the password recovery option on the login screen and follow the instructions sent to your registered email.'],
            ['q' => 'Where can I view my tickets?', 'a' => 'Sign in and open My Tickets from your profile menu to see active and past purchases.'],
        ],
    ],
    [
        'id' => 'technical',
        'label' => 'Tech',
        'title' => 'Technical Issues',
        'desc' => 'Troubleshooting access and ticket problems.',
        'faqs' => [
            ['q' => 'The site is not loading properly. What should I do?', 'a' => 'Try refreshing the page, clearing your browser cache, or switching to another browser or device.'],
            ['q' => 'I did not receive a confirmation email.', 'a' => 'Check your spam folder first. You can also sign in and check My Tickets for the confirmed order.'],
            ['q' => 'My QR code is not scanning.', 'a' => 'Increase your screen brightness and keep the code visible. If needed, present your ticket ID to event staff.'],
        ],
    ],
];

$popularTopics = [
    ['title' => 'Book high-demand events', 'text' => 'Learn how purchase limits, account sign-in, and future queue flows work.', 'href' => '#booking'],
    ['title' => 'Review refund options', 'text' => 'Understand cancellation handling and provider timelines.', 'href' => '#refunds'],
    ['title' => 'Find venue-based events', 'text' => 'Use venue filters to discover shows in your preferred arena.', 'href' => '#event-info'],
];

$searchQuery = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$activeCategory = isset($_GET['cat']) ? trim((string) $_GET['cat']) : '';

function helpHighlight(string $text, string $query): string {
    $safeText = htmlspecialchars($text);
    if ($query === '') {
        return $safeText;
    }

    return preg_replace('/(' . preg_quote(htmlspecialchars($query), '/') . ')/i', '<mark>$1</mark>', $safeText);
}

function helpSearchFaqs(array $categories, string $query): array {
    if ($query === '') {
        return [];
    }

    $results = [];
    $needle = strtolower($query);

    foreach ($categories as $category) {
        foreach ($category['faqs'] as $faq) {
            if (str_contains(strtolower($faq['q']), $needle) || str_contains(strtolower($faq['a']), $needle)) {
                $results[] = [
                    'category' => $category['title'],
                    'label' => $category['label'],
                    'q' => $faq['q'],
                    'a' => $faq['a'],
                ];
            }
        }
    }

    return $results;
}

function helpIcon(string $id): string {
    $icons = [
        'event-info' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16v12H4z"/><path d="M7 7V4h10v3"/><path d="M4 11h16"/><path d="M8 15h3"/><path d="M14 15h2"/><path d="M8 19v1"/><path d="M16 19v1"/></svg>',
        'booking' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6"/><path d="M9 12h6"/><path d="M9 16h3"/></svg>',
        'refunds' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/><circle cx="12" cy="14" r="2.5"/></svg>',
        'queue' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 8h10"/><path d="M7 12h10"/><path d="M7 16h6"/><path d="M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/></svg>',
        'account' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
        'technical' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L4 17v3h3l5.3-5.3a4 4 0 0 0 5.4-5.4l-2.4 2.4-3-3z"/></svg>',
    ];

    return $icons[$id] ?? $icons['event-info'];
}

$searchResults = helpSearchFaqs($categories, $searchQuery);
$displayCategories = $activeCategory !== ''
    ? array_values(array_filter($categories, static fn ($category) => $category['id'] === $activeCategory))
    : $categories;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Get help with ClicKet ticket booking, refunds, virtual queues, venues, accounts, and technical issues.">
  <title>ClicKet Help Center</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/partners-footer.css">

  <style>
    body.help-page {
      background: var(--light-bg);
      color: var(--text-primary);
    }

    .help-hero {
      position: relative;
      padding: 158px 0 68px;
      overflow: hidden;
      background:
        radial-gradient(circle at 88% 0%, rgba(232,22,43,.065) 0 260px, transparent 261px),
        radial-gradient(circle at 21% 100%, rgba(232,22,43,.065) 0 190px, transparent 191px),
        linear-gradient(180deg, #fff 0%, var(--light-bg) 100%);
      border-bottom: 1px solid var(--gray-200);
    }

    .help-hero-inner {
      display: grid;
      grid-template-columns: minmax(0, 1.05fr) minmax(300px, .75fr);
      gap: 44px;
      align-items: end;
    }

    .help-eyebrow,
    .help-section-kicker {
      margin: 0 0 10px;
      color: var(--red-primary);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .help-title {
      margin: 0;
      max-width: 760px;
      font-family: var(--font-display);
      font-size: clamp(48px, 7vw, 82px);
      line-height: .92;
      letter-spacing: 1px;
    }

    .help-title span {
      color: var(--red-primary);
    }

    .help-copy {
      max-width: 640px;
      margin: 22px 0 0;
      color: var(--gray-600);
      font-size: 16px;
      line-height: 1.7;
    }

    .help-search {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 10px;
      margin-top: 30px;
      max-width: 680px;
    }

    .help-search input {
      width: 100%;
      min-height: 50px;
      padding: 0 16px;
      border: 1.5px solid var(--gray-200);
      border-radius: 12px;
      background: #fff;
      color: var(--text-primary);
      font-size: 14px;
      font-weight: 600;
      outline: none;
    }

    .help-search input:focus {
      border-color: var(--red-primary);
      box-shadow: 0 0 0 4px rgba(232,22,43,.1);
    }

    .help-search button,
    .help-contact-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 50px;
      padding: 0 24px;
      border: 0;
      border-radius: var(--btn-radius);
      background: var(--red-primary);
      color: #fff;
      font-size: 13px;
      font-weight: 800;
      white-space: nowrap;
      transition: background var(--dur-fast), transform var(--dur-fast), box-shadow var(--dur-fast);
    }

    .help-search button:hover,
    .help-contact-btn:hover {
      background: var(--red-light);
      color: #fff;
      transform: translateY(-2px);
      box-shadow: var(--glow-red);
    }

    .help-quick-panel {
      padding: 24px;
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-card);
    }

    .help-quick-panel h2 {
      margin: 0 0 16px;
      font-size: 18px;
      font-weight: 900;
    }

    .help-topic-list {
      display: grid;
      gap: 10px;
    }

    .help-topic {
      display: block;
      padding: 12px 0;
      border-bottom: 1px solid var(--gray-200);
      color: inherit;
    }

    .help-topic:last-child {
      border-bottom: 0;
    }

    .help-topic strong {
      display: block;
      margin-bottom: 3px;
      font-size: 14px;
      font-weight: 900;
    }

    .help-topic span {
      display: block;
      color: var(--gray-500);
      font-size: 13px;
      line-height: 1.45;
    }

    .help-content {
      padding: 48px 0 72px;
    }

    .help-section-header {
      margin-bottom: 22px;
    }

    .help-section-title {
      margin: 0;
      font-family: var(--font-display);
      font-size: 44px;
      line-height: 1;
      letter-spacing: 1px;
    }

    .help-section-title span {
      color: var(--red-primary);
    }

    .help-category-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 16px;
      margin-bottom: 42px;
    }

    .help-category-card {
      display: grid;
      grid-template-columns: 50px minmax(0, 1fr) 34px;
      gap: 16px;
      align-items: center;
      min-height: 112px;
      padding: 18px 20px;
      border: 1px solid var(--light-border);
      border-radius: 8px;
      background: #fff;
      color: inherit;
      box-shadow: var(--shadow-sm);
      transition: border-color var(--dur-fast), transform var(--dur-fast), box-shadow var(--dur-fast);
    }

    .help-category-card:hover,
    .help-category-card.is-active {
      border-color: rgba(232,22,43,.35);
      transform: translateY(-3px);
      box-shadow: 0 14px 34px rgba(17,17,17,.1);
    }

    .help-category-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      color: var(--red-primary);
    }

    .help-category-icon svg {
      width: 42px;
      height: 42px;
      stroke: currentColor;
    }

    .help-category-card strong {
      display: block;
      margin-bottom: 5px;
      font-size: 15px;
      font-weight: 900;
    }

    .help-category-card span {
      color: var(--gray-500);
      font-size: 13px;
      line-height: 1.45;
    }

    .help-category-arrow {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 34px;
      height: 34px;
      border: 1.5px solid var(--gray-300);
      border-radius: 50%;
      color: var(--text-primary);
      transition: border-color var(--dur-fast), color var(--dur-fast), transform var(--dur-fast);
    }

    .help-category-arrow svg {
      width: 15px;
      height: 15px;
      stroke: currentColor;
    }

    .help-category-card:hover .help-category-arrow,
    .help-category-card.is-active .help-category-arrow {
      border-color: var(--red-primary);
      color: var(--red-primary);
      transform: translateX(2px);
    }

    .help-results,
    .help-faq-group,
    .help-policy-block,
    .help-contact-panel {
      margin-top: 28px;
    }

    .help-result-card,
    .help-faq-item,
    .help-policy-block,
    .help-contact-panel {
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-sm);
    }

    .help-result-card {
      padding: 18px;
      margin-bottom: 12px;
    }

    .help-result-badge {
      display: inline-flex;
      margin-bottom: 9px;
      padding: 4px 9px;
      border-radius: 999px;
      background: rgba(232,22,43,.1);
      color: var(--red-primary);
      font-size: 10px;
      font-weight: 900;
      letter-spacing: .7px;
      text-transform: uppercase;
    }

    .help-result-card h3 {
      margin: 0 0 6px;
      font-size: 16px;
      font-weight: 900;
    }

    .help-result-card p,
    .help-empty {
      margin: 0;
      color: var(--gray-600);
      font-size: 14px;
      line-height: 1.65;
    }

    mark {
      padding: 0 2px;
      border-radius: 3px;
      background: rgba(232,22,43,.14);
      color: inherit;
    }

    .help-faq-group {
      scroll-margin-top: 96px;
    }

    .help-faq-heading {
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 0 0 14px;
      font-size: 20px;
      font-weight: 900;
    }

    .help-faq-heading span {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      border-radius: 10px;
      background: rgba(232,22,43,.1);
      color: var(--red-primary);
    }

    .help-faq-heading span svg {
      width: 23px;
      height: 23px;
      stroke: currentColor;
    }

    .help-faq-item {
      overflow: hidden;
      margin-bottom: 10px;
    }

    .help-faq-question {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      padding: 17px 18px;
      border: 0;
      background: transparent;
      color: var(--text-primary);
      font-size: 15px;
      font-weight: 850;
      text-align: left;
    }

    .help-faq-question:hover,
    .help-faq-question.is-open {
      background: var(--gray-100);
    }

    .help-faq-chevron {
      width: 14px;
      height: 14px;
      flex: 0 0 14px;
      stroke: var(--red-primary);
      transition: transform var(--dur-fast);
    }

    .help-faq-question.is-open .help-faq-chevron {
      transform: rotate(180deg);
    }

    .help-faq-answer {
      display: none;
      padding: 0 18px 18px;
      color: var(--gray-600);
      font-size: 14px;
      line-height: 1.7;
    }

    .help-faq-answer.is-open {
      display: block;
    }

    .help-policy-block {
      padding: 22px;
      scroll-margin-top: 96px;
    }

    .help-policy-block h2,
    .help-contact-panel h2 {
      margin: 0 0 8px;
      font-size: 22px;
      font-weight: 900;
    }

    .help-policy-block p,
    .help-contact-panel p {
      margin: 0;
      color: var(--gray-600);
      font-size: 14px;
      line-height: 1.7;
    }

    .help-contact-panel {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 24px;
      padding: 24px;
      scroll-margin-top: 96px;
    }

    @media (max-width: 991px) {
      .help-hero-inner {
        grid-template-columns: 1fr;
      }

      .help-category-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 640px) {
      .help-hero {
        padding: 132px 0 48px;
        background:
          radial-gradient(circle at 108% 2%, rgba(232,22,43,.065) 0 150px, transparent 151px),
          radial-gradient(circle at 8% 100%, rgba(232,22,43,.065) 0 115px, transparent 116px),
          linear-gradient(180deg, #fff 0%, var(--light-bg) 100%);
      }

      .help-search,
      .help-contact-panel {
        grid-template-columns: 1fr;
        flex-direction: column;
        align-items: stretch;
      }

      .help-category-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body class="help-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>
  <section class="help-hero">
    <div class="container-xl px-4">
      <div class="help-hero-inner">
        <div>
          <p class="help-eyebrow">ClicKet Support</p>
          <h1 class="help-title">How can we <span>help?</span></h1>
          <p class="help-copy">
            Find clear answers for ticket booking, payment concerns, venue details, account access, and high-demand event releases.
          </p>
          <form class="help-search" method="get" action="help.php">
            <input type="search" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search booking, refunds, tickets, venues..." autocomplete="off">
            <button type="submit">Search</button>
          </form>
        </div>

        <aside class="help-quick-panel" aria-label="Popular support topics">
          <h2>Common Requests</h2>
          <div class="help-topic-list">
            <?php foreach ($popularTopics as $topic): ?>
              <a href="<?= htmlspecialchars($topic['href']) ?>" class="help-topic">
                <strong><?= htmlspecialchars($topic['title']) ?></strong>
                <span><?= htmlspecialchars($topic['text']) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="help-content">
    <div class="container-xl px-4">
      <?php if ($searchQuery !== ''): ?>
        <div class="help-section-header">
          <p class="help-section-kicker">Search Results</p>
          <h2 class="help-section-title">Matched <span>Answers</span></h2>
        </div>

        <div class="help-results">
          <?php if ($searchResults): ?>
            <?php foreach ($searchResults as $result): ?>
              <article class="help-result-card">
                <span class="help-result-badge"><?= htmlspecialchars($result['label']) ?> · <?= htmlspecialchars($result['category']) ?></span>
                <h3><?= helpHighlight($result['q'], $searchQuery) ?></h3>
                <p><?= helpHighlight($result['a'], $searchQuery) ?></p>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="help-empty">No matching answers found. Try a different keyword or browse the categories below.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="help-section-header">
        <p class="help-section-kicker">Browse Support</p>
        <h2 class="help-section-title">Help <span>Categories</span></h2>
      </div>

      <div class="help-category-grid">
        <?php foreach ($categories as $category): ?>
          <a href="help.php?cat=<?= urlencode($category['id']) ?>#<?= htmlspecialchars($category['id']) ?>" class="help-category-card <?= $activeCategory === $category['id'] ? 'is-active' : '' ?>">
            <span class="help-category-icon"><?= helpIcon($category['id']) ?></span>
            <span>
              <strong><?= htmlspecialchars($category['title']) ?></strong>
              <span><?= htmlspecialchars($category['desc']) ?></span>
            </span>
            <span class="help-category-arrow" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
          </a>
        <?php endforeach; ?>
      </div>

      <?php foreach ($displayCategories as $category): ?>
        <section class="help-faq-group" id="<?= htmlspecialchars($category['id']) ?>">
          <h2 class="help-faq-heading">
            <span><?= helpIcon($category['id']) ?></span>
            <?= htmlspecialchars($category['title']) ?>
          </h2>

          <?php foreach ($category['faqs'] as $index => $faq): ?>
            <article class="help-faq-item">
              <button class="help-faq-question" type="button" aria-expanded="false">
                <?= htmlspecialchars($faq['q']) ?>
                <svg class="help-faq-chevron" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div class="help-faq-answer">
                <?= htmlspecialchars($faq['a']) ?>
              </div>
            </article>
          <?php endforeach; ?>
        </section>
      <?php endforeach; ?>

      <section class="help-policy-block" id="terms">
        <h2>Terms and Conditions</h2>
        <p>
          ClicKet tickets are subject to event organizer rules, venue policies, identity checks, and payment-provider conditions.
          Keep your account details accurate and review each event page before checkout.
        </p>
      </section>

      <section class="help-contact-panel" id="contact">
        <div>
          <h2>Still need help?</h2>
          <p>Contact ClicKet support for account, payment, venue, or ticket concerns.</p>
        </div>
        <a href="contact.php" class="help-contact-btn">Contact Support</a>
      </section>
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

  document.querySelectorAll('.help-faq-question').forEach(button => {
    button.addEventListener('click', () => {
      const answer = button.nextElementSibling;
      const isOpen = button.classList.contains('is-open');

      document.querySelectorAll('.help-faq-question.is-open').forEach(openButton => {
        openButton.classList.remove('is-open');
        openButton.setAttribute('aria-expanded', 'false');
        openButton.nextElementSibling.classList.remove('is-open');
      });

      if (!isOpen) {
        button.classList.add('is-open');
        button.setAttribute('aria-expanded', 'true');
        answer.classList.add('is-open');
      }
    });
  });

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
})();
</script>
</body>
</html>
