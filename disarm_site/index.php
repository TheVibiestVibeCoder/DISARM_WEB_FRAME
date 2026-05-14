<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = '';

$count = function(string $table) use ($pdo): int {
    try { return (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn(); }
    catch (Exception $e) { return 0; }
};

$stats = [
    ['Techniques', $count('technique'),    'techniques.php'],
    ['Counters',   $count('counter'),      'counters.php'],
    ['Incidents',  $count('incident'),     'incidents.php'],
    ['Tactics',    $count('tactic'),       'tactics.php'],
    ['Detections', $count('detection'),    'detections.php'],
    ['Groups',     $count('externalgroup'),'groups.php'],
];

$phases  = $pdo->query("SELECT disarm_id, name FROM phase ORDER BY rank")->fetchAll();
$tactics = $pdo->query("SELECT disarm_id, name, phase_id FROM tactic ORDER BY disarm_id")->fetchAll();
$recent  = $pdo->query(
    "SELECT disarm_id, name, year_started, found_in_country
     FROM incident ORDER BY year_started DESC, disarm_id LIMIT 10"
)->fetchAll();

include 'includes/header.php';
?>

<!-- Hero -->
<section class="hero">
  <div class="container">
    <div class="hero-eyebrow">DISARM Foundation · Framework v<?= SITE_VERSION ?></div>
    <h1>Disinformation<br>Framework</h1>

    <!-- Stats -->
    <div class="stats-grid reveal">
      <?php foreach ($stats as [$label, $n, $link]): ?>
      <div class="stat-cell">
        <a href="<?= h($link) ?>">
          <div class="stat-num"><?= number_format($n) ?></div>
          <div class="stat-label"><?= h($label) ?></div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="hero-bottom reveal">
      <p class="hero-desc">
        A structured framework for describing and countering disinformation campaigns,
        modelled after MITRE ATT&amp;CK. Browse attacker techniques (Red Framework) and
        defensive countermeasures (Blue Framework) with full cross-references.
      </p>
      <div class="hero-cta">
        <a href="techniques.php" class="btn btn-ghost-inv">Red Framework →</a>
        <a href="counters.php"   class="btn" style="background:var(--white);color:var(--dark);border-color:var(--white)">Blue Framework →</a>
      </div>
    </div>
  </div>
</section>

<!-- Phases & Tactics -->
<section class="section">
  <div class="container">
    <div class="label reveal">Campaign Structure</div>

    <div class="grid-aside">
      <div>
        <h2 class="reveal" style="font-size:clamp(24px,3vw,40px);font-weight:300;letter-spacing:-0.02em;line-height:1.15;position:sticky;top:80px">
          Phases &amp;<br>Tactics
        </h2>
      </div>
      <div class="reveal">
        <?php foreach ($phases as $ph): ?>
        <div style="margin-bottom:clamp(28px,4vw,44px)">
          <div class="section-title" style="margin-bottom:14px">
            <span class="disarm-id"><?= h($ph['disarm_id']) ?></span>&nbsp;&nbsp;<?= h($ph['name']) ?>
          </div>
          <?php foreach (array_filter($tactics, fn($t) => $t['phase_id'] === $ph['disarm_id']) as $t): ?>
          <div style="border-top:1px solid var(--border);display:grid;grid-template-columns:110px 1fr;gap:12px;padding:11px 0;align-items:baseline">
            <?= id_badge($t['disarm_id'], 'tactic.php?id=' . urlencode($t['disarm_id'])) ?>
            <a href="tactic.php?id=<?= urlencode($t['disarm_id']) ?>"
               style="text-decoration:none;color:var(--text);font-size:14px;font-weight:400">
              <?= h($t['name']) ?>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <a href="tactics.php" class="btn btn-ghost btn-sm" style="margin-top:8px">All Tactics →</a>
      </div>
    </div>
  </div>
</section>

<!-- Recent Incidents -->
<?php if ($recent): ?>
<section class="section section-dark">
  <div class="container">
    <div class="label label-dark reveal">Recent Activity</div>
    <h2 class="reveal" style="font-size:clamp(22px,3vw,40px);font-weight:300;letter-spacing:-0.02em;margin-bottom:clamp(36px,5vw,56px);color:var(--white)">
      Documented Incidents
    </h2>
    <div class="reveal">
      <?php foreach ($recent as $inc): ?>
      <div style="border-top:1px solid var(--bd-dark);display:grid;grid-template-columns:110px 1fr 70px 160px;gap:16px;padding:14px 0;align-items:center">
        <?= id_badge($inc['disarm_id'], 'incident.php?id=' . urlencode($inc['disarm_id']), 'disarm-id-inv') ?>
        <a href="incident.php?id=<?= urlencode($inc['disarm_id']) ?>"
           style="text-decoration:none;color:var(--white);font-size:14px;font-weight:300">
          <?= h(truncate($inc['name'], 80)) ?>
        </a>
        <span style="font-size:12px;color:rgba(245,241,235,0.4)"><?= h($inc['year_started']) ?></span>
        <span style="font-size:12px;color:rgba(245,241,235,0.3)"><?= h(truncate($inc['found_in_country'] ?? '', 22)) ?></span>
      </div>
      <?php endforeach; ?>
      <div style="border-top:1px solid var(--bd-dark);padding-top:20px;margin-top:4px">
        <a href="incidents.php" class="btn btn-ghost-inv btn-sm">All Incidents →</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
