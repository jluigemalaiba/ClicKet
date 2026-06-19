<?php
$inventoryVenue = $payload['venues'][0] ?? null;
$seatStatuses = [
    ['Available', 'is-available', max(0, (int) ($inventoryVenue['available'] ?? 0))],
    ['Sold', 'is-sold', max(0, (int) ($inventoryVenue['sold'] ?? 0))],
    ['Held', 'is-held', max(0, (int) ($inventoryVenue['held'] ?? 0))],
    ['Blocked', 'is-blocked', 14],
    ['Accessible', 'is-accessible', 18],
    ['Complimentary', 'is-comp', 8],
];
?>

<section class="staff-grid-two staff-grid-two--inventory" data-subsection="seat-map">
  <article class="staff-card staff-card--wide">
    <div class="staff-card-heading">
      <div>
        <p>Interactive SVG Seat Map</p>
        <h2><?= sp_h($inventoryVenue['venue'] ?? 'Venue') ?> inventory control</h2>
      </div>
      <span><?= sp_h($inventoryVenue['variant'] ?? 'Seat map') ?></span>
    </div>

    <div class="staff-seat-toolbar">
      <label>
        <span>Seat Search</span>
        <input type="search" placeholder="Example: VIP 101 A-27">
      </label>
      <select>
        <option>All statuses</option>
        <option>Available seats</option>
        <option>Sold seats</option>
        <option>Held seats</option>
        <option>Blocked seats</option>
        <option>Accessible seats</option>
        <option>Complimentary seats</option>
      </select>
      <button class="staff-secondary-btn" type="button">Find Seat</button>
    </div>

    <div class="staff-seat-map-shell">
      <svg class="staff-seat-map" viewBox="0 0 620 360" role="img" aria-label="Interactive seat status map">
        <rect x="230" y="22" width="160" height="44" rx="8" class="seat-stage"></rect>
        <text x="310" y="50" text-anchor="middle" class="seat-stage-text">STAGE / COURT</text>
        <?php for ($row = 0; $row < 8; $row++): ?>
          <?php for ($col = 0; $col < 18; $col++):
              $index = ($row * 18) + $col;
              $status = ['is-available', 'is-sold', 'is-held', 'is-blocked', 'is-accessible', 'is-comp'][$index % 6];
              $x = 36 + ($col * 31);
              $y = 94 + ($row * 27);
          ?>
            <circle cx="<?= $x ?>" cy="<?= $y ?>" r="8" class="seat-dot <?= sp_h($status) ?>" data-seat="<?= sp_h('S' . ($row + 1) . '-' . ($col + 1)) ?>"></circle>
          <?php endfor; ?>
        <?php endfor; ?>
        <path d="M70 324 C190 282 430 282 550 324" class="seat-bowl"></path>
      </svg>
    </div>
  </article>

  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Seat Status</p>
        <h2>Operational counters</h2>
      </div>
    </div>
    <div class="staff-status-grid">
      <?php foreach ($seatStatuses as $status): ?>
        <div class="staff-status-tile">
          <span class="seat-dot <?= sp_h($status[1]) ?>"></span>
          <strong><?= sp_count($status[2]) ?></strong>
          <small><?= sp_h($status[0]) ?> seats</small>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="staff-card-actions">
      <button type="button">Block Seats</button>
      <button type="button">Release Held Seats</button>
      <button type="button">Mark Accessible</button>
      <button type="button">Issue Complimentary</button>
    </div>
  </article>
</section>

<section class="staff-grid-two" data-subsection="section">
  <article class="staff-card staff-card--flush">
    <div class="staff-card-heading staff-card-heading--padded">
      <div>
        <p>Section Inventory Analytics</p>
        <h2>Seats by section</h2>
      </div>
    </div>
    <div class="staff-table-wrap staff-table-wrap--embedded">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Venue</th>
            <th>Section</th>
            <th>Available</th>
            <th>Sold</th>
            <th>Held</th>
            <th>Blocked</th>
            <th>Accessible</th>
            <th>Comp</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($payload['sectionInventory'], 0, 10) as $section): ?>
            <tr data-search-row>
              <td><?= sp_h($section['venue']) ?></td>
              <td><strong><?= sp_h($section['section']) ?></strong></td>
              <td><?= sp_count($section['available']) ?></td>
              <td><?= sp_count($section['sold']) ?></td>
              <td><?= sp_count($section['held']) ?></td>
              <td><?= sp_count($section['blocked']) ?></td>
              <td><?= sp_count($section['accessible']) ?></td>
              <td><?= sp_count($section['complimentary']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>

  <article class="staff-card staff-card--flush" data-subsection="tier">
    <div class="staff-card-heading staff-card-heading--padded">
      <div>
        <p>Tier Inventory Analytics</p>
        <h2>Availability by price tier</h2>
      </div>
    </div>
    <div class="staff-table-wrap staff-table-wrap--embedded">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Tier</th>
            <th>Venue</th>
            <th>Capacity</th>
            <th>Sold</th>
            <th>Available</th>
            <th>Revenue</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($payload['tierInventory'], 0, 10) as $tier): ?>
            <tr data-search-row>
              <td><strong><?= sp_h($tier['tier']) ?></strong><small><?= sp_h($tier['variant']) ?></small></td>
              <td><?= sp_h($tier['venue']) ?></td>
              <td><?= sp_count($tier['capacity']) ?></td>
              <td><?= sp_count($tier['sold']) ?></td>
              <td><?= sp_count($tier['available']) ?></td>
              <td><?= sp_money((int) $tier['revenue']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>
</section>
