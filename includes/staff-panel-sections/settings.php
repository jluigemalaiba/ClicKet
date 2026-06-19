<section class="staff-section" data-subsection="system">
  <div class="staff-section-heading">
    <div>
      <p>Settings</p>
      <h2>System, payment, ticket, venue, and user role configuration</h2>
    </div>
    <button class="staff-action-btn" type="button" <?= $isAdmin ? '' : 'disabled' ?>>Save Settings</button>
  </div>

  <div class="staff-settings-grid">
    <article class="staff-card">
      <div class="staff-card-heading">
        <div>
          <p>System Settings</p>
          <h2>Platform behavior</h2>
        </div>
      </div>
      <div class="staff-setting-list">
        <label><span>Maintenance mode</span><input type="checkbox" <?= $isAdmin ? '' : 'disabled' ?>></label>
        <label><span>Require audit reason for archive</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
        <label><span>Enable organizer event drafts</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
      </div>
    </article>

    <article class="staff-card" data-subsection="payment">
      <div class="staff-card-heading">
        <div>
          <p>Payment Settings</p>
          <h2>Methods and review rules</h2>
        </div>
      </div>
      <div class="staff-setting-list">
        <label><span>GCash</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
        <label><span>Maya</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
        <label><span>Card payments</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
        <label><span>Manual proof review</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
      </div>
    </article>

    <article class="staff-card" data-subsection="ticket">
      <div class="staff-card-heading">
        <div>
          <p>Ticket Settings</p>
          <h2>Validation and print controls</h2>
        </div>
      </div>
      <div class="staff-setting-list">
        <label><span>Non-transferable tickets</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
        <label><span>Allow reissue with audit log</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
        <label><span>Block duplicate ticket use</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
      </div>
    </article>

    <article class="staff-card" data-subsection="roles">
      <div class="staff-card-heading">
        <div>
          <p>User Role Settings</p>
          <h2>Permission templates</h2>
        </div>
      </div>
      <div class="staff-setting-list">
        <label><span>Admin full access</span><input type="checkbox" checked disabled></label>
        <label><span>Organizer venue-scoped access</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
        <label><span>Organizer event management only</span><input type="checkbox" checked <?= $isAdmin ? '' : 'disabled' ?>></label>
      </div>
    </article>
  </div>
</section>

<section class="staff-section" data-subsection="venue">
  <div class="staff-section-heading">
    <div>
      <p>Venue Settings</p>
      <h2>Default rules by venue profile</h2>
    </div>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Venue</th>
          <th>Default Hold</th>
          <th>Service Fee</th>
          <th>Seat Map</th>
          <th>Organizer Approval</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payload['venues'] as $venue): ?>
          <tr data-search-row>
            <td><strong><?= sp_h($venue['venue']) ?></strong><small><?= sp_h($venue['variant']) ?></small></td>
            <td>15 minutes</td>
            <td>PHP 75 per seat</td>
            <td><?= sp_h($venue['svg']) ?></td>
            <td><?= $isAdmin ? 'Admin controlled' : 'Assigned organizer' ?></td>
            <td><span class="staff-status is-success">Enabled</span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
