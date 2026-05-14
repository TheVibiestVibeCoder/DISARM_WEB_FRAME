<?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = gp('id');
if (!$id) { header('Location: tactics.php'); exit; }

// ── Tactic ───────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT t.*, p.name AS phase_name FROM tactic t LEFT JOIN phase p ON p.disarm_id = t.phase_id WHERE t.disarm_id = ?");
$stmt->execute([$id]);
$tactic = $stmt->fetch();
if (!$tactic) { http_response_code(404); die('<h2>Tactic not found: ' . h($id) . '</h2>'); }

$page_title = $tactic['disarm_id'] . ' ' . $tactic['name'];

// ── Techniques in this tactic ─────────────────────────────────────────────────
$techniques = $pdo->prepare("SELECT disarm_id, name, summary FROM technique WHERE tactic_id = ? ORDER BY disarm_id");
$techniques->execute([$id]);
$techniques = $techniques->fetchAll();

// ── Counters for this tactic ──────────────────────────────────────────────────
$counters = $pdo->prepare("SELECT disarm_id, name, metatechnique_id, summary FROM counter WHERE tactic_id = ? ORDER BY disarm_id");
$counters->execute([$id]);
$counters = $counters->fetchAll();

// ── Detections for this tactic ────────────────────────────────────────────────
$detections = $pdo->prepare("SELECT disarm_id, name, summary FROM detection WHERE tactic_id = ? ORDER BY disarm_id");
$detections->execute([$id]);
$detections = $detections->fetchAll();

// ── Tasks ─────────────────────────────────────────────────────────────────────
$tasks = $pdo->prepare("SELECT disarm_id, name, summary FROM task WHERE tactic_id = ? ORDER BY disarm_id");
$tasks->execute([$id]);
$tasks = $tasks->fetchAll();

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item"><a href="tactics.php">Tactics</a></li>
    <li class="breadcrumb-item active"><?= h($tactic['disarm_id']) ?></li>
  </ol>
</nav>

<!-- ── Detail header ─────────────────────────────────────────────────────── -->
<div class="detail-header mb-4">
  <div class="detail-meta mb-2">
    <span class="disarm-id me-2"><?= h($tactic['disarm_id']) ?></span>
    <?php if ($tactic['phase_name']): ?>
    <a href="tactics.php?phase=<?= urlencode($tactic['phase_id']) ?>" class="badge bg-secondary text-decoration-none">
      <?= h($tactic['phase_id']) ?> <?= h($tactic['phase_name']) ?>
    </a>
    <?php endif; ?>
  </div>
  <h1><?= h($tactic['name']) ?></h1>
  <?php if ($tactic['summary']): ?>
  <p class="summary-text mb-0 mt-3"><?= h($tactic['summary']) ?></p>
  <?php endif; ?>
</div>

<div class="row g-3">

  <!-- ── Techniques ────────────────────────────────────────────────────── -->
  <div class="col-12">
    <div class="card">
      <div class="card-header" style="background:var(--disarm-red);color:#fff">
        <i class="bi bi-lightning-charge-fill me-2"></i>
        Techniques in this Tactic
        <span class="badge bg-light text-dark ms-2"><?= count($techniques) ?></span>
      </div>
      <?php if ($techniques): ?>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead><tr><th>ID</th><th>Technique</th><th>Summary</th></tr></thead>
          <tbody>
            <?php foreach ($techniques as $t): ?>
            <tr>
              <td><?= id_badge($t['disarm_id'], 'technique.php?id=' . urlencode($t['disarm_id'])) ?></td>
              <td><a href="technique.php?id=<?= urlencode($t['disarm_id']) ?>"><?= h($t['name']) ?></a></td>
              <td class="text-muted small"><?= h(truncate($t['summary'], 140)) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="card-body"><?= no_results('No techniques listed for this tactic.') ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Counters ──────────────────────────────────────────────────────── -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header" style="background:var(--disarm-blue);color:#fff">
        <i class="bi bi-shield-check me-2"></i>
        Countermeasures
        <span class="badge bg-light text-dark ms-2"><?= count($counters) ?></span>
      </div>
      <?php if ($counters): ?>
      <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
          <thead><tr><th>ID</th><th>Counter</th><th>Metatechnique</th></tr></thead>
          <tbody>
            <?php foreach ($counters as $c): ?>
            <tr>
              <td><?= id_badge($c['disarm_id'], 'counter.php?id=' . urlencode($c['disarm_id'])) ?></td>
              <td><a href="counter.php?id=<?= urlencode($c['disarm_id']) ?>"><?= h($c['name']) ?></a></td>
              <td><small class="text-muted"><?= h($c['metatechnique_id']) ?></small></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="card-body"><?= no_results('No counters for this tactic.') ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Detections + Tasks ────────────────────────────────────────────── -->
  <div class="col-lg-6">
    <?php if ($detections): ?>
    <div class="card mb-3">
      <div class="card-header bg-info text-dark">
        <i class="bi bi-eye me-2"></i>Detections
        <span class="badge bg-light text-dark ms-2"><?= count($detections) ?></span>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
          <thead><tr><th>ID</th><th>Detection</th></tr></thead>
          <tbody>
            <?php foreach ($detections as $d): ?>
            <tr>
              <td><?= id_badge($d['disarm_id']) ?></td>
              <td><small><?= h($d['name']) ?></small></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($tasks): ?>
    <div class="card">
      <div class="card-header bg-light">
        <i class="bi bi-list-check me-2"></i>Tasks
        <span class="badge bg-secondary ms-2"><?= count($tasks) ?></span>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($tasks as $task): ?>
        <li class="list-group-item py-2">
          <div class="disarm-id mb-1"><?= h($task['disarm_id']) ?></div>
          <div class="small fw-semibold"><?= h($task['name']) ?></div>
          <?php if ($task['summary']): ?>
          <div class="text-muted" style="font-size:.8rem"><?= h(truncate($task['summary'], 200)) ?></div>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
