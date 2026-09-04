<?php
// backend/utils/config.php - Database configuration
// For production: put these in a .env file at the project root (gitignored).
// A .env.example template is committed for reference.

// Load .env if present (does not override real environment variables).
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        if (!array_key_exists($k, $_ENV) && !array_key_exists($k, $_SERVER)) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

return [
    'db_host'     => getenv('DB_HOST') ?: 'localhost',
    'db_name'     => getenv('DB_NAME') ?: 'pos_inventory_system',
    'db_username' => getenv('DB_USER') ?: 'root',
    'db_password' => getenv('DB_PASS') ?: '',
    'db_charset'  => 'utf8mb4',

    // Security settings
    'session_secure'   => true,   // HTTPS is provided by InfinityFree
    'session_httponly'  => true,
    'session_samesite' => 'Lax',
    'session_path'     => '/',
];
