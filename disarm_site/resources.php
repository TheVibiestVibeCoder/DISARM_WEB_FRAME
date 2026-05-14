<?php
require_once 'config.php';
require_once 'includes/functions.php';
$page_title = 'Resources';

$filter_type = gp('type');
$filter_q    = gp('q');

$types = $pdo->query("SELECT DISTINCT resource_type FROM resource WHERE resource_type != '' ORDER BY resource_type")->fetchAll(PDO::FETCH_COLUMN);

$sql    = "SELECT disarm_id, name, summary, resource_type FROM resource WHERE 1=1";
$params = [];
if ($filter_type) { $sql .= " AND resource_type = ?"; $params[] = $filter_type; }
if ($filter_q) {
    $sql .= " AND (name LIKE ? OR summary LIKE ?)";
    $params[] = "%$filter_q%"; $params[] = "%$filter_q%";
}
$sql .= " ORDER BY resource_type, name";

$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $sql, $params, $page, 75);

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active">Resources</li>
  </ol>
</nav>

<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-md-auto">
        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All types</option>
          <?php foreach ($types as $t): ?>
          <option value="<?= h($t) ?>" <?= $filter_type === $t ? 'selected' : '' ?>><?= h($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search resources…" value="<?= h($filter_q) ?>">
      </div>
      <div class="col-auto d-flex gap-2">
        <button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button>
        <?php if ($filter_type || $filter_q): ?>
        <a href="resources.php" class="btn btn-sm btn-outline-secondary">Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h2 class="mb-0 fw-bold">
    <i class="bi bi-box me-2"></i>Resources
    <span class="badge bg-secondary fs-6"><?= number_format($result['total']) ?></span>
  </h2>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th style="width:100px">ID</th><th>Resource</th><th style="width:160px">Type</th><th>Summary</th></tr></thead>
      <tbody>
        <?php foreach ($result['rows'] as $row): ?>
        <tr>
          <td><?= id_badge($row['disarm_id']) ?></td>
          <td class="fw-semibold"><?= h($row['name']) ?></td>
          <td><span class="badge bg-light text-dark border"><?= h($row['resource_type']) ?></span></td>
          <td class="text-muted small"><?= h(truncate($row['summary'], 140)) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$result['rows']) echo '<tr><td colspan="4">' . no_results('No resources found.') . '</td></tr>'; ?>
      </tbody>
    </table>
  </div>
  <?php if ($result['total_pages'] > 1): ?>
  <div class="card-footer d-flex justify-content-end">
    <?= pagination_html($result, 'resources.php', array_filter(['type' => $filter_type, 'q' => $filter_q])) ?>
  </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
