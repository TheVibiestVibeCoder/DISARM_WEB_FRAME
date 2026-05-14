<?php
require_once 'config.php';
require_once 'includes/functions.php';
$page_title = 'Detections';

$filter_tactic = gp('tactic');
$filter_q      = gp('q');

$tactics = $pdo->query("SELECT disarm_id, name FROM tactic ORDER BY disarm_id")->fetchAll();

$sql    = "SELECT d.disarm_id, d.name, d.tactic_id, d.summary, ta.name AS tactic_name
           FROM detection d
           LEFT JOIN tactic ta ON ta.disarm_id = d.tactic_id
           WHERE 1=1";
$params = [];
if ($filter_tactic) { $sql .= " AND d.tactic_id = ?"; $params[] = $filter_tactic; }
if ($filter_q) {
    $sql .= " AND (d.name LIKE ? OR d.summary LIKE ?)";
    $params[] = "%$filter_q%"; $params[] = "%$filter_q%";
}
$sql .= " ORDER BY d.disarm_id";

$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $sql, $params, $page, 75);

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active">Detections</li>
  </ol>
</nav>

<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-md-auto">
        <select name="tactic" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All tactics</option>
          <?php foreach ($tactics as $ta): ?>
          <option value="<?= h($ta['disarm_id']) ?>" <?= $filter_tactic === $ta['disarm_id'] ? 'selected' : '' ?>>
            <?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search detections…" value="<?= h($filter_q) ?>">
      </div>
      <div class="col-auto d-flex gap-2">
        <button class="btn btn-sm btn-info text-dark" type="submit"><i class="bi bi-search"></i> Filter</button>
        <?php if ($filter_tactic || $filter_q): ?>
        <a href="detections.php" class="btn btn-sm btn-outline-secondary">Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h2 class="mb-0 fw-bold">
    <i class="bi bi-eye me-2 text-info"></i>Detections
    <span class="badge bg-secondary fs-6"><?= number_format($result['total']) ?></span>
  </h2>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th style="width:110px">ID</th><th>Detection</th><th style="width:200px">Tactic</th><th>Summary</th></tr>
      </thead>
      <tbody>
        <?php foreach ($result['rows'] as $row): ?>
        <tr>
          <td><?= id_badge($row['disarm_id']) ?></td>
          <td><?= h($row['name']) ?></td>
          <td>
            <?php if ($row['tactic_id']): ?>
            <a href="tactic.php?id=<?= urlencode($row['tactic_id']) ?>" class="text-muted small text-decoration-none">
              <span class="disarm-id"><?= h($row['tactic_id']) ?></span>
            </a>
            <?php endif; ?>
          </td>
          <td class="text-muted small"><?= h(truncate($row['summary'], 140)) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$result['rows']) echo '<tr><td colspan="4">' . no_results('No detections match your filters.') . '</td></tr>'; ?>
      </tbody>
    </table>
  </div>
  <?php if ($result['total_pages'] > 1): ?>
  <div class="card-footer d-flex justify-content-end">
    <?= pagination_html($result, 'detections.php', array_filter(['tactic' => $filter_tactic, 'q' => $filter_q])) ?>
  </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
