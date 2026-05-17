<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Red Framework Matrix';

// Phases in display order
$phases = $pdo->query("SELECT disarm_id, name FROM phase ORDER BY rank")->fetchAll(PDO::FETCH_ASSOC);

// Tactics ordered by phase rank then tactic rank
$tactics_raw = $pdo->query(
    "SELECT t.disarm_id, t.name, t.phase_id
     FROM tactic t
     JOIN phase p ON p.disarm_id = t.phase_id
     ORDER BY p.rank, t.rank, t.disarm_id"
)->fetchAll(PDO::FETCH_ASSOC);

// Group tactics by phase
$tactics_by_phase = [];
foreach ($tactics_raw as $ta) {
    $tactics_by_phase[$ta['phase_id']][] = $ta;
}

// All techniques ordered by tactic then ID
$techniques_raw = $pdo->query(
    "SELECT disarm_id, name, tactic_id FROM technique ORDER BY tactic_id, disarm_id"
)->fetchAll(PDO::FETCH_ASSOC);

// Build technique tree: tactic_id => [parent_id => [info, subs[]]]
$tech_tree = [];
foreach ($techniques_raw as $tc) {
    $tid = $tc['tactic_id'] ?? '_none';
    if (str_contains($tc['disarm_id'], '.')) {
        $parent = substr($tc['disarm_id'], 0, strpos($tc['disarm_id'], '.'));
        $tech_tree[$tid][$parent]['subs'][] = $tc;
    } else {
        if (!isset($tech_tree[$tid][$tc['disarm_id']]['info'])) {
            $tech_tree[$tid][$tc['disarm_id']]['info'] = $tc;
        }
    }
}

// Total tactic count for stats
$total_tactics = count($tactics_raw);

include 'includes/header.php';
?>

<div class="container">

  <!-- Page Header -->
  <div class="page-header">
    <div class="label"><span class="tag tag-red" style="margin-right:4px">RED</span>Framework</div>
    <div class="page-header-row">
      <div>
        <h1>Framework Matrix</h1>
        <div class="page-count"><?= $total_tactics ?> Tactics · <?= count($techniques_raw) ?> Techniques</div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <a href="techniques.php" class="btn btn-ghost btn-sm">List View</a>
        <a href="tactics.php"   class="btn btn-dark btn-sm">All Tactics</a>
      </div>
    </div>
  </div>

  <!-- Legend -->
  <div class="matrix-legend reveal">
    <?php foreach ($phases as $ph): ?>
    <div class="legend-item">
      <span class="legend-dot legend-dot-<?= strtolower(preg_replace('/\W+/','-',$ph['disarm_id'])) ?>"></span>
      <span class="legend-label"><?= h($ph['disarm_id']) ?> · <?= h($ph['name']) ?></span>
    </div>
    <?php endforeach; ?>
    <div class="legend-sep"></div>
    <div class="legend-item"><span class="legend-swatch legend-parent"></span><span class="legend-label">Technique</span></div>
    <div class="legend-item"><span class="legend-swatch legend-sub"></span><span class="legend-label">Sub-technique</span></div>
  </div>

</div><!-- /container — matrix goes full-bleed -->

<!-- Matrix (horizontally scrollable, full-width) -->
<div class="matrix-scroll reveal">
  <div class="matrix-inner">

    <!-- Phase Header Row -->
    <div class="matrix-phase-row">
      <?php foreach ($phases as $ph):
        $cols = count($tactics_by_phase[$ph['disarm_id']] ?? []);
        if (!$cols) continue;
      ?>
      <div class="matrix-phase-cell matrix-phase-<?= $ph['disarm_id'] ?>" style="--cols:<?= $cols ?>">
        <span class="matrix-phase-id"><?= h($ph['disarm_id']) ?></span>
        <?= h($ph['name']) ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Tactic Header Row -->
    <div class="matrix-tactic-row">
      <?php foreach ($phases as $ph):
        foreach (($tactics_by_phase[$ph['disarm_id']] ?? []) as $ta):
      ?>
      <div class="matrix-tactic-hd matrix-phase-<?= $ph['disarm_id'] ?>-tactic">
        <a href="tactic.php?id=<?= urlencode($ta['disarm_id']) ?>" class="matrix-tactic-link">
          <span class="matrix-tactic-id"><?= h($ta['disarm_id']) ?></span>
          <span class="matrix-tactic-name"><?= h($ta['name']) ?></span>
        </a>
      </div>
      <?php endforeach; endforeach; ?>
    </div>

    <!-- Technique Columns -->
    <div class="matrix-cols-row">
      <?php foreach ($phases as $ph):
        foreach (($tactics_by_phase[$ph['disarm_id']] ?? []) as $ta):
          $entries = $tech_tree[$ta['disarm_id']] ?? [];
      ?>
      <div class="matrix-col">
        <?php if (!$entries): ?>
          <div class="matrix-empty">—</div>
        <?php else: ?>
          <?php foreach ($entries as $parent_id => $entry):
            $info = $entry['info'] ?? null;
            $subs = $entry['subs'] ?? [];
          ?>
          <?php if ($info): ?>
          <a href="technique.php?id=<?= urlencode($info['disarm_id']) ?>" class="matrix-tech">
            <span class="matrix-tech-id"><?= h($info['disarm_id']) ?></span>
            <span class="matrix-tech-name"><?= h($info['name']) ?></span>
          </a>
          <?php endif; ?>
          <?php foreach ($subs as $sub): ?>
          <a href="technique.php?id=<?= urlencode($sub['disarm_id']) ?>" class="matrix-tech matrix-tech-sub">
            <span class="matrix-tech-id"><?= h($sub['disarm_id']) ?></span>
            <span class="matrix-tech-name"><?= h($sub['name']) ?></span>
          </a>
          <?php endforeach; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <?php endforeach; endforeach; ?>
    </div>

  </div><!-- /matrix-inner -->
</div><!-- /matrix-scroll -->

<div class="container">
  <div style="margin-top: clamp(48px,6vw,80px)"></div>
</div>

<?php include 'includes/footer.php'; ?>
