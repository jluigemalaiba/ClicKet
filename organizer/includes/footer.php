    </section></div>
  </main>
  <div class="staff-modal" data-staff-modal hidden><div class="staff-modal-backdrop" data-modal-close></div><section class="staff-modal-panel" role="dialog" aria-modal="true" aria-labelledby="staffModalTitle"><button class="staff-modal-close" type="button" data-modal-close aria-label="Close modal">x</button><p class="staff-eyebrow" id="staffModalEyebrow">Organizer</p><h2 id="staffModalTitle">Details</h2><div class="staff-modal-body" data-modal-body></div></section></div>
  <script type="application/json" id="staffEventLayoutOptionsJson"><?= json_encode($payload['eventVenueOptions'] ?? [], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
  <script src="js/staff-panel.js?v=<?= filemtime(dirname(__DIR__, 2) . '/js/staff-panel.js') ?>"></script>
</body>
</html>
