<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$page_title = 'Search';

$q       = gp('q');
$results = [];
$total   = 0;

if (strlen($q) >= 2) {
    $like  = "%$q%";
    $types = [
        ['table' => 'technique', 'page' => 'technique.php', 'label' => 'Technique', 'tag' => 'tag-red'],
        ['table' => 'counter',   'page' => 'counter.php',   'label' => 'Counter',   'tag' => 'tag-blue'],
        ['table' => 'tactic',    'page' => 'tactic.php',    'label' => 'Tactic',    'tag' => 'tag-muted'],
        ['table' => 'incident',  'page' => 'incident.php',  'label' => 'Incident',  'tag' => 'tag-muted'],
        ['table' => 'detection', 'page' => 'detections.php','label' => 'Detection', 'tag' => 'tag-muted'],
        ['table' => 'tool',      'page' => 'tools.php',     'label' => 'Tool',      'tag' => 'tag-muted'],
        ['table' => 'externalgroup','page'=>'groups.php',   'label' => 'Group',     'tag' => 'tag-muted'],
    ];
    foreach ($types as $type) {
        $stmt = $pdo->prepare("SELECT disarm_id, name, summary FROM `{$type['table']}` WHERE name LIKE ? OR summary LIKE ? ORDER BY name LIMIT 30");
        $stmt->execute([$like, $like]);
        $rows = $stmt->fetchAll();
        if ($rows) { $results[] = array_merge($type, ['rows' => $rows]); $total += count($rows); }
    }
}

function hl(string $text, string $q): string {
    if (!$q) return h($text);
    return preg_replace('/(' . preg_quote(h($q), '/') . ')/i', '<mark>$1</mark>', h($text));
}

include 'includes/header.php';
?>

<div class="container">

<div class="page-header">
  <div class="label">Framework</div>
  <h1>Search</h1>
</div>

<form method="get" style="display:grid;grid-template-columns:1fr auto;gap:0;margin-bottom:clamp(40px,5vw,60px)">
  <input type="text" name="q" placeholder="Search across all objects — techniques, counters, incidents…"
         value="<?= h($q) ?>" autofocus
         style="background:transparent;border:1px solid var(--border);border-right:none;padding:14px 18px;font-size:15px;font-weight:300;font-family:inherit;color:var(--text);outline:none">
  <button type="submit" class="btn btn-dark" style="padding:14px 28px">Search →</button>
</form>

<?php if ($q && strlen($q) < 2): ?>
<div class="flash flash-error">Please enter at least 2 characters.</div>

<?php elseif ($q && !$total): ?>
<div class="flash">No results for "<strong><?= h($q) ?></strong>".</div>

<?php elseif ($q): ?>
<p style="font-size:13px;color:var(--muted);margin-bottom:32px">
  <?= number_format($total) ?> results for "<strong><?= h($q) ?></strong>" (capped at 30 per category)
</p>

<?php foreach ($results as $group): ?>
<div style="margin-bottom:clamp(40px,5vw,60px)">
  <div class="section-title" style="margin-bottom:0">
    <span class="tag <?= $group['tag'] ?>" style="margin-right:8px"><?= $group['label'] ?>s</span>
    <?= count($group['rows']) ?>
  </div>
  <table class="data-table">
    <thead><tr><th class="col-id">ID</th><th>Name</th><th>Summary</th></tr></thead>
    <tbody>
      <?php foreach ($group['rows'] as $row):
          $has_detail = in_array($group['table'], ['technique','counter','tactic','incident']);
          $link = $has_detail
              ? basename($group['page'], '.php') . '.php?id=' . urlencode($row['disarm_id'])
              : $group['page'];
      ?>
      <tr>
        <td><?= id_badge($row['disarm_id']) ?></td>
        <td><a href="<?= h($link) ?>"><?= hl($row['name'], $q) ?></a></td>
        <td class="col-summary"><?= hl(truncate($row['summary'] ?? '', 150), $q) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endforeach; ?>

<?php else: ?>
<div class="grid-2" style="margin-top:clamp(20px,3vw,40px)">
  <div>
    <div class="section-title" style="margin-bottom:16px">Search tips</div>
    <?php foreach (['Search by name: sockpuppet', 'Search by keyword: social media', 'Search by ID: T0013', 'Search incidents by country: Russia', 'Search counters: labelling'] as $tip): ?>
    <div style="border-top:1px solid var(--border);padding:10px 0;font-size:13px;font-weight:300;color:var(--muted)"><?= h($tip) ?></div>
    <?php endforeach; ?>
  </div>
  <div>
    <div class="section-title" style="margin-bottom:16px">Quick links</div>
    <?php foreach ([['tactics.php','All Tactics'],['techniques.php','All Techniques (Red)'],['counters.php','All Counters (Blue)'],['incidents.php','All Incidents'],['groups.php','External Groups']] as [$href,$label]): ?>
    <div style="border-top:1px solid var(--border);padding:10px 0">
      <a href="<?= h($href) ?>" style="font-size:13px;font-weight:400;color:var(--text);text-decoration:none"><?= h($label) ?> →</a>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

</div>
<?php include 'includes/footer.php'; ?>
