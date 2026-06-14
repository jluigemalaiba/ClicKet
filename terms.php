<?php
// terms.php - ClicKet Terms of Use
require_once __DIR__ . '/includes/log.php';

$termsSections = [
    [
        'id' => 'agreement',
        'num' => '01',
        'title' => 'Agreement',
        'body' => [
            'These Terms of Use form an agreement between you and ClicKet. By accessing the website, creating an account, or purchasing tickets, you agree to follow these terms.',
            'If you do not agree with these terms, please do not use the platform.',
        ],
    ],
    [
        'id' => 'accounts',
        'num' => '02',
        'title' => 'Accounts',
        'body' => [
            'You need an account to purchase tickets and manage your bookings. You are responsible for keeping your login details confidential and for all activity under your account.',
            'ClicKet may suspend accounts that provide inaccurate information, misuse the service, or violate these terms.',
        ],
    ],
    [
        'id' => 'purchases',
        'num' => '03',
        'title' => 'Ticket Purchases',
        'body' => [
            'Ticket availability, seat access, event dates, and venue policies are controlled by organizers and venue partners.',
            'Once a transaction is confirmed, it is considered final unless the event policy or applicable law allows a refund, exchange, or cancellation.',
        ],
    ],
    [
        'id' => 'refunds',
        'num' => '04',
        'title' => 'Refunds and Cancellations',
        'body' => [
            'Refunds are handled according to the organizer policy shown for each event. Cancelled or postponed events may have separate instructions.',
            'Processing timelines may vary depending on the payment provider and the organizer review process.',
        ],
    ],
    [
        'id' => 'conduct',
        'num' => '05',
        'title' => 'User Conduct',
        'body' => [
            'You agree not to use ClicKet for unlawful activity, automated bulk purchases, ticket scalping, fraudulent transactions, or activity that harms other users.',
            'ClicKet may restrict access if suspicious or abusive activity is detected.',
        ],
    ],
    [
        'id' => 'ownership',
        'num' => '06',
        'title' => 'Intellectual Property',
        'body' => [
            'ClicKet branding, interface design, text, graphics, and platform content are owned by ClicKet or its licensors.',
            'You may not copy, reproduce, or distribute ClicKet materials without written permission.',
        ],
    ],
    [
        'id' => 'liability',
        'num' => '07',
        'title' => 'Limitation of Liability',
        'body' => [
            'ClicKet is not responsible for losses caused by organizer decisions, venue restrictions, event changes, network issues, or third-party payment provider delays.',
            'Where allowed by law, ClicKet liability is limited to the amount paid for the affected transaction.',
        ],
    ],
    [
        'id' => 'changes',
        'num' => '08',
        'title' => 'Changes to Terms',
        'body' => [
            'We may update these Terms of Use from time to time. Continued use of ClicKet after updates are posted means you accept the revised terms.',
        ],
    ],
    [
        'id' => 'contact',
        'num' => '09',
        'title' => 'Contact',
        'body' => [
            'For questions about these terms, contact ClicKet support at support@clicket.ph or use the Contact page.',
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Read the ClicKet Terms of Use for accounts, ticket purchases, refunds, conduct, and platform policies.">
  <title>ClicKet Terms of Use</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/partners-footer.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">

  <style>
    body.terms-page {
      background: var(--light-bg);
      color: var(--text-primary);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Geist', sans-serif;
      font-weight: 400;
      -webkit-font-smoothing: antialiased;
    }

    .terms-hero {
      position: relative;
      padding: 158px 0 66px;
      overflow: hidden;
      background:
        radial-gradient(circle at 88% 0%, rgba(232,22,43,.065) 0 260px, transparent 261px),
        radial-gradient(circle at 21% 100%, rgba(232,22,43,.065) 0 190px, transparent 191px),
        linear-gradient(180deg, #fff 0%, var(--light-bg) 100%);
      border-bottom: 1px solid var(--gray-200);
    }

    .terms-hero-inner {
      display: grid;
      grid-template-columns: minmax(0, 1fr) minmax(280px, .5fr);
      gap: 42px;
      align-items: end;
    }

    .terms-eyebrow,
    .terms-kicker {
      margin: 0 0 10px;
      color: var(--red-primary);
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .terms-title {
      margin: 0;
      max-width: 760px;
      font-family: var(--font-display);
      font-size: clamp(48px, 7vw, 82px);
      line-height: .92;
      letter-spacing: 1px;
      font-weight: 600;
    }

    .terms-title span {
      color: var(--red-primary);
    }

    .terms-copy {
      max-width: 680px;
      margin: 22px 0 0;
      color: var(--gray-600);
      font-size: 16px;
      line-height: 1.75;
      font-weight: 400;
    }

    .terms-summary {
      padding: 24px;
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-card);
    }

    .terms-summary strong {
      display: block;
      margin-bottom: 4px;
      font-size: 14px;
      font-weight: 600;
    }

    .terms-summary span {
      display: block;
      color: var(--gray-500);
      font-size: 13px;
      line-height: 1.5;
      font-weight: 400;
    }

    .terms-main {
      padding: 50px 0 82px;
    }

    .terms-layout {
      display: grid;
      grid-template-columns: 260px minmax(0, 1fr);
      gap: 34px;
      align-items: start;
    }

    .terms-nav {
      position: sticky;
      top: 94px;
      padding: 18px;
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-sm);
    }

    .terms-nav h2 {
      margin: 0 0 14px;
      color: var(--gray-600);
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 1.6px;
      text-transform: uppercase;
    }

    .terms-nav a {
      display: flex;
      align-items: center;
      gap: 9px;
      min-height: 34px;
      padding: 6px 8px;
      border-radius: 8px;
      color: var(--gray-600);
      font-size: 13px;
      font-weight: 500;
      transition: background var(--dur-fast), color var(--dur-fast);
    }

    .terms-nav a:hover,
    .terms-nav a.is-active {
      background: rgba(232,22,43,.08);
      color: var(--red-primary);
    }

    .terms-nav-num {
      color: var(--red-primary);
      font-size: 10px;
      font-weight: 600;
    }

    .terms-content {
      display: grid;
      gap: 14px;
    }

    .terms-section {
      scroll-margin-top: 104px;
      padding: 24px;
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-sm);
    }

    .terms-section h2 {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 0 0 14px;
      font-size: 22px;
      font-weight: 600;
    }

    .terms-section-num {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      border-radius: 10px;
      background: rgba(232,22,43,.1);
      color: var(--red-primary);
      font-size: 11px;
      font-weight: 600;
    }

    .terms-section p {
      margin: 0 0 12px;
      color: var(--gray-600);
      font-size: 14px;
      line-height: 1.75;
      font-weight: 400;
    }

    .terms-section p:last-child {
      margin-bottom: 0;
    }

    .terms-note {
      margin-top: 22px;
      padding: 18px 20px;
      border: 1px solid rgba(232,22,43,.18);
      border-radius: var(--card-radius);
      background: rgba(232,22,43,.06);
      color: var(--gray-700);
      font-size: 14px;
      line-height: 1.65;
      font-weight: 400;
    }

    .terms-note a {
      color: var(--red-primary);
      font-weight: 600;
    }

    @media (max-width: 991px) {
      .terms-hero-inner,
      .terms-layout {
        grid-template-columns: 1fr;
      }

      .terms-nav {
        position: static;
      }
    }

    @media (max-width: 640px) {
      .terms-hero {
        padding: 132px 0 48px;
        background:
          radial-gradient(circle at 108% 2%, rgba(232,22,43,.065) 0 150px, transparent 151px),
          radial-gradient(circle at 8% 100%, rgba(232,22,43,.065) 0 115px, transparent 116px),
          linear-gradient(180deg, #fff 0%, var(--light-bg) 100%);
      }

      .terms-section {
        padding: 20px;
      }
    }
  </style>
</head>
<body class="terms-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>
  <section class="terms-hero">
    <div class="container-xl px-4">
      <div class="terms-hero-inner">
        <div>
          <p class="terms-eyebrow">ClicKet Legal</p>
          <h1 class="terms-title">Terms <span>of Use</span></h1>
          <p class="terms-copy">
            These terms explain how accounts, ticket purchases, event policies, refunds, and platform conduct work across ClicKet.
          </p>
        </div>
        <aside class="terms-summary">
          <p class="terms-kicker">Effective Date</p>
          <strong><?= date('F j, Y') ?></strong>
          <span>ClicKet Philippines. Please review these terms before purchasing tickets or using account features.</span>
        </aside>
      </div>
    </div>
  </section>

  <section class="terms-main">
    <div class="container-xl px-4">
      <div class="terms-layout">
        <aside class="terms-nav" aria-label="Terms sections">
          <h2>Quick Links</h2>
          <?php foreach ($termsSections as $section): ?>
            <a href="#<?= htmlspecialchars($section['id']) ?>">
              <span class="terms-nav-num"><?= htmlspecialchars($section['num']) ?></span>
              <?= htmlspecialchars($section['title']) ?>
            </a>
          <?php endforeach; ?>
        </aside>

        <div class="terms-content">
          <?php foreach ($termsSections as $section): ?>
            <section class="terms-section" id="<?= htmlspecialchars($section['id']) ?>">
              <h2><span class="terms-section-num"><?= htmlspecialchars($section['num']) ?></span><?= htmlspecialchars($section['title']) ?></h2>
              <?php foreach ($section['body'] as $paragraph): ?>
                <p><?= htmlspecialchars($paragraph) ?></p>
              <?php endforeach; ?>
            </section>
          <?php endforeach; ?>

          <div class="terms-note">
            By using ClicKet, you confirm that you have read and understood these Terms of Use.
            For support, visit the <a href="help.php">Help Center</a> or <a href="contact.php">Contact Us</a>.
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  const navbar = document.querySelector('.navbar-clicket');
  const links = Array.from(document.querySelectorAll('.terms-nav a'));
  const sections = Array.from(document.querySelectorAll('.terms-section'));

  function handleScroll() {
    if (navbar) {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
    }
  }

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        links.forEach(link => link.classList.remove('is-active'));
        const active = document.querySelector('.terms-nav a[href="#' + entry.target.id + '"]');
        if (active) active.classList.add('is-active');
      });
    }, { rootMargin: '-35% 0px -55% 0px' });

    sections.forEach(section => observer.observe(section));
  }

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
})();
</script>
</body>
</html>
