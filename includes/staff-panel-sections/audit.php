<section class="staff-section" data-subsection="activity">
  <div class="staff-section-heading">
    <div>
      <p>Audit Logs</p>
      <h2>Admin, organizer, payment, seat, price, event, and archive activity</h2>
    </div>
    <button class="staff-action-btn" type="button">Export Audit Logs</button>
  </div>

  <div class="staff-filter-bar">
    <label>
      <span>Log Type</span>
      <select>
        <option>All logs</option>
        <option>Admin activity logs</option>
        <option>Organizer activity logs</option>
        <option>Payment approval logs</option>
        <option>Seat block logs</option>
        <option>Price change logs</option>
        <option>Event creation logs</option>
        <option>Archive logs</option>
      </select>
    </label>
    <label>
      <span>Actor</span>
      <input type="search" placeholder="Admin or organizer">
    </label>
    <label>
      <span>Date</span>
      <input type="text" placeholder="Today">
    </label>
    <button type="button">Apply Filters</button>
  </div>

  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Log Type</th>
          <th>Actor</th>
          <th>Scope</th>
          <th>Time</th>
          <th>Before / After</th>
          <th>Severity</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $auditRows = array_merge($payload['audit'], [
            ['type' => 'Admin activity logs', 'actor' => 'ClicKet Admin', 'scope' => 'Settings update', 'time' => 'Today 09:12', 'change' => 'Payment methods refreshed', 'severity' => 'Info'],
            ['type' => 'Organizer activity logs', 'actor' => 'MOA Organizer', 'scope' => 'BLACKPINK Born Pink Encore', 'time' => 'Today 10:42', 'change' => 'Schedule edited', 'severity' => 'Info'],
            ['type' => 'Payment approval logs', 'actor' => 'Finance Admin', 'scope' => 'PAY-F06A6F229F24', 'time' => 'Today 11:05', 'change' => 'Pending to Paid', 'severity' => 'Success'],
            ['type' => 'Seat block logs', 'actor' => 'Operations', 'scope' => 'VIP 101 A-27', 'time' => 'Today 12:18', 'change' => 'Available to Blocked', 'severity' => 'Warning'],
            ['type' => 'Price change logs', 'actor' => 'Admin', 'scope' => 'VIP tier', 'time' => 'Today 13:07', 'change' => 'PHP 9,500 to PHP 9,850', 'severity' => 'Warning'],
            ['type' => 'Event creation logs', 'actor' => 'Organizer', 'scope' => 'New event draft', 'time' => 'Today 14:22', 'change' => 'Draft created', 'severity' => 'Info'],
            ['type' => 'Archive logs', 'actor' => 'Admin', 'scope' => 'Past performance', 'time' => 'Today 15:11', 'change' => 'Restored from archive', 'severity' => 'Success'],
        ]);
        ?>
        <?php foreach ($auditRows as $audit): ?>
          <tr data-search-row>
            <td><strong><?= sp_h($audit['type']) ?></strong></td>
            <td><?= sp_h($audit['actor']) ?></td>
            <td><?= sp_h($audit['scope']) ?></td>
            <td><?= sp_h($audit['time']) ?></td>
            <td><?= sp_h($audit['change'] ?? 'Tracked per action') ?></td>
            <td><span class="staff-status <?= sp_status_class($audit['severity'] ?? 'Info') ?>"><?= sp_h($audit['severity'] ?? 'Info') ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
