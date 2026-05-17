<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = '';

$phases  = $pdo->query("SELECT disarm_id, name FROM phase ORDER BY rank")->fetchAll();
$tactics = $pdo->query("SELECT disarm_id, name, phase_id FROM tactic ORDER BY disarm_id")->fetchAll();

include 'includes/header.php';
?>

<!-- Hero -->
<section class="hero">
  <div class="container">
    <div class="hero-eyebrow">DISARM Foundation &nbsp;&middot;&nbsp; Framework v<?= SITE_VERSION ?></div>
    <h1>Disinformation<br>Framework</h1>

    <div class="hero-bottom reveal">
      <p class="hero-desc">
        A structured framework for describing and countering disinformation campaigns,
        modelled after MITRE ATT&amp;CK. Browse attacker techniques (Red Framework) and
        defensive countermeasures (Blue Framework) with full cross-references.
      </p>
      <div class="hero-cta">
        <a href="red.php"  class="btn btn-ghost-inv">Red Framework &rarr;</a>
        <a href="blue.php" class="btn" style="background:var(--white);color:var(--dark);border-color:var(--white)">Blue Framework &rarr;</a>
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
        <a href="red.php" class="btn btn-ghost btn-sm" style="margin-top:8px">All Tactics &amp; Techniques &rarr;</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
