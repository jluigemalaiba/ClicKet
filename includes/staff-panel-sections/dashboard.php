<?php
$revenueValues = array_column($payload['revenueTrend'], 'value');
$ticketValues = array_column($payload['ticketTrend'], 'value');
$revenueMax = max(1, $revenueValues ? max($revenueValues) : 0);
$ticketMax = max(1, $ticketValues ? max($ticketValues) : 0);
$recentOrders = array_slice(array_reverse($payload['orders']), 0, 6);
$recentPayments = array_slice(array_reverse($payload['payments']), 0, 6);
$dashboardKpis = $isAdmin ? [
    ['Total Sales', sp_money($metrics['sales']), 'Paid order revenue', 'up'],
    ['Tickets Sold', sp_count($metrics['ticketsSold']), 'Paid ticket count', 'up'],
    ['Active Events', sp_count($metrics['activeEvents']), 'Published and draft events', 'flat'],
    ['Pending Payments', sp_count($metrics['pendingPayments']), 'Needs finance review', 'warn'],
    ['Active Reservations', sp_count($metrics['activeReservations']), 'Seat holds still running', 'warn'],
    ['Low Inventory Alerts', sp_count($metrics['lowInventory']), 'Venues above threshold', 'alert'],
] : [
    ['Owned Events', sp_count($metrics['activeEvents']), 'Events created by your organizer account', 'up'],
    ['Active Reservations', sp_count($metrics['activeReservations']), 'Seat holds to monitor', 'warn'],
    ['News Drafts', sp_count(count(array_filter($payload['news'], static fn (array $article): bool => ($article['status'] ?? '') === 'Draft'))), 'Posts waiting for review', 'flat'],
    ['Published Events', sp_count(count(array_filter($payload['events'], static fn (array $event): bool => ($event['status'] ?? '') === 'Published'))), 'Visible owned events', 'up'],
];
?>

<section class="staff-hero staff-hero--dashboard" data-subsection="overview">
  <div class="staff-hero-copy">
    <p><?= $isAdmin ? 'Enterprise Operations' : 'Organizer Operations' ?></p>
    <h2><?= $isAdmin ? 'Revenue, inventory, payments, and venue health in one command view' : 'A focused dashboard for owned event management, reservations, and news posting' ?></h2>
    <span><?= sp_h($panelScope) ?></span>
    <div class="staff-hero-actions">
      <?php if ($isAdmin): ?>
        <button class="staff-action-btn" type="button" data-open-modal data-modal-title="Create Event" data-modal-type="event-form">Create Event</button>
        <button class="staff-secondary-btn" type="button" data-panel-shortcut="payments">Review Payments</button>
        <button class="staff-secondary-btn" type="button" data-panel-shortcut="reports">Export Reports</button>
      <?php else: ?>
        <button class="staff-action-btn" type="button" data-panel-shortcut="events">Manage Events</button>
        <button class="staff-secondary-btn" type="button" data-panel-shortcut="reservations">View Reservations</button>
        <button class="staff-secondary-btn" type="button" data-panel-shortcut="news">Post News</button>
      <?php endif; ?>
    </div>
  </div>
  <div class="staff-hero-panel">
    <span><?= $isAdmin ? 'Today' : 'Owned Scope' ?></span>
    <strong><?= $isAdmin ? sp_money($metrics['sales']) : sp_count($metrics['activeEvents']) ?></strong>
    <small><?= $isAdmin ? sp_count($metrics['ticketsSold']) . ' paid tickets - ' . sp_count($metrics['pendingPayments']) . ' pending payments' : sp_count($metrics['activeReservations']) . ' active reservations - news posting enabled' ?></small>
  </div>
</section>

<section class="staff-kpi-grid" aria-label="Dashboard summary" data-subsection="analytics">
  <?php foreach ($dashboardKpis as $card): ?>
    <article class="staff-kpi-card">
      <span><?= sp_h($card[0]) ?></span>
      <strong><?= sp_h($card[1]) ?></strong>
      <small><?= sp_h($card[2]) ?></small>
      <em class="staff-kpi-spark is-<?= sp_h($card[3]) ?>"></em>
    </article>
  <?php endforeach; ?>
</section>

<?php if ($isAdmin): ?>
<section class="staff-grid-two staff-grid-two--analytics" data-subsection="analytics">
  <article class="staff-card staff-chart-card">
    <div class="staff-card-heading">
      <div>
        <p>Revenue Trend Chart</p>
        <h2>Sales momentum</h2>
      </div>
      <span>Last <?= sp_count(count($payload['revenueTrend'])) ?> periods</span>
    </div>
    <div class="staff-chart-bars" aria-label="Revenue trend">
      <?php foreach ($payload['revenueTrend'] as $point): ?>
        <div class="staff-chart-column" style="--bar-height: <?= sp_percent((int) $point['value'], $revenueMax) ?>%">
          <span></span>
          <small><?= sp_h($point['label']) ?></small>
          <strong><?= sp_money((int) $point['value']) ?></strong>
        </div>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="staff-card staff-chart-card">
    <div class="staff-card-heading">
      <div>
        <p>Ticket Sales Chart</p>
        <h2>Volume by period</h2>
      </div>
      <span>Tickets issued</span>
    </div>
    <div class="staff-chart-bars staff-chart-bars--tickets" aria-label="Ticket sales chart">
      <?php foreach ($payload['ticketTrend'] as $point): ?>
        <div class="staff-chart-column" style="--bar-height: <?= sp_percent((int) $point['value'], $ticketMax) ?>%">
          <span></span>
          <small><?= sp_h($point['label']) ?></small>
          <strong><?= sp_count($point['value']) ?></strong>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
</section>

<section class="staff-grid-three" data-subsection="analytics">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Top Selling Events</p>
        <h2>Revenue ranked</h2>
      </div>
    </div>
    <div class="staff-list">
      <?php foreach ($payload['topEvents'] ?: [['title' => 'No paid orders yet', 'sales' => 0, 'tickets' => 0]] as $event): ?>
        <button class="staff-list-row staff-list-row--button" type="button" data-search-row data-open-modal data-modal-title="<?= sp_h($event['title']) ?>" data-modal-type="event-performance">
          <span><?= sp_h($event['title']) ?></span>
          <strong><?= sp_money((int) $event['sales']) ?></strong>
          <small><?= sp_count($event['tickets']) ?> tickets sold</small>
        </button>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Top Venues</p>
        <h2>Sales by venue</h2>
      </div>
    </div>
    <div class="staff-list">
      <?php foreach ($payload['topVenues'] ?: [['venue' => 'No venue sales yet', 'sales' => 0, 'orders' => 0]] as $venue): ?>
        <button class="staff-list-row staff-list-row--button" type="button" data-search-row data-open-modal data-modal-title="<?= sp_h($venue['venue']) ?>" data-modal-type="venue-detail">
          <span><?= sp_h($venue['venue']) ?></span>
          <strong><?= sp_money((int) $venue['sales']) ?></strong>
          <small><?= sp_count($venue['orders']) ?> orders</small>
        </button>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Low Inventory Alerts</p>
        <h2>Capacity risk</h2>
      </div>
    </div>
    <div class="staff-list">
      <?php foreach ($payload['lowInventory'] ?: array_slice($payload['venues'], 0, 4) as $venue): ?>
        <div class="staff-list-row" data-search-row>
          <span><?= sp_h($venue['venue']) ?> &middot; <?= sp_h($venue['variant']) ?></span>
          <strong><?= sp_count($venue['occupancy']) ?>%</strong>
          <small><?= sp_count($venue['available']) ?> available seats</small>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
</section>

<section class="staff-grid-two" data-subsection="orders">
  <article class="staff-card staff-card--flush">
    <div class="staff-card-heading staff-card-heading--padded">
      <div>
        <p>Recent Orders Table</p>
        <h2>Latest buyer activity</h2>
      </div>
      <button class="staff-secondary-btn" type="button" data-panel-shortcut="orders">Open Orders</button>
    </div>
    <div class="staff-table-wrap staff-table-wrap--embedded">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Buyer</th>
            <th>Event</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $order): ?>
            <tr data-search-row>
              <td><strong><?= sp_h($order['order_id'] ?? 'Order') ?></strong><small><?= sp_h($order['venue'] ?? '') ?></small></td>
              <td><?= sp_h($order['buyer_name'] ?? '') ?><small><?= sp_h($order['buyer_email'] ?? '') ?></small></td>
              <td><?= sp_h($order['event_title'] ?? $order['event'] ?? '') ?><small><?= sp_count(clicketStaffTicketCount($order)) ?> seats</small></td>
              <td><?= sp_money((int) ($order['total'] ?? 0)) ?></td>
              <td><span class="staff-status <?= sp_status_class($order['payment_status'] ?? 'Pending') ?>"><?= sp_h($order['payment_status'] ?? 'Pending') ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$recentOrders): ?>
            <tr><td colspan="5">No recent orders are available in this role scope.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </article>

  <article class="staff-card staff-card--flush">
    <div class="staff-card-heading staff-card-heading--padded">
      <div>
        <p>Recent Payment Activity</p>
        <h2>Finance queue</h2>
      </div>
      <button class="staff-secondary-btn" type="button" data-panel-shortcut="payments">Open Payments</button>
    </div>
    <div class="staff-table-wrap staff-table-wrap--embedded">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Proof</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentPayments as $order): ?>
            <tr data-search-row>
              <td><strong><?= sp_h($order['payment_reference'] ?? $order['reference'] ?? '') ?></strong><small><?= sp_h($order['order_id'] ?? '') ?></small></td>
              <td><?= sp_h($order['payment_method_label'] ?? $order['payment_method'] ?? 'Manual') ?></td>
              <td><?= sp_money((int) ($order['total'] ?? 0)) ?></td>
              <td><?= sp_h(($order['proof_of_payment'] ?? '') !== '' ? 'Uploaded' : 'Not required') ?></td>
              <td><span class="staff-status <?= sp_status_class($order['payment_status'] ?? 'Pending') ?>"><?= sp_h($order['payment_status'] ?? 'Pending') ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$recentPayments): ?>
            <tr><td colspan="5">No payment activity is available in this role scope.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </article>
</section>
<?php else: ?>
<section class="staff-grid-two" data-subsection="analytics">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Owned Event Management</p>
        <h2>Upcoming event worklist</h2>
      </div>
      <button class="staff-secondary-btn" type="button" data-panel-shortcut="events">Open Events</button>
    </div>
    <div class="staff-list">
      <?php foreach (array_slice($payload['events'], 0, 6) as $event): ?>
        <div class="staff-list-row" data-search-row>
          <span><?= sp_h($event['title']) ?></span>
          <strong><?= sp_h($event['status']) ?></strong>
          <small><?= sp_h($event['date']) ?> - <?= sp_h($event['venue']) ?></small>
        </div>
      <?php endforeach; ?>
      <?php if (!$payload['events']): ?>
        <div class="staff-list-row"><span>No owned events yet</span><strong>Empty</strong><small>Ask an admin to create or transfer event ownership to your account.</small></div>
      <?php endif; ?>
    </div>
  </article>

  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Reservation Overview</p>
        <h2>Active and expired holds</h2>
      </div>
      <button class="staff-secondary-btn" type="button" data-panel-shortcut="reservations">Open Reservations</button>
    </div>
    <div class="staff-list">
      <?php foreach (array_slice($payload['reservationRows'], 0, 5) as $hold): ?>
        <div class="staff-list-row" data-search-row>
          <span><?= sp_h($hold['id']) ?> - <?= sp_h($hold['event']) ?></span>
          <strong><?= sp_h($hold['status']) ?></strong>
          <small><?= sp_h($hold['venue']) ?> - <?= sp_count($hold['seats']) ?> seats</small>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
</section>

<section class="staff-section" data-subsection="overview">
  <div class="staff-section-heading">
    <div>
      <p>News Posting</p>
      <h2>Drafts and publishing requests</h2>
    </div>
    <button class="staff-action-btn" type="button" data-panel-shortcut="news">Create News Post</button>
  </div>
  <div class="staff-report-grid">
    <?php foreach ($payload['news'] as $article): ?>
      <article class="staff-module-card" data-search-row>
        <span class="staff-module-icon"><?= sp_h(sp_initials($article['status'])) ?></span>
        <strong><?= sp_h($article['title']) ?></strong>
        <small><?= sp_h($article['status']) ?> - <?= sp_h($article['updated']) ?></small>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="staff-section" data-subsection="overview">
  <div class="staff-section-heading">
    <div>
      <p>Role Coverage</p>
      <h2><?= $isAdmin ? 'Admin Panel module map' : 'Organizer Dashboard module map' ?></h2>
    </div>
  </div>
  <div class="staff-module-grid">
    <?php foreach ($moduleCards as $module): ?>
      <article class="staff-module-card" data-search-row>
        <span class="staff-module-icon"><?= sp_h(sp_initials($module[0])) ?></span>
        <strong><?= sp_h($module[0]) ?></strong>
        <small><?= sp_h($module[1]) ?></small>
      </article>
    <?php endforeach; ?>
  </div>
</section>
