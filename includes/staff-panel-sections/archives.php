<?php
/**
 * Archive categories shown across the quick-action grid, tabs, legend
 * and per-row icons. Keeping this as a single source of truth means the
 * navbar links, the filter tabs and the table chips never drift apart.
 */
if (!function_exists('sp_archive_categories')) {
    function sp_archive_categories(): array
    {
        return [
            'event'       => ['label' => 'Archived Events',          'code' => 'EV', 'accent' => 'var(--panel-red)',           'desc' => 'Events pulled from active listings'],
            'order'       => ['label' => 'Archived Orders',          'code' => 'OR', 'accent' => 'var(--panel-blue)',          'desc' => 'Completed or expired order records'],
            'ticket'      => ['label' => 'Archived Ticket Scans',    'code' => 'TS', 'accent' => 'var(--panel-teal)',          'desc' => 'Gate-scan logs from past events'],
            'cancelled'   => ['label' => 'Cancelled / Refunded',     'code' => 'CR', 'accent' => 'var(--status-danger)',      'desc' => 'Voided transactions and refunds'],
            'performance' => ['label' => 'Past Performances',       'code' => 'PP', 'accent' => 'var(--status-info)',        'desc' => 'Closed-out show schedules'],
            'user'        => ['label' => 'Archived Users',          'code' => 'US', 'accent' => 'var(--panel-amber)',         'desc' => 'Deactivated guest & buyer accounts'],
            'organizer'   => ['label' => 'Archived Organizers',     'code' => 'OG', 'accent' => 'var(--status-success)',     'desc' => 'Inactive organizer profiles'],
            'admin'       => ['label' => 'Archived Admin Accounts', 'code' => 'AD', 'accent' => 'var(--panel-ink)',           'desc' => 'Revoked staff & admin access'],
        ];
    }
}

if (!function_exists('sp_archive_meta')) {
    function sp_archive_meta(string $type): array
    {
        $t = strtolower($type);
        $categories = sp_archive_categories();

        $rules = [
            'cancelled'   => ['cancel', 'refund'],
            'performance' => ['performance', 'show'],
            'ticket'      => ['ticket', 'scan'],
            'organizer'   => ['organizer'],
            'admin'       => ['admin', 'staff'],
            'user'        => ['user', 'guest', 'buyer', 'account'],
            'order'       => ['order'],
            'event'       => ['event'],
        ];

        foreach ($rules as $key => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($t, $needle)) {
                    return ['key' => $key] + $categories[$key];
                }
            }
        }

        return ['key' => 'event'] + $categories['event'];
    }
}

if (!function_exists('sp_archive_relative')) {
    function sp_archive_relative(?string $datetime): string
    {
        if (!$datetime) {
            return '';
        }
        $ts = strtotime($datetime);
        if (!$ts) {
            return '';
        }
        $diff = time() - $ts;
        if ($diff < 60) {
            return 'Just now';
        }
        $units = [
            31536000 => 'y',
            2592000  => 'mo',
            604800   => 'w',
            86400    => 'd',
            3600     => 'h',
            60       => 'm',
        ];
        foreach ($units as $secs => $label) {
            if ($diff >= $secs) {
                $n = floor($diff / $secs);
                return $n . $label . ' ago';
            }
        }
        return 'Just now';
    }
}

$archiveRows   = $payload['archives'] ?? [];
$archiveGroups = [];
foreach ($archiveRows as $archive) {
    $meta = sp_archive_meta((string) ($archive['type'] ?? ''));
    $key  = $meta['key'];
    if (!isset($archiveGroups[$key])) {
        $archiveGroups[$key] = ['meta' => $meta, 'count' => 0];
    }
    $archiveGroups[$key]['count']++;
}
$archiveTotal = count($archiveRows);
?>
<section class="staff-archive-summary">
  <div class="staff-archive-summary-main">
    <span class="staff-archive-summary-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="5" rx="1.5"></rect><path d="M5 9v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9"></path><path d="M10 13h4"></path></svg>
    </span>
    <div>
      <p>Archive Center</p>
      <strong><?= (int) $archiveTotal ?></strong>
      <span><?= $isAdmin ? 'Total archived records, system-wide' : 'Total archived records in your assigned scope' ?></span>
    </div>
  </div>
  <div class="staff-archive-breakdown">
    <div class="staff-archive-bar" role="img" aria-label="Archive breakdown by category">
      <?php foreach ($archiveGroups as $group): ?>
        <span style="width:<?= $archiveTotal > 0 ? round(($group['count'] / $archiveTotal) * 100, 2) : 0 ?>%; background:<?= $group['meta']['accent'] ?>;"></span>
      <?php endforeach; ?>
      <?php if ($archiveTotal === 0): ?>
        <span style="width:100%; background:var(--panel-soft);"></span>
      <?php endif; ?>
    </div>
    <div class="staff-archive-legend">
      <?php foreach ($archiveGroups as $group): ?>
        <span><i style="background:<?= $group['meta']['accent'] ?>;"></i><?= sp_h($group['meta']['label']) ?> <strong><?= (int) $group['count'] ?></strong></span>
      <?php endforeach; ?>
      <?php if ($archiveTotal === 0): ?>
        <span><i style="background:var(--panel-line);"></i>No archived records yet</span>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="staff-grid-two" data-subsection="events">
  <article class="staff-card" id="archive-categories">
    <div class="staff-card-heading">
      <div>
        <p>Archives</p>
        <h2>Archived records and restore workflow</h2>
      </div>
      <span><?= $isAdmin ? 'System-wide' : 'Assigned scope' ?></span>
    </div>
    <div class="staff-archive-quick-grid">
      <?php foreach (sp_archive_categories() as $key => $cat): ?>
        <button type="button" class="staff-archive-quick-btn" data-archive-jump="<?= sp_h($key) ?>">
          <span class="staff-archive-quick-icon" style="background:<?= $cat['accent'] ?>;"><?= sp_h($cat['code']) ?></span>
          <span>
            <strong><?= sp_h($cat['label']) ?></strong>
            <small><?= sp_h($cat['desc']) ?></small>
          </span>
        </button>
      <?php endforeach; ?>
      <button type="button" class="staff-archive-quick-btn" data-archive-action="export">
        <span class="staff-archive-quick-icon" style="background:var(--panel-blue);">EX</span>
        <span>
          <strong>Export Archive Data</strong>
          <small>Download a CSV/JSON snapshot</small>
        </span>
      </button>
    </div>
  </article>

  <article class="staff-card" data-subsection="restore">
    <div class="staff-card-heading">
      <div>
        <p>Restore Functionality</p>
        <h2>Permission-controlled recovery</h2>
      </div>
    </div>
    <div class="staff-archive-permission-list">
      <div class="staff-archive-permission-row">
        <span class="staff-archive-permission-icon" style="background:var(--panel-red);" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"></path></svg>
        </span>
        <div>
          <span>Admin restore access <strong>All records</strong></span>
          <small>Can restore archived events, orders, ticket scans, users, organizers, and admin accounts</small>
        </div>
      </div>
      <div class="staff-archive-permission-row">
        <span class="staff-archive-permission-icon" style="background:var(--panel-blue);" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.64-6.36"></path><path d="M21 4v5h-5"></path></svg>
        </span>
        <div>
          <span>Organizer restore access <strong>Request only</strong></span>
          <small>Assigned records can be submitted for admin approval</small>
        </div>
      </div>
      <div class="staff-archive-permission-row">
        <span class="staff-archive-permission-icon" style="background:var(--panel-ink);" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="9"></circle></svg>
        </span>
        <div>
          <span>Audit requirement <strong>Reason required</strong></span>
          <small>Every archive and restore action writes an audit row</small>
        </div>
      </div>
    </div>
  </article>
</section>

<section class="staff-section" data-subsection="orders" id="archive-table">
  <div class="staff-section-heading">
    <div>
      <p>Archived Events, Orders, Ticket Scans &amp; More</p>
      <h2>Retention-ready archive table</h2>
    </div>
  </div>

  <div class="staff-archive-tabs" data-archive-tabs role="tablist">
    <button type="button" class="is-active" data-archive-tab="all">All <span><?= (int) $archiveTotal ?></span></button>
    <?php foreach ($archiveGroups as $key => $group): ?>
      <button type="button" data-archive-tab="<?= sp_h($key) ?>"><i style="background:<?= $group['meta']['accent'] ?>;"></i><?= sp_h($group['meta']['label']) ?> <span><?= (int) $group['count'] ?></span></button>
    <?php endforeach; ?>
  </div>

  <div class="staff-archive-toolbar">
    <label class="staff-archive-search">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
      <input type="search" placeholder="Search archived records…" data-archive-search>
    </label>
    <button type="button" class="staff-secondary-btn">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><path d="M4 6h16M7 12h10M10 18h4"></path></svg>
      Filters
    </button>
    <button type="button" class="staff-action-btn" data-archive-action="export">Export Archive Data</button>
  </div>

  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Type</th>
          <th>Record</th>
          <th>Scope</th>
          <th>Status</th>
          <th>Archived At</th>
          <th>Restore</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($archiveRows)): ?>
          <tr>
            <td colspan="6" class="staff-archive-empty">No archived records to show yet.</td>
          </tr>
        <?php endif; ?>
        <?php foreach ($archiveRows as $archive): ?>
          <?php
            $meta     = sp_archive_meta((string) ($archive['type'] ?? ''));
            $relative = sp_archive_relative($archive['archived_at'] ?? null);
          ?>
          <tr data-search-row data-archive-row data-archive-type="<?= sp_h($meta['key']) ?>">
            <td>
              <span class="staff-archive-type">
                <i style="background:<?= $meta['accent'] ?>;"><?= sp_h($meta['code']) ?></i>
                <span><?= sp_h($archive['type'] ?? $meta['label']) ?></span>
              </span>
            </td>
            <td><strong><?= sp_h($archive['title']) ?></strong></td>
            <td><?= sp_h($archive['scope']) ?></td>
            <td><span class="staff-status <?= sp_status_class($archive['status']) ?>"><?= sp_h($archive['status']) ?></span></td>
            <td>
              <strong><?= sp_h($archive['archived_at']) ?></strong>
              <?php if ($relative): ?><small><?= sp_h($relative) ?></small><?php endif; ?>
            </td>
            <td>
              <div class="staff-archive-row-actions">
                <button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Restore</button>
                <button type="button" class="staff-archive-kebab" aria-label="More actions">⋮</button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
(function () {
  var section = document.getElementById('archive-table');
  if (!section || section.dataset.archiveBound) return;
  section.dataset.archiveBound = '1';

  var tabsWrap = section.querySelector('[data-archive-tabs]');
  var searchInput = section.querySelector('[data-archive-search]');
  var rows = Array.prototype.slice.call(section.querySelectorAll('[data-archive-row]'));

  function applyFilters() {
    var activeTab = tabsWrap ? tabsWrap.querySelector('.is-active') : null;
    var type = activeTab ? activeTab.getAttribute('data-archive-tab') : 'all';
    var query = searchInput ? searchInput.value.trim().toLowerCase() : '';

    rows.forEach(function (row) {
      var matchesType = type === 'all' || row.getAttribute('data-archive-type') === type;
      var matchesQuery = !query || row.textContent.toLowerCase().indexOf(query) !== -1;
      row.style.display = (matchesType && matchesQuery) ? '' : 'none';
    });
  }

  if (tabsWrap) {
    tabsWrap.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-archive-tab]');
      if (!btn) return;
      tabsWrap.querySelectorAll('button').forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      applyFilters();
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
  }

  document.querySelectorAll('[data-archive-jump]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var key = btn.getAttribute('data-archive-jump');
      var target = tabsWrap ? tabsWrap.querySelector('[data-archive-tab="' + key + '"]') : null;
      section.scrollIntoView({ behavior: 'smooth', block: 'start' });
      if (target) target.click();
    });
  });

  var hash = (window.location.hash || '').replace('#', '');
  if (hash) {
    var hashTarget = tabsWrap ? tabsWrap.querySelector('[data-archive-tab="' + hash + '"]') : null;
    if (hashTarget) hashTarget.click();
  }
})();
</script>
