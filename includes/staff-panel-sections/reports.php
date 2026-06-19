<section class="staff-section" data-subsection="sales">
  <div class="staff-section-heading">
    <div>
      <p>Reports</p>
      <h2>Sales, venue, event, tier, section, attendance, and payment exports</h2>
    </div>
    <div class="staff-split-actions">
      <button class="staff-secondary-btn" type="button">Export PDF</button>
      <button class="staff-action-btn" type="button">Export Excel</button>
    </div>
  </div>

  <div class="staff-report-grid">
    <?php foreach ([
        ['Sales Reports', 'Gross, net, fees, refund impact, and daily revenue'],
        ['Venue Reports', 'Capacity, occupancy, events, revenue, and organizer performance'],
        ['Event Reports', 'Orders, tickets, conversion, performance dates, and status'],
        ['Tier Reports', 'Price tier sales, availability, held seats, and revenue'],
        ['Section Reports', 'Section-level seat movement and blocked-seat activity'],
        ['Attendance Reports', 'Used, remaining, duplicate warnings, and ticket activity'],
        ['Payment Reports', 'Method mix, proof queue, approval logs, refunds, and service fees'],
        ['Export Center', 'PDF and Excel exports for finance and capstone documentation'],
    ] as $report): ?>
      <article class="staff-module-card" data-search-row>
        <span class="staff-module-icon"><?= sp_h(sp_initials($report[0])) ?></span>
        <strong><?= sp_h($report[0]) ?></strong>
        <small><?= sp_h($report[1]) ?></small>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="staff-grid-two" data-subsection="venue">
  <article class="staff-card staff-card--flush">
    <div class="staff-card-heading staff-card-heading--padded">
      <div>
        <p>Venue Report</p>
        <h2>Occupancy and revenue</h2>
      </div>
    </div>
    <div class="staff-table-wrap staff-table-wrap--embedded">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Venue</th>
            <th>Capacity</th>
            <th>Sold</th>
            <th>Occupancy</th>
            <th>Revenue</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payload['venues'] as $venue): ?>
            <tr data-search-row>
              <td><strong><?= sp_h($venue['venue']) ?></strong><small><?= sp_h($venue['variant']) ?></small></td>
              <td><?= sp_count($venue['capacity']) ?></td>
              <td><?= sp_count($venue['sold']) ?></td>
              <td><?= sp_count($venue['occupancy']) ?>%</td>
              <td><?= sp_money((int) $venue['sales']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>

  <article class="staff-card" data-subsection="attendance">
    <div class="staff-card-heading">
      <div>
        <p>Attendance Report</p>
        <h2>Ticket usage performance</h2>
      </div>
    </div>
    <div class="staff-detail-list">
      <div><span>Valid Tickets</span><strong><?= sp_count($metrics['tickets']) ?></strong></div>
      <div><span>Scanned</span><strong><?= sp_count((int) floor($metrics['tickets'] * .42)) ?></strong></div>
      <div><span>Remaining</span><strong><?= sp_count(max(0, $metrics['tickets'] - (int) floor($metrics['tickets'] * .42))) ?></strong></div>
      <div><span>Duplicate Warnings</span><strong><?= sp_count(max(0, (int) floor($metrics['tickets'] * .02))) ?></strong></div>
    </div>
  </article>
</section>
