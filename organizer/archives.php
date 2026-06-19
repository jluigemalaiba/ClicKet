<?php $organizerPage = 'archives'; $organizerTitle = 'Archives'; require __DIR__ . '/includes/header.php'; $archives = $payload['archives'] ?? []; ?>
<section class="staff-section">
  <div class="staff-section-heading"><div><p>Archives</p><h2>Archived records in your event scope</h2></div><span><?= sp_count(count($archives)) ?> records</span></div>
  <div class="staff-table-wrap"><table class="staff-table"><thead><tr><th>Type</th><th>Record</th><th>Scope</th><th>Status</th><th>Archived</th></tr></thead><tbody>
    <?php foreach ($archives as $archive): ?><tr data-search-row><td><?= sp_h($archive['type'] ?? '') ?></td><td><strong><?= sp_h($archive['title'] ?? '') ?></strong></td><td><?= sp_h($archive['scope'] ?? '') ?></td><td><span class="staff-status <?= sp_status_class($archive['status'] ?? 'Archived') ?>"><?= sp_h($archive['status'] ?? 'Archived') ?></span></td><td><?= sp_h($archive['archived_at'] ?? '') ?></td></tr><?php endforeach; ?>
    <?php if (!$archives): ?><tr><td colspan="5">No archived records are available in your scope.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
