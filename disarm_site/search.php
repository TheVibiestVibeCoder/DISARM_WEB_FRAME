<?php
require_once 'config.php';
require_once 'includes/functions.php';
$page_title = 'Search';

$q = gp('q');

$results = [];
$total   = 0;

if (strlen($q) >= 2) {
    $like   = "%$q%";
    $types  = [
        ['table' => 'technique', 'page' => 'technique.php', 'label' => 'Technique', 'color' => 'bg-danger'],
        ['table' => 'counter',   'page' => 'counter.php',   'label' => 'Counter',   'color' => 'bg-primary'],
        ['table' => 'tactic',    'page' => 'tactic.php',    'label' => 'Tactic',    'color' => 'bg-dark'],
        ['table' => 'incident',  'page' => 'incident.php',  'label' => 'Incident',  'color' => 'bg-warning text-dark'],
        ['table' => 'detection', 'page' => 'detections.php','label' => 'Detection', 'color' => 'bg-info text-dark'],
        ['table' => 'tool',      'page' => 'tools.php',     'label' => 'Tool',      'color' => 'bg-success'],
        ['table' => 'externalgroup', 'page' => 'groups.php','label' => 'Group',     'color' => 'bg-secondary'],
    ];

    foreach ($types as $type) {
        $stmt = $pdo->prepare(
            "SELECT disarm_id, name, summary FROM `{$type['table']}`
             WHERE name LIKE ? OR summary LIKE ?
             ORDER BY name LIMIT 30"
        );
        $stmt->execute([$like, $like]);
        $rows = $stmt->fetchAll();
        if ($rows) {
            $results[] = array_merge($type, ['rows' => $rows]);
            $total    += count($rows);
        }
    }
}

// Highlight matching text
function highlight(string $text, string $q): string {
    if (!$q) return h($text);
    return preg_replace(
        '/(' . preg_quote(htmlspecialchars($q, ENT_QUOTES, 'UTF-8'), '/') . ')/i',
        '<mark>$1</mark>',
        h($text)
    );
}

include 'includes/header.php';
?>

<h2 class="fw-bold mb-3"><i class="bi bi-search me-2"></i>Search DISARM</h2>

<div class="card mb-4">
  <div class="card-body">
    <form method="get" class="row g-2">
      <div class="col">
        <input type="text" name="q" class="form-control form-control-lg"
               placeholder="Search across all objects — techniques, counters, incidents, tools…"
               value="<?= h($q) ?>" autofocus>
      </div>
      <div class="col-auto">
        <button class="btn btn-dark btn-lg" type="submit"><i class="bi bi-search me-1"></i>Search</button>
      </div>
    </form>
  </div>
</div>

<?php if ($q && strlen($q) < 2): ?>
<div class="alert alert-warning">Please enter at least 2 characters.</div>

<?php elseif ($q && !$total): ?>
<div class="alert alert-secondary">No results found for "<strong><?= h($q) ?></strong>".</div>

<?php elseif ($q): ?>
<p class="text-muted mb-3">
  Found <strong><?= number_format($total) ?></strong> results for "<strong><?= h($q) ?></strong>"
  (capped at 30 per category)
</p>

<?php foreach ($results as $group): ?>
<div class="card mb-4">
  <div class="card-header <?= $group['color'] ?>">
    <i class="bi bi-folder2 me-2"></i><?= $group['label'] ?>s
    <span class="badge bg-white text-dark ms-2"><?= count($group['rows']) ?></span>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th style="width:110px">ID</th><th>Name</th><th>Summary</th></tr></thead>
      <tbody>
        <?php foreach ($group['rows'] as $row):
            // Build the correct detail URL
            $detail_page = $group['page'];
            // For tools/groups/detections — no individual detail page, link to list
            $has_detail  = in_array($group['table'], ['technique','counter','tactic','incident']);
            $link        = $has_detail
                ? basename($group['page'], '.php') . '.php?id=' . urlencode($row['disarm_id'])
                : $group['page'];
        ?>
        <tr>
          <td><span class="disarm-id"><?= h($row['disarm_id']) ?></span></td>
          <td>
            <a href="<?= h($link) ?>">
              <?= highlight($row['name'], $q) ?>
            </a>
          </td>
          <td class="text-muted small">
            <?= highlight(truncate($row['summary'], 160), $q) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; ?>

<?php else: ?>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header bg-light"><i class="bi bi-lightbulb me-2"></i>Search Tips</div>
      <ul class="list-group list-group-flush">
        <li class="list-group-item small">Search by technique name: <em>sockpuppet</em></li>
        <li class="list-group-item small">Search by keyword in summary: <em>social media</em></li>
        <li class="list-group-item small">Search by DISARM ID: <em>T0013</em></li>
        <li class="list-group-item small">Search incidents by country: <em>Russia</em></li>
        <li class="list-group-item small">Search counters by type: <em>labelling</em></li>
      </ul>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header bg-light"><i class="bi bi-bookmark me-2"></i>Quick Links</div>
      <ul class="list-group list-group-flush">
        <li class="list-group-item"><a href="tactics.php">Browse all Tactics</a></li>
        <li class="list-group-item"><a href="techniques.php">Browse all Techniques (Red)</a></li>
        <li class="list-group-item"><a href="counters.php">Browse all Counters (Blue)</a></li>
        <li class="list-group-item"><a href="incidents.php">Browse all Incidents</a></li>
        <li class="list-group-item"><a href="groups.php">Browse External Groups</a></li>
      </ul>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
