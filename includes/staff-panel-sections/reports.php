<section class="staff-section">
  <div class="staff-section-heading">
    <div>
      <p>Reports</p>
      <h2>Sales, occupancy, payment, attendance, and user reports</h2>
    </div>
    <button class="staff-action-btn" type="button">Export Report</button>
  </div>
  <div class="staff-report-grid">
    <?php foreach ([
        ['Sales by venue', 'Compare revenue across enabled venues'],
        ['Sales by event', 'Rank events by revenue and tickets sold'],
        ['Sales by tier', 'Monitor tier performance and capacity'],
        ['Sales by section', 'Section-level seat movement'],
        ['Occupancy rate', 'Sold and held count against capacity'],
        ['Payment method report', 'Proof and method breakdown'],
        ['Attendance/check-in report', 'Scanned, remaining, duplicate warnings'],
        ['User purchase report', 'Buyer-level historical export'],
    ] as $report): ?>
      <article class="staff-module-card" data-search-row>
        <strong><?= sp_h($report[0]) ?></strong>
        <span><?= sp_h($report[1]) ?></span>
      </article>
    <?php endforeach; ?>
  </div>
</section>
