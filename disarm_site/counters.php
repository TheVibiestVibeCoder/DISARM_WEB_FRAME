<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Counters';

// ── CRUD ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (pp('action') === 'add') {
        $did  = strtoupper(trim(pp('disarm_id')));
        $name = pp('name');
        if ($did && $name) {
            $pdo->prepare("INSERT INTO counter (disarm_id,name,tactic_id,metatechnique_id,summary) VALUES (?,?,?,?,?)")
                ->execute([$did, $name, pp('tactic_id') ?: null, pp('metatechnique_id') ?: null, pp('summary')]);
            flash('success', "Counter $did added.");
            header('Location: counter.php?id=' . urlencode($did)); exit;
        }
        flash('error', 'ID and Name are required.');
        header('Location: counters.php?action=add'); exit;
    }
}

$filter_tactic = gp('tactic');
$filter_meta   = gp('meta');
$filter_q      = gp('q');

$tactics        = $pdo->query("SELECT disarm_id, name FROM tactic ORDER BY disarm_id")->fetchAll();
$metatechniques = $pdo->query("SELECT disarm_id, name FROM metatechnique ORDER BY disarm_id")->fetchAll();

$sql    = "SELECT c.disarm_id, c.name, c.tactic_id, c.metatechnique_id, c.summary,
                  ta.name AS tactic_name, mt.name AS meta_name
           FROM counter c
           LEFT JOIN tactic ta ON ta.disarm_id = c.tactic_id
           LEFT JOIN metatechnique mt ON mt.disarm_id = c.metatechnique_id
           WHERE 1=1";
$params = [];
if ($filter_tactic) { $sql .= " AND c.tactic_id = ?"; $params[] = $filter_tactic; }
if ($filter_meta)   { $sql .= " AND c.metatechnique_id = ?"; $params[] = $filter_meta; }
if ($filter_q)      { $sql .= " AND (c.name LIKE ? OR c.summary LIKE ?)"; $params[] = "%$filter_q%"; $params[] = "%$filter_q%"; }
$sql .= " ORDER BY c.disarm_id";

$page     = max(1, (int)gp('page', '1'));
$result   = paginate($pdo, $sql, $params, $page, 50);
$show_add = (gp('action') === 'add');

include 'includes/header.php';
?>

<div class="container">

<?= render_flash() ?>

<?php if ($show_add): ?>
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New Counter</div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="form-grid">
      <div class="form-group">
        <label>DISARM ID *</label>
        <input type="text" name="disarm_id" placeholder="e.g. C00340" required>
      </div>
      <div class="form-group">
        <label>Name *</label>
        <input type="text" name="name" required>
      </div>
      <div class="form-group">
        <label>Tactic</label>
        <select name="tactic_id">
          <option value="">— none —</option>
          <?php foreach ($tactics as $ta): ?>
          <option value="<?= h($ta['disarm_id']) ?>"><?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Metatechnique</label>
        <select name="metatechnique_id">
          <option value="">— none —</option>
          <?php foreach ($metatechniques as $mt): ?>
          <option value="<?= h($mt['disarm_id']) ?>"><?= h($mt['disarm_id']) ?> — <?= h($mt['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group" style="margin-top:20px">
      <label>Summary</label>
      <textarea name="summary" rows="4"></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Counter</button>
      <a href="counters.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="page-header">
  <div class="label"><span class="tag tag-blue" style="margin-right:4px">BLUE</span>Framework</div>
  <div class="page-header-row">
    <div>
      <h1>Countermeasures</h1>
      <div class="page-count"><?= number_format($result['total']) ?> counters</div>
    </div>
    <a href="counters.php?action=add" class="btn btn-dark btn-sm">+ New Counter</a>
  </div>
</div>

<form method="get" class="filter-bar">
  <select name="tactic" onchange="this.form.submit()">
    <option value="">All tactics</option>
    <?php foreach ($tactics as $ta): ?>
    <option value="<?= h($ta['disarm_id']) ?>" <?= $filter_tactic === $ta['disarm_id'] ? 'selected' : '' ?>>
      <?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?>
    </option>
    <?php endforeach; ?>
  </select>
  <select name="meta" onchange="this.form.submit()">
    <option value="">All metatechniques</option>
    <?php foreach ($metatechniques as $mt): ?>
    <option value="<?= h($mt['disarm_id']) ?>" <?= $filter_meta === $mt['disarm_id'] ? 'selected' : '' ?>>
      <?= h($mt['disarm_id']) ?> — <?= h($mt['name']) ?>
    </option>
    <?php endforeach; ?>
  </select>
  <input type="text" name="q" placeholder="Search counters…" value="<?= h($filter_q) ?>">
  <button type="submit" class="btn btn-dark btn-sm">Filter</button>
  <?php if ($filter_tactic || $filter_meta || $filter_q): ?>
  <a href="counters.php" class="btn btn-ghost btn-sm">Clear</a>
  <?php endif; ?>
</form>

<table class="data-table">
  <thead>
    <tr>
      <th class="col-id">ID</th>
      <th>Counter</th>
      <th class="col-mid">Tactic</th>
      <th class="col-mid">Metatechnique</th>
      <th>Summary</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($result['rows'] as $row): ?>
    <tr>
      <td><?= id_badge($row['disarm_id'], 'counter.php?id=' . urlencode($row['disarm_id'])) ?></td>
      <td><a href="counter.php?id=<?= urlencode($row['disarm_id']) ?>"><?= h($row['name']) ?></a></td>
      <td class="col-muted"><?= $row['tactic_id'] ? h($row['tactic_id']) : '' ?></td>
      <td class="col-muted"><?= $row['metatechnique_id'] ? h($row['metatechnique_id']) : '' ?></td>
      <td class="col-summary"><?= h(truncate($row['summary'], 110)) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?>
    <tr><td colspan="5"><?= no_results('No counters match your filters.') ?></td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?= pagination_html($result, 'counters.php', array_filter(['tactic' => $filter_tactic, 'meta' => $filter_meta, 'q' => $filter_q])) ?>

</div>
<?php include 'includes/footer.php'; ?>
