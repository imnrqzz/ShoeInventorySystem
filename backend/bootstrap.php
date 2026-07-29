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
    ini_set('session.cookie_path', '/ShoeInventorySystem');
    ini_set('session.cookie_lifetime', 0);
    if ($config['session_secure']) {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

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
            header('Location: /ShoeInventorySystem/frontend/login.php');
            exit;
        }
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        if (!isAdmin()) {
            header('Location: /ShoeInventorySystem/frontend/login.php');
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