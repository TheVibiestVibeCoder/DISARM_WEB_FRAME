<?php
require_once 'config.php';
require_once 'includes/functions.php';
$page_title = 'Home';

// ── Stats ─────────────────────────────────────────────────────────────────────
$count = function(string $table) use ($pdo): int {
    try { return (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn(); }
    catch (Exception $e) { return 0; }
};

$stats = [
    ['Tactics',          $count('tactic'),        'tactics.php',     'bi-diagram-3',           'text-dark'],
    ['Techniques',       $count('technique'),      'techniques.php',  'bi-lightning-charge',     'text-danger'],
    ['Counters',         $count('counter'),        'counters.php',    'bi-shield-check',         'text-primary'],
    ['Detections',       $count('detection'),      'detections.php',  'bi-eye',                  'text-info'],
    ['Incidents',        $count('incident'),       'incidents.php',   'bi-exclamation-triangle', 'text-warning'],
    ['External Groups',  $count('externalgroup'),  'groups.php',      'bi-people',               'text-secondary'],
    ['Tools',            $count('tool'),           'tools.php',       'bi-tools',                'text-success'],
    ['Playbooks',        $count('playbook'),       'playbooks.php',   'bi-journal-text',         'text-dark'],
];

// ── Phases + tactics ──────────────────────────────────────────────────────────
$phases  = $pdo->query("SELECT disarm_id, name FROM phase ORDER BY rank")->fetchAll();
$tactics = $pdo->query("SELECT disarm_id, name, phase_id FROM tactic ORDER BY disarm_id")->fetchAll();

// ── Latest incidents ──────────────────────────────────────────────────────────
$recent_incidents = $pdo->query(
    "SELECT disarm_id, name, year_started, found_in_country FROM incident ORDER BY year_started DESC, disarm_id LIMIT 6"
)->fetchAll();

include 'includes/header.php';
?>

<!-- ── Hero ──────────────────────────────────────────────────────────────── -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card p-0">
      <div class="card-body p-4" style="background:linear-gradient(135deg,var(--disarm-dark) 0%,#2c3e50 100%);color:#fff;border-radius:8px;">
        <div class="row align-items-center g-3">
          <div class="col-md-8">
            <h1 class="fw-bold mb-2">DISARM Framework</h1>
            <p class="mb-0 opacity-75 lh-lg">
              A structured framework for describing and countering disinformation campaigns,
              modelled after MITRE ATT&amp;CK. Browse attacker techniques (Red) and
              defensive countermeasures (Blue) with full cross-references.
            </p>
          </div>
          <div class="col-md-4 d-flex flex-wrap gap-2 justify-content-md-end">
            <a href="techniques.php" class="btn btn-danger">
              <i class="bi bi-lightning-charge-fill me-1"></i>Red Framework
            </a>
            <a href="counters.php" class="btn btn-primary">
              <i class="bi bi-shield-fill me-1"></i>Blue Framework
            </a>
            <a href="search.php" class="btn btn-outline-light">
              <i class="bi bi-search me-1"></i>Search
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Stats ─────────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
  <?php foreach ($stats as [$label, $n, $link, $icon, $color]): ?>
  <div class="col-6 col-sm-4 col-md-3 col-xl">
    <a href="<?= h($link) ?>" class="text-decoration-none">
      <div class="card stat-card h-100">
        <i class="bi <?= $icon ?> fs-2 <?= $color ?> mb-1"></i>
        <div class="stat-number <?= $color ?>"><?= number_format($n) ?></div>
        <div class="stat-label"><?= h($label) ?></div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Phases overview ───────────────────────────────────────────────────── -->
<div class="card mb-4">
  <div class="card-header bg-disarm text-white"><i class="bi bi-diagram-3 me-2"></i>Campaign Phases</div>
  <div class="card-body">
    <div class="row g-2">
      <?php foreach ($phases as $ph): ?>
      <div class="col-sm">
        <div class="border rounded p-2 text-center h-100">
          <div class="disarm-id mb-1"><?= h($ph['disarm_id']) ?></div>
          <div class="small fw-semibold"><?= h($ph['name']) ?></div>
          <div class="mt-1">
            <?php
              $n = count(array_filter($tactics, fn($t) => $t['phase_id'] === $ph['disarm_id']));
            ?>
            <span class="badge bg-secondary"><?= $n ?> tactics</span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── Red + Blue quick access ───────────────────────────────────────────── -->
<div class="row g-3 mb-4">

  <!-- Red -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header" style="background:var(--disarm-red);color:#fff">
        <i class="bi bi-lightning-charge-fill me-2"></i>Red Framework — Attacker Tactics
      </div>
      <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
          <thead><tr><th>ID</th><th>Tactic</th><th>Phase</th></tr></thead>
          <tbody>
            <?php foreach ($tactics as $t): ?>
            <tr>
              <td><?= id_badge($t['disarm_id'], 'tactic.php?id=' . urlencode($t['disarm_id'])) ?></td>
              <td><a href="tactic.php?id=<?= urlencode($t['disarm_id']) ?>"><?= h($t['name']) ?></a></td>
              <td><small class="text-muted"><?= h($t['phase_id']) ?></small></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer text-center py-2 border-top">
        <a href="techniques.php" class="small text-danger fw-semibold">Browse all techniques →</a>
      </div>
    </div>
  </div>

  <!-- Recent incidents -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header bg-warning text-dark">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>Recent Incidents
      </div>
      <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
          <thead><tr><th>ID</th><th>Incident</th><th>Year</th><th>Country</th></tr></thead>
          <tbody>
            <?php foreach ($recent_incidents as $inc): ?>
            <tr>
              <td><?= id_badge($inc['disarm_id'], 'incident.php?id=' . urlencode($inc['disarm_id'])) ?></td>
              <td><a href="incident.php?id=<?= urlencode($inc['disarm_id']) ?>"><?= h(truncate($inc['name'], 60)) ?></a></td>
              <td><?= h($inc['year_started']) ?></td>
              <td><small class="text-muted"><?= h($inc['found_in_country']) ?></small></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer text-center py-2 border-top">
        <a href="incidents.php" class="small text-warning fw-semibold">Browse all incidents →</a>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
