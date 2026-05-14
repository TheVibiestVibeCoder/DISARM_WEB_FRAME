<?php
require_once 'config.php';
require_once 'includes/functions.php';
$page_title = 'Playbooks';

$filter_q = gp('q');

$sql    = "SELECT disarm_id, object_id, name, summary FROM playbook WHERE 1=1";
$params = [];
if ($filter_q) {
    $sql .= " AND (name LIKE ? OR summary LIKE ?)";
    $params[] = "%$filter_q%"; $params[] = "%$filter_q%";
}
$sql .= " ORDER BY disarm_id";

$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $sql, $params, $page, 50);

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active">Playbooks</li>
  </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0 fw-bold">
    <i class="bi bi-journal-text me-2"></i>Playbooks
    <span class="badge bg-secondary fs-6"><?= number_format($result['total']) ?></span>
  </h2>
  <form method="get" class="d-flex gap-2">
    <input type="text" name="q" class="form-control form-control-sm" placeholder="Search playbooks…" value="<?= h($filter_q) ?>">
    <button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button>
    <?php if ($filter_q): ?><a href="playbooks.php" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
  </form>
</div>

<div class="row g-3">
  <?php foreach ($result['rows'] as $row): ?>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="disarm-id mb-2"><?= h($row['disarm_id']) ?></div>
        <h6 class="card-title fw-bold"><?= h($row['name']) ?></h6>
        <?php if ($row['object_id']): ?>
        <div class="text-muted small mb-2">Object: <?= h($row['object_id']) ?></div>
        <?php endif; ?>
        <?php if ($row['summary']): ?>
        <p class="card-text text-muted small"><?= h(truncate($row['summary'], 200)) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (!$result['rows']): ?>
  <div class="col-12"><?= no_results('No playbooks found.') ?></div>
  <?php endif; ?>
</div>

<?php if ($result['total_pages'] > 1): ?>
<div class="mt-3"><?= pagination_html($result, 'playbooks.php', array_filter(['q' => $filter_q])) ?></div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
