<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Resources';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (pp('action') === 'add') {
        $did = strtoupper(trim(pp('disarm_id'))); $name = pp('name');
        if ($did && $name) {
            $pdo->prepare("INSERT INTO resource (disarm_id,name,resource_type) VALUES (?,?,?)")
                ->execute([$did, $name, pp('resource_type')]);
            flash('success', "Resource $did added.");
        } else { flash('error', 'ID and Name are required.'); }
        header('Location: resources.php'); exit;
    }
    if (pp('action') === 'delete') {
        $did = pp('disarm_id');
        $pdo->prepare("DELETE FROM resource WHERE disarm_id=?")->execute([$did]);
        flash('success', "Resource $did deleted.");
        header('Location: resources.php'); exit;
    }
}

$sql    = "SELECT disarm_id, name, resource_type FROM resource ORDER BY disarm_id";
$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $sql, [], $page, 50);
$show_add= (gp('action') === 'add');

include 'includes/header.php';
?>
<div class="container">
<?= render_flash() ?>

<?php if ($show_add): ?>
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New Resource</div>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="form-grid">
      <div class="form-group"><label>DISARM ID *</label><input type="text" name="disarm_id" placeholder="e.g. RSC001" required></div>
      <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Resource Type</label><input type="text" name="resource_type"></div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Resource</button>
      <a href="resources.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="page-header">
  <div class="label">Counter Resources</div>
  <div class="page-header-row">
    <div><h1>Resources</h1><div class="page-count"><?= number_format($result['total']) ?> resources</div></div>
    <a href="resources.php?action=add" class="btn btn-dark btn-sm">+ New Resource</a>
  </div>
</div>

<table class="data-table">
  <thead><tr><th class="col-id">ID</th><th>Resource</th><th class="col-mid">Type</th><th class="col-sm"></th></tr></thead>
  <tbody>
    <?php foreach ($result['rows'] as $row): ?>
    <tr>
      <td><?= id_badge($row['disarm_id']) ?></td>
      <td><?= h($row['name']) ?></td>
      <td class="col-muted"><?= h($row['resource_type'] ?? '') ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Delete?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete">
          <input type="hidden" name="disarm_id" value="<?= h($row['disarm_id']) ?>">
          <button type="submit" class="btn btn-danger btn-sm">Del</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?><tr><td colspan="4"><?= no_results('No resources yet.') ?></td></tr><?php endif; ?>
  </tbody>
</table>
<?= pagination_html($result, 'resources.php') ?>
</div>
<?php include 'includes/footer.php'; ?>
