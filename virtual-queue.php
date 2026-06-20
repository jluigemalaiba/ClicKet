<?php

require_once __DIR__ . '/includes/ticketing.php';
require_once __DIR__ . '/includes/virtual-queue.php';

function vq_h(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function vq_safe_next(string $next, string $fallback): string {
    $next = trim($next);
    if ($next === '' || preg_match('/^[a-z][a-z0-9+.-]*:/i', $next) || str_starts_with($next, '//')) {
        return $fallback;
    }

    return $next;
}

$eventKey = trim((string) ($_GET['event'] ?? ''));
$performance = max(0, (int) ($_GET['performance'] ?? 0));
$resolved = clicketResolveEvent($eventKey);

if (!$resolved) {
    header('Location: events.php');
    exit;
}

$fallbackNext = 'ticket.php?event=' . rawurlencode($eventKey) . '&performance=' . $performance;
$next = vq_safe_next((string) ($_GET['next'] ?? ''), $fallbackNext);

if (!clicketVirtualQueueIsEnabled($eventKey)) {
    header('Location: ' . $next);
    exit;
}

$status = clicketVirtualQueueEnter($eventKey, $performance);
$event = $resolved['event'];
$performanceDate = $resolved['date'];
$performanceTime = $resolved['time'];
if ($resolved['categoryKey'] === 'theater' && $performance > 0) {
    $theaterSlots = [
        [$resolved['date'], $resolved['time']],
        [$resolved['date']->modify('+1 day'), '2:00 PM'],
        [$resolved['date']->modify('+1 day'), '7:30 PM'],
        [$resolved['date']->modify('+2 days'), '3:00 PM'],
    ];
    [$performanceDate, $performanceTime] = $theaterSlots[min($performance, 3)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Virtual queue waiting room for <?= vq_h($event['title'] ?? 'CLICKET event') ?>.">
  <title>Waiting Room | <?= vq_h($event['title'] ?? 'CLICKET') ?> | CLICKET</title>
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/virtual-queue.css?v=<?= filemtime(__DIR__ . '/css/virtual-queue.css') ?>">
</head>
<body class="virtual-queue-page">
  <main class="vq-shell" data-queue-page data-event="<?= vq_h($eventKey) ?>" data-performance="<?= (int) $performance ?>" data-next="<?= vq_h($next) ?>">
    <section class="vq-hero" style="--vq-banner:url('<?= vq_h($resolved['banner']) ?>')">
      <div class="vq-hero-shade"></div>
      <div class="vq-card">
        <a class="vq-brand" href="index.php" aria-label="CLICKET home">
          <img src="assets/Icon_Logo.png" alt="" aria-hidden="true">
          <img src="assets/Name_Logo.png" alt="CLICKET">
        </a>
        <p class="vq-kicker">Virtual Queue</p>
        <h1><?= vq_h($event['title'] ?? 'Event') ?></h1>
        <p class="vq-meta"><?= vq_h($performanceDate->format('D, M j, Y')) ?> · <?= vq_h($performanceTime) ?> · <?= vq_h($event['venue'] ?? '') ?></p>

        <div class="vq-status" aria-live="polite">
          <div>
            <span>Position</span>
            <strong data-vq-position><?= $status['position'] === 0 ? 'Admitted' : vq_h((string) ($status['position'] ?? 'Waiting')) ?></strong>
          </div>
          <div>
            <span>Users Ahead</span>
            <strong data-vq-ahead><?= vq_h((string) ($status['users_ahead'] ?? 0)) ?></strong>
          </div>
          <div>
            <span>Estimated Wait</span>
            <strong data-vq-wait><?= vq_h(clicketVirtualQueueFormatDuration((int) ($status['estimated_wait_seconds'] ?? 0))) ?></strong>
          </div>
        </div>

        <div class="vq-progress" aria-hidden="true"><span data-vq-progress></span></div>
        <p class="vq-message" data-vq-message>
          <?= !empty($status['admitted']) ? 'You are being admitted to seat selection.' : 'Keep this page open. Your place is held while the waiting room refreshes.' ?>
        </p>
        <a class="vq-link" href="show.php?event=<?= rawurlencode($eventKey) ?>">Back to event</a>
      </div>
    </section>
  </main>

  <script>
    (() => {
      const page = document.querySelector('[data-queue-page]');
      if (!page) return;
      const eventKey = page.dataset.event || '';
      const performance = page.dataset.performance || '0';
      const nextUrl = page.dataset.next || '';
      const position = document.querySelector('[data-vq-position]');
      const ahead = document.querySelector('[data-vq-ahead]');
      const wait = document.querySelector('[data-vq-wait]');
      const message = document.querySelector('[data-vq-message]');
      const progress = document.querySelector('[data-vq-progress]');

      function formatWait(seconds) {
        const total = Number(seconds || 0);
        if (total <= 0) return '0 min';
        if (total < 60) return `${total} sec`;
        const minutes = Math.ceil(total / 60);
        if (minutes < 60) return `${minutes} min`;
        const hours = Math.floor(minutes / 60);
        const rest = minutes % 60;
        return rest ? `${hours} hr ${rest} min` : `${hours} hr`;
      }

      async function refreshQueue() {
        try {
          const url = `virtual-queue-status.php?event=${encodeURIComponent(eventKey)}&performance=${encodeURIComponent(performance)}&next=${encodeURIComponent(nextUrl)}`;
          const response = await fetch(url, { credentials: 'same-origin' });
          const data = await response.json();
          if (!data.success) {
            if (message) message.textContent = data.message || 'Queue status could not be refreshed.';
            return;
          }

          if (position) position.textContent = data.admitted ? 'Admitted' : String(data.position || 'Waiting');
          if (ahead) ahead.textContent = String(data.users_ahead || 0);
          if (wait) wait.textContent = formatWait(data.estimated_wait_seconds || 0);
          if (progress) {
            const max = Math.max(1, Number(data.queue_size || 1));
            const pct = data.admitted ? 100 : Math.max(8, Math.min(95, ((max - Number(data.users_ahead || 0)) / max) * 100));
            progress.style.width = `${pct}%`;
          }
          if (message) {
            message.textContent = data.admitted
              ? 'Admission granted. Opening seat selection now.'
              : 'Keep this page open. We will move you forward automatically.';
          }
          if (data.admitted && data.redirect) {
            window.setTimeout(() => { window.location.href = data.redirect; }, 900);
          }
        } catch (error) {
          if (message) message.textContent = 'Still waiting. Reconnecting to the queue status service.';
        }
      }

      refreshQueue();
      window.setInterval(refreshQueue, 5000);
    })();
  </script>
</body>
</html>
