<?php require_once __DIR__ . '/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <base href="../">
  <title><?= sp_h($organizerTitle ?? 'Organizer Panel') ?> | CLICKET</title>
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/staff-panel.css?v=<?= filemtime(dirname(__DIR__, 2) . '/css/staff-panel.css') ?>">
</head>
<body class="staff-shell staff-role-organizer">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <main class="staff-main">
    <header class="staff-topbar">
      <button class="staff-icon-btn staff-mobile-nav" type="button" data-sidebar-toggle aria-label="Open navigation"><span></span><span></span><span></span></button>
      <a class="staff-topbar-logo" href="index.php"><img src="assets/Icon_Logo.png" alt=""><img src="assets/Name_Logo.png" alt="CLICKET"></a>
      <div class="staff-topbar-search"><label class="staff-search staff-search--topbar"><input type="search" id="staffPanelSearch" placeholder="Search this page"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path></svg></label></div>
      <div class="staff-topbar-actions"><div class="staff-topbar-profile"><span><?= sp_h(sp_initials((string) ($staff['name'] ?? 'Organizer'))) ?></span><strong><?= sp_h($staff['name'] ?? 'Organizer') ?></strong><small><?= sp_h($staff['email'] ?? '') ?></small></div></div>
    </header>
    <div class="staff-panel-stage"><section class="staff-panel-view is-active">
