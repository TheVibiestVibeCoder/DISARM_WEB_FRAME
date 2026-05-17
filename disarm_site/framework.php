<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Framework Matrix';

// ── Red team data ──────────────────────────────────────────────────────────────
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

// ── Blue team data ─────────────────────────────────────────────────────────────
$counters_raw = $pdo->query(
    "SELECT c.disarm_id, c.name, c.tactic_id, c.metatechnique_id, c.summary,
            mt.name AS meta_name
     FROM counter c
     LEFT JOIN metatechnique mt ON mt.disarm_id = c.metatechnique_id
     WHERE c.tactic_id IS NOT NULL
     ORDER BY c.tactic_id, c.metatechnique_id, c.disarm_id"
)->fetchAll();

$detections_raw = [];
try {
    $detections_raw = $pdo->query(
        "SELECT disarm_id, name, tactic_id, summary
         FROM detection WHERE tactic_id IS NOT NULL ORDER BY tactic_id, disarm_id"
    )->fetchAll();
} catch (Exception $e) {}

// ── Group by parent ────────────────────────────────────────────────────────────
$tactics_by_phase = [];
foreach ($tactics_raw as $ta) {
    $tactics_by_phase[$ta['phase_id']][] = $ta;
}

$techniques_by_tactic = [];
foreach ($techniques_raw as $tc) {
    $techniques_by_tactic[$tc['tactic_id'] ?? '_none'][] = $tc;
}

$tasks_by_tactic = [];
foreach ($tasks_raw as $tk) {
    $tasks_by_tactic[$tk['tactic_id']][] = $tk;
}

$counters_by_tactic = [];
foreach ($counters_raw as $c) {
    $counters_by_tactic[$c['tactic_id']][] = $c;
}

$detections_by_tactic = [];
foreach ($detections_raw as $d) {
    $detections_by_tactic[$d['tactic_id']][] = $d;
}

// ── Phase colours ──────────────────────────────────────────────────────────────
$phase_colors = [
    'P01' => ['bg' => '#1a3a1a', 'accent' => '#1a3a1a'],
    'P02' => ['bg' => '#7A1515', 'accent' => '#7A1515'],
    'P03' => ['bg' => '#0F2D4A', 'accent' => '#0F2D4A'],
    'P04' => ['bg' => '#4a3000', 'accent' => '#4a3000'],
];

// ── JS payloads ────────────────────────────────────────────────────────────────
$js_phases = $js_tactics = $js_techniques = $js_tasks = $js_counters = $js_detections = [];

foreach ($phases as $ph) {
    $js_phases[] = ['id' => $ph['disarm_id'], 'name' => $ph['name'], 'summary' => $ph['summary'] ?? ''];
}
foreach ($tactics_raw as $ta) {
    $js_tactics[] = ['id' => $ta['disarm_id'], 'phase' => $ta['phase_id'], 'name' => $ta['name'], 'summary' => $ta['summary'] ?? ''];
}
foreach ($techniques_raw as $tc) {
    $js_techniques[] = ['id' => $tc['disarm_id'], 'tactic' => $tc['tactic_id'] ?? '', 'name' => $tc['name'], 'sub' => str_contains($tc['disarm_id'], '.'), 'summary' => $tc['summary'] ?? ''];
}
foreach ($tasks_raw as $tk) {
    $js_tasks[] = ['id' => $tk['disarm_id'], 'tactic' => $tk['tactic_id'] ?? '', 'name' => $tk['name'], 'summary' => $tk['summary'] ?? ''];
}
foreach ($counters_raw as $c) {
    $js_counters[] = ['id' => $c['disarm_id'], 'tactic' => $c['tactic_id'] ?? '', 'meta' => $c['metatechnique_id'] ?? '', 'metaName' => $c['meta_name'] ?? '', 'name' => $c['name'], 'summary' => $c['summary'] ?? ''];
}
foreach ($detections_raw as $d) {
    $js_detections[] = ['id' => $d['disarm_id'], 'tactic' => $d['tactic_id'] ?? '', 'name' => $d['name'], 'summary' => $d['summary'] ?? ''];
}

include 'includes/header.php';
?>

<style>
/* ── Mode switching ── */
#fw-wrap[data-mode="red"]  .fw-blue { display: none; }
#fw-wrap[data-mode="blue"] .fw-red  { display: none; }

/* In blue mode: tactic left-border turns blue */
#fw-wrap[data-mode="blue"] .fw-tactic-hd { border-left-color: var(--blue-fw) !important; }

/* ── Toggle control ── */
.fw-toggle { display: flex; border: 1px solid var(--border); }
.fw-toggle .btn { border: none; }
.fw-toggle .btn.fw-active { background: var(--dark); color: var(--bg); }

/* ── Matrix scroll shell ── */
.fw-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 8px;
}

/* ── Phase-column grid ── */
.fw-matrix {
    display: grid;
    grid-template-columns: repeat(<?= count($phases) ?>, minmax(260px, 1fr));
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
    min-width: <?= count($phases) * 262 ?>px;
}

/* ── Phase column ── */
.fw-col { background: var(--bg); display: flex; flex-direction: column; }

/* ── Phase header strip ── */
.fw-phase-hd {
    padding: 11px 13px;
    color: var(--white);
    cursor: pointer;
    user-select: none;
    transition: filter 0.15s;
}
.fw-phase-hd:hover { filter: brightness(1.1); }
.fw-ph-id {
    font-family: 'SF Mono','Fira Code','Courier New',monospace;
    font-size: 8px; letter-spacing: 0.05em; font-weight: 500;
    background: rgba(245,241,235,0.15);
    padding: 1px 5px; display: inline-block; margin-bottom: 5px;
}
.fw-ph-name {
    font-size: 9px; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase; display: block;
}
.fw-ph-sub {
    font-size: 9px; opacity: 0.55; font-weight: 400;
    letter-spacing: 0.04em; margin-top: 3px; display: block;
}

/* ── Tactic card ── */
.fw-tactic { border-bottom: 1px solid var(--border); }

.fw-tactic-hd {
    padding: 9px 12px;
    cursor: pointer;
    display: flex; justify-content: space-between; align-items: flex-start;
    transition: background 0.15s, border-left-color 0.25s;
    user-select: none;
    border-left: 2px solid transparent;
}
.fw-tactic-hd:hover,
.fw-tactic-hd.active { background: rgba(13,13,13,0.04); }

.fw-ta-id {
    font-family: 'SF Mono','Fira Code','Courier New',monospace;
    font-size: 9px; font-weight: 500; color: var(--muted);
    letter-spacing: 0.04em; display: block; margin-bottom: 3px;
}
.fw-ta-name {
    font-size: 11px; font-weight: 500;
    color: var(--text); line-height: 1.3; display: block;
}
.fw-ta-cnt {
    font-size: 9px; color: var(--muted); margin-top: 3px; display: block;
}

.fw-arrow {
    font-size: 9px; color: var(--muted);
    margin-left: 6px; margin-top: 3px;
    transition: transform 0.18s; flex-shrink: 0;
}
.fw-arrow.open { transform: rotate(90deg); }

/* ── Expandable panel ── */
.fw-panel { display: none; border-top: 1px solid var(--border); }
.fw-panel.open { display: block; }

/* ── RED: Technique rows ── */
.fw-tech {
    display: flex; gap: 6px; align-items: flex-start;
    padding: 5px 10px;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    background: rgba(122,21,21,0.03);
    transition: background 0.12s;
}
.fw-tech:hover { background: rgba(122,21,21,0.09); }
.fw-tech.fw-sub {
    padding-left: 20px;
    background: rgba(122,21,21,0.012);
    border-left: 2px solid rgba(122,21,21,0.15);
}
.fw-tech.fw-sub:hover { background: rgba(122,21,21,0.06); }
.fw-tech-id {
    font-family: 'SF Mono','Fira Code','Courier New',monospace;
    font-size: 8px; font-weight: 500; color: var(--muted);
    flex-shrink: 0; min-width: 50px; letter-spacing: 0.04em; padding-top: 1px;
}
.fw-tech-name {
    font-size: 10.5px; font-weight: 400; color: var(--text); line-height: 1.3;
}
.fw-tech:hover .fw-tech-id,
.fw-tech:hover .fw-tech-name { color: var(--red-fw); }

/* ── RED: Task rows ── */
.fw-tasks-hd {
    font-size: 8px; text-transform: uppercase; letter-spacing: 0.15em;
    font-weight: 600; color: var(--muted);
    padding: 5px 10px 3px;
    border-top: 1px dashed var(--border);
}
.fw-task {
    display: flex; gap: 6px; align-items: flex-start;
    padding: 4px 10px;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    background: rgba(15,45,74,0.025);
    transition: background 0.12s;
}
.fw-task:hover { background: rgba(15,45,74,0.07); }
.fw-task-id {
    font-family: 'SF Mono','Fira Code','Courier New',monospace;
    font-size: 8px; font-weight: 500; color: var(--muted);
    flex-shrink: 0; min-width: 50px; letter-spacing: 0.04em; padding-top: 1px;
}
.fw-task-name {
    font-size: 10px; font-weight: 400; color: var(--text); line-height: 1.3;
}
.fw-task:hover .fw-task-id,
.fw-task:hover .fw-task-name { color: var(--blue-fw); }

/* ── BLUE: Metatechnique sub-header ── */
.fw-meta-hd {
    font-size: 8px; text-transform: uppercase; letter-spacing: 0.15em;
    font-weight: 600; color: var(--blue-fw);
    padding: 5px 10px 3px;
    border-top: 1px dashed var(--border);
    background: rgba(15,45,74,0.04);
}

/* ── BLUE: Counter rows ── */
.fw-counter {
    display: flex; gap: 6px; align-items: flex-start;
    padding: 5px 10px;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    background: rgba(15,45,74,0.035);
    transition: background 0.12s;
}
.fw-counter:hover { background: rgba(15,45,74,0.1); }
.fw-counter-id {
    font-family: 'SF Mono','Fira Code','Courier New',monospace;
    font-size: 8px; font-weight: 500; color: var(--muted);
    flex-shrink: 0; min-width: 56px; letter-spacing: 0.04em; padding-top: 1px;
}
.fw-counter-name {
    font-size: 10.5px; font-weight: 400; color: var(--text); line-height: 1.3;
}
.fw-counter:hover .fw-counter-id,
.fw-counter:hover .fw-counter-name { color: var(--blue-fw); }

/* ── BLUE: Detection rows ── */
.fw-dets-hd {
    font-size: 8px; text-transform: uppercase; letter-spacing: 0.15em;
    font-weight: 600; color: #2A6B2A;
    padding: 5px 10px 3px;
    border-top: 1px dashed var(--border);
    background: rgba(42,107,42,0.04);
}
.fw-det {
    display: flex; gap: 6px; align-items: flex-start;
    padding: 4px 10px;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    background: rgba(42,107,42,0.025);
    transition: background 0.12s;
}
.fw-det:hover { background: rgba(42,107,42,0.08); }
.fw-det-id {
    font-family: 'SF Mono','Fira Code','Courier New',monospace;
    font-size: 8px; font-weight: 500; color: var(--muted);
    flex-shrink: 0; min-width: 56px; letter-spacing: 0.04em; padding-top: 1px;
}
.fw-det-name {
    font-size: 10px; font-weight: 400; color: var(--text); line-height: 1.3;
}
.fw-det:hover .fw-det-id,
.fw-det:hover .fw-det-name { color: #2A6B2A; }

/* ── Detail slide-out ── */
.fw-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(13,13,13,0.4); z-index: 200; cursor: pointer;
}
.fw-overlay.open { display: block; }

.fw-detail {
    position: fixed; right: 0; top: 0; height: 100%;
    width: 420px; max-width: 94vw;
    background: var(--bg);
    border-left: 1px solid var(--border);
    z-index: 201; overflow-y: auto;
    padding: 28px 24px;
    transform: translateX(100%);
    transition: transform 0.22s ease;
    cursor: default;
}
.fw-detail.open { transform: translateX(0); }

.fw-detail-close {
    position: absolute; top: 16px; right: 18px;
    cursor: pointer; font-size: 1rem;
    color: var(--muted); background: none; border: none;
    font-family: inherit; line-height: 1; transition: color 0.15s;
}
.fw-detail-close:hover { color: var(--text); }

.fw-detail-type {
    font-size: 9px; text-transform: uppercase;
    letter-spacing: 0.2em; font-weight: 600;
    color: var(--muted); margin-bottom: 4px;
}
.fw-detail-id {
    font-family: 'SF Mono','Fira Code','Courier New',monospace;
    font-size: 11px; font-weight: 500;
    background: rgba(13,13,13,0.07);
    padding: 3px 7px; display: inline-block;
    color: var(--text); margin-bottom: 8px;
}
.fw-detail-name {
    font-size: clamp(16px,2vw,22px); font-weight: 300;
    letter-spacing: -0.015em; line-height: 1.3;
    color: var(--text); margin-bottom: 14px;
}
.fw-detail-sum {
    font-size: 13px; font-weight: 300;
    color: var(--muted); line-height: 1.7;
}
.fw-detail-div { border: none; border-top: 1px solid var(--border); margin: 16px 0; }
.fw-detail-sec {
    font-size: 9px; text-transform: uppercase;
    letter-spacing: 0.18em; font-weight: 600;
    color: var(--muted); margin-bottom: 8px;
}
.fw-detail-link {
    display: inline-block; margin-top: 16px;
    font-size: 10px; font-weight: 500;
    letter-spacing: 0.1em; text-transform: uppercase;
    border: 1px solid var(--border);
    color: var(--text); padding: 7px 16px;
    text-decoration: none; transition: border-color 0.15s;
}
.fw-detail-link:hover { border-color: var(--text); }

.fw-bc { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; margin-bottom: 16px; }
.fw-bc-item {
    font-size: 9px; font-weight: 600;
    letter-spacing: 0.12em; text-transform: uppercase;
    padding: 2px 7px; background: rgba(13,13,13,0.07); color: var(--muted);
}
.fw-bc-sep { color: var(--muted); font-size: 10px; }
</style>

<div id="fw-wrap" data-mode="red">

<div class="container">

  <!-- Page header -->
  <div class="page-header">
    <div class="label">Framework</div>
    <div class="page-header-row">
      <div>
        <h1>Framework Matrix</h1>
        <div class="page-count">Phases &rarr; Tactics &rarr;
          <span class="fw-red">Techniques &rarr; Tasks</span>
          <span class="fw-blue">Counters &rarr; Detections</span>
        </div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <!-- RED / BLUE toggle -->
        <div class="fw-toggle">
          <button id="btn-red"  class="btn btn-sm fw-active" onclick="setMode('red')">Red Team</button>
          <button id="btn-blue" class="btn btn-sm"           onclick="setMode('blue')">Blue Team</button>
        </div>
        <span class="fw-red">
          <a href="red.php" class="btn btn-ghost btn-sm">Red Team</a>
        </span>
        <span class="fw-blue">
          <a href="blue.php" class="btn btn-ghost btn-sm">Blue Team</a>
        </span>
      </div>
    </div>
  </div>

  <!-- Legend -->
  <div class="matrix-legend">
    <?php foreach ($phases as $ph):
      $pc = $phase_colors[$ph['disarm_id']] ?? ['bg' => '#555'];
    ?>
    <div class="legend-item">
      <span class="legend-dot" style="background:<?= $pc['bg'] ?>"></span>
      <span class="legend-label"><?= h($ph['disarm_id']) ?> <?= h($ph['name']) ?></span>
    </div>
    <?php endforeach; ?>

    <div class="legend-sep"></div>

    <!-- Red legend -->
    <div class="legend-item fw-red">
      <span class="legend-swatch legend-parent"></span>
      <span class="legend-label">Technique</span>
    </div>
    <div class="legend-item fw-red">
      <span class="legend-swatch legend-sub"></span>
      <span class="legend-label">Sub-technique</span>
    </div>
    <div class="legend-item fw-red">
      <span class="legend-dot" style="background:rgba(15,45,74,0.2)"></span>
      <span class="legend-label">Task</span>
    </div>

    <!-- Blue legend -->
    <div class="legend-item fw-blue">
      <span class="legend-dot" style="background:rgba(15,45,74,0.35)"></span>
      <span class="legend-label">Counter</span>
    </div>
    <div class="legend-item fw-blue">
      <span class="legend-dot" style="background:rgba(42,107,42,0.35)"></span>
      <span class="legend-label">Detection</span>
    </div>

    <div class="legend-sep"></div>
    <div class="legend-item" style="font-size:10px;color:var(--muted);font-weight:400;letter-spacing:0">
      Click tactics to expand &nbsp;&middot;&nbsp; Click items for details
    </div>
  </div>

</div><!-- /container -->

<!-- Full-width matrix -->
<div class="fw-scroll" style="padding:0 clamp(20px,5vw,80px) clamp(40px,5vw,64px)">
  <div class="fw-matrix">

    <?php foreach ($phases as $ph):
      $p_id    = $ph['disarm_id'];
      $p_col   = $phase_colors[$p_id] ?? ['bg' => '#333', 'accent' => '#333'];
      $p_tacts = $tactics_by_phase[$p_id] ?? [];
      $p_tech_count = 0;
      $p_ctr_count  = 0;
      foreach ($p_tacts as $ta) {
          $p_tech_count += count($techniques_by_tactic[$ta['disarm_id']] ?? []);
          $p_ctr_count  += count($counters_by_tactic[$ta['disarm_id']]   ?? []);
      }
    ?>
    <div class="fw-col">

      <!-- Phase strip -->
      <div class="fw-phase-hd"
           style="background:<?= $p_col['bg'] ?>"
           onclick="showDetail('phase','<?= h($p_id) ?>')">
        <span class="fw-ph-id"><?= h($p_id) ?></span>
        <span class="fw-ph-name"><?= h($ph['name']) ?></span>
        <span class="fw-ph-sub fw-red"><?= count($p_tacts) ?> tactics &middot; <?= $p_tech_count ?> techniques</span>
        <span class="fw-ph-sub fw-blue"><?= count($p_tacts) ?> tactics &middot; <?= $p_ctr_count ?> counters</span>
      </div>

      <!-- Tactic cards -->
      <?php foreach ($p_tacts as $ta):
        $ta_id   = $ta['disarm_id'];
        $techs   = $techniques_by_tactic[$ta_id] ?? [];
        $tasks   = $tasks_by_tactic[$ta_id]       ?? [];
        $ctrs    = $counters_by_tactic[$ta_id]     ?? [];
        $dets    = $detections_by_tactic[$ta_id]   ?? [];
        $card_id = 'card-' . preg_replace('/\W/', '', $ta_id);

        // Group counters by metatechnique for display
        $ctrs_by_meta = [];
        foreach ($ctrs as $c) {
            $ctrs_by_meta[$c['metatechnique_id'] ?? '_none'][] = $c;
        }
      ?>
      <div class="fw-tactic">
        <div class="fw-tactic-hd"
             style="border-left-color:<?= $p_col['accent'] ?>"
             onclick="toggleCard('<?= $card_id ?>')"
             ondblclick="showDetail('tactic','<?= h($ta_id) ?>')"
             title="Double-click for full detail">
          <div>
            <span class="fw-ta-id"><?= h($ta_id) ?></span>
            <span class="fw-ta-name"><?= h($ta['name']) ?></span>
            <span class="fw-ta-cnt">
              <span class="fw-red"><?= count($techs) ?> techniques &middot; <?= count($tasks) ?> tasks</span>
              <span class="fw-blue"><?= count($ctrs) ?> counters<?= count($dets) ? ' &middot; ' . count($dets) . ' detections' : '' ?></span>
            </span>
          </div>
          <span class="fw-arrow" id="arrow-<?= $card_id ?>">&#9654;</span>
        </div>

        <div class="fw-panel" id="<?= $card_id ?>">

          <!-- ── RED content ── -->
          <div class="fw-red">
            <?php foreach ($techs as $tc):
              $is_sub = str_contains($tc['disarm_id'], '.');
            ?>
            <div class="fw-tech <?= $is_sub ? 'fw-sub' : '' ?>"
                 onclick="showDetail('technique','<?= h($tc['disarm_id']) ?>')">
              <span class="fw-tech-id"><?= h($tc['disarm_id']) ?></span>
              <span class="fw-tech-name"><?= h($tc['name']) ?></span>
            </div>
            <?php endforeach; ?>

            <?php if ($tasks): ?>
            <div class="fw-tasks-hd">Tasks</div>
            <?php foreach ($tasks as $tk): ?>
            <div class="fw-task" onclick="showDetail('task','<?= h($tk['disarm_id']) ?>')">
              <span class="fw-task-id"><?= h($tk['disarm_id']) ?></span>
              <span class="fw-task-name"><?= h($tk['name']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!$techs && !$tasks): ?>
            <div style="padding:10px 12px;font-size:11px;color:var(--border)">No techniques</div>
            <?php endif; ?>
          </div><!-- /fw-red -->

          <!-- ── BLUE content ── -->
          <div class="fw-blue">
            <?php if ($ctrs_by_meta):
              $first_meta = true;
              foreach ($ctrs_by_meta as $meta_key => $meta_ctrs):
                $meta_label = ($meta_key !== '_none') ? $meta_ctrs[0]['meta_name'] : null;
            ?>
            <?php if ($meta_label): ?>
            <div class="fw-meta-hd"><?= h($meta_label) ?></div>
            <?php endif; ?>
            <?php foreach ($meta_ctrs as $c): ?>
            <div class="fw-counter" onclick="showDetail('counter','<?= h($c['disarm_id']) ?>')">
              <span class="fw-counter-id"><?= h($c['disarm_id']) ?></span>
              <span class="fw-counter-name"><?= h($c['name']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($dets): ?>
            <div class="fw-dets-hd">Detections</div>
            <?php foreach ($dets as $d): ?>
            <div class="fw-det" onclick="showDetail('detection','<?= h($d['disarm_id']) ?>')">
              <span class="fw-det-id"><?= h($d['disarm_id']) ?></span>
              <span class="fw-det-name"><?= h($d['name']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!$ctrs && !$dets): ?>
            <div style="padding:10px 12px;font-size:11px;color:var(--border)">No counters</div>
            <?php endif; ?>
          </div><!-- /fw-blue -->

        </div><!-- /fw-panel -->
      </div><!-- /fw-tactic -->
      <?php endforeach; ?>

    </div><!-- /fw-col -->
    <?php endforeach; ?>

  </div><!-- /fw-matrix -->
</div><!-- /fw-scroll -->

</div><!-- /fw-wrap -->

<!-- Detail slide-out -->
<div class="fw-overlay" id="fw-overlay" onclick="closeDetail()"></div>
<div class="fw-detail"  id="fw-detail">
  <button class="fw-detail-close" onclick="closeDetail()">&#10005;</button>
  <div id="fw-detail-body"></div>
</div>

<script>
const FW_PHASES     = <?= json_encode($js_phases,     JSON_UNESCAPED_UNICODE) ?>;
const FW_TACTICS    = <?= json_encode($js_tactics,    JSON_UNESCAPED_UNICODE) ?>;
const FW_TECHNIQUES = <?= json_encode($js_techniques, JSON_UNESCAPED_UNICODE) ?>;
const FW_TASKS      = <?= json_encode($js_tasks,      JSON_UNESCAPED_UNICODE) ?>;
const FW_COUNTERS   = <?= json_encode($js_counters,   JSON_UNESCAPED_UNICODE) ?>;
const FW_DETECTIONS = <?= json_encode($js_detections, JSON_UNESCAPED_UNICODE) ?>;

const phaseMap     = Object.fromEntries(FW_PHASES.map(p     => [p.id, p]));
const tacticMap    = Object.fromEntries(FW_TACTICS.map(t    => [t.id, t]));
const techMap      = Object.fromEntries(FW_TECHNIQUES.map(t => [t.id, t]));
const taskMap      = Object.fromEntries(FW_TASKS.map(t      => [t.id, t]));
const counterMap   = Object.fromEntries(FW_COUNTERS.map(c   => [c.id, c]));
const detectionMap = Object.fromEntries(FW_DETECTIONS.map(d => [d.id, d]));

const techsByTactic    = {};
FW_TECHNIQUES.forEach(t => { (techsByTactic[t.tactic]    = techsByTactic[t.tactic]    || []).push(t); });
const tasksByTactic    = {};
FW_TASKS.forEach(t      => { (tasksByTactic[t.tactic]    = tasksByTactic[t.tactic]    || []).push(t); });
const tacticsByPhase   = {};
FW_TACTICS.forEach(t    => { (tacticsByPhase[t.phase]    = tacticsByPhase[t.phase]    || []).push(t); });
const countersByTactic = {};
FW_COUNTERS.forEach(c   => { (countersByTactic[c.tactic] = countersByTactic[c.tactic] || []).push(c); });
const detsByTactic     = {};
FW_DETECTIONS.forEach(d => { (detsByTactic[d.tactic]     = detsByTactic[d.tactic]     || []).push(d); });

// ── Mode toggle ──────────────────────────────────────────────────────────────
function setMode(mode) {
    document.getElementById('fw-wrap').dataset.mode = mode;
    document.getElementById('btn-red').classList.toggle('fw-active',  mode === 'red');
    document.getElementById('btn-blue').classList.toggle('fw-active', mode === 'blue');
}

// ── Accordion ────────────────────────────────────────────────────────────────
function toggleCard(id) {
    const panel = document.getElementById(id);
    const arrow = document.getElementById('arrow-' + id);
    const hd    = panel.previousElementSibling;
    const open  = panel.classList.toggle('open');
    if (arrow) arrow.classList.toggle('open', open);
    if (hd)    hd.classList.toggle('active', open);
}

// ── Phase accent colours ─────────────────────────────────────────────────────
const phaseAccent = { P01:'#1a3a1a', P02:'#7A1515', P03:'#0F2D4A', P04:'#4a3000' };

const typeLinks = {
    tactic:    id => `tactic.php?id=${encodeURIComponent(id)}`,
    technique: id => `technique.php?id=${encodeURIComponent(id)}`,
    counter:   id => `counter.php?id=${encodeURIComponent(id)}`,
};

const typeAccent = {
    phase: null, tactic: null,
    technique: '#7A1515', task: '#0F2D4A',
    counter: '#0F2D4A', detection: '#2A6B2A',
};

function esc(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Detail panel ─────────────────────────────────────────────────────────────
function showDetail(type, id) {
    let item, phaseId, tacticName;

    if      (type === 'phase')     { item = phaseMap[id]; }
    else if (type === 'tactic')    { item = tacticMap[id];    phaseId = item?.phase; }
    else if (type === 'technique') {
        item = techMap[id];
        const tac = tacticMap[item?.tactic];
        phaseId = tac?.phase; tacticName = tac ? `${tac.id}: ${tac.name}` : null;
    }
    else if (type === 'task') {
        item = taskMap[id];
        const tac = tacticMap[item?.tactic];
        phaseId = tac?.phase; tacticName = tac ? `${tac.id}: ${tac.name}` : null;
    }
    else if (type === 'counter') {
        item = counterMap[id];
        const tac = tacticMap[item?.tactic];
        phaseId = tac?.phase; tacticName = tac ? `${tac.id}: ${tac.name}` : null;
    }
    else if (type === 'detection') {
        item = detectionMap[id];
        const tac = tacticMap[item?.tactic];
        phaseId = tac?.phase; tacticName = tac ? `${tac.id}: ${tac.name}` : null;
    }
    if (!item) return;

    const phase  = phaseId ? phaseMap[phaseId] : null;
    const accent = typeAccent[type] || (phaseId ? phaseAccent[phaseId] : '#0D0D0D');
    const typeLabel = { phase:'Phase', tactic:'Tactic', technique:'Technique',
                        task:'Task', counter:'Counter', detection:'Detection' }[type];

    // Breadcrumb
    let bc = '';
    if (phase)      bc += `<span class="fw-bc-item">${esc(phase.id)}: ${esc(phase.name)}</span><span class="fw-bc-sep">›</span>`;
    if (tacticName) bc += `<span class="fw-bc-item">${esc(tacticName)}</span><span class="fw-bc-sep">›</span>`;

    // Metatechnique badge for counters
    let metaBadge = '';
    if (type === 'counter' && item.metaName) {
        metaBadge = `<div style="margin-bottom:10px">
            <span style="font-size:9px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;padding:2px 7px;background:rgba(15,45,74,0.1);color:var(--blue-fw)">${esc(item.metaName)}</span>
        </div>`;
    }

    // Related section
    let related = '';
    if (type === 'tactic') {
        const mode   = document.getElementById('fw-wrap').dataset.mode;
        const techs  = techsByTactic[id]    || [];
        const tasks  = tasksByTactic[id]    || [];
        const ctrs   = countersByTactic[id] || [];
        const dets   = detsByTactic[id]     || [];
        if (mode === 'red') {
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
        } else {
            if (ctrs.length) {
                related += `<hr class="fw-detail-div"><div class="fw-detail-sec">${ctrs.length} Counters</div>`;
                related += ctrs.map(c =>
                    `<div class="fw-counter" onclick="showDetail('counter','${esc(c.id)}')" style="cursor:pointer">
                       <span class="fw-counter-id">${esc(c.id)}</span>
                       <span class="fw-counter-name">${esc(c.name)}</span>
                     </div>`
                ).join('');
            }
            if (dets.length) {
                related += `<hr class="fw-detail-div"><div class="fw-detail-sec">${dets.length} Detections</div>`;
                related += dets.map(d =>
                    `<div class="fw-det" onclick="showDetail('detection','${esc(d.id)}')" style="cursor:pointer">
                       <span class="fw-det-id">${esc(d.id)}</span>
                       <span class="fw-det-name">${esc(d.name)}</span>
                     </div>`
                ).join('');
            }
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

    const detailLink = typeLinks[type]
        ? `<a class="fw-detail-link" href="${typeLinks[type](item.id)}">Full Detail Page &rarr;</a>`
        : '';

    document.getElementById('fw-detail-body').innerHTML = `
        ${bc ? `<div class="fw-bc">${bc}</div>` : ''}
        <div class="fw-detail-type" style="color:${accent}">${typeLabel}</div>
        <div class="fw-detail-id">${esc(item.id)}</div>
        <div class="fw-detail-name">${esc(item.name)}</div>
        ${metaBadge}
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
