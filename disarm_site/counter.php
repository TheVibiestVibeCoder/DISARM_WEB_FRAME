<?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = gp('id');
if (!$id) { header('Location: counters.php'); exit; }

// ── Counter ───────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT c.*, ta.name AS tactic_name, mt.name AS meta_name
     FROM counter c
     LEFT JOIN tactic      ta ON ta.disarm_id = c.tactic_id
     LEFT JOIN metatechnique mt ON mt.disarm_id = c.metatechnique_id
     WHERE c.disarm_id = ?"
);
$stmt->execute([$id]);
$counter = $stmt->fetch();
if (!$counter) { http_response_code(404); die('<h2>Counter not found: ' . h($id) . '</h2>'); }

$page_title = $counter['disarm_id'] . ' ' . $counter['name'];

// ── Techniques this counter addresses ─────────────────────────────────────────
$techniques = $pdo->prepare(
    "SELECT tc.disarm_id, tc.name, tc.tactic_id
     FROM technique tc
     INNER JOIN counter_technique ct ON ct.technique_id = tc.disarm_id
     WHERE ct.counter_id = ?
     ORDER BY tc.disarm_id"
);
$techniques->execute([$id]);
$techniques = $techniques->fetchAll();

// ── Tactics this counter covers (via counter_tactic) ─────────────────────────
$all_tactics = $pdo->prepare(
    "SELECT ct.tactic_id, ct.main_tactic, ta.name AS tactic_name
     FROM counter_tactic ct
     LEFT JOIN tactic ta ON ta.disarm_id = ct.tactic_id
     WHERE ct.counter_id = ?
     ORDER BY ct.main_tactic DESC, ct.tactic_id"
);
$all_tactics->execute([$id]);
$all_tactics = $all_tactics->fetchAll();

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item"><a href="counters.php">Countermeasures</a></li>
    <li class="breadcrumb-item active"><?= h($counter['disarm_id']) ?></li>
  </ol>
</nav>

<!-- ── Detail header ─────────────────────────────────────────────────────── -->
<div class="detail-header mb-4">
  <div class="detail-meta mb-2">
    <span class="disarm-id me-2"><?= h($counter['disarm_id']) ?></span>
    <span class="fw-blue me-2">BLUE</span>
    <?php if ($counter['tactic_id']): ?>
    <a href="tactic.php?id=<?= urlencode($counter['tactic_id']) ?>" class="badge bg-secondary text-decoration-none me-2">
      <?= h($counter['tactic_id']) ?> <?= h($counter['tactic_name']) ?>
    </a>
    <?php endif; ?>
    <?php if ($counter['metatechnique_id']): ?>
    <span class="badge bg-info text-dark"><?= h($counter['metatechnique_id']) ?> <?= h($counter['meta_name']) ?></span>
    <?php endif; ?>
  </div>
  <h1><?= h($counter['name']) ?></h1>
  <?php if ($counter['summary']): ?>
  <p class="summary-text mb-0 mt-3"><?= h($counter['summary']) ?></p>
  <?php endif; ?>
</div>

<div class="row g-3">

  <!-- ── Techniques addressed ──────────────────────────────────────────── -->
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header" style="background:var(--disarm-red);color:#fff">
        <i class="bi bi-lightning-charge-fill me-2"></i>Techniques this Counter Addresses
        <span class="badge bg-light text-dark ms-2"><?= count($techniques) ?></span>
      </div>
      <?php if ($techniques): ?>
      <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
          <thead><tr><th>ID</th><th>Technique</th><th>Tactic</th></tr></thead>
          <tbody>
            <?php foreach ($techniques as $t): ?>
            <tr>
              <td><?= id_badge($t['disarm_id'], 'technique.php?id=' . urlencode($t['disarm_id'])) ?></td>
              <td><a href="technique.php?id=<?= urlencode($t['disarm_id']) ?>"><?= h($t['name']) ?></a></td>
              <td><small class="text-muted"><?= h($t['tactic_id']) ?></small></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="card-body"><?= no_results('No specific techniques linked.') ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Tactics coverage ──────────────────────────────────────────────── -->
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header bg-light">
        <i class="bi bi-diagram-3 me-2"></i>Tactic Coverage
      </div>
      <?php if ($all_tactics): ?>
      <ul class="list-group list-group-flush">
        <?php foreach ($all_tactics as $ta): ?>
        <li class="list-group-item py-2 d-flex align-items-center gap-2">
          <a href="tactic.php?id=<?= urlencode($ta['tactic_id']) ?>" class="text-decoration-none">
            <span class="disarm-id"><?= h($ta['tactic_id']) ?></span>
          </a>
          <span class="small"><?= h($ta['tactic_name']) ?></span>
          <?php if ($ta['main_tactic'] === 'Y'): ?>
          <span class="badge bg-primary ms-auto">primary</span>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
      <div class="card-body"><?= no_results('No tactic links found.') ?></div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
