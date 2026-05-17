<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Framework Matrix';

$phases = $pdo->query(
    "SELECT disarm_id, name, summary FROM phase ORDER BY rank"
)->fetchAll();

$tactics_raw = $pdo->query(
    "SELECT t.disarm_id, t.name, t.phase_id, t.summary
     FROM tactic t
     JOIN phase p ON p.disarm_id = t.phase_id
     ORDER BY p.rank, t.rank, t.disarm_id"
)->fetchAll();

$techniques_raw = $pdo->query(
    "SELECT disarm_id, name, tactic_id, summary FROM technique ORDER BY tactic_id, disarm_id"
)->fetchAll();

$tasks_raw = [];
try {
    $tasks_raw = $pdo->query(
        "SELECT disarm_id, name, tactic_id, summary FROM task ORDER BY tactic_id, disarm_id"
    )->fetchAll();
} catch (Exception $e) {}

// Group by parent
$tactics_by_phase = [];
foreach ($tactics_raw as $ta) {
    $tactics_by_phase[$ta['phase_id']][] = $ta;
}

$techniques_by_tactic = [];
foreach ($techniques_raw as $tc) {
    $tid = $tc['tactic_id'] ?? '_none';
    $techniques_by_tactic[$tid][] = $tc;
}

$tasks_by_tactic = [];
foreach ($tasks_raw as $tk) {
    $tasks_by_tactic[$tk['tactic_id']][] = $tk;
}

// Phase colours matching layout_CORRECT.html
$phase_colors = [
    'P01' => ['bg'=>'#1a4f7a', 'border'=>'#2a6fa0', 'label'=>'p01'],
    'P02' => ['bg'=>'#7a4a10', 'border'=>'#b06820', 'label'=>'p02'],
    'P03' => ['bg'=>'#7a1a1a', 'border'=>'#a03030', 'label'=>'p03'],
    'P04' => ['bg'=>'#1a6a3a', 'border'=>'#2a8a50', 'label'=>'p04'],
];

// Build JSON payloads for JS detail panel
$js_tactics     = [];
$js_techniques  = [];
$js_tasks       = [];
$js_phases      = [];

foreach ($phases as $ph) {
    $js_phases[] = [
        'id'      => $ph['disarm_id'],
        'name'    => $ph['name'],
        'summary' => $ph['summary'] ?? '',
    ];
}
foreach ($tactics_raw as $ta) {
    $js_tactics[] = [
        'id'      => $ta['disarm_id'],
        'phase'   => $ta['phase_id'],
        'name'    => $ta['name'],
        'summary' => $ta['summary'] ?? '',
    ];
}
foreach ($techniques_raw as $tc) {
    $js_techniques[] = [
        'id'      => $tc['disarm_id'],
        'tactic'  => $tc['tactic_id'] ?? '',
        'name'    => $tc['name'],
        'sub'     => str_contains($tc['disarm_id'], '.'),
        'summary' => $tc['summary'] ?? '',
    ];
}
foreach ($tasks_raw as $tk) {
    $js_tasks[] = [
        'id'      => $tk['disarm_id'],
        'tactic'  => $tk['tactic_id'] ?? '',
        'name'    => $tk['name'],
        'summary' => $tk['summary'] ?? '',
    ];
}

include 'includes/header.php';
?>

<!-- Framework-page dark override -->
<style>
.fw-page {
    background: #0f1117;
    min-height: calc(100vh - 60px);
    color: #e0e0e0;
    padding-bottom: 60px;
}
.fw-header {
    background: #1a1d27;
    border-bottom: 2px solid #2a2d3e;
    padding: 18px clamp(16px,4vw,40px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.fw-header h1 { font-size: 1.3rem; font-weight: 700; color: #fff; letter-spacing: 1px; margin: 0; }
.fw-header p  { font-size: 0.78rem; color: #888; margin: 3px 0 0; }
.fw-header-links { display: flex; gap: 8px; }
.fw-header-links a {
    font-size: 10px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase;
    padding: 6px 14px; border: 1px solid #2a2d3e; color: #888;
    text-decoration: none; transition: color 0.15s, border-color 0.15s;
}
.fw-header-links a:hover { color: #ccc; border-color: #555; }

/* Legend */
.fw-legend {
    display: flex; gap: 12px; padding: 10px clamp(16px,4vw,40px);
    background: #1a1d27; border-bottom: 1px solid #2a2d3e; flex-wrap: wrap;
}
.fw-legend-item { display: flex; align-items: center; gap: 5px; font-size: 0.7rem; color: #888; }
.fw-legend-dot { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }

/* Matrix */
.fw-matrix-wrap { padding: 20px clamp(12px,3vw,32px); overflow-x: auto; }
.fw-matrix {
    display: grid;
    grid-template-columns: repeat(<?= count($phases) ?>, minmax(270px, 1fr));
    gap: 12px;
    min-width: <?= count($phases) * 280 ?>px;
}

/* Phase column */
.fw-phase-col { display: flex; flex-direction: column; gap: 8px; }

/* Phase header */
.fw-phase-hd {
    padding: 12px 14px;
    border-radius: 6px 6px 0 0;
    font-weight: 700; font-size: 0.82rem;
    letter-spacing: 0.8px; text-transform: uppercase;
    display: flex; justify-content: space-between; align-items: flex-start;
    cursor: pointer; user-select: none;
    color: #fff;
}
.fw-phase-hd:hover { filter: brightness(1.12); }
.fw-phase-id   { font-size: 1rem; opacity: 0.85; display: block; margin-bottom: 3px; }
.fw-phase-sub  { font-size: 0.68rem; opacity: 0.7; font-weight: 400; display: block; }
.fw-phase-count {
    font-size: 0.65rem; background: rgba(255,255,255,0.15);
    padding: 2px 7px; border-radius: 10px; white-space: nowrap; flex-shrink: 0;
}

/* Tactic card */
.fw-tactic { background: #1e2130; border-radius: 4px; overflow: hidden; border: 1px solid #2a2d3e; }
.fw-tactic + .fw-tactic { margin-top: 6px; }

.fw-tactic-hd {
    padding: 10px 12px; cursor: pointer;
    display: flex; justify-content: space-between; align-items: flex-start;
    transition: background 0.15s; user-select: none;
}
.fw-tactic-hd:hover  { background: #272b3f; }
.fw-tactic-hd.active { background: #272b3f; }

.fw-tactic-meta { flex: 1; min-width: 0; }
.fw-tactic-id   { font-size: 0.65rem; font-weight: 700; color: #888; letter-spacing: 0.5px; font-family: 'SF Mono','Fira Code',monospace; }
.fw-tactic-name { font-size: 0.8rem; font-weight: 600; color: #d0d8f0; margin-top: 2px; line-height: 1.3; }
.fw-tactic-cnt  { font-size: 0.62rem; color: #666; margin-top: 3px; }

.fw-arrow { font-size: 0.65rem; color: #555; margin-left: 8px; margin-top: 4px;
    transition: transform 0.18s; flex-shrink: 0; }
.fw-arrow.open { transform: rotate(90deg); color: #888; }

/* Expandable panel */
.fw-panel { display: none; background: #171a27; border-top: 1px solid #2a2d3e; }
.fw-panel.open { display: block; }

/* Technique items */
.fw-tech {
    padding: 5px 10px; display: flex; gap: 6px; align-items: flex-start;
    cursor: pointer; transition: background 0.1s; border-bottom: 1px solid #1e2030;
}
.fw-tech:hover { background: #2a2d3e; }
.fw-tech.fw-sub { padding-left: 22px; }
.fw-tech-id {
    font-size: 0.6rem; font-weight: 700; font-family: 'SF Mono','Fira Code',monospace;
    color: #6a9fd8; flex-shrink: 0; padding-top: 1px; min-width: 58px;
}
.fw-tech.fw-sub .fw-tech-id { color: #5580a8; }
.fw-tech-name { font-size: 0.72rem; color: #b0bcd8; line-height: 1.35; }

/* Task items */
.fw-tasks-hd {
    font-size: 0.6rem; text-transform: uppercase; letter-spacing: 1px;
    color: #555; padding: 6px 10px 3px; border-top: 1px dashed #2a2d3e;
}
.fw-task {
    padding: 4px 10px; display: flex; gap: 6px; align-items: flex-start;
    cursor: pointer; transition: background 0.1s; border-bottom: 1px solid #1a1d28;
}
.fw-task:hover { background: #2a2d3e; }
.fw-task-id   { font-size: 0.6rem; font-weight: 700; font-family: 'SF Mono','Fira Code',monospace; color: #8a7fc8; flex-shrink: 0; min-width: 58px; padding-top: 1px; }
.fw-task-name { font-size: 0.7rem; color: #9090b0; line-height: 1.3; }

/* Detail overlay / panel */
.fw-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.55); z-index: 200; cursor: pointer;
}
.fw-overlay.open { display: block; }
.fw-detail {
    position: fixed; right: 0; top: 0; height: 100%;
    width: 420px; max-width: 94vw;
    background: #1a1d2e; border-left: 2px solid #2a2d4e;
    z-index: 201; overflow-y: auto; padding: 24px 22px;
    transform: translateX(100%); transition: transform 0.22s ease;
    cursor: default;
}
.fw-detail.open { transform: translateX(0); }
.fw-detail-close {
    position: absolute; top: 14px; right: 16px; cursor: pointer;
    font-size: 1.1rem; color: #666; background: none; border: none;
}
.fw-detail-close:hover { color: #aaa; }
.fw-detail-type { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px; }
.fw-detail-id   { font-family: 'SF Mono','Fira Code',monospace; font-size: 0.95rem; font-weight: 700; margin-bottom: 5px; }
.fw-detail-name { font-size: 1.05rem; font-weight: 700; color: #fff; margin-bottom: 12px; line-height: 1.4; }
.fw-detail-sum  { font-size: 0.78rem; color: #9090a8; line-height: 1.65; }
.fw-detail-div  { border: none; border-top: 1px solid #2a2d4e; margin: 14px 0; }
.fw-detail-sec  { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 1.5px; color: #666; margin-bottom: 7px; }
.fw-detail-link {
    display: inline-block; margin-top: 14px; padding: 7px 16px;
    font-size: 0.7rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
    border: 1px solid #2a2d4e; color: #6a9fd8; text-decoration: none;
    transition: border-color 0.15s, color 0.15s;
}
.fw-detail-link:hover { border-color: #6a9fd8; color: #8ab8e8; }
.fw-bc { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; margin-bottom: 14px; }
.fw-bc-item {
    font-size: 0.68rem; padding: 2px 8px; border-radius: 10px;
    font-weight: 600;
}
.fw-bc-phase  { background: #1a3a5a; color: #6ab0e0; }
.fw-bc-tactic { background: #2a2040; color: #9080d0; }
.fw-bc-sep    { color: #444; font-size: 0.75rem; }
</style>

<div class="fw-page">

  <!-- Header -->
  <div class="fw-header">
    <div>
      <h1>DISARM Framework Matrix</h1>
      <p>Phases &rarr; Tactics &rarr; Techniques &rarr; Tasks &nbsp;&middot;&nbsp;
         Click tactics to expand &nbsp;&middot;&nbsp; Click items for details</p>
    </div>
    <div class="fw-header-links">
      <a href="techniques.php">All Techniques</a>
      <a href="tactics.php">All Tactics</a>
    </div>
  </div>

  <!-- Legend -->
  <div class="fw-legend">
    <div class="fw-legend-item"><div class="fw-legend-dot" style="background:#2a6fa0"></div> Phase</div>
    <div class="fw-legend-item"><div class="fw-legend-dot" style="background:#3a4060"></div> Tactic</div>
    <div class="fw-legend-item"><div class="fw-legend-dot" style="background:#6a9fd8"></div> Technique</div>
    <div class="fw-legend-item"><div class="fw-legend-dot" style="background:#5580a8"></div> Sub-technique</div>
    <div class="fw-legend-item"><div class="fw-legend-dot" style="background:#8a7fc8"></div> Task</div>
  </div>

  <!-- Matrix -->
  <div class="fw-matrix-wrap">
    <div class="fw-matrix">

      <?php foreach ($phases as $ph):
        $p_id    = $ph['disarm_id'];
        $p_col   = $phase_colors[$p_id] ?? ['bg'=>'#333','border'=>'#555'];
        $p_tacts = $tactics_by_phase[$p_id] ?? [];
        $p_tech_count = 0;
        foreach ($p_tacts as $ta) {
            $p_tech_count += count($techniques_by_tactic[$ta['disarm_id']] ?? []);
        }
      ?>
      <div class="fw-phase-col">

        <!-- Phase header -->
        <div class="fw-phase-hd"
             style="background:<?= $p_col['bg'] ?>;border:1px solid <?= $p_col['border'] ?>"
             onclick="showDetail('phase','<?= h($p_id) ?>')">
          <div>
            <span class="fw-phase-id"><?= h($p_id) ?></span>
            <?= h($ph['name']) ?>
            <span class="fw-phase-sub"><?= count($p_tacts) ?> tactics &middot; <?= $p_tech_count ?> techniques</span>
          </div>
        </div>

        <!-- Tactic cards -->
        <?php foreach ($p_tacts as $idx => $ta):
          $ta_id   = $ta['disarm_id'];
          $techs   = $techniques_by_tactic[$ta_id] ?? [];
          $tasks   = $tasks_by_tactic[$ta_id] ?? [];
          $card_id = 'card-' . preg_replace('/\W/', '', $ta_id);
        ?>
        <div class="fw-tactic <?= $idx === 0 ? 'first' : '' ?>">
          <div class="fw-tactic-hd" onclick="toggleCard('<?= $card_id ?>')"
               ondblclick="showDetail('tactic','<?= h($ta_id) ?>')"
               title="Double-click for full detail">
            <div class="fw-tactic-meta">
              <div class="fw-tactic-id"><?= h($ta_id) ?></div>
              <div class="fw-tactic-name"><?= h($ta['name']) ?></div>
              <div class="fw-tactic-cnt"><?= count($techs) ?> techniques &middot; <?= count($tasks) ?> tasks</div>
            </div>
            <span class="fw-arrow" id="arrow-<?= $card_id ?>">&#9654;</span>
          </div>

          <div class="fw-panel" id="<?= $card_id ?>">

            <!-- Techniques -->
            <?php foreach ($techs as $tc):
              $is_sub = str_contains($tc['disarm_id'], '.');
            ?>
            <div class="fw-tech <?= $is_sub ? 'fw-sub' : '' ?>"
                 onclick="showDetail('technique','<?= h($tc['disarm_id']) ?>')">
              <span class="fw-tech-id"><?= h($tc['disarm_id']) ?></span>
              <span class="fw-tech-name"><?= h($tc['name']) ?></span>
            </div>
            <?php endforeach; ?>

            <!-- Tasks -->
            <?php if ($tasks): ?>
            <div class="fw-tasks-hd">Tasks</div>
            <?php foreach ($tasks as $tk): ?>
            <div class="fw-task" onclick="showDetail('task','<?= h($tk['disarm_id']) ?>')">
              <span class="fw-task-id"><?= h($tk['disarm_id']) ?></span>
              <span class="fw-task-name"><?= h($tk['name']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

          </div><!-- /fw-panel -->
        </div><!-- /fw-tactic -->
        <?php endforeach; ?>

      </div><!-- /fw-phase-col -->
      <?php endforeach; ?>

    </div><!-- /fw-matrix -->
  </div><!-- /fw-matrix-wrap -->

</div><!-- /fw-page -->

<!-- Detail slide-out -->
<div class="fw-overlay" id="fw-overlay" onclick="closeDetail()"></div>
<div class="fw-detail" id="fw-detail">
  <button class="fw-detail-close" onclick="closeDetail()">&#10005;</button>
  <div id="fw-detail-body"></div>
</div>

<script>
// Data injected from PHP
const FW_PHASES     = <?= json_encode($js_phases, JSON_UNESCAPED_UNICODE) ?>;
const FW_TACTICS    = <?= json_encode($js_tactics, JSON_UNESCAPED_UNICODE) ?>;
const FW_TECHNIQUES = <?= json_encode($js_techniques, JSON_UNESCAPED_UNICODE) ?>;
const FW_TASKS      = <?= json_encode($js_tasks, JSON_UNESCAPED_UNICODE) ?>;

// Lookup maps
const phaseMap = Object.fromEntries(FW_PHASES.map(p => [p.id, p]));
const tacticMap = Object.fromEntries(FW_TACTICS.map(t => [t.id, t]));
const techMap   = Object.fromEntries(FW_TECHNIQUES.map(t => [t.id, t]));
const taskMap   = Object.fromEntries(FW_TASKS.map(t => [t.id, t]));

const techsByTactic = {};
FW_TECHNIQUES.forEach(t => { (techsByTactic[t.tactic] = techsByTactic[t.tactic]||[]).push(t); });
const tasksByTactic = {};
FW_TASKS.forEach(t => { (tasksByTactic[t.tactic] = tasksByTactic[t.tactic]||[]).push(t); });
const tacticsByPhase = {};
FW_TACTICS.forEach(t => { (tacticsByPhase[t.phase] = tacticsByPhase[t.phase]||[]).push(t); });

// Accordion
function toggleCard(id) {
  const panel = document.getElementById(id);
  const arrow = document.getElementById('arrow-' + id);
  const hd = panel.previousElementSibling;
  const open = panel.classList.toggle('open');
  if (arrow) arrow.classList.toggle('open', open);
  if (hd) hd.classList.toggle('active', open);
}

// Detail panel
const phaseColors = {
  P01: '#2a6fa0', P02: '#c07020', P03: '#b03030', P04: '#2a8a50'
};
const typeColors = {
  phase: '#2a6fa0', tactic: '#9080d0', technique: '#6a9fd8', task: '#8a7fc8'
};
const typeLinks = {
  tactic:    id => `tactic.php?id=${encodeURIComponent(id)}`,
  technique: id => `technique.php?id=${encodeURIComponent(id)}`,
};

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showDetail(type, id) {
  let item, phaseName, tacticName, phaseId;

  if (type === 'phase') {
    item = phaseMap[id];
  } else if (type === 'tactic') {
    item = tacticMap[id];
    phaseId = item?.phase;
  } else if (type === 'technique') {
    item = techMap[id];
    const tac = tacticMap[item?.tactic];
    phaseId = tac?.phase;
    tacticName = tac ? `${tac.id}: ${tac.name}` : null;
  } else if (type === 'task') {
    item = taskMap[id];
    const tac = tacticMap[item?.tactic];
    phaseId = tac?.phase;
    tacticName = tac ? `${tac.id}: ${tac.name}` : null;
  }
  if (!item) return;

  const phase = phaseId ? phaseMap[phaseId] : null;
  const color = typeColors[type] || '#888';

  // Breadcrumb
  let bc = '';
  if (phase) bc += `<span class="fw-bc-item fw-bc-phase">${esc(phase.id)}: ${esc(phase.name)}</span><span class="fw-bc-sep">›</span>`;
  if (tacticName) bc += `<span class="fw-bc-item fw-bc-tactic">${esc(tacticName)}</span><span class="fw-bc-sep">›</span>`;

  // Related section
  let related = '';
  const typeLabel = {phase:'Phase',tactic:'Tactic',technique:'Technique',task:'Task'}[type];

  if (type === 'tactic') {
    const techs = techsByTactic[id] || [];
    const tasks = tasksByTactic[id] || [];
    if (techs.length) {
      related += `<hr class="fw-detail-div"><div class="fw-detail-sec">${techs.length} Techniques</div>`;
      related += techs.map(t =>
        `<div class="fw-tech${t.sub?' fw-sub':''}" onclick="showDetail('technique','${esc(t.id)}')" style="cursor:pointer">
           <span class="fw-tech-id">${esc(t.id)}</span>
           <span class="fw-tech-name">${esc(t.name)}</span>
         </div>`
      ).join('');
    }
    if (tasks.length) {
      related += `<hr class="fw-detail-div"><div class="fw-detail-sec">${tasks.length} Tasks</div>`;
      related += tasks.map(t =>
        `<div class="fw-task" onclick="showDetail('task','${esc(t.id)}')" style="cursor:pointer">
           <span class="fw-task-id">${esc(t.id)}</span>
           <span class="fw-task-name">${esc(t.name)}</span>
         </div>`
      ).join('');
    }
  } else if (type === 'phase') {
    const tacts = tacticsByPhase[id] || [];
    if (tacts.length) {
      related += `<hr class="fw-detail-div"><div class="fw-detail-sec">${tacts.length} Tactics</div>`;
      related += tacts.map(t =>
        `<div class="fw-tech" onclick="showDetail('tactic','${esc(t.id)}')" style="cursor:pointer">
           <span class="fw-tech-id">${esc(t.id)}</span>
           <span class="fw-tech-name">${esc(t.name)}</span>
         </div>`
      ).join('');
    }
  }

  // Detail link to full PHP page
  let detailLink = '';
  if (typeLinks[type]) {
    detailLink = `<a class="fw-detail-link" href="${typeLinks[type](item.id)}">Full Detail Page &rarr;</a>`;
  }

  document.getElementById('fw-detail-body').innerHTML = `
    ${bc ? `<div class="fw-bc">${bc}</div>` : ''}
    <div class="fw-detail-type" style="color:${color}">${typeLabel}</div>
    <div class="fw-detail-id"  style="color:${color}">${esc(item.id)}</div>
    <div class="fw-detail-name">${esc(item.name)}</div>
    <div class="fw-detail-sum">${esc(item.summary || '')}</div>
    ${detailLink}
    ${related}
  `;

  document.getElementById('fw-overlay').classList.add('open');
  document.getElementById('fw-detail').classList.add('open');
}

function closeDetail() {
  document.getElementById('fw-overlay').classList.remove('open');
  document.getElementById('fw-detail').classList.remove('open');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });
</script>

<?php include 'includes/footer.php'; ?>
