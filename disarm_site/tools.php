<?php
require_once 'config.php';
require_once 'includes/functions.php';
$page_title = 'Tools';

$filter_category = gp('category');
$filter_q        = gp('q');

$categories = $pdo->query("SELECT DISTINCT category FROM tool WHERE category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$sql    = "SELECT disarm_id, name, summary, category, platform, url, function, accessibility
           FROM tool WHERE 1=1";
$params = [];
if ($filter_category) { $sql .= " AND category = ?"; $params[] = $filter_category; }
if ($filter_q) {
    $sql .= " AND (name LIKE ? OR summary LIKE ? OR function LIKE ?)";
    $params[] = "%$filter_q%"; $params[] = "%$filter_q%"; $params[] = "%$filter_q%";
}
$sql .= " ORDER BY name";

$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $sql, $params, $page, 50);

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active">Tools</li>
  </ol>
</nav>

<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-md-auto">
        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All categories</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= h($cat) ?>" <?= $filter_category === $cat ? 'selected' : '' ?>><?= h($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search tools…" value="<?= h($filter_q) ?>">
      </div>
      <div class="col-auto d-flex gap-2">
        <button class="btn btn-sm btn-success" type="submit"><i class="bi bi-search"></i> Filter</button>
        <?php if ($filter_category || $filter_q): ?>
        <a href="tools.php" class="btn btn-sm btn-outline-secondary">Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h2 class="mb-0 fw-bold">
    <i class="bi bi-tools me-2"></i>Tools
    <span class="badge bg-secondary fs-6"><?= number_format($result['total']) ?></span>
  </h2>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th style="width:100px">ID</th>
          <th>Tool</th>
          <th style="width:130px">Category</th>
          <th style="width:130px">Platform</th>
          <th style="width:90px">Access</th>
          <th>Summary</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result['rows'] as $row): ?>
        <tr>
          <td><?= id_badge($row['disarm_id']) ?></td>
          <td>
            <?php if ($row['url']): ?>
            <a href="<?= h($row['url']) ?>" target="_blank" rel="noopener" class="fw-semibold">
              <?= h($row['name']) ?> <i class="bi bi-box-arrow-up-right" style="font-size:.7rem"></i>
            </a>
            <?php else: ?>
            <span class="fw-semibold"><?= h($row['name']) ?></span>
            <?php endif; ?>
          </td>
          <td><small><?= h($row['category']) ?></small></td>
          <td><small class="text-muted"><?= h($row['platform']) ?></small></td>
          <td>
            <small class="badge bg-light text-dark border"><?= h($row['accessibility']) ?></small>
          </td>
          <td class="text-muted small"><?= h(truncate($row['summary'], 130)) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$result['rows']) echo '<tr><td colspan="6">' . no_results('No tools match your filters.') . '</td></tr>'; ?>
      </tbody>
    </table>
  </div>
  <?php if ($result['total_pages'] > 1): ?>
  <div class="card-footer d-flex justify-content-end">
    <?= pagination_html($result, 'tools.php', array_filter(['category' => $filter_category, 'q' => $filter_q])) ?>
  </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
