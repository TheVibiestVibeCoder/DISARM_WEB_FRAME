<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Techniques';

// ── CRUD ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (pp('action') === 'add') {
        $did  = strtoupper(trim(pp('disarm_id')));
        $name = pp('name');
        if ($did && $name) {
            $pdo->prepare("INSERT INTO technique (disarm_id,name,tactic_id,summary) VALUES (?,?,?,?)")
                ->execute([$did, $name, pp('tactic_id'), pp('summary')]);
            flash('success', "Technique $did added.");
            header('Location: technique.php?id=' . urlencode($did)); exit;
        }
        flash('error', 'ID and Name are required.');
        header('Location: techniques.php?action=add'); exit;
    }
}

$filter_tactic = gp('tactic');
$filter_q      = gp('q');
$tactics = $pdo->query("SELECT disarm_id, name FROM tactic ORDER BY disarm_id")->fetchAll();

$sql    = "SELECT tc.disarm_id, tc.name, tc.tactic_id, tc.summary, ta.name AS tactic_name
           FROM technique tc LEFT JOIN tactic ta ON ta.disarm_id = tc.tactic_id WHERE 1=1";
$params = [];
if ($filter_tactic) { $sql .= " AND tc.tactic_id = ?"; $params[] = $filter_tactic; }
if ($filter_q)      { $sql .= " AND (tc.name LIKE ? OR tc.summary LIKE ?)"; $params[] = "%$filter_q%"; $params[] = "%$filter_q%"; }
$sql .= " ORDER BY tc.disarm_id";

$page   = max(1, (int)gp('page', '1'));
$result = paginate($pdo, $sql, $params, $page, 75);
$show_add = (gp('action') === 'add');

include 'includes/header.php';
?>

<div class="container">

<?= render_flash() ?>

<?php if ($show_add): ?>
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New Technique</div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="form-grid">
      <div class="form-group">
        <label>DISARM ID *</label>
        <input type="text" name="disarm_id" placeholder="e.g. T2050 or T2050.001" required>
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
    </div>
    <div class="form-group" style="margin-top:20px">
      <label>Summary</label>
      <textarea name="summary" rows="4"></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Technique</button>
      <a href="techniques.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="page-header">
  <div class="label"><span class="tag tag-red" style="margin-right:4px">RED</span>Framework</div>
  <div class="page-header-row">
    <div>
      <h1>Techniques</h1>
      <div class="page-count"><?= number_format($result['total']) ?> techniques</div>
    </div>
    <a href="techniques.php?action=add" class="btn btn-dark btn-sm">+ New Technique</a>
  </div>
</div>

<!-- Filters -->
<form method="get" class="filter-bar">
  <select name="tactic" onchange="this.form.submit()">
    <option value="">All tactics</option>
    <?php foreach ($tactics as $ta): ?>
    <option value="<?= h($ta['disarm_id']) ?>" <?= $filter_tactic === $ta['disarm_id'] ? 'selected' : '' ?>>
      <?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?>
    </option>
    <?php endforeach; ?>
  </select>
  <input type="text" name="q" placeholder="Search techniques…" value="<?= h($filter_q) ?>">
  <button type="submit" class="btn btn-dark btn-sm">Filter</button>
  <?php if ($filter_tactic || $filter_q): ?>
  <a href="techniques.php" class="btn btn-ghost btn-sm">Clear</a>
  <?php endif; ?>
</form>

<table class="data-table">
  <thead>
    <tr>
      <th class="col-id">ID</th>
      <th>Technique</th>
      <th class="col-mid">Tactic</th>
      <th>Summary</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($result['rows'] as $row): ?>
    <tr>
      <td><?= id_badge($row['disarm_id'], 'technique.php?id=' . urlencode($row['disarm_id'])) ?></td>
      <td>
        <a href="technique.php?id=<?= urlencode($row['disarm_id']) ?>">
          <?php if ($filter_q): echo preg_replace('/(' . preg_quote(h($filter_q), '/') . ')/i', '<mark>$1</mark>', h($row['name']));
          else: echo h($row['name']); endif; ?>
        </a>
        <?php if (str_contains($row['disarm_id'], '.')): ?>
        <span class="tag tag-muted" style="margin-left:6px">sub</span>
        <?php endif; ?>
      </td>
      <td>
        <?php if ($row['tactic_id']): ?>
        <a href="tactic.php?id=<?= urlencode($row['tactic_id']) ?>" style="text-decoration:none">
          <?= id_badge($row['tactic_id']) ?>
        </a>
        <?php endif; ?>
      </td>
      <td class="col-summary"><?= h(truncate($row['summary'], 130)) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?>
    <tr><td colspan="4"><?= no_results('No techniques match your filters.') ?></td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?= pagination_html($result, 'techniques.php', array_filter(['tactic' => $filter_tactic, 'q' => $filter_q])) ?>

</div>
<?php include 'includes/footer.php'; ?>
