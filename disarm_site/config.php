<?php
// Load .env (one level above web root)
$_dotenv = [];
foreach (file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
    if ($_line[0] === '#' || strpos($_line, '=') === false) continue;
    [$_k, $_v] = explode('=', $_line, 2);
    $_dotenv[trim($_k)] = trim($_v);
}

define('DB_HOST',       $_dotenv['DB_HOST']       ?? 'localhost');
define('DB_USER',       $_dotenv['DB_USER']       ?? '');
define('DB_PASS',       $_dotenv['DB_PASS']       ?? '');
define('DB_NAME',       $_dotenv['DB_NAME']       ?? '');
define('SITE_TITLE',    'DISARM Framework');
define('SITE_VERSION',  '2.0');
define('SITE_PASSWORD', $_dotenv['SITE_PASSWORD'] ?? '');

// Session
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

// PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die(
        '<style>body{font-family:sans-serif;padding:2rem;background:#f8d7da}</style>'
        . '<h2>&#9888; Database connection failed</h2>'
        . '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>'
    );
}
