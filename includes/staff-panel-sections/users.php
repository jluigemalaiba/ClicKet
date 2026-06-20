<?php
$people = $payload['users'] ?? [];
$people = array_values(array_filter($people, static function (array $person): bool {
    return empty($person['disabled']) && strtolower((string) ($person['status'] ?? 'active')) === 'active';
}));
$peopleByRole = ['customer' => [], 'admin' => [], 'organizer' => []];
foreach ($people as $person) {
    $role = strtolower((string) ($person['role'] ?? 'customer'));
    if (isset($peopleByRole[$role])) $peopleByRole[$role][] = $person;
}
$venueAssignments = clicketStaffAssignmentOptions();
$peopleForClient = array_map(static function (array $person): array {
    unset($person['password']);
    return $person;
}, $people);
?>

<section class="staff-people-workspace" data-subsection="table">
  <header class="staff-people-head"><div><p>People management</p><h2>People, roles, and access—kept in their own lanes.</h2><span>Open a person to review their details. Archived accounts can no longer sign in.</span></div><div class="staff-people-head__counts"><span><b><?= sp_count(count($peopleByRole['customer'])) ?></b> users</span><span><b><?= sp_count(count($peopleByRole['admin'])) ?></b> admins</span><span><b><?= sp_count(count($peopleByRole['organizer'])) ?></b> organizers</span></div></header>
  <nav class="staff-people-tabs" aria-label="People account groups">
    <button class="is-active" type="button" data-people-tab="customer"><span>Users</span><b><?= sp_count(count($peopleByRole['customer'])) ?></b></button>
    <button type="button" data-people-tab="admin"><span>Admins</span><b><?= sp_count(count($peopleByRole['admin'])) ?></b></button>
    <button type="button" data-people-tab="organizer"><span>Organizers</span><b><?= sp_count(count($peopleByRole['organizer'])) ?></b></button>
  </nav>

  <?php
  $groups = [
    'customer' => ['Users', 'Website accounts', 'Archive account'],
    'admin' => ['Admins', 'Full platform access', 'Disable account'],
    'organizer' => ['Organizers', 'Venue-scoped access', 'Disable account'],
  ];
  ?>
  <?php foreach ($groups as $role => [$label, $description, $actionLabel]): ?>
    <section class="staff-people-group <?= $role === 'customer' ? 'is-active' : '' ?>" data-people-group="<?= sp_h($role) ?>" data-subsection="<?= $role === 'customer' ? 'table' : ($role === 'admin' ? 'roles' : 'assignment') ?>">
      <div class="staff-people-group__head"><div><p><?= sp_h($label) ?></p><h3><?= sp_h($description) ?></h3></div><?php if ($role !== 'customer'): ?><button class="staff-action-btn" type="button" data-person-create="<?= sp_h($role) ?>">+ Add <?= rtrim($label, 's') ?></button><?php endif; ?></div>
      <div class="staff-people-grid">
        <?php foreach ($peopleByRole[$role] as $person): ?>
          <?php $isArchived = !empty($person['disabled']) || strtolower((string) ($person['status'] ?? 'Active')) !== 'active'; $venues = is_array($person['venues'] ?? null) ? array_map('clicketStaffAssignmentLabel', array_filter($person['venues'])) : []; ?>
          <article class="staff-person-card <?= $isArchived ? 'is-archived' : '' ?>" data-search-row data-person-id="<?= sp_h($person['id'] ?? '') ?>">
            <div class="staff-person-card__top"><span class="staff-person-avatar"><?= sp_h(sp_initials((string) ($person['name'] ?? 'User'))) ?></span><span class="staff-status <?= $isArchived ? 'is-muted' : 'is-success' ?>"><?= $isArchived ? 'Archived' : 'Active' ?></span></div>
            <strong><?= sp_h($person['name'] ?? 'Unnamed user') ?></strong><small><?= sp_h($person['email'] ?? '') ?></small>
            <div class="staff-person-card__facts"><span><b>Role</b><?= sp_h(ucfirst($role)) ?></span><?php if ($role === 'organizer'): ?><span><b>Venue</b><?= sp_h(implode(', ', $venues) ?: 'Not assigned') ?></span><?php elseif ($role === 'customer'): ?><span><b>Joined</b><?= sp_h(clicketNewsDate((string) ($person['created_at'] ?? ''))) ?></span><?php else: ?><span><b>Scope</b>All venues</span><?php endif; ?></div>
            <div class="staff-person-card__actions"><button type="button" data-person-view="<?= sp_h($person['id'] ?? '') ?>">View</button><?php if ($role === 'organizer' && !$isArchived): ?><button type="button" data-person-assign="<?= sp_h($person['id'] ?? '') ?>">Assign venue</button><?php endif; ?><?php if (!$isArchived): ?><button type="button" class="is-danger" data-person-action="<?= $role === 'customer' ? 'archive' : 'disable' ?>" data-person-id="<?= sp_h($person['id'] ?? '') ?>"><?= sp_h($actionLabel) ?></button><?php endif; ?></div>
          </article>
        <?php endforeach; ?>
        <?php if (!$peopleByRole[$role]): ?><p class="staff-empty-state">No <?= strtolower($label) ?> found yet.</p><?php endif; ?>
      </div>
    </section>
  <?php endforeach; ?>
</section>

<script type="application/json" id="staffPeopleJson"><?= json_encode(['people' => $peopleForClient, 'venues' => $venueAssignments], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
