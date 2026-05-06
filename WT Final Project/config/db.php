<?php
// config/db.php - PDO Database Connection (Singleton)

define('DB_HOST', getenv('SKILLSWAP_DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('SKILLSWAP_DB_PORT') ?: '3306');
define('DB_NAME', getenv('SKILLSWAP_DB_NAME') ?: 'skillswap');
define('DB_USER', getenv('SKILLSWAP_DB_USER') ?: 'root');
define('DB_PASS', getenv('SKILLSWAP_DB_PASS') ?: '');
define('DB_CHARSET', getenv('SKILLSWAP_DB_CHARSET') ?: 'utf8mb4');

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:2rem;background:#fee2e2;border:1px solid #ef4444;border-radius:8px;margin:2rem">
                <h2 style="color:#dc2626">Database Connection Failed</h2>
                <p>Could not connect to MySQL. Please check your config/db.php settings and ensure MySQL is running.</p>
                <code style="color:#7f1d1d">' . htmlspecialchars($e->getMessage()) . '</code>
                </div>');
        }
    }
    return $pdo;
}

// Alias for convenience
$pdo = get_db();
