<?php
require_once 'config.php';
require_once 'includes/functions.php';
$page_title = 'Tactics';

// ── Filters ───────────────────────────────────────────────────────────────────
$filter_phase = gp('phase');

$phases = $pdo->query("SELECT disarm_id, name FROM phase ORDER BY rank")->fetchAll();

// ── Query ─────────────────────────────────────────────────────────────────────
$sql    = "SELECT t.disarm_id, t.name, t.phase_id, t.summary,
                  (SELECT COUNT(*) FROM technique tc WHERE tc.tactic_id = t.disarm_id) AS tech_count,
                  (SELECT COUNT(*) FROM counter  c  WHERE c.tactic_id  = t.disarm_id) AS counter_count
           FROM tactic t WHERE 1=1";
$params = [];
if ($filter_phase) {
    $sql    .= " AND t.phase_id = ?";
    $params[] = $filter_phase;
}
$sql .= " ORDER BY t.disarm_id";

$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $sql, $params, $page, 50);

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active">Tactics</li>
  </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h2 class="mb-0 fw-bold">Tactics <span class="badge bg-secondary fs-6"><?= number_format($result['total']) ?></span></h2>

  <!-- Phase filter -->
  <div class="d-flex gap-2 flex-wrap">
    <a href="tactics.php" class="btn btn-sm <?= !$filter_phase ? 'btn-dark' : 'btn-outline-secondary' ?>">All phases</a>
    <?php foreach ($phases as $ph): ?>
    <a href="<?= h(url('tactics.php', ['phase' => $ph['disarm_id']])) ?>"
       class="btn btn-sm <?= $filter_phase === $ph['disarm_id'] ? 'btn-dark' : 'btn-outline-secondary' ?>">
      <?= h($ph['disarm_id']) ?> <?= h($ph['name']) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th style="width:90px">ID</th>
          <th>Tactic</th>
          <th style="width:90px">Phase</th>
          <th style="width:100px" class="text-center">Techniques</th>
          <th style="width:100px" class="text-center">Counters</th>
          <th>Summary</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result['rows'] as $row): ?>
        <tr>
          <td><?= id_badge($row['disarm_id'], 'tactic.php?id=' . urlencode($row['disarm_id'])) ?></td>
          <td><a href="tactic.php?id=<?= urlencode($row['disarm_id']) ?>"><?= h($row['name']) ?></a></td>
          <td><span class="badge bg-secondary"><?= h($row['phase_id']) ?></span></td>
          <td class="text-center">
            <?php if ($row['tech_count']): ?>
            <a href="techniques.php?tactic=<?= urlencode($row['disarm_id']) ?>" class="badge bg-danger text-decoration-none">
              <?= $row['tech_count'] ?>
            </a>
            <?php else: echo '<span class="text-muted">—</span>'; endif; ?>
          </td>
          <td class="text-center">
            <?php if ($row['counter_count']): ?>
            <a href="counters.php?tactic=<?= urlencode($row['disarm_id']) ?>" class="badge bg-primary text-decoration-none">
              <?= $row['counter_count'] ?>
            </a>
            <?php else: echo '<span class="text-muted">—</span>'; endif; ?>
          </td>
          <td class="text-muted small"><?= h(truncate($row['summary'], 120)) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$result['rows']) echo '<tr><td colspan="6">' . no_results() . '</td></tr>'; ?>
      </tbody>
    </table>
  </div>
  <?php if ($result['total_pages'] > 1): ?>
  <div class="card-footer"><?= pagination_html($result, 'tactics.php', $filter_phase ? ['phase' => $filter_phase] : []) ?></div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
