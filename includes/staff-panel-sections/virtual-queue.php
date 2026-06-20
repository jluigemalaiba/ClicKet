<?php
$queueRows = is_array($payload['virtualQueue'] ?? null) ? $payload['virtualQueue'] : [];
$queueEnabledCount = count(array_filter($queueRows, static fn (array $row): bool => !empty($row['enabled'])));
$queueAverageWait = clicketVirtualQueueFormatDuration((int) ($metrics['queueAverageWaitSeconds'] ?? 0));
?>

<section class="staff-grid-two" data-subsection="overview">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Virtual Queue</p>
        <h2>Live waiting room load</h2>
      </div>
      <span><?= sp_count($queueEnabledCount) ?> enabled</span>
    </div>
    <div class="staff-status-grid">
      <div class="staff-status-tile"><strong><?= sp_count($metrics['queueSize'] ?? 0) ?></strong><small>Queued users</small></div>
      <div class="staff-status-tile"><strong><?= sp_count($metrics['queueActiveSessions'] ?? 0) ?></strong><small>Admitted sessions</small></div>
      <div class="staff-status-tile"><strong><?= sp_h($queueAverageWait) ?></strong><small>Average wait</small></div>
      <div class="staff-status-tile"><strong><?= sp_count(count($queueRows)) ?></strong><small>Events in scope</small></div>
    </div>
  </article>

  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Admission Control</p>
        <h2><?= $isAdmin ? 'Admin configuration' : 'Organizer visibility' ?></h2>
      </div>
    </div>
    <p class="staff-muted">
      <?= $isAdmin
        ? 'Enable high-demand waiting rooms per event without changing the database schema.'
        : 'Queue statistics are scoped to events owned by your organizer account.' ?>
    </p>
  </article>
</section>

<section class="staff-section" data-subsection="events">
  <div class="staff-section-heading">
    <div>
      <p>Event Queues</p>
      <h2>Waiting room controls and metrics</h2>
    </div>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Event</th>
          <th>Venue</th>
          <th>Status</th>
          <th>Queue</th>
          <th>Active</th>
          <th>Average Wait</th>
          <th><?= $isAdmin ? 'Controls' : 'Configuration' ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payload['events'] as $event): ?>
          <?php
            $eventKey = (string) ($event['event_key'] ?? $event['key'] ?? '');
            if ($eventKey === '') {
                continue;
            }
            $row = $queueRows[$eventKey] ?? [
                'enabled' => false,
                'queue_size' => 0,
                'active_count' => 0,
                'average_wait_seconds' => 0,
                'max_active' => 25,
                'timeout_seconds' => 300,
                'throughput_per_minute' => 12,
            ];
          ?>
          <tr data-search-row>
            <td><strong><?= sp_h($event['title'] ?? 'Untitled event') ?></strong><small><?= sp_h($eventKey) ?></small></td>
            <td><?= sp_h($event['venue'] ?? '') ?></td>
            <td><span class="staff-status <?= !empty($row['enabled']) ? 'is-success' : 'is-muted' ?>"><?= !empty($row['enabled']) ? 'Enabled' : 'Disabled' ?></span></td>
            <td><?= sp_count($row['queue_size'] ?? 0) ?></td>
            <td><?= sp_count($row['active_count'] ?? 0) ?> / <?= sp_count($row['max_active'] ?? 0) ?></td>
            <td><?= sp_h(clicketVirtualQueueFormatDuration((int) ($row['average_wait_seconds'] ?? 0))) ?></td>
            <td>
              <?php if ($isAdmin): ?>
                <form class="staff-queue-form" method="post" action="staff-virtual-queue-api.php">
                  <input type="hidden" name="csrf_token" value="<?= sp_h(clicketCsrfToken('staff_queue')) ?>">
                  <input type="hidden" name="event_key" value="<?= sp_h($eventKey) ?>">
                  <label><span>Enabled</span><input type="checkbox" name="enabled" value="1" <?= !empty($row['enabled']) ? 'checked' : '' ?>></label>
                  <label><span>Max active</span><input type="number" name="max_active" min="1" max="500" value="<?= sp_h($row['max_active'] ?? 25) ?>"></label>
                  <label><span>Timeout sec</span><input type="number" name="timeout_seconds" min="60" max="3600" step="30" value="<?= sp_h($row['timeout_seconds'] ?? 300) ?>"></label>
                  <label><span>Throughput/min</span><input type="number" name="throughput_per_minute" min="1" max="300" value="<?= sp_h($row['throughput_per_minute'] ?? 12) ?>"></label>
                  <button type="submit">Save</button>
                </form>
              <?php else: ?>
                <span><?= sp_count($row['max_active'] ?? 0) ?> active max · <?= sp_h(clicketVirtualQueueFormatDuration((int) ($row['timeout_seconds'] ?? 300))) ?> timeout</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
