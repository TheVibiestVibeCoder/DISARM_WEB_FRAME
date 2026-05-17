<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Red Team';

// ── CRUD ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = pp('action');
    if ($act === 'add-tactic') {
        $did = strtoupper(trim(pp('disarm_id'))); $name = pp('name');
        if ($did && $name) {
            $pdo->prepare("INSERT INTO tactic (disarm_id,name,phase_id,rank,summary) VALUES (?,?,?,?,?)")
                ->execute([$did, $name, pp('phase_id') ?: null, pp('rank') ?: null, pp('summary')]);
            flash('success', "Tactic $did added.");
        } else { flash('error', 'ID and Name are required.'); }
        header('Location: red.php'); exit;
    }
    if ($act === 'add-technique') {
        $did = strtoupper(trim(pp('disarm_id'))); $name = pp('name');
        if ($did && $name) {
            $pdo->prepare("INSERT INTO technique (disarm_id,name,tactic_id,summary) VALUES (?,?,?,?)")
                ->execute([$did, $name, pp('tactic_id') ?: null, pp('summary')]);
            flash('success', "Technique $did added.");
            header('Location: technique.php?id=' . urlencode($did)); exit;
        }
        flash('error', 'ID and Name are required.');
        header('Location: red.php?add=technique'); exit;
    }
}

// ── Data ───────────────────────────────────────────────────────────────────────
$phases = $pdo->query("SELECT disarm_id, name FROM phase ORDER BY rank")->fetchAll();

$tactics_raw = $pdo->query(
    "SELECT t.disarm_id, t.name, t.phase_id,
            (SELECT COUNT(*) FROM technique tc WHERE tc.tactic_id = t.disarm_id) AS tech_count
     FROM tactic t
     JOIN phase p ON p.disarm_id = t.phase_id
     ORDER BY p.rank, t.rank, t.disarm_id"
)->fetchAll();

$techniques_raw = $pdo->query(
    "SELECT disarm_id, name, tactic_id FROM technique ORDER BY tactic_id, disarm_id"
)->fetchAll();

$techs_by_tactic = [];
foreach ($techniques_raw as $tc) {
    $techs_by_tactic[$tc['tactic_id']][] = $tc;
}
$tactics_by_phase = [];
foreach ($tactics_raw as $ta) {
    $tactics_by_phase[$ta['phase_id']][] = $ta;
}

$filter_phase = gp('phase');
$add          = gp('add'); // 'tactic' | 'technique'

$phase_colors = [
    'P01' => '#1a3a1a', 'P02' => '#7A1515',
    'P03' => '#0F2D4A', 'P04' => '#4a3000',
];

include 'includes/header.php';
?>

<style>
/* ── Red Team accordion list ── */
.rt-phase { margin-bottom: clamp(28px,4vw,48px); }

.rt-phase-hd {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 14px; margin-bottom: 1px;
    font-size: 9px; font-weight: 700;
    letter-spacing: 0.2em; text-transform: uppercase; color: var(--white);
}
.rt-phase-id {
    font-family: 'SF Mono','Fira Code','Courier New',monospace;
    font-size: 8px; padding: 2px 5px;
    background: rgba(245,241,235,0.15); letter-spacing: 0.05em;
}

.rt-tactic { border-top: 1px solid var(--border); }
.rt-tactic:last-child { border-bottom: 1px solid var(--border); }

.rt-tactic-row {
    display: grid;
    grid-template-columns: 110px 1fr auto 20px;
    gap: 16px; padding: 11px 0; align-items: center;
    cursor: pointer; user-select: none;
    transition: background 0.15s;
}
.rt-tactic-row:hover,
.rt-tactic-row.open { background: rgba(13,13,13,0.03); }

.rt-ta-name {
    font-size: 14px; font-weight: 400; color: var(--text);
}
.rt-ta-cnt {
    font-size: 10px; font-weight: 600; letter-spacing: 0.1em;
    text-transform: uppercase; color: var(--muted); white-space: nowrap;
}
.rt-arrow {
    font-size: 9px; color: var(--muted);
    transition: transform 0.18s; text-align: right;
}
.rt-arrow.open { transform: rotate(90deg); }

.rt-panel { display: none; }
.rt-panel.open { display: block; }

.rt-tech-row {
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: 16px; padding: 7px 0 7px 14px;
    border-top: 1px solid var(--border);
    align-items: baseline;
    background: rgba(122,21,21,0.025);
    transition: background 0.12s;
}
.rt-tech-row:hover { background: rgba(122,21,21,0.08); }
.rt-tech-row.rt-sub {
    padding-left: 30px;
    background: rgba(122,21,21,0.012);
    border-left: 2px solid rgba(122,21,21,0.15);
}
.rt-tech-row.rt-sub:hover { background: rgba(122,21,21,0.05); }
.rt-tech-row a {
    font-size: 13px; font-weight: 400;
    color: var(--text); text-decoration: none; line-height: 1.4;
}
.rt-tech-row a:hover { color: var(--red-fw); }

.rt-no-tech {
    padding: 10px 14px; border-top: 1px solid var(--border);
    font-size: 12px; font-weight: 300; color: var(--muted);
}
</style>

<div class="container">

<?= render_flash() ?>

<?php if ($add === 'tactic'): ?>
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New Tactic</div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add-tactic">
    <div class="form-grid">
      <div class="form-group"><label>DISARM ID *</label><input type="text" name="disarm_id" placeholder="e.g. TA11" required></div>
      <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Phase</label>
        <select name="phase_id"><option value="">— none —</option>
          <?php foreach ($phases as $ph): ?>
          <option value="<?= h($ph['disarm_id']) ?>"><?= h($ph['disarm_id']) ?> — <?= h($ph['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Rank</label><input type="text" name="rank" placeholder="e.g. 11"></div>
    </div>
    <div class="form-group" style="margin-top:20px"><label>Summary</label><textarea name="summary" rows="4"></textarea></div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Tactic</button>
      <a href="red.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<?php if ($add === 'technique'): ?>
<div class="form-section" style="margin-top:clamp(56px,8vw,100px);border-top:none">
  <div class="form-section-title">New Technique</div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add-technique">
    <div class="form-grid">
      <div class="form-group"><label>DISARM ID *</label><input type="text" name="disarm_id" placeholder="e.g. T2050 or T2050.001" required></div>
      <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Tactic</label>
        <select name="tactic_id"><option value="">— none —</option>
          <?php foreach ($tactics_raw as $ta): ?>
          <option value="<?= h($ta['disarm_id']) ?>"><?= h($ta['disarm_id']) ?> — <?= h($ta['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group" style="margin-top:20px"><label>Summary</label><textarea name="summary" rows="4"></textarea></div>
    <div class="form-actions">
      <button type="submit" class="btn btn-dark">Add Technique</button>
      <a href="red.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Page header -->
<div class="page-header">
  <div class="label"><span class="tag tag-red" style="margin-right:4px">RED</span>Framework</div>
  <div class="page-header-row">
    <div>
      <h1>Red Team</h1>
      <div class="page-count">Attacker tactics &amp; techniques</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="red.php?add=tactic"    class="btn btn-ghost btn-sm">+ Tactic</a>
      <a href="red.php?add=technique" class="btn btn-dark  btn-sm">+ Technique</a>
    </div>
  </div>
</div>

<!-- Filter / search bar -->
<div class="filter-bar" style="margin-bottom:clamp(28px,4vw,48px)">
  <a href="red.php" class="btn btn-sm <?= !$filter_phase ? 'btn-dark' : 'btn-ghost' ?>">All</a>
  <?php foreach ($phases as $ph): ?>
  <a href="red.php?phase=<?= urlencode($ph['disarm_id']) ?>"
     class="btn btn-sm <?= $filter_phase === $ph['disarm_id'] ? 'btn-dark' : 'btn-ghost' ?>">
    <?= h($ph['disarm_id']) ?> <?= h($ph['name']) ?>
  </a>
  <?php endforeach; ?>
  <input type="text" id="rt-search" placeholder="Search tactics &amp; techniques…"
         style="margin-left:auto" oninput="rtSearch(this.value)">
</div>

<!-- Accordion list -->
<?php
$phases_to_show = $filter_phase
    ? array_filter($phases, fn($p) => $p['disarm_id'] === $filter_phase)
    : $phases;
?>
<?php foreach ($phases_to_show as $ph):
  $p_id    = $ph['disarm_id'];
  $p_color = $phase_colors[$p_id] ?? '#333';
  $p_tacts = $tactics_by_phase[$p_id] ?? [];
  if (!$p_tacts) continue;
?>
<div class="rt-phase" data-phase="<?= h($p_id) ?>">

  <div class="rt-phase-hd" style="background:<?= $p_color ?>">
    <span class="rt-phase-id"><?= h($p_id) ?></span>
    <?= h($ph['name']) ?>
  </div>

  <?php foreach ($p_tacts as $ta):
    $ta_id  = $ta['disarm_id'];
    $techs  = $techs_by_tactic[$ta_id] ?? [];
    $acc_id = 'rt-' . preg_replace('/\W/', '', $ta_id);
  ?>
  <div class="rt-tactic" data-name="<?= h(strtolower($ta['name'])) ?>">

    <div class="rt-tactic-row" id="row-<?= $acc_id ?>" onclick="rtToggle('<?= $acc_id ?>')">
      <?= id_badge($ta_id, 'tactic.php?id=' . urlencode($ta_id)) ?>
      <span class="rt-ta-name"><?= h($ta['name']) ?></span>
      <span class="rt-ta-cnt"><?= $ta['tech_count'] ?> techniques</span>
      <span class="rt-arrow" id="arr-<?= $acc_id ?>">&#9654;</span>
    </div>

    <div class="rt-panel" id="<?= $acc_id ?>">
      <?php if ($techs): ?>
        <?php foreach ($techs as $tc):
          $is_sub = str_contains($tc['disarm_id'], '.');
        ?>
        <div class="rt-tech-row <?= $is_sub ? 'rt-sub' : '' ?>"
             data-name="<?= h(strtolower($tc['name'])) ?>">
          <?= id_badge($tc['disarm_id'], 'technique.php?id=' . urlencode($tc['disarm_id'])) ?>
          <a href="technique.php?id=<?= urlencode($tc['disarm_id']) ?>"><?= h($tc['name']) ?></a>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="rt-no-tech">No techniques for this tactic yet.</div>
      <?php endif; ?>
    </div>

  </div>
  <?php endforeach; ?>

</div>
<?php endforeach; ?>

</div><!-- /container -->

<script>
function rtToggle(id) {
    const panel = document.getElementById(id);
    const arrow = document.getElementById('arr-' + id);
    const row   = document.getElementById('row-' + id);
    const open  = panel.classList.toggle('open');
    if (arrow) arrow.classList.toggle('open', open);
    if (row)   row.classList.toggle('open', open);
}

function rtSearch(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.rt-phase').forEach(function(ph) {
        let phVisible = false;
        ph.querySelectorAll('.rt-tactic').forEach(function(ta) {
            const taMatch = !q || ta.dataset.name.includes(q);
            let anyTech = false;
            ta.querySelectorAll('.rt-tech-row').forEach(function(tr) {
                const show = !q || taMatch || tr.dataset.name.includes(q);
                tr.style.display = show ? '' : 'none';
                if (q && tr.dataset.name.includes(q)) anyTech = true;
            });
            const visible = !q || taMatch || anyTech;
            ta.style.display = visible ? '' : 'none';
            if (visible) phVisible = true;
            if (q && (anyTech || taMatch)) {
                ta.querySelectorAll('.rt-panel').forEach(p => p.classList.add('open'));
                ta.querySelectorAll('.rt-arrow').forEach(a => a.classList.add('open'));
                ta.querySelectorAll('.rt-tactic-row').forEach(r => r.classList.add('open'));
            }
        });
        ph.style.display = (!q || phVisible) ? '' : 'none';
    });
}
</script>

<?php include 'includes/footer.php'; ?>
