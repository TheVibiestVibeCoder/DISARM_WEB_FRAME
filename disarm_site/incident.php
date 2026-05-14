<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$id = gp('id');
if (!$id) { header('Location: incidents.php'); exit; }

// ── CRUD ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = pp('action');
    if ($action === 'edit') {
        $pdo->prepare("UPDATE incident SET name=?, summary=?, year_started=?, attributions_seen=?, found_in_country=?, objecttype=? WHERE disarm_id=?")
            ->execute([pp('name'), pp('summary'), pp('year_started'), pp('attributions_seen'), pp('found_in_country'), pp('objecttype'), $id]);
        flash('success', 'Incident updated.');
        header("Location: incident.php?id=" . urlencode($id)); exit;
    }
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM incident WHERE disarm_id=?")->execute([$id]);
        flash('success', "Incident $id deleted.");
        header('Location: incidents.php'); exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM incident WHERE disarm_id = ?");
$stmt->execute([$id]);
$incident = $stmt->fetch();
if (!$incident) { http_response_code(404); die('<h2>Incident not found: ' . h($id) . '</h2>'); }

$page_title = $incident['disarm_id'] . ' ' . $incident['name'];

$techniques = $pdo->prepare("SELECT tc.disarm_id, tc.name, tc.tactic_id, it.summary AS it_summary FROM technique tc INNER JOIN incident_technique it ON it.technique_id = tc.disarm_id WHERE it.incident_id = ? ORDER BY tc.disarm_id");
$techniques->execute([$id]); $techniques = $techniques->fetchAll();

$show_edit = (gp('action') === 'edit');

include 'includes/header.php';
?>

<div class="container">

<?= render_flash() ?>

<div class="breadcrumb">
  <a href="index.php">Home</a><span class="breadcrumb-sep">/</span>
  <a href="incidents.php">Incidents</a><span class="breadcrumb-sep">/</span>
  <span><?= h($incident['disarm_id']) ?></span>
</div>

<div class="detail-header">
  <div class="detail-meta">
    <span class="disarm-id"><?= h($incident['disarm_id']) ?></span>
    <?php if ($incident['year_started']): ?>
    <span class="tag tag-muted"><?= h($incident['year_started']) ?></span>
    <?php endif; ?>
    <?php if ($incident['found_in_country']): ?>
    <span class="tag tag-muted"><?= h($incident['found_in_country']) ?></span>
    <?php endif; ?>
    <?php if ($incident['attributions_seen']): ?>
    <span class="tag tag-muted"><?= h($incident['attributions_seen']) ?></span>
    <?php endif; ?>
  </div>
  <h1><?= h($incident['name']) ?></h1>
  <?php if ($incident['summary']): ?>
  <p class="detail-summary"><?= h($incident['summary']) ?></p>
  <?php endif; ?>
  <div class="detail-actions">
    <a href="incident.php?id=<?= urlencode($id) ?>&action=edit" class="btn btn-ghost btn-sm">Edit</a>
    <form method="post" style="display:inline" onsubmit="return confirm('Permanently delete this incident?')">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="delete">
      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
  </div>
</div>

<?php if ($show_edit): ?>
<div class="form-section" style="border-top:none;margin-top:0;padding-top:0;margin-bottom:clamp(40px,5vw,60px)">
  <div class="form-section-title">Edit Incident</div>
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
        <input type="text" name="name" value="<?= h($incident['name']) ?>" required>
      </div>
      <div class="form-group">
        <label>Year Started</label>
        <input type="text" name="year_started" value="<?= h($incident['year_started'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Found in Country</label>
        <input type="text" name="found_in_country" value="<?= h($incident['found_in_country'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Attributions Seen</label>
        <input type="text" name="attributions_seen" value="<?= h($incident['attributions_seen'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Object Type</label>
        <input type="text" name="objecttype" value="<?= h($incident['objecttype'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group" style="margin-top:20px">
      <label>Summary</label>
      <textarea name="summary" rows="6"><?= h($incident['summary'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Save Changes</button>
      <a href="incident.php?id=<?= urlencode($id) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Techniques -->
<div>
  <div class="section-title" style="margin-bottom:0">
    <span class="tag tag-red" style="vertical-align:middle;margin-right:8px">RED</span>
    Techniques Observed — <?= count($techniques) ?>
  </div>
  <?php if ($techniques): ?>
  <table class="data-table">
    <thead><tr><th class="col-id">ID</th><th>Technique</th><th class="col-sm">Tactic</th><th>Notes</th></tr></thead>
    <tbody>
      <?php foreach ($techniques as $t): ?>
      <tr>
        <td><?= id_badge($t['disarm_id'], 'technique.php?id=' . urlencode($t['disarm_id'])) ?></td>
        <td><a href="technique.php?id=<?= urlencode($t['disarm_id']) ?>"><?= h($t['name']) ?></a></td>
        <td class="col-muted"><?= h($t['tactic_id']) ?></td>
        <td class="col-summary"><?= h(truncate($t['it_summary'] ?? '', 100)) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: echo no_results('No techniques linked to this incident yet.'); endif; ?>
</div>

</div>
<?php include 'includes/footer.php'; ?>
