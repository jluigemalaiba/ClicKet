<?php $featuredVenue = $payload['venues'][0] ?? null; ?>

<section class="staff-section" data-subsection="cards">
  <div class="staff-section-heading">
    <div>
      <p><?= $isAdmin ? 'Venue Management' : 'My Venues' ?></p>
      <h2>Supported venues, SVG seat maps, capacity, revenue, and assignment</h2>
    </div>
    <button class="staff-action-btn" type="button" data-open-modal data-modal-title="Venue Details" data-modal-type="venue-detail">Open Venue Details</button>
  </div>

  <div class="staff-venue-grid">
    <?php foreach ($payload['venues'] as $venue): ?>
      <article class="staff-venue-card" data-search-row>
        <div class="staff-venue-card-head">
          <div>
            <h3><?= sp_h($venue['venue']) ?></h3>
            <span><?= sp_h($venue['variant']) ?> &middot; <?= sp_h($venue['profileVenue']) ?></span>
          </div>
          <span class="staff-status is-success">Enabled</span>
        </div>

        <div class="staff-venue-media">
          <img src="assets/<?= sp_h($venue['svg']) ?>" alt="<?= sp_h($venue['venue']) ?> SVG seat map preview">
        </div>

        <div class="staff-meter" aria-label="Occupancy">
          <span style="width: <?= (int) $venue['occupancy'] ?>%"></span>
        </div>

        <div class="staff-venue-stats">
          <span><strong><?= sp_count($venue['capacity']) ?></strong> capacity</span>
          <span><strong><?= sp_count($venue['sold']) ?></strong> sold</span>
          <span><strong><?= sp_money((int) $venue['sales']) ?></strong> revenue</span>
        </div>

        <div class="staff-meta-grid">
          <span>Organizer <strong><?= $isAdmin ? 'Assignable' : sp_h($staff['name'] ?? 'Organizer') ?></strong></span>
          <span>Status <strong>Live selling</strong></span>
        </div>

        <div class="staff-card-actions">
          <button type="button" data-open-modal data-modal-title="<?= sp_h($venue['venue']) ?> Seat Map" data-modal-type="seat-map">View SVG Seat Map</button>
          <button type="button" data-open-modal data-modal-title="Assign Organizer" data-modal-type="assignment" <?= $isAdmin ? '' : 'disabled' ?>>Assign Organizer</button>
          <button type="button" data-open-modal data-modal-title="Venue Status" data-modal-type="status-control" <?= $isAdmin ? '' : 'disabled' ?>>Enable/Disable</button>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php if ($featuredVenue): ?>
  <section class="staff-grid-two" data-subsection="details">
    <article class="staff-card staff-map-card">
      <div class="staff-card-heading">
        <div>
          <p>SVG Seat Map Viewer</p>
          <h2><?= sp_h($featuredVenue['venue']) ?> map preview</h2>
        </div>
        <span><?= sp_h($featuredVenue['svg']) ?></span>
      </div>
      <div class="staff-svg-viewer">
        <img src="assets/<?= sp_h($featuredVenue['svg']) ?>" alt="<?= sp_h($featuredVenue['venue']) ?> interactive SVG seat map">
      </div>
    </article>

    <article class="staff-card">
      <div class="staff-card-heading">
        <div>
          <p>Venue Details Page</p>
          <h2>Operational summary</h2>
        </div>
      </div>
      <div class="staff-detail-list">
        <div><span>Venue</span><strong><?= sp_h($featuredVenue['venue']) ?></strong></div>
        <div><span>Variant</span><strong><?= sp_h($featuredVenue['variant']) ?></strong></div>
        <div><span>Total Capacity</span><strong><?= sp_count($featuredVenue['capacity']) ?></strong></div>
        <div><span>Available Seats</span><strong><?= sp_count($featuredVenue['available']) ?></strong></div>
        <div><span>Revenue</span><strong><?= sp_money((int) $featuredVenue['sales']) ?></strong></div>
        <div><span>Occupancy</span><strong><?= sp_count($featuredVenue['occupancy']) ?>%</strong></div>
      </div>
      <div class="staff-assignment-panel" data-subsection="assignment">
        <label>
          <span>Organizer Assignment</span>
          <select <?= $isAdmin ? '' : 'disabled' ?>>
            <?php foreach ($payload['staff'] as $account): ?>
              <option><?= sp_h($account['name'] ?? $account['email'] ?? 'Organizer') ?> - <?= sp_h($account['role'] ?? 'organizer') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          <span>Venue Availability</span>
          <select <?= $isAdmin ? '' : 'disabled' ?>>
            <option>Enabled for public sale</option>
            <option>Disabled for maintenance</option>
            <option>Hidden from new event creation</option>
          </select>
        </label>
      </div>
    </article>
  </section>
<?php endif; ?>

<section class="staff-section" data-subsection="assignment">
  <div class="staff-section-heading">
    <div>
      <p>Capacity & Revenue Overview</p>
      <h2>All venue variants in scope</h2>
    </div>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Venue</th>
          <th>Variant</th>
          <th>SVG Map</th>
          <th>Capacity</th>
          <th>Sold</th>
          <th>Available</th>
          <th>Revenue</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payload['venues'] as $venue): ?>
          <tr data-search-row>
            <td><strong><?= sp_h($venue['venue']) ?></strong><small><?= sp_h($venue['profileVenue']) ?></small></td>
            <td><?= sp_h($venue['variant']) ?></td>
            <td><?= sp_h($venue['svg']) ?></td>
            <td><?= sp_count($venue['capacity']) ?></td>
            <td><?= sp_count($venue['sold']) ?></td>
            <td><?= sp_count($venue['available']) ?></td>
            <td><?= sp_money((int) $venue['sales']) ?></td>
            <td><span class="staff-status is-success">Enabled</span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
