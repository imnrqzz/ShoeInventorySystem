<?php
// backend/utils/config.php - Database configuration
// Reads credentials directly from a .env file (no reliance on putenv/getenv
// ordering). Scans common locations and uses the FIRST one that exists.

if (!function_exists('loadConfigEnvFile')) {
function loadConfigEnvFile(string $path): array {
    $out = [];
    if (!file_exists($path)) return $out;
    $raw = file_get_contents($path);
    // Strip UTF-8 BOM if present
    if (substr($raw, 0, 3) === "\xef\xbb\xbf") $raw = substr($raw, 3);
    // Normalize line endings
    $raw = str_replace("\r\n", "\n", $raw);
    $raw = str_replace("\r", "\n", $raw);
    $lines = explode("\n", $raw);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $name  = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1), " \t\"'");
        if ($name !== '') $out[$name] = $value;
    }
    return $out;
}
}

// Scan for .env in several likely spots (relative to this file's directory).
$selfDir = __DIR__;               // .../backend/utils
$candidates = [
    $selfDir . '/../../storefront/.env',  // prefer the storefront .env
    $selfDir . '/.env',                  // backend/utils/.env
    $selfDir . '/../.env',               // backend/.env
    $selfDir . '/../../.env',            // htdocs/.env
    $selfDir . '/../../admin/.env',
    $selfDir . '/../../frontend/.env',
];

$loadedFrom = '';
$env = [];
foreach ($candidates as $c) {
    $got = loadConfigEnvFile($c);
    // Only accept if it actually has DB credentials (skip empty/stale files)
    if (!empty($got['DB_HOST']) && !empty($got['DB_NAME'])) {
        $env = $got;
        $loadedFrom = $c;
        break;
    }
}
if ($loadedFrom === '' && !empty($got)) {
    // fall back to last candidate even if incomplete (so debug shows something)
    $env = $got;
    $loadedFrom = $c;
}

// Also merge any real environment variables (set by the host) on top.
foreach (['DB_HOST','DB_NAME','DB_USER','DB_PASS','APP_URL','SESSION_SECURE','SESSION_PATH'] as $k) {
    $v = getenv($k);
    if ($v !== false && $v !== '') $env[$k] = $v;
}

return [
    'db_host'     => $env['DB_HOST'] ?? 'localhost',
    'db_name'     => $env['DB_NAME'] ?? 'pos_inventory_system',
    'db_username' => $env['DB_USER'] ?? 'root',
    'db_password' => $env['DB_PASS'] ?? '',
    'db_charset'  => 'utf8mb4',

    // Debug info (remove after deploy)
    '_env_loaded_from' => $loadedFrom,

    // Security settings
    'session_secure'   => ($env['SESSION_SECURE'] ?? '') === 'true',
    'session_httponly' => true,
    'session_samesite' => 'Lax',
    'session_path'     => $env['SESSION_PATH'] ?? '/',
];
