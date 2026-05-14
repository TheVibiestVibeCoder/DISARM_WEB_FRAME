<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Detections';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (pp('action') === 'add') {
        $did = strtoupper(trim(pp('disarm_id'))); $name = pp('name');
        if ($did && $name) {
            $pdo->prepare("INSERT INTO detection (disarm_id,name,tactic_id,summary) VALUES (?,?,?,?)")
                ->execute([$did, $name, pp('tactic_id') ?: null, pp('summary')]);
            flash('success', "Detection $did added.");
        } else { flash('error', 'ID and Name are required.'); }
        header('Location: detections.php'); exit;
    }
    if (pp('action') === 'delete') {
        $did = pp('disarm_id');
        $pdo->prepare("DELETE FROM detection WHERE disarm_id=?")->execute([$did]);
        flash('success', "Detection $did deleted.");
        header('Location: detections.php'); exit;
    }
}

$filter_q = gp('q');
$tactics  = $pdo->query("SELECT disarm_id, name FROM tactic ORDER BY disarm_id")->fetchAll();
$sql      = "SELECT d.disarm_id, d.name, d.tactic_id, d.summary FROM detection d WHERE 1=1";
$params   = [];
if ($filter_q) { $sql .= " AND (d.name LIKE ? OR d.summary LIKE ?)"; $params[] = "%$filter_q%"; $params[] = "%$filter_q%"; }
$sql .= " ORDER BY d.disarm_id";
$page    = max(1, (int)gp('page', '1'));
$result  = paginate($pdo, $sql, $params, $page, 50);
$show_add= (gp('action') === 'add');

include 'includes/header.php';
?>
<div class="container">
<?= render_flash() ?>

<?php if ($show_add): ?>
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New Detection</div>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="form-grid">
      <div class="form-group"><label>DISARM ID *</label><input type="text" name="disarm_id" placeholder="e.g. DET001" required></div>
      <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Tactic</label>
        <select name="tactic_id"><option value="">— none —</option>
        <?php foreach ($tactics as $ta): ?><option value="<?= h($ta['disarm_id']) ?>"><?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group" style="margin-top:20px"><label>Summary</label><textarea name="summary" rows="4"></textarea></div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Detection</button>
      <a href="detections.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="page-header">
  <div class="label">Detection Methods</div>
  <div class="page-header-row">
    <div><h1>Detections</h1><div class="page-count"><?= number_format($result['total']) ?> detections</div></div>
    <a href="detections.php?action=add" class="btn btn-dark btn-sm">+ New Detection</a>
  </div>
</div>

<form method="get" class="filter-bar">
  <input type="text" name="q" placeholder="Search detections…" value="<?= h($filter_q) ?>">
  <button type="submit" class="btn btn-dark btn-sm">Filter</button>
  <?php if ($filter_q): ?><a href="detections.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
</form>

<table class="data-table">
  <thead><tr><th class="col-id">ID</th><th>Detection</th><th class="col-mid">Tactic</th><th>Summary</th><th class="col-sm"></th></tr></thead>
  <tbody>
    <?php foreach ($result['rows'] as $row): ?>
    <tr>
      <td><?= id_badge($row['disarm_id']) ?></td>
      <td><?= h($row['name']) ?></td>
      <td class="col-muted"><?= h($row['tactic_id'] ?? '') ?></td>
      <td class="col-summary"><?= h(truncate($row['summary'], 110)) ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Delete?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete">
          <input type="hidden" name="disarm_id" value="<?= h($row['disarm_id']) ?>">
          <button type="submit" class="btn btn-danger btn-sm">Del</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?><tr><td colspan="5"><?= no_results('No detections yet. Add the first one above.') ?></td></tr><?php endif; ?>
  </tbody>
</table>
<?= pagination_html($result, 'detections.php', $filter_q ? ['q' => $filter_q] : []) ?>
</div>
<?php include 'includes/footer.php'; ?>
