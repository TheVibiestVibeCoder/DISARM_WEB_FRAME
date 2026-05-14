<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
$_title  = ($page_title ?? '') ? h($page_title) . ' — ' . SITE_TITLE : SITE_TITLE;

function nav_link(string $href, string $label, string $current, array $matches = []): string {
    $pages  = array_merge([$href], $matches);
    $active = in_array(basename($_SERVER['PHP_SELF'], '.php'), array_map(fn($p) => basename($p, '.php'), $pages));
    return '<li class="nav-item"><a class="nav-link' . ($active ? ' active' : '') . '" href="' . h($href) . '">' . $label . '</a></li>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $_title ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-disarm sticky-top shadow-sm">
  <div class="container-xl">
    <a class="navbar-brand fw-bold" href="index.php">
      <i class="bi bi-shield-exclamation me-2"></i>DISARM
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nb">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nb">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?= nav_link('tactics.php',    'Tactics',    $current) ?>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array($current, ['techniques','technique']) ? 'active' : '' ?>"
             href="#" data-bs-toggle="dropdown">
            <span class="badge bg-danger me-1">RED</span>Techniques
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="techniques.php">All Techniques</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array($current, ['counters','counter','detections','detection']) ? 'active' : '' ?>"
             href="#" data-bs-toggle="dropdown">
            <span class="badge bg-primary me-1">BLUE</span>Counters
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="counters.php">Countermeasures</a></li>
            <li><a class="dropdown-item" href="detections.php">Detections</a></li>
          </ul>
        </li>

        <?= nav_link('incidents.php',  'Incidents',  $current) ?>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array($current, ['groups','tools','resources','playbooks']) ? 'active' : '' ?>"
             href="#" data-bs-toggle="dropdown">More</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="groups.php"><i class="bi bi-people me-2"></i>External Groups</a></li>
            <li><a class="dropdown-item" href="tools.php"><i class="bi bi-tools me-2"></i>Tools</a></li>
            <li><a class="dropdown-item" href="resources.php"><i class="bi bi-box me-2"></i>Resources</a></li>
            <li><a class="dropdown-item" href="playbooks.php"><i class="bi bi-journal-text me-2"></i>Playbooks</a></li>
          </ul>
        </li>
      </ul>

      <form class="d-flex gap-2" action="search.php" method="get">
        <input class="form-control form-control-sm" type="search" name="q"
               placeholder="Search DISARM…"
               value="<?= h(gp('q')) ?>" style="min-width:180px">
        <button class="btn btn-sm btn-outline-light" type="submit">
          <i class="bi bi-search"></i>
        </button>
      </form>
    </div>
  </div>
</nav>

<main class="container-xl py-4">
