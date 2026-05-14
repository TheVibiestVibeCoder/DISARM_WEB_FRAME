<?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = gp('id');
if (!$id) { header('Location: incidents.php'); exit; }

// ── Incident ──────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM incident WHERE disarm_id = ?");
$stmt->execute([$id]);
$incident = $stmt->fetch();
if (!$incident) { http_response_code(404); die('<h2>Incident not found: ' . h($id) . '</h2>'); }

$page_title = $incident['disarm_id'] . ' ' . $incident['name'];

// ── Techniques in this incident ───────────────────────────────────────────────
$techniques = $pdo->prepare(
    "SELECT tc.disarm_id, tc.name, tc.tactic_id, it.summary AS context_summary
     FROM technique tc
     INNER JOIN incident_technique it ON it.technique_id = tc.disarm_id
     WHERE it.incident_id = ?
     ORDER BY tc.disarm_id"
);
$techniques->execute([$id]);
$techniques = $techniques->fetchAll();

// ── Counters relevant to those techniques ─────────────────────────────────────
$counters = [];
if ($techniques) {
    $tech_ids    = array_column($techniques, 'disarm_id');
    $placeholders = implode(',', array_fill(0, count($tech_ids), '?'));
    $counters = $pdo->prepare(
        "SELECT DISTINCT c.disarm_id, c.name, c.metatechnique_id
         FROM counter c
         INNER JOIN counter_technique ct ON ct.counter_id = c.disarm_id
         WHERE ct.technique_id IN ($placeholders)
         ORDER BY c.disarm_id"
    );
    $counters->execute($tech_ids);
    $counters = $counters->fetchAll();
}

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item"><a href="incidents.php">Incidents</a></li>
    <li class="breadcrumb-item active"><?= h($incident['disarm_id']) ?></li>
  </ol>
</nav>

<!-- ── Detail header ─────────────────────────────────────────────────────── -->
<div class="detail-header mb-4">
  <div class="detail-meta mb-2">
    <span class="disarm-id me-2"><?= h($incident['disarm_id']) ?></span>
    <?php if ($incident['year_started']): ?>
    <span class="badge bg-secondary me-1"><?= h($incident['year_started']) ?></span>
    <?php endif; ?>
    <?php if ($incident['found_in_country']): ?>
    <span class="badge bg-light text-dark border me-1"><?= h($incident['found_in_country']) ?></span>
    <?php endif; ?>
    <?php if ($incident['objecttype']): ?>
    <span class="badge bg-dark"><?= h($incident['objecttype']) ?></span>
    <?php endif; ?>
  </div>
  <h1><?= h($incident['name']) ?></h1>

  <div class="row mt-3 g-2">
    <?php if ($incident['attributions_seen']): ?>
    <div class="col-auto">
      <span class="section-heading d-block">Attribution</span>
      <span class="badge bg-light text-dark border"><?= h($incident['attributions_seen']) ?></span>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($incident['summary']): ?>
  <p class="summary-text mb-0 mt-3"><?= h($incident['summary']) ?></p>
  <?php endif; ?>
</div>

<div class="row g-3">

  <!-- ── Techniques used ───────────────────────────────────────────────── -->
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header" style="background:var(--disarm-red);color:#fff">
        <i class="bi bi-lightning-charge-fill me-2"></i>Techniques Used
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
              <td>
                <a href="technique.php?id=<?= urlencode($t['disarm_id']) ?>"><?= h($t['name']) ?></a>
                <?php if ($t['context_summary'] && $t['context_summary'] !== 'N/A'): ?>
                <div class="text-muted" style="font-size:.78rem"><?= h(truncate($t['context_summary'], 100)) ?></div>
                <?php endif; ?>
              </td>
              <td><small class="text-muted"><?= h($t['tactic_id']) ?></small></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="card-body"><?= no_results('No techniques linked to this incident.') ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Applicable counters ───────────────────────────────────────────── -->
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header" style="background:var(--disarm-blue);color:#fff">
        <i class="bi bi-shield-check me-2"></i>Applicable Counters
        <span class="badge bg-light text-dark ms-2"><?= count($counters) ?></span>
      </div>
      <?php if ($counters): ?>
      <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
          <thead><tr><th>ID</th><th>Counter</th><th>Meta</th></tr></thead>
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
      <div class="card-body"><?= no_results('No counters linked.') ?></div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
