<?php
/** Escape for HTML output */
function h(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/** Build URL with query params */
function url(string $path, array $params = []): string {
    return empty($params) ? $path : $path . '?' . http_build_query($params);
}

/** Get a sanitised GET parameter */
function gp(string $name, string $default = ''): string {
    return trim($_GET[$name] ?? $default);
}

/**
 * Render a <span class="disarm-id"> badge.
 * If $link is provided, wraps it in an <a>.
 */
function id_badge(string $id, string $link = ''): string {
    $badge = '<span class="disarm-id">' . h($id) . '</span>';
    return $link ? '<a href="' . h($link) . '">' . $badge . '</a>' : $badge;
}

/** Truncate text to $max chars, adding ellipsis */
function truncate(?string $text, int $max = 200): string {
    $text = $text ?? '';
    return mb_strlen($text) > $max ? mb_substr($text, 0, $max) . '…' : $text;
}

/**
 * Run a paginated query.
 * Returns ['rows', 'total', 'page', 'total_pages', 'per_page']
 */
function paginate(PDO $pdo, string $sql, array $params, int $page, int $per_page = 50): array {
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM ($sql) AS _sub");
    $count_stmt->execute($params);
    $total = (int)$count_stmt->fetchColumn();

    $total_pages = max(1, (int)ceil($total / $per_page));
    $page        = max(1, min($page, $total_pages));
    $offset      = ($page - 1) * $per_page;

    $stmt = $pdo->prepare("$sql LIMIT $per_page OFFSET $offset");
    $stmt->execute($params);

    return [
        'rows'        => $stmt->fetchAll(),
        'total'       => $total,
        'page'        => $page,
        'total_pages' => $total_pages,
        'per_page'    => $per_page,
    ];
}

/** Render Bootstrap 5 pagination nav */
function pagination_html(array $result, string $base_url, array $extra = []): string {
    if ($result['total_pages'] <= 1) return '';
    $html = '<nav aria-label="Page navigation"><ul class="pagination pagination-sm flex-wrap mb-0">';
    for ($i = 1; $i <= $result['total_pages']; $i++) {
        $active = ($i === $result['page']) ? ' active' : '';
        $params = array_merge($extra, ['page' => $i]);
        $href   = url($base_url, $params);
        $html  .= "<li class=\"page-item$active\"><a class=\"page-link\" href=\"" . h($href) . "\">$i</a></li>";
    }
    return $html . '</ul></nav>';
}

/** Render a "no results" alert */
function no_results(string $msg = 'No items found.'): string {
    return '<div class="alert alert-secondary">' . h($msg) . '</div>';
}
