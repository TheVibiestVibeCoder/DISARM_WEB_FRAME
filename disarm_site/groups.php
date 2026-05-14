<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'External Groups';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (pp('action') === 'add') {
        $did = strtoupper(trim(pp('disarm_id'))); $name = pp('name');
        if ($did && $name) {
            $pdo->prepare("INSERT INTO externalgroup (disarm_id,name,region,country,primary_role) VALUES (?,?,?,?,?)")
                ->execute([$did, $name, pp('region'), pp('country'), pp('primary_role')]);
            flash('success', "Group $did added.");
        } else { flash('error', 'ID and Name are required.'); }
        header('Location: groups.php'); exit;
    }
    if (pp('action') === 'delete') {
        $did = pp('disarm_id');
        $pdo->prepare("DELETE FROM externalgroup WHERE disarm_id=?")->execute([$did]);
        flash('success', "Group $did deleted.");
        header('Location: groups.php'); exit;
    }
}

$filter_q = gp('q');
$sql      = "SELECT disarm_id, name, region, country, primary_role FROM externalgroup WHERE 1=1";
$params   = [];
if ($filter_q) { $sql .= " AND (name LIKE ? OR region LIKE ? OR country LIKE ?)"; $params[] = "%$filter_q%"; $params[] = "%$filter_q%"; $params[] = "%$filter_q%"; }
$sql .= " ORDER BY disarm_id";
$page    = max(1, (int)gp('page', '1'));
$result  = paginate($pdo, $sql, $params, $page, 50);
$show_add= (gp('action') === 'add');

include 'includes/header.php';
?>
<div class="container">
<?= render_flash() ?>

<?php if ($show_add): ?>
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New External Group</div>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="form-grid">
      <div class="form-group"><label>DISARM ID *</label><input type="text" name="disarm_id" placeholder="e.g. G001" required></div>
      <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Region</label><input type="text" name="region"></div>
      <div class="form-group"><label>Country</label><input type="text" name="country"></div>
      <div class="form-group"><label>Primary Role</label><input type="text" name="primary_role"></div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Group</button>
      <a href="groups.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="page-header">
  <div class="label">Threat Actors</div>
  <div class="page-header-row">
    <div><h1>External Groups</h1><div class="page-count"><?= number_format($result['total']) ?> groups</div></div>
    <a href="groups.php?action=add" class="btn btn-dark btn-sm">+ New Group</a>
  </div>
</div>

<form method="get" class="filter-bar">
  <input type="text" name="q" placeholder="Search groups…" value="<?= h($filter_q) ?>">
  <button type="submit" class="btn btn-dark btn-sm">Filter</button>
  <?php if ($filter_q): ?><a href="groups.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
</form>

<table class="data-table">
  <thead><tr><th class="col-id">ID</th><th>Group</th><th class="col-mid">Region</th><th class="col-mid">Country</th><th>Role</th><th class="col-sm"></th></tr></thead>
  <tbody>
    <?php foreach ($result['rows'] as $row): ?>
    <tr>
      <td><?= id_badge($row['disarm_id']) ?></td>
      <td><?= h($row['name']) ?></td>
      <td class="col-muted"><?= h($row['region'] ?? '') ?></td>
      <td class="col-muted"><?= h($row['country'] ?? '') ?></td>
      <td class="col-muted"><?= h($row['primary_role'] ?? '') ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Delete?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete">
          <input type="hidden" name="disarm_id" value="<?= h($row['disarm_id']) ?>">
          <button type="submit" class="btn btn-danger btn-sm">Del</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?><tr><td colspan="6"><?= no_results('No groups yet.') ?></td></tr><?php endif; ?>
  </tbody>
</table>
<?= pagination_html($result, 'groups.php', $filter_q ? ['q' => $filter_q] : []) ?>
</div>
<?php include 'includes/footer.php'; ?>
