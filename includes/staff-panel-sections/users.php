<section class="staff-section" data-subsection="table">
  <div class="staff-section-heading">
    <div>
      <p>User Management</p>
      <h2>Customers, organizers, role controls, and assignments</h2>
    </div>
    <button class="staff-action-btn" type="button" data-open-modal data-modal-title="Role Management" data-modal-type="role-management">Manage Roles</button>
  </div>

  <div class="staff-filter-bar">
    <label>
      <span>Role</span>
      <select>
        <option>All roles</option>
        <option>Admin</option>
        <option>Organizer</option>
        <option>Customer</option>
      </select>
    </label>
    <label>
      <span>Status</span>
      <select>
        <option>Active and restricted</option>
        <option>Active</option>
        <option>Suspended</option>
        <option>Disabled</option>
      </select>
    </label>
    <label>
      <span>Search</span>
      <input type="search" placeholder="Name or email">
    </label>
    <button type="button">Apply Filters</button>
  </div>

  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Email</th>
          <th>Role</th>
          <th>Assigned Venue</th>
          <th>Order History</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_merge($payload['staff'], array_slice($payload['users'], 0, 10)) as $index => $user):
            $roleName = (string) ($user['role'] ?? 'customer');
            $venues = is_array($user['venues'] ?? null) ? implode(', ', $user['venues']) : 'Customer account';
        ?>
          <tr data-search-row>
            <td><strong><?= sp_h($user['name'] ?? 'User') ?></strong><small><?= sp_h($user['id'] ?? '') ?></small></td>
            <td><?= sp_h($user['email'] ?? '') ?></td>
            <td><?= sp_h(ucwords(str_replace('_', ' ', $roleName))) ?></td>
            <td><?= sp_h($venues) ?></td>
            <td><?= sp_count(($index * 3) % 12) ?> orders</td>
            <td><span class="staff-status <?= $index % 9 === 0 ? 'is-warning' : 'is-success' ?>"><?= $index % 9 === 0 ? 'Review' : 'Active' ?></span></td>
            <td>
              <button type="button" data-open-modal data-modal-title="<?= sp_h($user['name'] ?? 'User') ?>" data-modal-type="user-history">History</button>
              <button type="button">Assign</button>
              <button type="button">Suspend</button>
              <button type="button">Disable</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="staff-grid-two" data-subsection="roles">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>Role Management</p>
        <h2>Permission boundaries</h2>
      </div>
    </div>
    <div class="staff-list">
      <div class="staff-list-row"><span>Admin</span><strong>Full access</strong><small>Can manage all venues, events, users, payments, archives, logs, and settings</small></div>
      <div class="staff-list-row"><span>Organizer</span><strong>Assigned scope</strong><small>Can manage assigned venues/events, payments, inventory, orders, tickets, and reports</small></div>
      <div class="staff-list-row"><span>Customer</span><strong>Self service</strong><small>Can browse, favorite, reserve, pay, and view tickets</small></div>
    </div>
  </article>

  <article class="staff-card" data-subsection="assignment">
    <div class="staff-card-heading">
      <div>
        <p>Organizer Assignment</p>
        <h2>Venue access controls</h2>
      </div>
    </div>
    <div class="staff-assignment-panel">
      <label>
        <span>Organizer</span>
        <select>
          <?php foreach ($payload['staff'] as $account): ?>
            <?php if (($account['role'] ?? '') === 'organizer'): ?>
              <option><?= sp_h($account['name'] ?? $account['email']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        <span>Assigned Venue</span>
        <select>
          <?php foreach ($payload['venues'] as $venue): ?>
            <option><?= sp_h($venue['venue']) ?> - <?= sp_h($venue['variant']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <div class="staff-form-actions">
        <button class="staff-secondary-btn" type="button">Preview Scope</button>
        <button class="staff-action-btn" type="button">Save Assignment</button>
      </div>
    </div>
  </article>
</section>
