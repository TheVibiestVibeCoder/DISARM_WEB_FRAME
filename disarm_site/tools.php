<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Tools';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (pp('action') === 'add') {
        $did = strtoupper(trim(pp('disarm_id'))); $name = pp('name');
        if ($did && $name) {
            $pdo->prepare("INSERT INTO tool (disarm_id,name,category,platform,url) VALUES (?,?,?,?,?)")
                ->execute([$did, $name, pp('category'), pp('platform'), pp('url')]);
            flash('success', "Tool $did added.");
        } else { flash('error', 'ID and Name are required.'); }
        header('Location: tools.php'); exit;
    }
    if (pp('action') === 'delete') {
        $did = pp('disarm_id');
        $pdo->prepare("DELETE FROM tool WHERE disarm_id=?")->execute([$did]);
        flash('success', "Tool $did deleted.");
        header('Location: tools.php'); exit;
    }
}

$filter_q = gp('q');
$sql      = "SELECT disarm_id, name, category, platform, url FROM tool WHERE 1=1";
$params   = [];
if ($filter_q) { $sql .= " AND (name LIKE ? OR category LIKE ?)"; $params[] = "%$filter_q%"; $params[] = "%$filter_q%"; }
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
  <div class="form-section-title">New Tool</div>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="form-grid">
      <div class="form-group"><label>DISARM ID *</label><input type="text" name="disarm_id" placeholder="e.g. TOL001" required></div>
      <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Category</label><input type="text" name="category"></div>
      <div class="form-group"><label>Platform</label><input type="text" name="platform"></div>
      <div class="form-group"><label>URL</label><input type="url" name="url"></div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Tool</button>
      <a href="tools.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="page-header">
  <div class="label">Software &amp; Platforms</div>
  <div class="page-header-row">
    <div><h1>Tools</h1><div class="page-count"><?= number_format($result['total']) ?> tools</div></div>
    <a href="tools.php?action=add" class="btn btn-dark btn-sm">+ New Tool</a>
  </div>
</div>

<form method="get" class="filter-bar">
  <input type="text" name="q" placeholder="Search tools…" value="<?= h($filter_q) ?>">
  <button type="submit" class="btn btn-dark btn-sm">Filter</button>
  <?php if ($filter_q): ?><a href="tools.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
</form>

<table class="data-table">
  <thead><tr><th class="col-id">ID</th><th>Tool</th><th class="col-mid">Category</th><th class="col-mid">Platform</th><th>URL</th><th class="col-sm"></th></tr></thead>
  <tbody>
    <?php foreach ($result['rows'] as $row): ?>
    <tr>
      <td><?= id_badge($row['disarm_id']) ?></td>
      <td><?= h($row['name']) ?></td>
      <td class="col-muted"><?= h($row['category'] ?? '') ?></td>
      <td class="col-muted"><?= h($row['platform'] ?? '') ?></td>
      <td class="col-summary"><?= $row['url'] ? '<a href="' . h($row['url']) . '" target="_blank" rel="noopener" style="color:var(--muted)">' . h(truncate($row['url'], 40)) . '</a>' : '' ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Delete?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete">
          <input type="hidden" name="disarm_id" value="<?= h($row['disarm_id']) ?>">
          <button type="submit" class="btn btn-danger btn-sm">Del</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?><tr><td colspan="6"><?= no_results('No tools yet.') ?></td></tr><?php endif; ?>
  </tbody>
</table>
<?= pagination_html($result, 'tools.php', $filter_q ? ['q' => $filter_q] : []) ?>
</div>
<?php include 'includes/footer.php'; ?>
