<?php
$revenueValues = array_column($payload['revenueTrend'], 'value');
$ticketValues = array_column($payload['ticketTrend'], 'value');
$revenueMax = max(1, $revenueValues ? max($revenueValues) : 0);
$ticketMax = max(1, $ticketValues ? max($ticketValues) : 0);
$recentOrders = array_slice(array_reverse($payload['orders']), 0, 5);
$recentPayments = array_slice(array_reverse($payload['payments']), 0, 5);
$draftNewsCount = count(array_filter($payload['news'], static fn (array $article): bool => ($article['status'] ?? '') === 'Draft'));
$publishedEvents = count(array_filter($payload['events'], static fn (array $event): bool => ($event['status'] ?? '') === 'Published'));
$paidOrdersCount = count(array_filter($payload['orders'], static fn (array $order): bool => in_array(strtolower((string) ($order['payment_status'] ?? '')), ['paid', 'payment verified'], true)));
$paymentClearance = sp_percent($paidOrdersCount, max(1, $metrics['orders']));
$eventPublishRate = sp_percent($publishedEvents, max(1, $metrics['activeEvents']));
$ticketFillRate = sp_percent($metrics['ticketsSold'], max(1, $metrics['ticketsSold'] + $metrics['activeReservations'] + 25));
$reservationLoad = sp_percent($metrics['activeReservations'], max(1, $metrics['activeReservations'] + 12));
$chartWidth = 680;
$chartHeight = 270;
$chartPadLeft = 58;
$chartPadRight = 24;
$chartPadTop = 22;
$chartPadBottom = 44;
$chartPlotWidth = $chartWidth - $chartPadLeft - $chartPadRight;
$chartPlotHeight = $chartHeight - $chartPadTop - $chartPadBottom;
$linePointCount = count($payload['revenueTrend']);
$linePoints = [];
foreach ($payload['revenueTrend'] as $index => $point) {
    $x = $chartPadLeft + ($linePointCount > 1 ? ($index / ($linePointCount - 1)) * $chartPlotWidth : 0);
    $y = $chartPadTop + ($chartPlotHeight * (1 - min(1, ((int) $point['value']) / $revenueMax)));
    $linePoints[] = ['x' => round($x, 2), 'y' => round($y, 2), 'point' => $point];
}
$linePathValue = '';
$lineAreaValue = '';
if ($linePoints) {
    $firstPoint = $linePoints[0];
    $linePathValue = 'M ' . $firstPoint['x'] . ' ' . $firstPoint['y'];
    $lineAreaValue = 'M ' . $chartPadLeft . ' ' . ($chartHeight - $chartPadBottom) . ' L ' . $firstPoint['x'] . ' ' . $firstPoint['y'];
    for ($index = 1; $index < count($linePoints); $index++) {
        $previousPoint = $linePoints[$index - 1];
        $currentPoint = $linePoints[$index];
        $midX = round(($previousPoint['x'] + $currentPoint['x']) / 2, 2);
        $curve = ' C ' . $midX . ' ' . $previousPoint['y'] . ', ' . $midX . ' ' . $currentPoint['y'] . ', ' . $currentPoint['x'] . ' ' . $currentPoint['y'];
        $linePathValue .= $curve;
        $lineAreaValue .= $curve;
    }
    $lineAreaValue .= ' L ' . ($chartWidth - $chartPadRight) . ' ' . ($chartHeight - $chartPadBottom) . ' Z';
}
$lastLinePoint = $linePoints ? $linePoints[array_key_last($linePoints)] : null;
$lastRevenueValue = $revenueValues ? (int) $revenueValues[array_key_last($revenueValues)] : 0;
$previousRevenueValue = count($revenueValues) > 1 ? (int) $revenueValues[count($revenueValues) - 2] : 0;
$revenueDelta = $lastRevenueValue - $previousRevenueValue;
$revenueDeltaPercent = $previousRevenueValue > 0 ? (int) round(($revenueDelta / $previousRevenueValue) * 100) : 0;
$chartTicks = [];
foreach ([1, .66, .33, 0] as $tick) {
    $chartTicks[] = [
        'y' => round($chartPadTop + ($chartPlotHeight * (1 - $tick)), 2),
        'label' => sp_money((int) round($revenueMax * $tick)),
    ];
}
$circleStats = $isAdmin ? [
    ['Payment Clearance', $paymentClearance, sp_count($paidOrdersCount) . ' paid orders'],
    ['Event Publish Rate', $eventPublishRate, sp_count($publishedEvents) . ' published events'],
    ['Ticket Fill Pace', $ticketFillRate, sp_count($metrics['ticketsSold']) . ' tickets sold'],
    ['Reservation Load', $reservationLoad, sp_count($metrics['activeReservations']) . ' active holds'],
] : [
    ['Event Publish Rate', $eventPublishRate, sp_count($publishedEvents) . ' published events'],
    ['Reservation Load', $reservationLoad, sp_count($metrics['activeReservations']) . ' active holds'],
    ['News Progress', sp_percent($draftNewsCount, max(1, count($payload['news']))), sp_count($draftNewsCount) . ' drafts'],
];

$dashboardCards = $isAdmin ? [
    ['Total Sales', sp_money($metrics['sales']), 'Paid revenue', 'sales'],
    ['Tickets Sold', sp_count($metrics['ticketsSold']), 'Issued from paid orders', 'tickets'],
    ['Pending Payments', sp_count($metrics['pendingPayments']), 'Needs review', 'payments'],
    ['Active Events', sp_count($metrics['activeEvents']), 'Published and draft', 'events'],
    ['Reservations', sp_count($metrics['activeReservations']), 'Active seat holds', 'reservations'],
    ['Revenue Fees', sp_money($metrics['serviceFees']), 'Service fee capture', 'revenue'],
] : [
    ['Owned Events', sp_count($metrics['activeEvents']), 'Assigned to your account', 'events'],
    ['Tickets Sold', sp_count($metrics['ticketsSold']), 'Issued from paid orders', 'tickets'],
    ['Published', sp_count($publishedEvents), 'Visible owned events', 'events'],
    ['Sales', sp_money($metrics['sales']), 'Paid revenue in scope', 'reports'],
];

$dashboardUpdates = $isAdmin ? [
    ['Payment queue', sp_count($metrics['pendingPayments']) . ' orders still need approval', 'payments'],
    ['Ticket volume', sp_count($metrics['ticketsSold']) . ' tickets sold from paid orders', 'tickets'],
    ['Reservation monitor', sp_count($metrics['activeReservations']) . ' active holds are currently running', 'reservations'],
    ['Inventory watch', sp_count($metrics['lowInventory']) . ' venues are near low inventory threshold', 'events'],
] : [
    ['Event worklist', sp_count($metrics['activeEvents']) . ' owned events in your current scope', 'events'],
    ['Ticket registry', sp_count($metrics['tickets']) . ' issued tickets in your current scope', 'tickets'],
    ['Reports', sp_money($metrics['sales']) . ' paid revenue in your current scope', 'reports'],
];
?>

<section class="staff-dashboard-shell" data-subsection="overview">
  <div class="staff-dashboard-head">
    <div>
      <p><?= $isAdmin ? 'Admin Overview' : 'Organizer Overview' ?></p>
      <h2>Good day, <?= sp_h($staff['name'] ?? 'CLICKET Admin') ?></h2>
    </div>
    <div class="staff-dashboard-actions">
      <?php if ($isAdmin): ?>
        <button class="staff-action-btn" type="button" data-panel-shortcut="payments">Review Payments</button>
        <button class="staff-secondary-btn" type="button" data-panel-shortcut="events">Manage Events</button>
      <?php else: ?>
        <button class="staff-action-btn" type="button" data-panel-shortcut="events">Manage Events</button>
        <button class="staff-secondary-btn" type="button" data-panel-shortcut="reports">View Reports</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="staff-dashboard-kpis" aria-label="Dashboard summary">
    <?php foreach ($dashboardCards as $cardIndex => $card): ?>
      <article class="staff-dashboard-kpi is-<?= sp_h($card[3]) ?>" style="--dashboard-index: <?= (int) $cardIndex ?>" data-search-row>
        <span><?= sp_panel_icon((string) $card[3]) ?></span>
        <small><?= sp_h($card[0]) ?></small>
        <strong><?= sp_h($card[1]) ?></strong>
        <em><?= sp_h($card[2]) ?></em>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php if ($isAdmin): ?>
<section class="staff-dashboard-grid" data-subsection="analytics">
  <article class="staff-dashboard-panel staff-dashboard-panel--wide">
    <div class="staff-card-heading">
      <div>
        <p>Revenue</p>
        <h2>Revenue line</h2>
      </div>
      <span><?= sp_count(count($payload['revenueTrend'])) ?> periods</span>
    </div>
    <div class="staff-line-chart-summary">
      <div>
        <span>Total paid revenue</span>
        <strong><?= sp_money($metrics['sales']) ?></strong>
      </div>
      <em class="<?= $revenueDelta >= 0 ? 'is-up' : 'is-down' ?>"><?= $revenueDelta >= 0 ? '+' : '' ?><?= sp_count($revenueDeltaPercent) ?>%</em>
    </div>
    <div class="staff-line-chart" data-revenue-chart aria-label="Revenue line graph">
      <svg viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight ?>" role="img">
        <title>Revenue trend</title>
        <desc>Paid revenue trend across <?= sp_count(count($payload['revenueTrend'])) ?> reporting periods.</desc>
        <defs>
          <linearGradient id="staffRevenueFill" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0%" stop-color="#e8162b" stop-opacity=".24"></stop>
            <stop offset="72%" stop-color="#e8162b" stop-opacity=".06"></stop>
            <stop offset="100%" stop-color="#e8162b" stop-opacity="0"></stop>
          </linearGradient>
        </defs>
        <?php foreach ($chartTicks as $tick): ?>
          <line class="staff-line-chart-grid" x1="<?= $chartPadLeft ?>" x2="<?= $chartWidth - $chartPadRight ?>" y1="<?= sp_h($tick['y']) ?>" y2="<?= sp_h($tick['y']) ?>"></line>
          <text class="staff-line-chart-y" x="0" y="<?= sp_h($tick['y'] + 4) ?>"><?= sp_h($tick['label']) ?></text>
        <?php endforeach; ?>
        <path class="staff-line-chart-area" d="<?= sp_h($lineAreaValue) ?>" pathLength="1"></path>
        <?php if ($lastLinePoint): ?>
          <line class="staff-line-chart-guide" x1="<?= sp_h($lastLinePoint['x']) ?>" x2="<?= sp_h($lastLinePoint['x']) ?>" y1="<?= $chartPadTop ?>" y2="<?= $chartHeight - $chartPadBottom ?>"></line>
        <?php endif; ?>
        <path class="staff-line-chart-path" d="<?= sp_h($linePathValue) ?>" pathLength="1"></path>
        <?php foreach ($linePoints as $lineIndex => $linePoint): ?>
          <circle class="staff-line-chart-dot <?= $lineIndex === array_key_last($linePoints) ? 'is-active' : '' ?>" data-revenue-dot="<?= (int) $lineIndex ?>" cx="<?= sp_h($linePoint['x']) ?>" cy="<?= sp_h($linePoint['y']) ?>" r="4.5" style="--point-index: <?= (int) $lineIndex ?>"></circle>
        <?php endforeach; ?>
        <?php if ($lastLinePoint): ?>
          <g class="staff-line-chart-callout">
            <rect x="<?= sp_h(max($chartPadLeft, $lastLinePoint['x'] - 92)) ?>" y="<?= sp_h(max(12, $lastLinePoint['y'] - 42)) ?>" width="88" height="30" rx="8"></rect>
            <text x="<?= sp_h(max($chartPadLeft, $lastLinePoint['x'] - 82)) ?>" y="<?= sp_h(max(31, $lastLinePoint['y'] - 22)) ?>"><?= sp_h(sp_money($lastRevenueValue)) ?></text>
          </g>
        <?php endif; ?>
      </svg>
      <div class="staff-line-chart-labels">
        <?php foreach ($linePoints as $lineIndex => $linePoint): ?>
          <button class="staff-line-chart-period <?= $lineIndex === array_key_last($linePoints) ? 'is-active' : '' ?>" type="button" data-revenue-period="<?= (int) $lineIndex ?>" data-revenue-label="<?= sp_h($linePoint['point']['label']) ?>" data-revenue-value="<?= sp_h(sp_money((int) $linePoint['point']['value'])) ?>">
            <span><?= sp_h($linePoint['point']['label']) ?></span><strong><?= sp_money((int) $linePoint['point']['value']) ?></strong>
          </button>
        <?php endforeach; ?>
      </div>
      <p class="staff-line-chart-selection" data-revenue-selection aria-live="polite">Selected period: <?= sp_h($lastLinePoint['point']['label'] ?? 'Latest') ?>, <?= sp_money($lastRevenueValue) ?></p>
    </div>
  </article>

  <article class="staff-dashboard-panel">
    <div class="staff-card-heading">
      <div>
        <p>Percentages</p>
        <h2>Health rings</h2>
      </div>
    </div>
    <div class="staff-ring-grid">
      <?php foreach ($circleStats as $circle): ?>
        <div class="staff-ring-card" style="--percent: <?= (int) $circle[1] ?>;">
          <span class="staff-ring"><strong><?= sp_count($circle[1]) ?>%</strong></span>
          <div>
            <strong><?= sp_h($circle[0]) ?></strong>
            <small><?= sp_h($circle[2]) ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
</section>

<section class="staff-dashboard-grid" data-subsection="updates">
  <article class="staff-dashboard-panel">
    <div class="staff-card-heading">
      <div>
        <p>Notifications</p>
        <h2>Updates</h2>
      </div>
    </div>
    <div class="staff-update-list">
      <?php foreach ($dashboardUpdates as $update): ?>
        <button class="staff-update-row" type="button" data-panel-shortcut="<?= sp_h($update[2]) ?>" data-search-row>
          <span><?= sp_panel_icon((string) $update[2]) ?></span>
          <strong><?= sp_h($update[0]) ?></strong>
          <small><?= sp_h($update[1]) ?></small>
        </button>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="staff-dashboard-panel">
    <div class="staff-card-heading">
      <div>
        <p>Recent Orders</p>
        <h2>Latest buyers</h2>
      </div>
      <button class="staff-secondary-btn" type="button" data-panel-shortcut="orders">Open</button>
    </div>
    <div class="staff-compact-list">
      <?php foreach ($recentOrders as $order): ?>
        <div class="staff-compact-row" data-search-row>
          <span>
            <strong><?= sp_h($order['order_id'] ?? 'Order') ?></strong>
            <small><?= sp_h($order['buyer_name'] ?? '') ?></small>
          </span>
          <em><?= sp_money((int) ($order['total'] ?? 0)) ?></em>
        </div>
      <?php endforeach; ?>
      <?php if (!$recentOrders): ?>
        <div class="staff-compact-row"><span><strong>No recent orders</strong><small>Order activity appears here.</small></span></div>
      <?php endif; ?>
    </div>
  </article>

  <article class="staff-dashboard-panel">
    <div class="staff-card-heading">
      <div>
        <p>Payments</p>
        <h2>Finance queue</h2>
      </div>
      <button class="staff-secondary-btn" type="button" data-panel-shortcut="payments">Open</button>
    </div>
    <div class="staff-compact-list">
      <?php foreach ($recentPayments as $order): ?>
        <div class="staff-compact-row" data-search-row>
          <span>
            <strong><?= sp_h($order['payment_reference'] ?? $order['reference'] ?? 'Payment') ?></strong>
            <small><?= sp_h($order['payment_method_label'] ?? $order['payment_method'] ?? 'Manual') ?></small>
          </span>
          <em class="<?= sp_status_class($order['payment_status'] ?? 'Pending') ?>"><?= sp_h($order['payment_status'] ?? 'Pending') ?></em>
        </div>
      <?php endforeach; ?>
      <?php if (!$recentPayments): ?>
        <div class="staff-compact-row"><span><strong>No payment activity</strong><small>Payment records appear here.</small></span></div>
      <?php endif; ?>
    </div>
  </article>
</section>
<?php else: ?>
<section class="staff-dashboard-grid" data-subsection="analytics">
  <article class="staff-dashboard-panel staff-dashboard-panel--wide">
    <div class="staff-card-heading">
      <div>
        <p>Owned Events</p>
        <h2>Event worklist</h2>
      </div>
      <button class="staff-secondary-btn" type="button" data-panel-shortcut="events">Open Events</button>
    </div>
    <div class="staff-compact-list">
      <?php foreach (array_slice($payload['events'], 0, 6) as $event): ?>
        <div class="staff-compact-row" data-search-row>
          <span>
            <strong><?= sp_h($event['title']) ?></strong>
            <small><?= sp_h($event['date']) ?> - <?= sp_h($event['venue']) ?></small>
          </span>
          <em><?= sp_h($event['status']) ?></em>
        </div>
      <?php endforeach; ?>
      <?php if (!$payload['events']): ?>
        <div class="staff-compact-row"><span><strong>No owned events yet</strong><small>Ask an admin to assign an event.</small></span></div>
      <?php endif; ?>
    </div>
  </article>

  <article class="staff-dashboard-panel">
    <div class="staff-card-heading">
      <div>
        <p>Notifications</p>
        <h2>Updates</h2>
      </div>
    </div>
    <div class="staff-update-list">
      <?php foreach ($dashboardUpdates as $update): ?>
        <button class="staff-update-row" type="button" data-panel-shortcut="<?= sp_h($update[2]) ?>" data-search-row>
          <span><?= sp_panel_icon((string) $update[2]) ?></span>
          <strong><?= sp_h($update[0]) ?></strong>
          <small><?= sp_h($update[1]) ?></small>
        </button>
      <?php endforeach; ?>
    </div>
  </article>
</section>
<?php endif; ?>
