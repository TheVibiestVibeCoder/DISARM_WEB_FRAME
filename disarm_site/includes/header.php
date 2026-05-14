<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
$_title  = ($page_title ?? '') ? h($page_title) . ' — ' . SITE_TITLE : SITE_TITLE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $_title ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<nav class="nav">
  <div class="container">
    <a class="nav-logo" href="index.php">DISARM</a>
    <ul class="nav-links">
      <li>
        <a href="tactics.php" class="<?= $current === 'tactics' ? 'active' : '' ?>">Tactics</a>
      </li>
      <li class="has-drop">
        <a href="techniques.php" class="<?= in_array($current, ['techniques','technique']) ? 'active' : '' ?>">Techniques</a>
        <div class="drop-menu">
          <a href="techniques.php">All Techniques</a>
        </div>
      </li>
      <li class="has-drop">
        <a href="counters.php" class="<?= in_array($current, ['counters','counter','detections']) ? 'active' : '' ?>">Counters</a>
        <div class="drop-menu">
          <a href="counters.php">Countermeasures</a>
          <a href="detections.php">Detections</a>
        </div>
      </li>
      <li>
        <a href="incidents.php" class="<?= in_array($current, ['incidents','incident']) ? 'active' : '' ?>">Incidents</a>
      </li>
      <li class="has-drop">
        <a href="#" class="<?= in_array($current, ['groups','tools','resources','playbooks']) ? 'active' : '' ?>">More</a>
        <div class="drop-menu">
          <a href="groups.php">External Groups</a>
          <a href="tools.php">Tools</a>
          <a href="resources.php">Resources</a>
          <a href="playbooks.php">Playbooks</a>
        </div>
      </li>
      <li>
        <a href="search.php" class="<?= $current === 'search' ? 'active' : '' ?>">Search</a>
      </li>
    </ul>
    <div class="nav-right">
      <a href="logout.php" class="btn btn-ghost btn-sm">Logout</a>
    </div>
  </div>
</nav>

<main>
