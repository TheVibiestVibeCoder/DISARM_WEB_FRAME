<?php
/** Escape for HTML output */
function h(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/** Get a sanitised GET parameter */
function gp(string $name, string $default = ''): string {
    return trim($_GET[$name] ?? $default);
}

/** Get a sanitised POST parameter */
function pp(string $name, string $default = ''): string {
    return trim($_POST[$name] ?? $default);
}

/** Build URL with query params */
function url(string $path, array $params = []): string {
    return empty($params) ? $path : $path . '?' . http_build_query($params);
}

/** Render a DISARM ID badge (optionally linked) */
function id_badge(string $id, string $link = '', string $extra_class = ''): string {
    $cls   = 'disarm-id' . ($extra_class ? ' ' . $extra_class : '');
    $inner = '<span class="' . $cls . '">' . h($id) . '</span>';
    return $link ? '<a href="' . h($link) . '" style="text-decoration:none">' . $inner . '</a>' : $inner;
}

/** Truncate text to $max chars */
function truncate(?string $text, int $max = 200): string {
    $text = $text ?? '';
    return mb_strlen($text) > $max ? mb_substr($text, 0, $max) . '…' : $text;
}

/** CSRF hidden field */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . h($_SESSION['csrf'] ?? '') . '">';
}

/** Validate CSRF token — dies on failure */
function csrf_check(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        die('Security token mismatch. Please go back and try again.');
    }
}

/** Flash a message via session */
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

/** Render and clear flash */
function render_flash(): string {
    if (empty($_SESSION['flash'])) return '';
    $f   = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $cls = $f['type'] === 'success' ? 'flash flash-success' : 'flash flash-error';
    return '<div class="' . $cls . '">' . h($f['msg']) . '</div>';
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

/** Render pagination nav */
function pagination_html(array $result, string $base_url, array $extra = []): string {
    if ($result['total_pages'] <= 1) return '';
    $html = '<div class="pagination">';
    for ($i = 1; $i <= $result['total_pages']; $i++) {
        $params = array_merge($extra, ['page' => $i]);
        $href   = url($base_url, $params);
        if ($i === $result['page']) {
            $html .= '<span class="current">' . $i . '</span>';
        } else {
            $html .= '<a href="' . h($href) . '">' . $i . '</a>';
        }
    }
    return $html . '</div>';
}

/** Render empty state */
function no_results(string $msg = 'No items found.'): string {
    return '<div class="empty">' . h($msg) . '</div>';
}
