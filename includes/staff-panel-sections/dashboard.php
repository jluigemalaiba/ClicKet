<section class="staff-hero" data-subsection="overview">
  <div class="staff-hero-copy">
    <p><?= $isAdmin ? 'Complete Access' : 'Venue Scoped' ?></p>
    <h2><?= $isAdmin ? 'System-wide ticketing command center' : 'Manage only the venues assigned to you' ?></h2>
    <span><?= $isAdmin ? 'Admins can view and override all organizer-created events, archived records, payments, seats, and logs.' : 'Organizer actions are restricted by assigned venue/event scope. Unassigned venues stay hidden from management tables.' ?></span>
  </div>
  <div class="staff-hero-panel">
    <strong><?= sp_count(count($payload['venues'])) ?></strong>
    <span><?= $isAdmin ? 'venue layouts available' : 'assigned venue layouts' ?></span>
  </div>
</section>

<section class="staff-kpi-grid" aria-label="Dashboard summary" data-subsection="sales">
  <?php foreach ([
      ['Total sales', sp_money($metrics['sales']), 'Across paid orders in scope'],
      ['Tickets sold', sp_count($metrics['ticketsSold']), 'Valid paid ticket count'],
      ['Active events', sp_count($metrics['activeEvents']), 'Published/draft events in scope'],
      ['Pending payments', sp_count($metrics['pendingPayments']), 'Proof/payment review queue'],
      ['Active reservations', sp_count($metrics['activeReservations']), 'Seat holds still running'],
      ['Low inventory alerts', sp_count($metrics['lowInventory']), 'Venues above alert threshold'],
  ] as $card): ?>
    <article class="staff-kpi-card">
      <span><?= sp_h($card[0]) ?></span>
      <strong><?= sp_h($card[1]) ?></strong>
      <small><?= sp_h($card[2]) ?></small>
    </article>
  <?php endforeach; ?>
</section>

<section class="staff-section staff-module-section" data-subsection="overview">
  <div class="staff-section-heading">
    <div>
      <p>Role Modules</p>
      <h2><?= $isAdmin ? 'Admin Panel Coverage' : 'Organizer Panel Coverage' ?></h2>
    </div>
  </div>
  <div class="staff-module-grid">
    <?php foreach (($isAdmin ? $adminModules : $organizerModules) as $module): ?>
      <article class="staff-module-card">
        <strong><?= sp_h($module[0]) ?></strong>
        <span><?= sp_h($module[1]) ?></span>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="staff-grid-two" data-subsection="alerts">
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Top Events</h2>
      <span>Revenue ranked</span>
    </div>
    <div class="staff-list">
      <?php foreach ($payload['topEvents'] ?: [['title' => 'No paid orders yet', 'sales' => 0, 'tickets' => 0]] as $event): ?>
        <div class="staff-list-row" data-search-row>
          <span><?= sp_h($event['title']) ?></span>
          <strong><?= sp_money((int) $event['sales']) ?></strong>
          <small><?= sp_count($event['tickets']) ?> tickets</small>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
  <article class="staff-card">
    <div class="staff-card-heading">
      <h2>Top Venues</h2>
      <span>Sales by location</span>
    </div>
    <div class="staff-list">
      <?php foreach ($payload['topVenues'] ?: [['venue' => 'No venue sales yet', 'sales' => 0, 'orders' => 0]] as $venue): ?>
        <div class="staff-list-row" data-search-row>
          <span><?= sp_h($venue['venue']) ?></span>
          <strong><?= sp_money((int) $venue['sales']) ?></strong>
          <small><?= sp_count($venue['orders']) ?> orders</small>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
</section>
