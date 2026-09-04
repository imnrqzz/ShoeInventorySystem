<?php
// backend/bootstrap.php - Central auth/bootstrap layer.
// Include once at the top of any page. Provides:
//   $pdo       - PDO database connection
//   safe()     - XSS-safe output escaping
//   isAdmin()  - Check if current user is admin
//   requireAdmin() - Check admin or redirect
//   csrf_token() / csrf_field() / verify_csrf() - CSRF protection

// 1. Session with security flags
if (session_status() !== PHP_SESSION_ACTIVE) {
    $config = require __DIR__ . '/utils/config.php';
    ini_set('session.cookie_httponly', $config['session_httponly'] ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', $config['session_samesite']);
    // Dynamic session cookie path: works on localhost (/ShoeInventorySystem)
    // and on InfinityFree (e.g. /admin or /). Uses the base of the current script.
    $cookiePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    // Walk up to the project base (the folder that contains /backend).
    $scriptParts = explode('/', trim($cookiePath, '/'));
    // Find the segment right before /backend or /frontend to use as project root.
    $projBase = '';
    for ($i = 0; $i < count($scriptParts); $i++) {
        if (in_array($scriptParts[$i], ['backend', 'frontend', 'storefront', 'admin'], true)) {
            $projBase = '/' . implode('/', array_slice($scriptParts, 0, $i));
            break;
        }
    }
    ini_set('session.cookie_path', $projBase === '' ? '/' : $projBase);
    ini_set('session.cookie_lifetime', 0);
    if ($config['session_secure']) {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Admin base URL (the /frontend or /admin folder). Computed so it works both
// locally (/ShoeInventorySystem/frontend) and on InfinityFree (/admin).
// Walks up from the current script's directory to find the folder that
// actually contains login.php, then returns its URL path.
function admin_base(): string {
    $root = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/');
    $abs = $root . str_replace('\\', '/', $scriptDir);
    $current = $abs;
    while (true) {
        foreach (['frontend', 'admin'] as $candidate) {
            if (file_exists($current . '/' . $candidate . '/login.php')) {
                $rel = str_replace('\\', '/', substr($current, strlen($root)));
                return ($rel === '' ? '' : $rel) . '/' . $candidate;
            }
        }
        $parent = dirname($current);
        if ($parent === $current) break;
        $current = $parent;
    }
    return '/frontend';
}
define('ADMIN_BASE', admin_base());

// 2. Cache headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// 3. Database connection
require_once __DIR__ . '/Classes/Database.php';
$database = new Database();
$pdo = $database->getConnection();

// 4. safe() - XSS output escaping
if (!function_exists('safe')) {
    function safe($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

// 5. Role helpers
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['role']) && strtolower((string)$_SESSION['role']) === 'admin';
    }
}

if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isset($_SESSION['username'])) {
            header('Location: ' . ADMIN_BASE . '/login.php');
            exit;
        }
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        if (!isAdmin()) {
            header('Location: ' . ADMIN_BASE . '/login.php');
            exit;
        }
    }
}

// 6. CSRF protection
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf() {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals(csrf_token(), $token)) {
            http_response_code(403);
            exit('Invalid CSRF token.');
        }
    }
}