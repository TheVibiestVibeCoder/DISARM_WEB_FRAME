<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$id = gp('id');
if (!$id) { header('Location: blue.php'); exit; }

// ── CRUD ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = pp('action');
    if ($action === 'edit') {
        $pdo->prepare("UPDATE counter SET name=?, tactic_id=?, metatechnique_id=?, summary=? WHERE disarm_id=?")
            ->execute([pp('name'), pp('tactic_id') ?: null, pp('metatechnique_id') ?: null, pp('summary'), $id]);
        flash('success', 'Counter updated.');
        header("Location: counter.php?id=" . urlencode($id)); exit;
    }
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM counter WHERE disarm_id=?")->execute([$id]);
        flash('success', "Counter $id deleted.");
        header('Location: blue.php'); exit;
    }
}

$stmt = $pdo->prepare("SELECT c.*, ta.name AS tactic_name, mt.name AS meta_name FROM counter c LEFT JOIN tactic ta ON ta.disarm_id = c.tactic_id LEFT JOIN metatechnique mt ON mt.disarm_id = c.metatechnique_id WHERE c.disarm_id = ?");
$stmt->execute([$id]);
$counter = $stmt->fetch();
if (!$counter) { http_response_code(404); die('<h2>Counter not found: ' . h($id) . '</h2>'); }

$page_title = $counter['disarm_id'] . ' ' . $counter['name'];

$techniques = $pdo->prepare("SELECT tc.disarm_id, tc.name, tc.tactic_id FROM technique tc INNER JOIN counter_technique ct ON ct.technique_id = tc.disarm_id WHERE ct.counter_id = ? ORDER BY tc.disarm_id");
$techniques->execute([$id]); $techniques = $techniques->fetchAll();

$all_tactics = $pdo->prepare("SELECT ct.tactic_id, ct.main_tactic, ta.name AS tactic_name FROM counter_tactic ct LEFT JOIN tactic ta ON ta.disarm_id = ct.tactic_id WHERE ct.counter_id = ? ORDER BY ct.main_tactic DESC, ct.tactic_id");
$all_tactics->execute([$id]); $all_tactics = $all_tactics->fetchAll();

$tactics        = $pdo->query("SELECT disarm_id, name FROM tactic ORDER BY disarm_id")->fetchAll();
$metatechniques = $pdo->query("SELECT disarm_id, name FROM metatechnique ORDER BY disarm_id")->fetchAll();
$show_edit      = (gp('action') === 'edit');

include 'includes/header.php';
?>

<div class="container">

<?= render_flash() ?>

<div class="breadcrumb">
  <a href="index.php">Home</a><span class="breadcrumb-sep">/</span>
  <a href="blue.php">Blue Team</a><span class="breadcrumb-sep">/</span>
  <span><?= h($counter['disarm_id']) ?></span>
</div>

<div class="detail-header">
  <div class="detail-meta">
    <span class="disarm-id"><?= h($counter['disarm_id']) ?></span>
    <span class="tag tag-blue">BLUE</span>
    <?php if ($counter['tactic_id']): ?>
    <a href="tactic.php?id=<?= urlencode($counter['tactic_id']) ?>" style="text-decoration:none">
      <span class="tag tag-muted"><?= h($counter['tactic_id']) ?> <?= h($counter['tactic_name']) ?></span>
    </a>
    <?php endif; ?>
    <?php if ($counter['metatechnique_id']): ?>
    <span class="tag tag-muted"><?= h($counter['metatechnique_id']) ?> <?= h($counter['meta_name']) ?></span>
    <?php endif; ?>
  </div>
  <h1><?= h($counter['name']) ?></h1>
  <?php if ($counter['summary']): ?>
  <p class="detail-summary"><?= h($counter['summary']) ?></p>
  <?php endif; ?>
  <div class="detail-actions">
    <a href="counter.php?id=<?= urlencode($id) ?>&action=edit" class="btn btn-ghost btn-sm">Edit</a>
    <form method="post" style="display:inline" onsubmit="return confirm('Permanently delete this counter?')">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="delete">
      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
  </div>
</div>

<?php if ($show_edit): ?>
<div class="form-section" style="border-top:none;margin-top:0;padding-top:0;margin-bottom:clamp(40px,5vw,60px)">
  <div class="form-section-title">Edit Counter</div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="edit">
    <div class="form-grid">
      <div class="form-group">
        <label>DISARM ID</label>
        <input type="text" value="<?= h($id) ?>" readonly>
      </div>
      <div class="form-group">
        <label>Name *</label>
        <input type="text" name="name" value="<?= h($counter['name']) ?>" required>
      </div>
      <div class="form-group">
        <label>Tactic</label>
        <select name="tactic_id">
          <option value="">— none —</option>
          <?php foreach ($tactics as $ta): ?>
          <option value="<?= h($ta['disarm_id']) ?>" <?= $counter['tactic_id'] === $ta['disarm_id'] ? 'selected' : '' ?>>
            <?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Metatechnique</label>
        <select name="metatechnique_id">
          <option value="">— none —</option>
          <?php foreach ($metatechniques as $mt): ?>
          <option value="<?= h($mt['disarm_id']) ?>" <?= $counter['metatechnique_id'] === $mt['disarm_id'] ? 'selected' : '' ?>>
            <?= h($mt['disarm_id']) ?> — <?= h($mt['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group" style="margin-top:20px">
      <label>Summary</label>
      <textarea name="summary" rows="6"><?= h($counter['summary'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Save Changes</button>
      <a href="counter.php?id=<?= urlencode($id) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="grid-2">
  <!-- Techniques addressed -->
  <div>
    <div class="section-title" style="margin-bottom:0">
      <span class="tag tag-red" style="vertical-align:middle;margin-right:8px">RED</span>
      Techniques Addressed — <?= count($techniques) ?>
    </div>
    <?php if ($techniques): ?>
    <table class="data-table">
      <thead><tr><th class="col-id">ID</th><th>Technique</th><th class="col-sm">Tactic</th></tr></thead>
      <tbody>
        <?php foreach ($techniques as $t): ?>
        <tr>
          <td><?= id_badge($t['disarm_id'], 'technique.php?id=' . urlencode($t['disarm_id'])) ?></td>
          <td><a href="technique.php?id=<?= urlencode($t['disarm_id']) ?>"><?= h($t['name']) ?></a></td>
          <td class="col-muted"><?= h($t['tactic_id']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: echo no_results('No specific techniques linked.'); endif; ?>
  </div>

  <!-- Tactic coverage -->
  <div>
    <div class="section-title" style="margin-bottom:0">Tactic Coverage — <?= count($all_tactics) ?></div>
    <?php if ($all_tactics): ?>
    <table class="data-table">
      <thead><tr><th class="col-id">ID</th><th>Tactic</th><th class="col-sm">Primary</th></tr></thead>
      <tbody>
        <?php foreach ($all_tactics as $ta): ?>
        <tr>
          <td><?= id_badge($ta['tactic_id'], 'tactic.php?id=' . urlencode($ta['tactic_id'])) ?></td>
          <td><a href="tactic.php?id=<?= urlencode($ta['tactic_id']) ?>"><?= h($ta['tactic_name']) ?></a></td>
          <td class="col-muted"><?= $ta['main_tactic'] === 'Y' ? 'Yes' : '' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: echo no_results('No tactic links found.'); endif; ?>
  </div>
</div>

</div>
<?php include 'includes/footer.php'; ?>
