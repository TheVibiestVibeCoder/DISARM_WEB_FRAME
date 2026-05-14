<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Incidents';

// ── CRUD ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (pp('action') === 'add') {
        $did  = strtoupper(trim(pp('disarm_id')));
        $name = pp('name');
        if ($did && $name) {
            $pdo->prepare("INSERT INTO incident (disarm_id,name,summary,year_started,attributions_seen,found_in_country,objecttype) VALUES (?,?,?,?,?,?,?)")
                ->execute([$did, $name, pp('summary'), pp('year_started'), pp('attributions_seen'), pp('found_in_country'), pp('objecttype')]);
            flash('success', "Incident $did added.");
            header('Location: incident.php?id=' . urlencode($did)); exit;
        }
        flash('error', 'ID and Name are required.');
        header('Location: incidents.php?action=add'); exit;
    }
}

$filter_year    = gp('year');
$filter_country = gp('country');
$filter_q       = gp('q');

$years     = $pdo->query("SELECT DISTINCT year_started FROM incident WHERE year_started != '' ORDER BY year_started DESC")->fetchAll(PDO::FETCH_COLUMN);
$countries = $pdo->query("SELECT DISTINCT found_in_country FROM incident WHERE found_in_country != '' ORDER BY found_in_country")->fetchAll(PDO::FETCH_COLUMN);

$sql    = "SELECT i.disarm_id, i.name, i.summary, i.year_started, i.found_in_country FROM incident i WHERE 1=1";
$params = [];
if ($filter_year)    { $sql .= " AND i.year_started = ?";     $params[] = $filter_year;    }
if ($filter_country) { $sql .= " AND i.found_in_country = ?"; $params[] = $filter_country; }
if ($filter_q)       { $sql .= " AND (i.name LIKE ? OR i.summary LIKE ?)"; $params[] = "%$filter_q%"; $params[] = "%$filter_q%"; }
$sql .= " ORDER BY i.year_started DESC, i.disarm_id";

$page     = max(1, (int)gp('page', '1'));
$result   = paginate($pdo, $sql, $params, $page, 50);
$show_add = (gp('action') === 'add');

include 'includes/header.php';
?>

<div class="container">

<?= render_flash() ?>

<?php if ($show_add): ?>
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New Incident</div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="form-grid">
      <div class="form-group">
        <label>DISARM ID *</label>
        <input type="text" name="disarm_id" placeholder="e.g. I00134" required>
      </div>
      <div class="form-group">
        <label>Name *</label>
        <input type="text" name="name" required>
      </div>
      <div class="form-group">
        <label>Year Started</label>
        <input type="text" name="year_started" placeholder="e.g. 2023">
      </div>
      <div class="form-group">
        <label>Found in Country</label>
        <input type="text" name="found_in_country">
      </div>
      <div class="form-group">
        <label>Attributions Seen</label>
        <input type="text" name="attributions_seen">
      </div>
      <div class="form-group">
        <label>Object Type</label>
        <input type="text" name="objecttype">
      </div>
    </div>
    <div class="form-group" style="margin-top:20px">
      <label>Summary</label>
      <textarea name="summary" rows="5"></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Incident</button>
      <a href="incidents.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="page-header">
  <div class="label">Intelligence</div>
  <div class="page-header-row">
    <div>
      <h1>Incidents</h1>
      <div class="page-count"><?= number_format($result['total']) ?> incidents</div>
    </div>
    <a href="incidents.php?action=add" class="btn btn-dark btn-sm">+ New Incident</a>
  </div>
</div>

<form method="get" class="filter-bar">
  <select name="year" onchange="this.form.submit()">
    <option value="">All years</option>
    <?php foreach ($years as $y): ?>
    <option value="<?= h($y) ?>" <?= $filter_year === $y ? 'selected' : '' ?>><?= h($y) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="country" onchange="this.form.submit()">
    <option value="">All countries</option>
    <?php foreach ($countries as $c): ?>
    <option value="<?= h($c) ?>" <?= $filter_country === $c ? 'selected' : '' ?>><?= h($c) ?></option>
    <?php endforeach; ?>
  </select>
  <input type="text" name="q" placeholder="Search incidents…" value="<?= h($filter_q) ?>">
  <button type="submit" class="btn btn-dark btn-sm">Filter</button>
  <?php if ($filter_year || $filter_country || $filter_q): ?>
  <a href="incidents.php" class="btn btn-ghost btn-sm">Clear</a>
  <?php endif; ?>
</form>

<table class="data-table">
  <thead>
    <tr>
      <th class="col-id">ID</th>
      <th>Incident</th>
      <th class="col-sm">Year</th>
      <th class="col-mid">Country</th>
      <th>Summary</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($result['rows'] as $row): ?>
    <tr>
      <td><?= id_badge($row['disarm_id'], 'incident.php?id=' . urlencode($row['disarm_id'])) ?></td>
      <td><a href="incident.php?id=<?= urlencode($row['disarm_id']) ?>"><?= h($row['name']) ?></a></td>
      <td class="col-muted"><?= h($row['year_started']) ?></td>
      <td class="col-muted"><?= h($row['found_in_country']) ?></td>
      <td class="col-summary"><?= h(truncate($row['summary'], 120)) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?>
    <tr><td colspan="5"><?= no_results('No incidents match your filters.') ?></td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?= pagination_html($result, 'incidents.php', array_filter(['year' => $filter_year, 'country' => $filter_country, 'q' => $filter_q])) ?>

</div>
<?php include 'includes/footer.php'; ?>
