<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Tactics';

// ── CRUD ─────────────────────────────────────────────────────────────────────
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = pp('action');
    if ($action === 'add') {
        $did  = strtoupper(trim(pp('disarm_id')));
        $name = pp('name');
        $pid  = pp('phase_id');
        $rank = pp('rank');
        $sum  = pp('summary');
        if ($did && $name) {
            $pdo->prepare("INSERT INTO tactic (disarm_id,name,phase_id,rank,summary) VALUES (?,?,?,?,?)")
                ->execute([$did, $name, $pid, $rank, $sum]);
            flash('success', "Tactic $did added.");
        } else {
            flash('error', 'ID and Name are required.');
        }
        header('Location: tactics.php'); exit;
    }
}

$filter_phase = gp('phase');
$phases  = $pdo->query("SELECT disarm_id, name FROM phase ORDER BY rank")->fetchAll();
$sql     = "SELECT t.disarm_id, t.name, t.phase_id, t.summary,
                   (SELECT COUNT(*) FROM technique tc WHERE tc.tactic_id = t.disarm_id) AS tech_count,
                   (SELECT COUNT(*) FROM counter  c  WHERE c.tactic_id  = t.disarm_id) AS counter_count
            FROM tactic t WHERE 1=1";
$params  = [];
if ($filter_phase) { $sql .= " AND t.phase_id = ?"; $params[] = $filter_phase; }
$sql .= " ORDER BY t.disarm_id";
$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $sql, $params, $page, 50);

$show_add = (gp('action') === 'add');
include 'includes/header.php';
?>

<div class="container">

<?= render_flash() ?>

<?php if ($show_add): ?>
<!-- Add Form -->
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New Tactic</div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="form-grid">
      <div class="form-group">
        <label>DISARM ID *</label>
        <input type="text" name="disarm_id" placeholder="e.g. TA11" required>
      </div>
      <div class="form-group">
        <label>Name *</label>
        <input type="text" name="name" required>
      </div>
      <div class="form-group">
        <label>Phase</label>
        <select name="phase_id">
          <option value="">— none —</option>
          <?php foreach ($phases as $ph): ?>
          <option value="<?= h($ph['disarm_id']) ?>"><?= h($ph['disarm_id']) ?> — <?= h($ph['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Rank / Order</label>
        <input type="text" name="rank" placeholder="e.g. 11">
      </div>
    </div>
    <div class="form-group" style="margin-top:20px">
      <label>Summary</label>
      <textarea name="summary" rows="4"></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Tactic</button>
      <a href="tactics.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
  <div class="label">Framework</div>
  <div class="page-header-row">
    <div>
      <h1>Tactics</h1>
      <div class="page-count"><?= number_format($result['total']) ?> tactics</div>
    </div>
    <a href="tactics.php?action=add" class="btn btn-dark btn-sm">+ New Tactic</a>
  </div>
</div>

<!-- Filter -->
<div class="filter-bar">
  <a href="tactics.php" class="btn btn-sm <?= !$filter_phase ? 'btn-dark' : 'btn-ghost' ?>">All phases</a>
  <?php foreach ($phases as $ph): ?>
  <a href="<?= h(url('tactics.php', ['phase' => $ph['disarm_id']])) ?>"
     class="btn btn-sm <?= $filter_phase === $ph['disarm_id'] ? 'btn-dark' : 'btn-ghost' ?>">
    <?= h($ph['disarm_id']) ?> <?= h($ph['name']) ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- Table -->
<table class="data-table">
  <thead>
    <tr>
      <th class="col-id">ID</th>
      <th>Tactic</th>
      <th class="col-mid">Phase</th>
      <th class="col-sm" style="text-align:center">Techniques</th>
      <th class="col-sm" style="text-align:center">Counters</th>
      <th>Summary</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($result['rows'] as $row): ?>
    <tr>
      <td><?= id_badge($row['disarm_id'], 'tactic.php?id=' . urlencode($row['disarm_id'])) ?></td>
      <td><a href="tactic.php?id=<?= urlencode($row['disarm_id']) ?>"><?= h($row['name']) ?></a></td>
      <td class="col-muted"><?= h($row['phase_id']) ?></td>
      <td style="text-align:center">
        <?php if ($row['tech_count']): ?>
        <a href="techniques.php?tactic=<?= urlencode($row['disarm_id']) ?>"><?= $row['tech_count'] ?></a>
        <?php else: ?><span class="col-muted">—</span><?php endif; ?>
      </td>
      <td style="text-align:center">
        <?php if ($row['counter_count']): ?>
        <a href="counters.php?tactic=<?= urlencode($row['disarm_id']) ?>"><?= $row['counter_count'] ?></a>
        <?php else: ?><span class="col-muted">—</span><?php endif; ?>
      </td>
      <td class="col-summary"><?= h(truncate($row['summary'], 120)) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?>
    <tr><td colspan="6"><?= no_results() ?></td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?= pagination_html($result, 'tactics.php', $filter_phase ? ['phase' => $filter_phase] : []) ?>

</div>
<?php include 'includes/footer.php'; ?>
