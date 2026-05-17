<?php
$current    = basename($_SERVER['PHP_SELF'], '.php');
$page_title = $page_title ?? '';
$_title     = $page_title ? h($page_title) . ' — ' . SITE_TITLE : SITE_TITLE;
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
        <a href="framework.php" class="<?= $current === 'framework' ? 'active' : '' ?>">Matrix</a>
      </li>
      <li>
        <a href="red.php" class="<?= $current === 'red' ? 'active' : '' ?>">Red Team</a>
      </li>
      <li>
        <a href="blue.php" class="<?= $current === 'blue' ? 'active' : '' ?>">Blue Team</a>
      </li>
      <li>
        <a href="incidents.php" class="<?= in_array($current, ['incidents','incident']) ? 'active' : '' ?>">Incidents</a>
      </li>
    </ul>
    <div class="nav-right">
      <a href="logout.php" class="btn btn-ghost btn-sm">Logout</a>
    </div>
  </div>
</nav>

<main>
