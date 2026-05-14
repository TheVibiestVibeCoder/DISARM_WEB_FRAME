<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$id = gp('id');
if (!$id) { header('Location: techniques.php'); exit; }

// ── CRUD ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = pp('action');
    if ($action === 'edit') {
        $pdo->prepare("UPDATE technique SET name=?, tactic_id=?, summary=? WHERE disarm_id=?")
            ->execute([pp('name'), pp('tactic_id') ?: null, pp('summary'), $id]);
        flash('success', 'Technique updated.');
        header("Location: technique.php?id=" . urlencode($id)); exit;
    }
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM technique WHERE disarm_id=?")->execute([$id]);
        flash('success', "Technique $id deleted.");
        header('Location: techniques.php'); exit;
    }
}

$stmt = $pdo->prepare("SELECT tc.*, ta.name AS tactic_name FROM technique tc LEFT JOIN tactic ta ON ta.disarm_id = tc.tactic_id WHERE tc.disarm_id = ?");
$stmt->execute([$id]);
$tech = $stmt->fetch();
if (!$tech) { http_response_code(404); die('<h2>Technique not found: ' . h($id) . '</h2>'); }

$page_title = $tech['disarm_id'] . ' ' . $tech['name'];

$is_parent = !str_contains($id, '.');
$parent_id = str_contains($id, '.') ? explode('.', $id)[0] : null;

$subtechs = [];
if ($is_parent) {
    $st = $pdo->prepare("SELECT disarm_id, name, summary FROM technique WHERE disarm_id LIKE ? AND disarm_id != ? ORDER BY disarm_id");
    $st->execute(["$id.%", $id]); $subtechs = $st->fetchAll();
}
$parent = null;
if ($parent_id) {
    $pt = $pdo->prepare("SELECT disarm_id, name FROM technique WHERE disarm_id = ?");
    $pt->execute([$parent_id]); $parent = $pt->fetch();
}

$counters = $pdo->prepare("SELECT c.disarm_id, c.name, c.metatechnique_id FROM counter c INNER JOIN counter_technique ct ON ct.counter_id = c.disarm_id WHERE ct.technique_id = ? ORDER BY c.disarm_id");
$counters->execute([$id]); $counters = $counters->fetchAll();

$detections = $pdo->prepare("SELECT d.disarm_id, d.name FROM detection d INNER JOIN detection_technique dt ON dt.detection_id = d.disarm_id WHERE dt.technique_id = ? ORDER BY d.disarm_id");
$detections->execute([$id]); $detections = $detections->fetchAll();

$incidents = $pdo->prepare("SELECT i.disarm_id, i.name, i.year_started FROM incident i INNER JOIN incident_technique it ON it.incident_id = i.disarm_id WHERE it.technique_id = ? ORDER BY i.year_started DESC, i.disarm_id");
$incidents->execute([$id]); $incidents = $incidents->fetchAll();

$tactics   = $pdo->query("SELECT disarm_id, name FROM tactic ORDER BY disarm_id")->fetchAll();
$show_edit = (gp('action') === 'edit');

include 'includes/header.php';
?>

<div class="container">

<?= render_flash() ?>

<div class="breadcrumb">
  <a href="index.php">Home</a><span class="breadcrumb-sep">/</span>
  <a href="techniques.php">Techniques</a><span class="breadcrumb-sep">/</span>
  <?php if ($parent): ?>
  <a href="technique.php?id=<?= urlencode($parent['disarm_id']) ?>"><?= h($parent['disarm_id']) ?></a>
  <span class="breadcrumb-sep">/</span>
  <?php endif; ?>
  <span><?= h($tech['disarm_id']) ?></span>
</div>

<div class="detail-header">
  <div class="detail-meta">
    <span class="disarm-id"><?= h($tech['disarm_id']) ?></span>
    <span class="tag tag-red">RED</span>
    <?php if ($tech['tactic_id']): ?>
    <a href="tactic.php?id=<?= urlencode($tech['tactic_id']) ?>" style="text-decoration:none">
      <span class="tag tag-muted"><?= h($tech['tactic_id']) ?> <?= h($tech['tactic_name']) ?></span>
    </a>
    <?php endif; ?>
    <?php if ($parent): ?>
    <span class="tag tag-muted">Sub-technique of <?= h($parent['disarm_id']) ?></span>
    <?php endif; ?>
  </div>
  <h1><?= h($tech['name']) ?></h1>
  <?php if ($tech['summary']): ?>
  <p class="detail-summary"><?= h($tech['summary']) ?></p>
  <?php endif; ?>
  <div class="detail-actions">
    <a href="technique.php?id=<?= urlencode($id) ?>&action=edit" class="btn btn-ghost btn-sm">Edit</a>
    <form method="post" style="display:inline" onsubmit="return confirm('Permanently delete this technique?')">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="delete">
      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
  </div>
</div>

<?php if ($show_edit): ?>
<div class="form-section" style="border-top:none;margin-top:0;padding-top:0;margin-bottom:clamp(40px,5vw,60px)">
  <div class="form-section-title">Edit Technique</div>
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
        <input type="text" name="name" value="<?= h($tech['name']) ?>" required>
      </div>
      <div class="form-group">
        <label>Tactic</label>
        <select name="tactic_id">
          <option value="">— none —</option>
          <?php foreach ($tactics as $ta): ?>
          <option value="<?= h($ta['disarm_id']) ?>" <?= $tech['tactic_id'] === $ta['disarm_id'] ? 'selected' : '' ?>>
            <?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group" style="margin-top:20px">
      <label>Summary</label>
      <textarea name="summary" rows="6"><?= h($tech['summary'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Save Changes</button>
      <a href="technique.php?id=<?= urlencode($id) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Sub-techniques -->
<?php if ($subtechs): ?>
<div style="margin-bottom:clamp(40px,5vw,60px)">
  <div class="section-title" style="margin-bottom:0">Sub-techniques — <?= count($subtechs) ?></div>
  <table class="data-table">
    <thead><tr><th class="col-id">ID</th><th>Sub-technique</th><th>Summary</th></tr></thead>
    <tbody>
      <?php foreach ($subtechs as $st): ?>
      <tr>
        <td><?= id_badge($st['disarm_id'], 'technique.php?id=' . urlencode($st['disarm_id'])) ?></td>
        <td><a href="technique.php?id=<?= urlencode($st['disarm_id']) ?>"><?= h($st['name']) ?></a></td>
        <td class="col-summary"><?= h(truncate($st['summary'], 130)) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="grid-2">
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
    <?php else: echo no_results('No specific counters linked.'); endif; ?>
  </div>

  <!-- Incidents + Detections -->
  <div>
    <?php if ($incidents): ?>
    <div style="margin-bottom:clamp(32px,4vw,48px)">
      <div class="section-title" style="margin-bottom:0">Incidents — <?= count($incidents) ?></div>
      <table class="data-table">
        <thead><tr><th class="col-id">ID</th><th>Incident</th><th class="col-sm">Year</th></tr></thead>
        <tbody>
          <?php foreach ($incidents as $inc): ?>
          <tr>
            <td><?= id_badge($inc['disarm_id'], 'incident.php?id=' . urlencode($inc['disarm_id'])) ?></td>
            <td><a href="incident.php?id=<?= urlencode($inc['disarm_id']) ?>"><?= h(truncate($inc['name'], 60)) ?></a></td>
            <td class="col-muted"><?= h($inc['year_started']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <?php if ($detections): ?>
    <div>
      <div class="section-title" style="margin-bottom:0">Detections — <?= count($detections) ?></div>
      <table class="data-table">
        <thead><tr><th class="col-id">ID</th><th>Detection</th></tr></thead>
        <tbody>
          <?php foreach ($detections as $d): ?>
          <tr>
            <td><?= id_badge($d['disarm_id']) ?></td>
            <td class="col-muted"><?= h($d['name']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <?php if (!$incidents && !$detections): echo no_results('No incidents or detections linked yet.'); endif; ?>
  </div>
</div>

</div>
<?php include 'includes/footer.php'; ?>
