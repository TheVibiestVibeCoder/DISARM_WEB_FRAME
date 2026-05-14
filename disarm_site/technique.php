<?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = gp('id');
if (!$id) { header('Location: techniques.php'); exit; }

// ── Technique ─────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT tc.*, ta.name AS tactic_name
     FROM technique tc
     LEFT JOIN tactic ta ON ta.disarm_id = tc.tactic_id
     WHERE tc.disarm_id = ?"
);
$stmt->execute([$id]);
$tech = $stmt->fetch();
if (!$tech) { http_response_code(404); die('<h2>Technique not found: ' . h($id) . '</h2>'); }

$page_title = $tech['disarm_id'] . ' ' . $tech['name'];

// ── Sub-techniques (if this is a parent) ─────────────────────────────────────
$is_parent  = !str_contains($id, '.');
$parent_id  = str_contains($id, '.') ? explode('.', $id)[0] : null;

$subtechs = [];
if ($is_parent) {
    $st = $pdo->prepare("SELECT disarm_id, name, summary FROM technique WHERE disarm_id LIKE ? AND disarm_id != ? ORDER BY disarm_id");
    $st->execute(["$id.%", $id]);
    $subtechs = $st->fetchAll();
}

// ── Parent technique (if this is a sub-technique) ─────────────────────────────
$parent = null;
if ($parent_id) {
    $pt = $pdo->prepare("SELECT disarm_id, name FROM technique WHERE disarm_id = ?");
    $pt->execute([$parent_id]);
    $parent = $pt->fetch();
}

// ── Counters for this technique ───────────────────────────────────────────────
$counters = $pdo->prepare(
    "SELECT c.disarm_id, c.name, c.metatechnique_id, c.tactic_id
     FROM counter c
     INNER JOIN counter_technique ct ON ct.counter_id = c.disarm_id
     WHERE ct.technique_id = ?
     ORDER BY c.disarm_id"
);
$counters->execute([$id]);
$counters = $counters->fetchAll();

// ── Detections for this technique ─────────────────────────────────────────────
$detections = $pdo->prepare(
    "SELECT d.disarm_id, d.name
     FROM detection d
     INNER JOIN detection_technique dt ON dt.detection_id = d.disarm_id
     WHERE dt.technique_id = ?
     ORDER BY d.disarm_id"
);
$detections->execute([$id]);
$detections = $detections->fetchAll();

// ── Incidents demonstrating this technique ────────────────────────────────────
$incidents = $pdo->prepare(
    "SELECT i.disarm_id, i.name, i.year_started, i.found_in_country, it.summary AS it_summary
     FROM incident i
     INNER JOIN incident_technique it ON it.incident_id = i.disarm_id
     WHERE it.technique_id = ?
     ORDER BY i.year_started DESC, i.disarm_id"
);
$incidents->execute([$id]);
$incidents = $incidents->fetchAll();

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item"><a href="techniques.php">Techniques</a></li>
    <?php if ($parent): ?>
    <li class="breadcrumb-item">
      <a href="technique.php?id=<?= urlencode($parent['disarm_id']) ?>"><?= h($parent['disarm_id']) ?></a>
    </li>
    <?php endif; ?>
    <li class="breadcrumb-item active"><?= h($tech['disarm_id']) ?></li>
  </ol>
</nav>

<!-- ── Detail header ─────────────────────────────────────────────────────── -->
<div class="detail-header mb-4">
  <div class="detail-meta mb-2">
    <span class="disarm-id me-2"><?= h($tech['disarm_id']) ?></span>
    <span class="fw-red me-2">RED</span>
    <?php if ($tech['tactic_id']): ?>
    <a href="tactic.php?id=<?= urlencode($tech['tactic_id']) ?>" class="badge bg-secondary text-decoration-none me-2">
      <?= h($tech['tactic_id']) ?> <?= h($tech['tactic_name']) ?>
    </a>
    <?php endif; ?>
    <?php if ($parent): ?>
    <span class="badge bg-light text-dark border">
      Sub-technique of <a href="technique.php?id=<?= urlencode($parent['disarm_id']) ?>" class="text-dark"><?= h($parent['disarm_id']) ?></a>
    </span>
    <?php endif; ?>
  </div>
  <h1><?= h($tech['name']) ?></h1>
  <?php if ($tech['summary']): ?>
  <p class="summary-text mb-0 mt-3"><?= h($tech['summary']) ?></p>
  <?php endif; ?>
</div>

<div class="row g-3">

  <!-- ── Sub-techniques ────────────────────────────────────────────────── -->
  <?php if ($subtechs): ?>
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-light">
        <i class="bi bi-diagram-2 me-2"></i>Sub-techniques
        <span class="badge bg-secondary ms-2"><?= count($subtechs) ?></span>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
          <thead><tr><th>ID</th><th>Sub-technique</th><th>Summary</th></tr></thead>
          <tbody>
            <?php foreach ($subtechs as $st): ?>
            <tr>
              <td><?= id_badge($st['disarm_id'], 'technique.php?id=' . urlencode($st['disarm_id'])) ?></td>
              <td><a href="technique.php?id=<?= urlencode($st['disarm_id']) ?>"><?= h($st['name']) ?></a></td>
              <td class="text-muted small"><?= h(truncate($st['summary'], 140)) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Counters ──────────────────────────────────────────────────────── -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header" style="background:var(--disarm-blue);color:#fff">
        <i class="bi bi-shield-check me-2"></i>Countermeasures
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
      <div class="card-body"><?= no_results('No specific counters linked to this technique.') ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Incidents + Detections ────────────────────────────────────────── -->
  <div class="col-lg-6">

    <?php if ($incidents): ?>
    <div class="card mb-3">
      <div class="card-header bg-warning text-dark">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>Seen in Incidents
        <span class="badge bg-dark ms-2"><?= count($incidents) ?></span>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
          <thead><tr><th>ID</th><th>Incident</th><th>Year</th></tr></thead>
          <tbody>
            <?php foreach ($incidents as $inc): ?>
            <tr>
              <td><?= id_badge($inc['disarm_id'], 'incident.php?id=' . urlencode($inc['disarm_id'])) ?></td>
              <td><a href="incident.php?id=<?= urlencode($inc['disarm_id']) ?>"><?= h(truncate($inc['name'], 60)) ?></a></td>
              <td><?= h($inc['year_started']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($detections): ?>
    <div class="card">
      <div class="card-header bg-info text-dark">
        <i class="bi bi-eye me-2"></i>Detections
        <span class="badge bg-dark ms-2"><?= count($detections) ?></span>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
          <thead><tr><th>ID</th><th>Detection</th></tr></thead>
          <tbody>
            <?php foreach ($detections as $d): ?>
            <tr>
              <td><?= id_badge($d['disarm_id']) ?></td>
              <td class="small"><?= h($d['name']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!$incidents && !$detections): ?>
    <div class="card">
      <div class="card-body"><?= no_results('No incidents or detections linked yet.') ?></div>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
