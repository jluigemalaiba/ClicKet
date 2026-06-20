<?php
$revenueTrend = $payload['revenueTrend'] ?? [];
$paymentMethods = $payload['paymentMethods'] ?? [];
$maxTrend = max(1, ...array_map(static fn (array $point): int => (int) ($point['value'] ?? 0), $revenueTrend ?: [['value' => 1]]));
$scannedTickets = (int) ($metrics['checkedIn'] ?? 0);
$attendanceRate = (int) ($metrics['attendanceRate'] ?? 0);
$duplicateScans = (int) ($metrics['duplicateScans'] ?? 0);
$paymentTotal = max(1, array_sum(array_map(static fn (array $method): int => (int) ($method['sales'] ?? 0), $paymentMethods)));
$primaryPayment = $paymentMethods[0] ?? ['method' => 'No payments', 'sales' => 0, 'orders' => 0];
$topEvents = $payload['topEvents'] ?? [];
$recentOrders = array_slice(array_reverse($payload['orders'] ?? []), 0, 5);
$heatValues = array_values(array_map(static fn (array $point): int => (int) ($point['value'] ?? 0), $revenueTrend));
if (!$heatValues) $heatValues = [18, 30, 44, 22, 61, 40, 25];
$heatMax = max(1, ...$heatValues);
$ticketTrend = $payload['ticketTrend'] ?? [];
$lineMax = max(1, ...array_map(static fn (array $point): int => (int) ($point['value'] ?? 0), $ticketTrend ?: [['value' => 1]]));
$linePoints = [];
$lineLabels = [];
foreach ($ticketTrend as $index => $point) {
    $count = max(1, count($ticketTrend) - 1);
    $x = 6 + ($index * (88 / $count));
    $y = 84 - (68 * ((int) ($point['value'] ?? 0) / $lineMax));
    $linePoints[] = round($x, 2) . ',' . round($y, 2);
    $lineLabels[] = (string) ($point['label'] ?? '');
}
?>

<section class="staff-reports-workspace" data-subsection="sales">
  <header class="staff-reports-head"><div><p>Analytics overview</p><h2>See the story behind every ticket.</h2><span>Sales, attendance, venue performance, and payment health in one reporting view.</span></div><div class="staff-report-actions"><button class="staff-secondary-btn" type="button">Last 30 days</button><button class="staff-secondary-btn" type="button">Export PDF</button><button class="staff-action-btn" type="button">Export Excel</button></div></header>

  <div class="staff-report-kpis">
    <article><span>Gross revenue</span><strong><?= sp_money($metrics['sales']) ?></strong><small>Paid order total</small></article>
    <article><span>Tickets issued</span><strong><?= sp_count($metrics['tickets']) ?></strong><small><?= sp_count($metrics['orders']) ?> orders in scope</small></article>
    <article><span>Attendance</span><strong><?= sp_count($attendanceRate) ?>%</strong><small><?= sp_count($scannedTickets) ?> tickets scanned</small></article>
    <article><span>Service fees</span><strong><?= sp_money($metrics['serviceFees']) ?></strong><small>Captured across all payments</small></article>
  </div>

  <div class="staff-report-analytics-grid">
    <article class="staff-report-card staff-report-card--trend"><header><div><p>Revenue performance</p><h3>Sales over time</h3></div><span><?= sp_money($metrics['sales']) ?> total</span></header><div class="staff-report-chart"><div class="staff-report-chart__scale"><span><?= sp_money($maxTrend) ?></span><span><?= sp_money((int) ($maxTrend / 2)) ?></span><span>PHP 0</span></div><div class="staff-report-bars"><?php foreach ($revenueTrend as $point): ?><div><i style="height: <?= sp_percent((int) ($point['value'] ?? 0), $maxTrend) ?>%"></i><span><?= sp_h($point['label'] ?? '') ?></span></div><?php endforeach; ?><?php if (!$revenueTrend): ?><p>No revenue data yet.</p><?php endif; ?></div></div><footer><span>Daily paid sales</span><strong>Live order data</strong></footer></article>
    <article class="staff-report-card staff-report-card--line"><header><div><p>Ticket movement</p><h3>Tickets issued over time</h3></div><span><?= sp_count($metrics['tickets']) ?> total</span></header><div class="staff-line-chart"><svg viewBox="0 0 100 100" preserveAspectRatio="none" role="img" aria-label="Ticket movement line graph"><defs><linearGradient id="ticketLineFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#e8162b" stop-opacity=".25"></stop><stop offset="100%" stop-color="#e8162b" stop-opacity="0"></stop></linearGradient></defs><path class="staff-line-chart__grid" d="M5 16H95M5 50H95M5 84H95"></path><?php if ($linePoints): ?><polygon points="6,84 <?= implode(' ', $linePoints) ?> 94,84" fill="url(#ticketLineFill)"></polygon><polyline points="<?= implode(' ', $linePoints) ?>" class="staff-line-chart__path"></polyline><?php foreach ($linePoints as $point): ?><circle cx="<?= explode(',', $point)[0] ?>" cy="<?= explode(',', $point)[1] ?>" r="1.8" class="staff-line-chart__dot"></circle><?php endforeach; ?><?php endif; ?></svg><div class="staff-line-chart__labels"><?php foreach ($lineLabels as $label): ?><span><?= sp_h($label) ?></span><?php endforeach; ?><?php if (!$lineLabels): ?><span>No ticket data yet.</span><?php endif; ?></div></div><footer><span>Issued tickets</span><strong>Daily movement</strong></footer></article>
    <article class="staff-report-card staff-report-card--payments"><header><div><p>Payment health</p><h3>Source of revenue</h3></div><span><?= sp_count(count($paymentMethods)) ?> methods</span></header><div class="staff-payment-mix"><div class="staff-payment-mix__donut" style="--primary: <?= sp_percent((int) ($primaryPayment['sales'] ?? 0), $paymentTotal) ?>%"><span><?= sp_percent((int) ($primaryPayment['sales'] ?? 0), $paymentTotal) ?>%</span><small><?= sp_h($primaryPayment['method'] ?? '') ?></small></div><div class="staff-payment-mix__list"><?php foreach (array_slice($paymentMethods, 0, 4) as $method): ?><div><span><i></i><?= sp_h($method['method']) ?></span><strong><?= sp_percent((int) ($method['sales'] ?? 0), $paymentTotal) ?>%</strong><small><?= sp_money((int) ($method['sales'] ?? 0)) ?></small></div><?php endforeach; ?></div></div></article>
    <article class="staff-report-card staff-report-card--attendance" data-subsection="attendance"><header><div><p>Attendance</p><h3>Gate activity</h3></div><span>Event scans</span></header><div class="staff-attendance-meter"><div style="--attendance: <?= $attendanceRate ?>%"><span><?= $attendanceRate ?>%</span></div><section><strong><?= sp_count($scannedTickets) ?></strong><span>Scanned tickets</span><strong><?= sp_count(max(0, $metrics['tickets'] - $scannedTickets)) ?></strong><span>Still unused</span></section></div><footer><span>Duplicate warnings</span><strong><?= sp_count($duplicateScans) ?></strong></footer></article>
  </div>
</section>

<section class="staff-report-lower-grid" data-subsection="venue">
  <article class="staff-report-card staff-report-card--table"><header><div><p>Venue performance</p><h3>Revenue and occupancy</h3></div><span><?= sp_count(count($payload['venues'])) ?> venues</span></header><div class="staff-report-venue-list"><?php foreach ($payload['venues'] as $venue): ?><div><div><strong><?= sp_h($venue['venue']) ?></strong><small><?= sp_h($venue['variant']) ?> · <?= sp_count($venue['sold']) ?> sold</small></div><div class="staff-report-progress"><i style="width: <?= sp_count($venue['occupancy']) ?>%"></i></div><b><?= sp_count($venue['occupancy']) ?>%</b><em><?= sp_money((int) $venue['sales']) ?></em></div><?php endforeach; ?><?php if (!$payload['venues']): ?><p class="staff-empty-state">Venue performance will appear once events are active.</p><?php endif; ?></div></article>
  <article class="staff-report-card staff-report-card--exports" data-subsection="exports"><header><div><p>Export center</p><h3>Ready when your report is.</h3></div></header><div class="staff-report-export-list"><button type="button"><span>PDF</span><div><strong>Executive summary</strong><small>Sales, attendance, and venue performance</small></div><b>→</b></button><button type="button"><span>XLS</span><div><strong>Raw report workbook</strong><small>Orders, payments, tickets, and fees</small></div><b>→</b></button><button type="button"><span>CSV</span><div><strong>Venue performance data</strong><small>Occupancy and revenue by venue</small></div><b>→</b></button></div></article>
</section>

<section class="staff-report-secondary-grid">
  <article class="staff-report-card staff-report-card--heatmap"><header><div><p>Sales per week</p><h3>Revenue intensity</h3></div><span>Last 4 weeks</span></header><div class="staff-heatmap"><div class="staff-heatmap__days"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div><?php for ($week = 0; $week < 4; $week++): ?><div class="staff-heatmap__week"><small>W<?= $week + 1 ?></small><div><?php for ($day = 0; $day < 7; $day++): ?><?php $value = $heatValues[($week * 2 + $day) % count($heatValues)]; ?><i style="--heat: <?= max(14, sp_percent($value, $heatMax)) ?>%" title="<?= sp_money($value) ?>"></i><?php endfor; ?></div></div><?php endfor; ?><footer><span>Light = lower sales</span><strong>Deep red = higher sales</strong></footer></div></article>
  <article class="staff-report-card staff-report-card--events"><header><div><p>Top events</p><h3>Revenue leaders</h3></div><span>Live ranking</span></header><div class="staff-report-top-events"><?php foreach (array_slice($topEvents, 0, 5) as $index => $event): ?><div><b><?= $index + 1 ?></b><section><strong><?= sp_h($event['title'] ?? 'Untitled event') ?></strong><small><?= sp_count($event['tickets'] ?? 0) ?> tickets sold</small></section><em><?= sp_money((int) ($event['sales'] ?? 0)) ?></em></div><?php endforeach; ?><?php if (!$topEvents): ?><p class="staff-empty-state">Event rankings will appear after paid orders are recorded.</p><?php endif; ?></div></article>
  <article class="staff-report-card staff-report-card--activity"><header><div><p>Recent activity</p><h3>Order movement</h3></div><span><?= sp_count(count($recentOrders)) ?> latest</span></header><div class="staff-report-activity-list"><?php foreach ($recentOrders as $order): ?><div><i></i><section><strong><?= sp_h($order['buyer_name'] ?? 'Customer') ?></strong><span><?= sp_h($order['event_title'] ?? $order['event'] ?? 'Event') ?></span><small><?= sp_h($order['booked_at'] ?? '') ?></small></section><em><?= sp_money((int) ($order['total'] ?? 0)) ?></em></div><?php endforeach; ?><?php if (!$recentOrders): ?><p class="staff-empty-state">Recent activity will appear after new orders are placed.</p><?php endif; ?></div></article>
</section>
