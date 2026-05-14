<?php
require_once 'config.php';
require_once 'includes/functions.php';
$page_title = 'External Groups';

$filter_region = gp('region');
$filter_q      = gp('q');

$regions = $pdo->query("SELECT DISTINCT region FROM externalgroup WHERE region != '' ORDER BY region")->fetchAll(PDO::FETCH_COLUMN);

$sql    = "SELECT disarm_id, name, summary, sector, primary_role, region, country, twitter_handle, url
           FROM externalgroup WHERE 1=1";
$params = [];
if ($filter_region) { $sql .= " AND region = ?"; $params[] = $filter_region; }
if ($filter_q) {
    $sql .= " AND (name LIKE ? OR summary LIKE ? OR country LIKE ?)";
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
    <li class="breadcrumb-item active">External Groups</li>
  </ol>
</nav>

<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-md-auto">
        <select name="region" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All regions</option>
          <?php foreach ($regions as $r): ?>
          <option value="<?= h($r) ?>" <?= $filter_region === $r ? 'selected' : '' ?>><?= h($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search groups, countries…" value="<?= h($filter_q) ?>">
      </div>
      <div class="col-auto d-flex gap-2">
        <button class="btn btn-sm btn-secondary" type="submit"><i class="bi bi-search"></i> Filter</button>
        <?php if ($filter_region || $filter_q): ?>
        <a href="groups.php" class="btn btn-sm btn-outline-secondary">Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h2 class="mb-0 fw-bold">
    <i class="bi bi-people me-2"></i>External Groups
    <span class="badge bg-secondary fs-6"><?= number_format($result['total']) ?></span>
  </h2>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th style="width:100px">ID</th>
          <th>Name</th>
          <th style="width:140px">Role</th>
          <th style="width:120px">Region</th>
          <th style="width:120px">Country</th>
          <th style="width:100px">Links</th>
          <th>Summary</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result['rows'] as $row): ?>
        <tr>
          <td><?= id_badge($row['disarm_id']) ?></td>
          <td class="fw-semibold"><?= h($row['name']) ?></td>
          <td><small class="text-muted"><?= h($row['primary_role']) ?></small></td>
          <td><small><?= h($row['region']) ?></small></td>
          <td><small><?= h($row['country']) ?></small></td>
          <td>
            <?php if ($row['url']): ?>
            <a href="<?= h($row['url']) ?>" target="_blank" rel="noopener" class="small me-1"><i class="bi bi-box-arrow-up-right"></i></a>
            <?php endif; ?>
            <?php if ($row['twitter_handle']): ?>
            <span class="text-muted small">@<?= h($row['twitter_handle']) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-muted small"><?= h(truncate($row['summary'], 120)) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$result['rows']) echo '<tr><td colspan="7">' . no_results('No groups match your filters.') . '</td></tr>'; ?>
      </tbody>
    </table>
  </div>
  <?php if ($result['total_pages'] > 1): ?>
  <div class="card-footer d-flex justify-content-end">
    <?= pagination_html($result, 'groups.php', array_filter(['region' => $filter_region, 'q' => $filter_q])) ?>
  </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
