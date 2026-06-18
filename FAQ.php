<?php
require_once __DIR__ . '/includes/log.php';

$faqs = [
    [
        'id' => 'tickets',
        'label' => 'Tickets',
        'title' => 'Tickets & Seat Selection',
        'desc' => 'Choosing seats, limits, and ticket access.',
        'faqs' => [
            ['q' => 'How many tickets can I select per order?', 'a' => 'You can select up to 4 seats per order. This helps keep seat availability fair and prevents one buyer from holding too many seats at once.'],
            ['q' => 'How do I choose my seats?', 'a' => 'Open an event, go to ticket selection, choose a section on the seat map, then pick available seat dots from the seat panel. Selected seats appear in your checkout summary.'],
            ['q' => 'Why did my selected seat become unavailable?', 'a' => 'A seat can become unavailable if another buyer completed payment first or if your reservation timer expired. When that happens, choose a different available seat.'],
            ['q' => 'Where can I view my tickets after payment?', 'a' => 'After a successful order, open your account tickets panel. Your tickets include ticket IDs, validation codes, voucher IDs, and printable forms.'],
        ],
    ],
    [
        'id' => 'payment',
        'label' => 'Payment',
        'title' => 'Payment & Approval',
        'desc' => 'Payment methods, proof screenshots, and status.',
        'faqs' => [
            ['q' => 'What payment methods are supported?', 'a' => 'ClicKet supports the payment methods shown during checkout, such as wallet, QR, and card-style options depending on the event setup.'],
            ['q' => 'Do I need to upload a payment screenshot?', 'a' => 'If the selected payment method requires proof, upload the screenshot during checkout. The assigned organizer for that venue reviews the proof and approves or rejects it.'],
            ['q' => 'Who approves my payment?', 'a' => 'Payments are reviewed by the organizer assigned to the event venue. Admins can still view and manage all payment records.'],
            ['q' => 'What happens if my payment is rejected?', 'a' => 'Your order will be marked as payment rejected or failed. Contact support or the organizer if you believe the proof was valid.'],
        ],
    ],
    [
        'id' => 'virtual-queue',
        'label' => 'Queue',
        'title' => 'Virtual Queue',
        'desc' => 'Fair access for high-demand events.',
        'faqs' => [
            ['q' => 'What is the Virtual Queue?', 'a' => 'The Virtual Queue is ClicKet\'s fair ticketing system for high-demand events. Instead of a first-come-first-served rush, eligible users receive queue access so traffic stays controlled and fair.'],
            ['q' => 'Is the Virtual Queue used for all events?', 'a' => 'No. It is only used for events expected to have very high demand. Regular events use the normal event page and checkout flow.'],
            ['q' => 'Can I improve my queue position?', 'a' => 'No. Queue access cannot be purchased, boosted, or transferred. Any third-party claiming this is unsafe and should be ignored.'],
            ['q' => 'Do I need to refresh while waiting?', 'a' => 'No. Keep the page open and avoid repeated refreshes because that can interrupt your session.', 'tip' => 'Use one browser tab and one account for the event.'],
        ],
    ],
    [
        'id' => 'reservation',
        'label' => 'Holds',
        'title' => 'Reservations & Seat Holds',
        'desc' => 'Timers, expired holds, and released seats.',
        'faqs' => [
            ['q' => 'How long are seats held?', 'a' => 'Selected seats are held for a limited checkout window. The timer is shown during seat selection and checkout.'],
            ['q' => 'What happens when my reservation expires?', 'a' => 'Expired reservations release the seats back to the event map so other buyers can select them. You will need to select seats again.'],
            ['q' => 'Can I hold seats without checking out?', 'a' => 'No. Seat holds are temporary and are only meant to give you enough time to complete checkout.'],
            ['q' => 'Why can I only select a few seats?', 'a' => 'ClicKet limits orders to a small number of seats so more customers have a fair chance to buy tickets.'],
        ],
    ],
    [
        'id' => 'issues',
        'label' => 'Issues',
        'title' => 'Troubleshooting',
        'desc' => 'Common problems and how to fix them.',
        'faqs' => [
            ['q' => 'The page is not loading properly. What do I do?', 'a' => 'Try these steps in order:', 'list' => ['Refresh the page once', 'Clear your browser cache or try a different browser', 'Disable browser extensions that may block scripts', 'Switch from Wi-Fi to mobile data or vice versa', 'Try a different device if available'], 'tip' => 'If issues persist, contact support@clicket.ph immediately.'],
            ['q' => 'I cannot see my selected seats in checkout.', 'a' => 'Your seat reservation may have expired or the browser session may have refreshed. Return to the event page and select seats again.'],
            ['q' => 'My payment proof upload did not appear.', 'a' => 'Check that the file is a valid screenshot and submit the form again. If the issue continues, contact support with your order ID.'],
            ['q' => 'My ticket is showing as invalid.', 'a' => 'Tickets may show as invalid if the order was cancelled, refunded, voided, or payment was not approved. Check your order status or contact support.'],
        ],
    ],
    [
        'id' => 'checkout',
        'label' => 'Checkout',
        'title' => 'Completing Purchase',
        'desc' => 'What to do when it is your turn to buy.',
        'faqs' => [
            ['q' => 'What do I do before payment?', 'a' => 'Review your event, performance date, selected seats, subtotal, service fee, and total amount before submitting payment.'],
            ['q' => 'How much time do I have to complete checkout?', 'a' => 'Checkout uses a visible countdown timer. Complete payment before the timer expires so your selected seats stay reserved.', 'tip' => 'Prepare your payment method before choosing seats for high-demand events.'],
            ['q' => 'What happens if I do not complete my purchase in time?', 'a' => 'Your held seats are released and your checkout session expires. You can return to seat selection if tickets are still available.'],
            ['q' => 'Can I see ticket prices before checkout?', 'a' => 'Yes. Prices and service fees are shown before final payment so you can review the total amount.'],
            ['q' => 'Is my payment information secure during checkout?', 'a' => 'Yes. ClicKet uses industry-standard encryption and secure payment gateways. Your payment information is protected and never stored on our servers.'],
        ],
    ],
];

function helpIcon(string $id): string {
    $icons = [
        'tickets' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5V9a3 3 0 0 0 0 6v1.5a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5V15a3 3 0 0 0 0-6V7.5Z"/><path d="M13.5 8.5h3M13.5 12h3M13.5 15.5h3"/></svg>',
        'payment' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/><path d="M7 15h4"/></svg>',
        'virtual-queue' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 7h14"/><path d="M5 12h14"/><path d="M5 17h14"/><circle cx="8" cy="7" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="11" cy="17" r="2"/></svg>',
        'reservation' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><polyline points="12 6 12 12 16 14"/></svg>',
        'issues' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L4 17v3h3l5.3-5.3a4 4 0 0 0 5.4-5.4l-2.4 2.4-3-3z"/></svg>',
        'checkout' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/><path d="M7 15h4"/></svg>',
    ];
    return $icons[$id] ?? $icons['tickets'];
}

function supportIcon(string $id): string {
    $icons = [
        'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>',
        'clock' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
        'tip' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M8.5 14.5A6 6 0 1 1 15.5 14.5c-.9.7-1.5 1.7-1.5 2.5h-4c0-.8-.6-1.8-1.5-2.5Z"/></svg>',
    ];
    return $icons[$id] ?? $icons['shield'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ClicKet FAQ - Answers about tickets, seat selection, payment, virtual queue, checkout, and account support.">
  <title>ClicKet Help Center - FAQ</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/partners-footer.css">

  <style>
    body.faq-page {
      background: var(--light-bg);
      color: var(--text-primary);
    }

    .faq-hero {
      position: relative;
      padding: 158px 0 74px;
      overflow: hidden;
      background:
        radial-gradient(circle at 88% 0%, rgba(232,22,43,.065) 0 260px, transparent 261px),
        radial-gradient(circle at 21% 100%, rgba(232,22,43,.065) 0 190px, transparent 191px),
        linear-gradient(180deg, #fff 0%, var(--light-bg) 100%);
      border-bottom: 1px solid var(--gray-200);
    }

    .faq-hero::after {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      height: 5px;
      background: var(--red-primary);
    }

    .faq-hero-inner {
      max-width: 920px;
    }

    .faq-eyebrow,
    .faq-section-kicker {
      margin: 0 0 10px;
      color: var(--red-primary);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .faq-title {
      margin: 0;
      max-width: 830px;
      font-family: var(--font-display);
      font-size: clamp(48px, 7vw, 84px);
      line-height: .92;
      letter-spacing: 1px;
      font-weight: 700;
    }

    .faq-title span {
      color: var(--red-primary);
    }

    .faq-copy {
      max-width: 680px;
      margin: 22px 0 0;
      color: var(--gray-600);
      font-size: 16px;
      line-height: 1.7;
    }

    .faq-hero-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 26px;
    }

    .faq-hero-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      min-height: 38px;
      padding: 0 13px;
      border: 1px solid rgba(232,22,43,.18);
      border-radius: 999px;
      background: #fff;
      color: var(--gray-700);
      font-size: 13px;
      font-weight: 700;
      box-shadow: var(--shadow-sm);
    }

    .faq-hero-chip svg {
      width: 16px;
      height: 16px;
      stroke: var(--red-primary);
    }

    .faq-content {
      padding: 50px 0 82px;
    }

    .faq-section-header {
      display: flex;
      justify-content: space-between;
      align-items: end;
      gap: 24px;
      margin-bottom: 22px;
    }

    .faq-section-title {
      margin: 0;
      font-family: var(--font-display);
      font-size: clamp(34px, 4vw, 48px);
      line-height: 1;
      letter-spacing: 1px;
      font-weight: 700;
    }

    .faq-section-title span {
      color: var(--red-primary);
    }

    .faq-section-note {
      max-width: 370px;
      margin: 0;
      color: var(--gray-500);
      font-size: 13px;
      line-height: 1.6;
    }

    .faq-category-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
      margin-bottom: 34px;
    }

    .faq-category-card {
      display: grid;
      grid-template-columns: 46px minmax(0, 1fr);
      gap: 14px;
      align-items: center;
      min-height: 104px;
      padding: 18px;
      border: 1px solid var(--light-border);
      border-radius: 8px;
      background: #fff;
      color: inherit;
      box-shadow: var(--shadow-sm);
      transition: border-color var(--dur-fast), transform var(--dur-fast), box-shadow var(--dur-fast);
      cursor: pointer;
      text-align: left;
    }

    .faq-category-card:hover,
    .faq-category-card.is-active {
      border-color: rgba(232,22,43,.38);
      transform: translateY(-3px);
      box-shadow: 0 16px 34px rgba(17,17,17,.1);
    }

    .faq-category-icon,
    .faq-heading-icon,
    .faq-tip-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(232,22,43,.08);
      color: var(--red-primary);
    }

    .faq-category-icon {
      width: 46px;
      height: 46px;
      border-radius: 12px;
    }

    .faq-category-icon svg {
      width: 25px;
      height: 25px;
      stroke: currentColor;
    }

    .faq-category-card strong {
      display: block;
      margin-bottom: 5px;
      color: var(--text-primary);
      font-size: 15px;
      font-weight: 800;
    }

    .faq-category-card span span {
      display: block;
      color: var(--gray-500);
      font-size: 13px;
      line-height: 1.45;
    }

    .faq-panel {
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-card);
      overflow: hidden;
    }

    .faq-group {
      display: none;
      scroll-margin-top: 104px;
    }

    .faq-group.visible {
      display: block;
    }

    .faq-heading {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 0;
      padding: 22px 22px 18px;
      border-bottom: 1px solid var(--gray-200);
      font-size: 22px;
      font-weight: 800;
    }

    .faq-heading-icon {
      width: 40px;
      height: 40px;
      flex: 0 0 40px;
      border-radius: 10px;
    }

    .faq-heading-icon svg {
      width: 23px;
      height: 23px;
      stroke: currentColor;
    }

    .faq-list {
      display: grid;
      gap: 0;
    }

    .faq-item + .faq-item {
      border-top: 1px solid var(--gray-200);
    }

    .faq-question {
      width: 100%;
      display: grid;
      grid-template-columns: minmax(0, 1fr) 34px;
      gap: 16px;
      align-items: center;
      padding: 18px 22px;
      border: 0;
      background: #fff;
      color: var(--text-primary);
      font-size: 15px;
      font-weight: 800;
      text-align: left;
      cursor: pointer;
      transition: background var(--dur-fast), color var(--dur-fast);
    }

    .faq-question:hover {
      background: var(--gray-100);
    }

    .faq-question.is-open {
      background: #fff;
      color: var(--red-primary);
    }

    .faq-chevron {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 34px;
      height: 34px;
      border: 1px solid var(--gray-200);
      border-radius: 50%;
      color: var(--red-primary);
      transition: transform var(--dur-fast), border-color var(--dur-fast);
    }

    .faq-chevron svg {
      width: 15px;
      height: 15px;
      stroke: currentColor;
    }

    .faq-question.is-open .faq-chevron {
      border-color: rgba(232,22,43,.35);
      transform: rotate(180deg);
    }

    .faq-answer {
      display: none;
      padding: 0 22px 22px;
      color: var(--gray-600);
      font-size: 14px;
      line-height: 1.75;
    }

    .faq-answer.is-open {
      display: block;
    }

    .faq-answer ul {
      margin: 12px 0 0;
      padding-left: 20px;
    }

    .faq-answer li {
      margin-bottom: 8px;
    }

    .faq-tip {
      display: grid;
      grid-template-columns: 28px minmax(0, 1fr);
      gap: 10px;
      align-items: start;
      margin-top: 14px;
      padding: 13px 14px;
      border: 1px solid rgba(232,22,43,.14);
      border-radius: 8px;
      background: rgba(232,22,43,.05);
      color: var(--gray-700);
      font-size: 13px;
      line-height: 1.6;
    }

    .faq-tip-icon {
      width: 28px;
      height: 28px;
      border-radius: 8px;
    }

    .faq-tip-icon svg {
      width: 16px;
      height: 16px;
      stroke: currentColor;
    }

    .faq-contact-panel {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 24px;
      margin-top: 24px;
      padding: 24px;
      border: 1px solid var(--light-border);
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: var(--shadow-sm);
    }

    .faq-contact-panel h2 {
      margin: 0 0 8px;
      font-size: 22px;
      font-weight: 800;
    }

    .faq-contact-panel p {
      margin: 0;
      color: var(--gray-600);
      font-size: 14px;
      line-height: 1.7;
    }

    .faq-contact-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-height: 50px;
      padding: 0 22px;
      border: 0;
      border-radius: var(--btn-radius);
      background: var(--red-primary);
      color: #fff;
      font-size: 13px;
      font-weight: 800;
      white-space: nowrap;
      text-decoration: none;
      transition: background var(--dur-fast), transform var(--dur-fast), box-shadow var(--dur-fast);
    }

    .faq-contact-btn svg {
      width: 16px;
      height: 16px;
      stroke: currentColor;
    }

    .faq-contact-btn:hover {
      background: var(--red-light);
      color: #fff;
      transform: translateY(-2px);
      box-shadow: var(--glow-red);
    }

    @media (max-width: 991px) {
      .faq-section-header {
        display: block;
      }

      .faq-section-note {
        margin-top: 12px;
      }

      .faq-category-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 640px) {
      .faq-hero {
        padding: 132px 0 52px;
      }

      .faq-category-grid {
        grid-template-columns: 1fr;
      }

      .faq-category-card {
        min-height: 92px;
      }

      .faq-contact-panel {
        flex-direction: column;
        align-items: flex-start;
      }

      .faq-contact-btn {
        width: 100%;
      }
    }
  </style>
</head>
<body class="faq-page">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main>
  <section class="faq-hero">
    <div class="container-xl px-4">
      <div class="faq-hero-inner">
        <p class="faq-eyebrow">ClicKet Support</p>
        <h1 class="faq-title">Frequently Asked <span>Questions</span></h1>
        <p class="faq-copy">
          Quick answers about tickets, seat selection, payment approval, virtual queue, checkout, reservations, and common account concerns.
        </p>
        <div class="faq-hero-actions" aria-label="Support highlights">
          <span class="faq-hero-chip"><?= supportIcon('shield') ?> Secure ticketing</span>
          <span class="faq-hero-chip"><?= supportIcon('clock') ?> Reservation timers</span>
          <span class="faq-hero-chip"><?= supportIcon('mail') ?> Support ready when needed</span>
        </div>
      </div>
    </div>
  </section>

  <section class="faq-content">
    <div class="container-xl px-4">
      <div class="faq-section-header">
        <div>
          <p class="faq-section-kicker">Choose a Section</p>
          <h2 class="faq-section-title">ClicKet <span>Help</span></h2>
        </div>
        <p class="faq-section-note">Pick a topic below, then open only the question you need. Virtual Queue is included together with ticketing, payments, checkout, and troubleshooting.</p>
      </div>

      <div class="faq-category-grid" aria-label="FAQ categories">
        <?php foreach ($faqs as $cat): ?>
          <button type="button" class="faq-category-card" data-category="<?= htmlspecialchars($cat['id']) ?>">
            <span class="faq-category-icon"><?= helpIcon($cat['id']) ?></span>
            <span>
              <strong><?= htmlspecialchars($cat['title']) ?></strong>
              <span><?= htmlspecialchars($cat['desc']) ?></span>
            </span>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="faq-panel">
        <?php foreach ($faqs as $cat): ?>
          <section class="faq-group" id="<?= htmlspecialchars($cat['id']) ?>">
            <h2 class="faq-heading">
              <span class="faq-heading-icon"><?= helpIcon($cat['id']) ?></span>
              <?= htmlspecialchars($cat['title']) ?>
            </h2>

            <div class="faq-list">
              <?php foreach ($cat['faqs'] as $faq): ?>
                <article class="faq-item">
                  <button class="faq-question" type="button" aria-expanded="false">
                    <span><?= htmlspecialchars($faq['q']) ?></span>
                    <span class="faq-chevron" aria-hidden="true">
                      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </span>
                  </button>
                  <div class="faq-answer">
                    <?= htmlspecialchars($faq['a']) ?>
                    <?php if (!empty($faq['list'])): ?>
                      <ul>
                        <?php foreach ($faq['list'] as $li): ?>
                          <li><?= htmlspecialchars($li) ?></li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                    <?php if (!empty($faq['tip'])): ?>
                      <div class="faq-tip">
                        <span class="faq-tip-icon"><?= supportIcon('tip') ?></span>
                        <span><?= htmlspecialchars($faq['tip']) ?></span>
                      </div>
                    <?php endif; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endforeach; ?>
      </div>

      <section class="faq-contact-panel" id="contact">
        <div>
          <h2>Still have questions?</h2>
          <p>Contact ClicKet support if you need additional help with ticketing, payment, checkout, or virtual queue concerns.</p>
        </div>
        <a href="contact.php" class="faq-contact-btn">
          <?= supportIcon('mail') ?>
          Contact Support
        </a>
      </section>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  const navbar = document.querySelector('.navbar-clicket');
  const categoryButtons = Array.from(document.querySelectorAll('.faq-category-card'));
  const groups = Array.from(document.querySelectorAll('.faq-group'));

  function handleScroll() {
    if (navbar) {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
    }
  }

  function closeOpenQuestion() {
    document.querySelectorAll('.faq-question.is-open').forEach(openButton => {
      openButton.classList.remove('is-open');
      openButton.setAttribute('aria-expanded', 'false');
      openButton.nextElementSibling.classList.remove('is-open');
    });
  }

  function showCategory(categoryId, shouldScroll) {
    groups.forEach(group => group.classList.toggle('visible', group.id === categoryId));
    categoryButtons.forEach(button => {
      button.classList.toggle('is-active', button.dataset.category === categoryId);
    });
    closeOpenQuestion();

    const activeGroup = document.getElementById(categoryId);
    if (shouldScroll && activeGroup) {
      activeGroup.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  categoryButtons.forEach(button => {
    button.addEventListener('click', () => showCategory(button.dataset.category, true));
  });

  document.querySelectorAll('.faq-question').forEach(button => {
    button.addEventListener('click', () => {
      const answer = button.nextElementSibling;
      const isOpen = button.classList.contains('is-open');

      closeOpenQuestion();

      if (!isOpen) {
        button.classList.add('is-open');
        button.setAttribute('aria-expanded', 'true');
        answer.classList.add('is-open');
      }
    });
  });

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();

  if (categoryButtons.length > 0) {
    showCategory(categoryButtons[0].dataset.category, false);
  }
})();
</script>

</body>
</html>
