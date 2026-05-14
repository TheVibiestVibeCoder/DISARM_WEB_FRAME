<?php
require_once 'config.php';
require_once 'includes/functions.php';
$page_title = 'Countermeasures';

// ── Filters ───────────────────────────────────────────────────────────────────
$filter_tactic = gp('tactic');
$filter_meta   = gp('meta');
$filter_q      = gp('q');

$tactics      = $pdo->query("SELECT disarm_id, name FROM tactic ORDER BY disarm_id")->fetchAll();
$metatechs    = $pdo->query("SELECT disarm_id, name FROM metatechnique ORDER BY disarm_id")->fetchAll();

// ── Query ─────────────────────────────────────────────────────────────────────
$sql    = "SELECT c.disarm_id, c.name, c.tactic_id, c.metatechnique_id, c.summary,
                  ta.name AS tactic_name,
                  mt.name AS meta_name
           FROM counter c
           LEFT JOIN tactic ta ON ta.disarm_id = c.tactic_id
           LEFT JOIN metatechnique mt ON mt.disarm_id = c.metatechnique_id
           WHERE 1=1";
$params = [];

if ($filter_tactic) { $sql .= " AND c.tactic_id = ?";        $params[] = $filter_tactic; }
if ($filter_meta)   { $sql .= " AND c.metatechnique_id = ?"; $params[] = $filter_meta;   }
if ($filter_q) {
    $sql .= " AND (c.name LIKE ? OR c.summary LIKE ?)";
    $params[] = "%$filter_q%";
    $params[] = "%$filter_q%";
}
$sql .= " ORDER BY c.disarm_id";

$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $sql, $params, $page, 75);

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active">Countermeasures</li>
  </ol>
</nav>

<!-- ── Filters ───────────────────────────────────────────────────────────── -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-md-auto">
        <select name="tactic" class="form-select form-select-sm">
          <option value="">All tactics</option>
          <?php foreach ($tactics as $ta): ?>
          <option value="<?= h($ta['disarm_id']) ?>" <?= $filter_tactic === $ta['disarm_id'] ? 'selected' : '' ?>>
            <?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-auto">
        <select name="meta" class="form-select form-select-sm">
          <option value="">All metatechniques</option>
          <?php foreach ($metatechs as $mt): ?>
          <option value="<?= h($mt['disarm_id']) ?>" <?= $filter_meta === $mt['disarm_id'] ? 'selected' : '' ?>>
            <?= h($mt['disarm_id']) ?> <?= h($mt['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search counters…" value="<?= h($filter_q) ?>">
      </div>
      <div class="col-auto d-flex gap-2">
        <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> Filter</button>
        <?php if ($filter_tactic || $filter_meta || $filter_q): ?>
        <a href="counters.php" class="btn btn-sm btn-outline-secondary">Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h2 class="mb-0 fw-bold">
    <span class="fw-blue me-2">BLUE</span>Countermeasures
    <span class="badge bg-secondary fs-6"><?= number_format($result['total']) ?></span>
  </h2>
  <?= pagination_html($result, 'counters.php', array_filter(['tactic' => $filter_tactic, 'meta' => $filter_meta, 'q' => $filter_q])) ?>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th style="width:100px">ID</th>
          <th>Countermeasure</th>
          <th style="width:180px">Tactic</th>
          <th style="width:160px">Metatechnique</th>
          <th>Summary</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result['rows'] as $row): ?>
        <tr>
          <td><?= id_badge($row['disarm_id'], 'counter.php?id=' . urlencode($row['disarm_id'])) ?></td>
          <td>
            <a href="counter.php?id=<?= urlencode($row['disarm_id']) ?>">
              <?php if ($filter_q): ?>
              <?= preg_replace('/(' . preg_quote(h($filter_q), '/') . ')/i', '<mark>$1</mark>', h($row['name'])) ?>
              <?php else: ?>
              <?= h($row['name']) ?>
              <?php endif; ?>
            </a>
          </td>
          <td>
            <?php if ($row['tactic_id']): ?>
            <a href="tactic.php?id=<?= urlencode($row['tactic_id']) ?>" class="text-muted small text-decoration-none">
              <span class="disarm-id"><?= h($row['tactic_id']) ?></span>
            </a>
            <?php endif; ?>
          </td>
          <td>
            <small class="text-muted"><?= h($row['metatechnique_id']) ?></small>
            <?php if ($row['meta_name']): ?>
            <div style="font-size:.75rem;color:#888"><?= h(truncate($row['meta_name'], 30)) ?></div>
            <?php endif; ?>
          </td>
          <td class="text-muted small"><?= h(truncate($row['summary'], 130)) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$result['rows']) echo '<tr><td colspan="5">' . no_results('No counters match your filters.') . '</td></tr>'; ?>
      </tbody>
    </table>
  </div>
  <?php if ($result['total_pages'] > 1): ?>
  <div class="card-footer d-flex justify-content-end">
    <?= pagination_html($result, 'counters.php', array_filter(['tactic' => $filter_tactic, 'meta' => $filter_meta, 'q' => $filter_q])) ?>
  </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
