<?php
require_once 'config.php';
require_once 'includes/functions.php';
$page_title = 'Incidents';

// ── Filters ───────────────────────────────────────────────────────────────────
$filter_year    = gp('year');
$filter_country = gp('country');
$filter_q       = gp('q');

$years     = $pdo->query("SELECT DISTINCT year_started FROM incident WHERE year_started != '' ORDER BY year_started DESC")->fetchAll(PDO::FETCH_COLUMN);
$countries = $pdo->query("SELECT DISTINCT found_in_country FROM incident WHERE found_in_country != '' ORDER BY found_in_country")->fetchAll(PDO::FETCH_COLUMN);

// ── Query ─────────────────────────────────────────────────────────────────────
$sql    = "SELECT i.disarm_id, i.name, i.summary, i.year_started, i.attributions_seen, i.found_in_country, i.objecttype,
                  (SELECT COUNT(*) FROM incident_technique it WHERE it.incident_id = i.disarm_id) AS tech_count
           FROM incident i WHERE 1=1";
$params = [];

if ($filter_year)    { $sql .= " AND i.year_started = ?";      $params[] = $filter_year;    }
if ($filter_country) { $sql .= " AND i.found_in_country = ?";  $params[] = $filter_country; }
if ($filter_q) {
    $sql .= " AND (i.name LIKE ? OR i.summary LIKE ?)";
    $params[] = "%$filter_q%";
    $params[] = "%$filter_q%";
}
$sql .= " ORDER BY i.year_started DESC, i.disarm_id";

$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $sql, $params, $page, 50);

include 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active">Incidents</li>
  </ol>
</nav>

<!-- ── Filters ───────────────────────────────────────────────────────────── -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-md-auto">
        <select name="year" class="form-select form-select-sm">
          <option value="">All years</option>
          <?php foreach ($years as $y): ?>
          <option value="<?= h($y) ?>" <?= $filter_year === $y ? 'selected' : '' ?>><?= h($y) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-auto">
        <select name="country" class="form-select form-select-sm">
          <option value="">All countries</option>
          <?php foreach ($countries as $c): ?>
          <option value="<?= h($c) ?>" <?= $filter_country === $c ? 'selected' : '' ?>><?= h($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search incidents…" value="<?= h($filter_q) ?>">
      </div>
      <div class="col-auto d-flex gap-2">
        <button class="btn btn-sm btn-warning text-dark" type="submit"><i class="bi bi-search"></i> Filter</button>
        <?php if ($filter_year || $filter_country || $filter_q): ?>
        <a href="incidents.php" class="btn btn-sm btn-outline-secondary">Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h2 class="mb-0 fw-bold">Incidents
    <span class="badge bg-secondary fs-6"><?= number_format($result['total']) ?></span>
  </h2>
  <?= pagination_html($result, 'incidents.php', array_filter(['year' => $filter_year, 'country' => $filter_country, 'q' => $filter_q])) ?>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th style="width:100px">ID</th>
          <th>Incident</th>
          <th style="width:70px">Year</th>
          <th style="width:140px">Country</th>
          <th style="width:80px" class="text-center">Techniques</th>
          <th>Summary</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result['rows'] as $row): ?>
        <tr>
          <td><?= id_badge($row['disarm_id'], 'incident.php?id=' . urlencode($row['disarm_id'])) ?></td>
          <td><a href="incident.php?id=<?= urlencode($row['disarm_id']) ?>"><?= h($row['name']) ?></a></td>
          <td><?= h($row['year_started']) ?></td>
          <td><small class="text-muted"><?= h($row['found_in_country']) ?></small></td>
          <td class="text-center">
            <?php if ($row['tech_count']): ?>
            <span class="badge bg-danger"><?= $row['tech_count'] ?></span>
            <?php else: echo '<span class="text-muted">—</span>'; endif; ?>
          </td>
          <td class="text-muted small"><?= h(truncate($row['summary'], 120)) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$result['rows']) echo '<tr><td colspan="6">' . no_results('No incidents match your filters.') . '</td></tr>'; ?>
      </tbody>
    </table>
  </div>
  <?php if ($result['total_pages'] > 1): ?>
  <div class="card-footer d-flex justify-content-end">
    <?= pagination_html($result, 'incidents.php', array_filter(['year' => $filter_year, 'country' => $filter_country, 'q' => $filter_q])) ?>
  </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
