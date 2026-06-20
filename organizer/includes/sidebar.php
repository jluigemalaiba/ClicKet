<?php
$organizerLinks = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'dashboard'],
    'events' => ['label' => 'Events', 'icon' => 'events'],
    'venues' => ['label' => 'Venues', 'icon' => 'venues'],
    'tickets' => ['label' => 'Tickets', 'icon' => 'tickets'],
    'queue' => ['label' => 'Virtual Queue', 'icon' => 'virtual_queue'],
    'attendees' => ['label' => 'Attendees', 'icon' => 'attendees'],
    'reports' => ['label' => 'Reports', 'icon' => 'reports'],
    'archives' => ['label' => 'Archives', 'icon' => 'archives'],
];

function organizer_sidebar_icon(string $key): string {
    $paths = match ($key) {
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect>',
        'events' => '<path d="M8 2v4"></path><path d="M16 2v4"></path><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M3 10h18"></path><path d="M8 14h.01"></path><path d="M12 14h.01"></path><path d="M16 14h.01"></path>',
        'venues' => '<path d="M4 21V9l8-5 8 5v12"></path><path d="M9 21v-8h6v8"></path><path d="M4 9h16"></path>',
        'tickets' => '<path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7z"></path><path d="M13 5v14"></path>',
        'checkin' => '<path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7z"></path><path d="M9 12l2 2 4-5"></path>',
        'virtual_queue' => '<path d="M4 7h12"></path><path d="M4 12h16"></path><path d="M4 17h10"></path><circle cx="19" cy="7" r="2"></circle><path d="M18 17h2"></path>',
        'attendees' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
        'reports' => '<path d="M4 19V5"></path><path d="M4 19h16"></path><rect x="7" y="11" width="3" height="5"></rect><rect x="12" y="7" width="3" height="9"></rect><rect x="17" y="9" width="3" height="7"></rect>',
        'archives' => '<rect x="3" y="4" width="18" height="5" rx="1"></rect><path d="M5 9v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9"></path><path d="M10 13h4"></path>',
        'signout' => '<path d="M10 17l5-5-5-5"></path><path d="M15 12H3"></path><path d="M21 3v18h-7"></path>',
        default => '<circle cx="12" cy="12" r="8"></circle>',
    };

    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $paths . '</svg>';
}
?>
<aside class="staff-sidebar" id="staffSidebar" aria-label="Organizer navigation">
  <div class="staff-sidebar-header">
    <a class="staff-brand" href="index.php" aria-label="CLICKET home"><img src="assets/Icon_Logo.png" alt=""><img src="assets/Name_Logo.png" alt="CLICKET"></a>
    <button class="staff-sidebar-collapse" type="button" data-sidebar-collapse aria-label="Collapse sidebar" aria-expanded="true"><svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"></path></svg></button>
  </div>
  <nav class="staff-nav staff-nav-tree">
    <?php foreach ($organizerLinks as $key => $item): ?>
      <section class="staff-nav-group">
        <a class="staff-nav-parent <?= $organizerPage === $key ? 'is-active' : '' ?>" href="organizer/<?= sp_h($key) ?>.php" title="<?= sp_h($item['label']) ?>">
          <span class="staff-nav-icon" aria-hidden="true"><?= organizer_sidebar_icon($item['icon']) ?></span>
          <span class="staff-nav-label"><?= sp_h($item['label']) ?></span>
        </a>
      </section>
    <?php endforeach; ?>
  </nav>
  <div class="staff-sidebar-footer">
    <div class="staff-account-card"><span class="staff-account-avatar"><?= sp_h(sp_initials((string) ($staff['name'] ?? 'Organizer'))) ?></span><span class="staff-account-meta"><strong><?= sp_h($staff['name'] ?? 'Organizer') ?></strong><small>Organizer</small></span></div>
    <a class="staff-signout-link" href="auth.php?staff_logout=1"><span aria-hidden="true"><?= organizer_sidebar_icon('signout') ?></span><strong>Sign out</strong></a>
  </div>
</aside>
