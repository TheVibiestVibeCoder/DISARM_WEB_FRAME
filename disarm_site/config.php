<?php
// ─── DISARM Framework — Database Configuration ───────────────────────────────
// Credentials are loaded from ../.env (one level above the web root).
// Fill in ../.env after creating the MySQL database in cPanel:
//   cPanel > MySQL Databases > create DB + user, then grant ALL PRIVILEGES.

$_dotenv = parse_ini_file(__DIR__ . '/../.env');

define('DB_HOST', $_dotenv['DB_HOST'] ?? 'localhost');
define('DB_USER', $_dotenv['DB_USER'] ?? '');
define('DB_PASS', $_dotenv['DB_PASS'] ?? '');
define('DB_NAME', $_dotenv['DB_NAME'] ?? '');

define('SITE_TITLE', 'DISARM Framework Browser');
define('SITE_VERSION', '2.0');

// ─── PDO Connection ───────────────────────────────────────────────────────────
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
        . '<p>Check the settings in <code>config.php</code>.</p>'
        . '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>'
    );
}
