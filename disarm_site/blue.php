<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Blue Team';

// ── CRUD ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = pp('action');
    if ($act === 'add-counter') {
        $did = strtoupper(trim(pp('disarm_id'))); $name = pp('name');
        if ($did && $name) {
            try {
                $pdo->prepare("INSERT INTO counter (disarm_id,name,tactic_id,metatechnique_id,summary) VALUES (?,?,?,?,?)")
                    ->execute([$did, $name, pp('tactic_id') ?: null, pp('metatechnique_id') ?: null, pp('summary')]);
                flash('success', "Counter $did added.");
                header('Location: counter.php?id=' . urlencode($did)); exit;
            } catch (Exception $e) {
                flash('error', 'Could not add counter: ' . $e->getMessage());
            }
        } else { flash('error', 'ID and Name are required.'); }
        header('Location: blue.php?add=counter'); exit;
    }
    if ($act === 'add-detection') {
        $did = strtoupper(trim(pp('disarm_id'))); $name = pp('name');
        if ($did && $name) {
            try {
                $pdo->prepare("INSERT INTO detection (disarm_id,name,tactic_id,summary) VALUES (?,?,?,?)")
                    ->execute([$did, $name, pp('tactic_id') ?: null, pp('summary')]);
                flash('success', "Detection $did added.");
            } catch (Exception $e) {
                flash('error', 'Could not add detection: ' . $e->getMessage());
            }
        } else { flash('error', 'ID and Name are required.'); }
        header('Location: blue.php'); exit;
    }
    if ($act === 'delete-detection') {
        try {
            $pdo->prepare("DELETE FROM detection WHERE disarm_id=?")->execute([pp('disarm_id')]);
            flash('success', 'Detection deleted.');
        } catch (Exception $e) {
            flash('error', 'Could not delete: ' . $e->getMessage());
        }
        header('Location: blue.php'); exit;
    }
}

// ── Filters ────────────────────────────────────────────────────────────────────
$filter_tactic = gp('tactic');
$filter_meta   = gp('meta');
$filter_q      = gp('q');
$add           = gp('add'); // 'counter' | 'detection'

// ── Reference data ─────────────────────────────────────────────────────────────
$tactics        = $pdo->query("SELECT disarm_id, name FROM tactic ORDER BY disarm_id")->fetchAll();
$metatechniques = $pdo->query("SELECT disarm_id, name FROM metatechnique ORDER BY disarm_id")->fetchAll();

// ── Counters (paginated) ───────────────────────────────────────────────────────
$ctr_sql    = "SELECT c.disarm_id, c.name, c.tactic_id, c.metatechnique_id, c.summary,
                      ta.name AS tactic_name, mt.name AS meta_name
               FROM counter c
               LEFT JOIN tactic ta ON ta.disarm_id = c.tactic_id
               LEFT JOIN metatechnique mt ON mt.disarm_id = c.metatechnique_id
               WHERE 1=1";
$ctr_params = [];
if ($filter_tactic) { $ctr_sql .= " AND c.tactic_id = ?";       $ctr_params[] = $filter_tactic; }
if ($filter_meta)   { $ctr_sql .= " AND c.metatechnique_id = ?"; $ctr_params[] = $filter_meta; }
if ($filter_q)      { $ctr_sql .= " AND (c.name LIKE ? OR c.summary LIKE ?)"; $ctr_params[] = "%$filter_q%"; $ctr_params[] = "%$filter_q%"; }
$ctr_sql .= " ORDER BY c.disarm_id";

$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $ctr_sql, $ctr_params, $page, 50);

// ── Detections (all, simple) ───────────────────────────────────────────────────
$det_sql    = "SELECT disarm_id, name, tactic_id, summary FROM detection WHERE 1=1";
$det_params = [];
if ($filter_q) { $det_sql .= " AND (name LIKE ? OR summary LIKE ?)"; $det_params[] = "%$filter_q%"; $det_params[] = "%$filter_q%"; }
$det_sql .= " ORDER BY disarm_id";
try {
    $det_stmt = $pdo->prepare($det_sql);
    $det_stmt->execute($det_params);
    $detections = $det_stmt->fetchAll();
} catch (Exception $e) {
    $detections = [];
}

include 'includes/header.php';
?>

<div class="container">

<?= render_flash() ?>

<?php if ($add === 'counter'): ?>
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New Counter</div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add-counter">
    <div class="form-grid">
      <div class="form-group"><label>DISARM ID *</label><input type="text" name="disarm_id" placeholder="e.g. C00340" required></div>
      <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Tactic</label>
        <select name="tactic_id"><option value="">— none —</option>
          <?php foreach ($tactics as $ta): ?>
          <option value="<?= h($ta['disarm_id']) ?>"><?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Metatechnique</label>
        <select name="metatechnique_id"><option value="">— none —</option>
          <?php foreach ($metatechniques as $mt): ?>
          <option value="<?= h($mt['disarm_id']) ?>"><?= h($mt['disarm_id']) ?> — <?= h($mt['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group" style="margin-top:20px"><label>Summary</label><textarea name="summary" rows="4"></textarea></div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Counter</button>
      <a href="blue.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<?php if ($add === 'detection'): ?>
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New Detection</div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add-detection">
    <div class="form-grid">
      <div class="form-group"><label>DISARM ID *</label><input type="text" name="disarm_id" placeholder="e.g. DET001" required></div>
      <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Tactic</label>
        <select name="tactic_id"><option value="">— none —</option>
          <?php foreach ($tactics as $ta): ?>
          <option value="<?= h($ta['disarm_id']) ?>"><?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group" style="margin-top:20px"><label>Summary</label><textarea name="summary" rows="4"></textarea></div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Detection</button>
      <a href="blue.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Page header -->
<div class="page-header">
  <div class="label"><span class="tag tag-blue" style="margin-right:4px">BLUE</span>Framework</div>
  <div class="page-header-row">
    <div>
      <h1>Blue Team</h1>
      <div class="page-count">Countermeasures &amp; detections</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="blue.php?add=detection" class="btn btn-ghost btn-sm">+ Detection</a>
      <a href="blue.php?add=counter"   class="btn btn-dark  btn-sm">+ Counter</a>
    </div>
  </div>
</div>

<!-- ── Counters ──────────────────────────────────────────────────────────────── -->
<div class="label" style="margin-bottom:20px">Countermeasures</div>

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
  <input type="text" name="q" placeholder="Search…" value="<?= h($filter_q) ?>">
  <button type="submit" class="btn btn-dark btn-sm">Filter</button>
  <?php if ($filter_tactic || $filter_meta || $filter_q): ?>
  <a href="blue.php" class="btn btn-ghost btn-sm">Clear</a>
  <?php endif; ?>
</form>

<table class="data-table" style="margin-bottom:clamp(60px,8vw,100px)">
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
      <td><a href="counter.php?id=<?= urlencode($row['disarm_id']) ?>">
        <?php if ($filter_q): echo preg_replace('/(' . preg_quote(h($filter_q), '/') . ')/i', '<mark>$1</mark>', h($row['name']));
        else: echo h($row['name']); endif; ?>
      </a></td>
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

<?= pagination_html($result, 'blue.php', array_filter(['tactic' => $filter_tactic, 'meta' => $filter_meta, 'q' => $filter_q])) ?>

<!-- ── Detections ────────────────────────────────────────────────────────────── -->
<div class="label" style="margin-bottom:20px;margin-top:clamp(40px,6vw,80px)">Detections</div>

<table class="data-table">
  <thead>
    <tr>
      <th class="col-id">ID</th>
      <th>Detection</th>
      <th class="col-mid">Tactic</th>
      <th>Summary</th>
      <th class="col-sm"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($detections as $d): ?>
    <tr>
      <td><?= id_badge($d['disarm_id']) ?></td>
      <td><?= h($d['name']) ?></td>
      <td class="col-muted"><?= h($d['tactic_id'] ?? '') ?></td>
      <td class="col-summary"><?= h(truncate($d['summary'] ?? '', 110)) ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Delete?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action"    value="delete-detection">
          <input type="hidden" name="disarm_id" value="<?= h($d['disarm_id']) ?>">
          <button type="submit" class="btn btn-danger btn-sm">Del</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$detections): ?>
    <tr><td colspan="5"><?= no_results('No detections yet.') ?></td></tr>
    <?php endif; ?>
  </tbody>
</table>

</div><!-- /container -->
<?php include 'includes/footer.php'; ?>
