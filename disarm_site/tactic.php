<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$id = gp('id');
if (!$id) { header('Location: tactics.php'); exit; }

// ── CRUD ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = pp('action');
    if ($action === 'edit') {
        $pdo->prepare("UPDATE tactic SET name=?, phase_id=?, rank=?, summary=? WHERE disarm_id=?")
            ->execute([pp('name'), pp('phase_id'), pp('rank'), pp('summary'), $id]);
        flash('success', 'Tactic updated.');
        header("Location: tactic.php?id=" . urlencode($id)); exit;
    }
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM tactic WHERE disarm_id=?")->execute([$id]);
        flash('success', "Tactic $id deleted.");
        header('Location: red.php'); exit;
    }
}

$stmt = $pdo->prepare("SELECT t.*, p.name AS phase_name FROM tactic t LEFT JOIN phase p ON p.disarm_id = t.phase_id WHERE t.disarm_id = ?");
$stmt->execute([$id]);
$tactic = $stmt->fetch();
if (!$tactic) { http_response_code(404); die('<h2>Tactic not found: ' . h($id) . '</h2>'); }

$page_title = $tactic['disarm_id'] . ' ' . $tactic['name'];

$techniques = $pdo->prepare("SELECT disarm_id, name, summary FROM technique WHERE tactic_id = ? ORDER BY disarm_id");
$techniques->execute([$id]); $techniques = $techniques->fetchAll();

$counters = $pdo->prepare("SELECT disarm_id, name, metatechnique_id FROM counter WHERE tactic_id = ? ORDER BY disarm_id");
$counters->execute([$id]); $counters = $counters->fetchAll();

$phases = $pdo->query("SELECT disarm_id, name FROM phase ORDER BY rank")->fetchAll();
$show_edit = (gp('action') === 'edit');

include 'includes/header.php';
?>

<div class="container">

<?= render_flash() ?>

<div class="breadcrumb">
  <a href="index.php">Home</a><span class="breadcrumb-sep">/</span>
  <a href="red.php">Red Team</a><span class="breadcrumb-sep">/</span>
  <span><?= h($tactic['disarm_id']) ?></span>
</div>

<div class="detail-header">
  <div class="detail-meta">
    <span class="disarm-id"><?= h($tactic['disarm_id']) ?></span>
    <?php if ($tactic['phase_name']): ?>
    <span class="tag tag-muted"><?= h($tactic['phase_id']) ?> <?= h($tactic['phase_name']) ?></span>
    <?php endif; ?>
  </div>
  <h1><?= h($tactic['name']) ?></h1>
  <?php if ($tactic['summary']): ?>
  <p class="detail-summary"><?= h($tactic['summary']) ?></p>
  <?php endif; ?>
  <div class="detail-actions">
    <a href="tactic.php?id=<?= urlencode($id) ?>&action=edit" class="btn btn-ghost btn-sm">Edit</a>
    <form method="post" style="display:inline" onsubmit="return confirm('Delete this tactic? This may break technique/counter links.')">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="delete">
      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
  </div>
</div>

<?php if ($show_edit): ?>
<div class="form-section" style="border-top:none;margin-top:0;padding-top:0;margin-bottom:clamp(40px,5vw,60px)">
  <div class="form-section-title">Edit Tactic</div>
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
        <input type="text" name="name" value="<?= h($tactic['name']) ?>" required>
      </div>
      <div class="form-group">
        <label>Phase</label>
        <select name="phase_id">
          <option value="">— none —</option>
          <?php foreach ($phases as $ph): ?>
          <option value="<?= h($ph['disarm_id']) ?>" <?= $tactic['phase_id'] === $ph['disarm_id'] ? 'selected' : '' ?>>
            <?= h($ph['disarm_id']) ?> — <?= h($ph['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Rank</label>
        <input type="text" name="rank" value="<?= h($tactic['rank'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group" style="margin-top:20px">
      <label>Summary</label>
      <textarea name="summary" rows="6"><?= h($tactic['summary'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Save Changes</button>
      <a href="tactic.php?id=<?= urlencode($id) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Techniques -->
<div style="margin-bottom:clamp(40px,5vw,60px)">
  <div class="section-title" style="margin-bottom:0">
    <span class="tag tag-red" style="vertical-align:middle;margin-right:8px">RED</span>
    Techniques — <?= count($techniques) ?>
  </div>
  <?php if ($techniques): ?>
  <table class="data-table">
    <thead><tr><th class="col-id">ID</th><th>Technique</th><th>Summary</th></tr></thead>
    <tbody>
      <?php foreach ($techniques as $t): ?>
      <tr>
        <td><?= id_badge($t['disarm_id'], 'technique.php?id=' . urlencode($t['disarm_id'])) ?></td>
        <td><a href="technique.php?id=<?= urlencode($t['disarm_id']) ?>"><?= h($t['name']) ?></a></td>
        <td class="col-summary"><?= h(truncate($t['summary'], 130)) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: echo no_results('No techniques for this tactic yet.'); endif; ?>
</div>

<!-- Counters -->
<div>
  <div class="section-title" style="margin-bottom:0">
    <span class="tag tag-blue" style="vertical-align:middle;margin-right:8px">BLUE</span>
    Countermeasures — <?= count($counters) ?>
  </div>
  <?php if ($counters): ?>
  <table class="data-table">
    <thead><tr><th class="col-id">ID</th><th>Counter</th><th class="col-mid">Metatechnique</th></tr></thead>
    <tbody>
      <?php foreach ($counters as $c): ?>
      <tr>
        <td><?= id_badge($c['disarm_id'], 'counter.php?id=' . urlencode($c['disarm_id'])) ?></td>
        <td><a href="counter.php?id=<?= urlencode($c['disarm_id']) ?>"><?= h($c['name']) ?></a></td>
        <td class="col-muted"><?= h($c['metatechnique_id']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: echo no_results('No counters for this tactic yet.'); endif; ?>
</div>

</div>
<?php include 'includes/footer.php'; ?>
