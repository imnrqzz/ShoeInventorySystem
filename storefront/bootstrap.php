<?php
/**
 * Storefront bootstrap — separate session from admin panel.
 */

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/security.php';

/** Base URL path for storefront assets and links */
define('STOREFRONT_BASE', '/ShoeInventorySystem/storefront');

// Distinct session cookie so customer auth never collides with admin sessions
if (session_status() !== PHP_SESSION_ACTIVE) {
    $adminConfig = require __DIR__ . '/../backend/utils/config.php';
    session_name('storefront_session');
    
    // Explicitly configure secure session cookie settings
    $isSecure = !empty($adminConfig['session_secure']) || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    // Clear old path-restricted storefront session cookies if they exist to prevent browser path collisions
    if (isset($_COOKIE['storefront_session'])) {
        setcookie('storefront_session', '', [
            'expires' => time() - 3600,
            'path' => STOREFRONT_BASE,
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        setcookie('storefront_session', '', [
            'expires' => time() - 3600,
            'path' => strtolower(STOREFRONT_BASE),
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    // Explicitly configure secure session cookie settings
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if ($isSecure) {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

/** Resolve inventory item image paths for the storefront */
function item_image_url(?string $path): ?string
{
    if ($path === null || $path === '') {
        return null;
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return '/ShoeInventorySystem/' . ltrim($path, '/');
}

/** Check if a customer is logged in */
function is_customer_logged_in(): bool
{
    return !empty($_SESSION['customer']['id']);
}

/** Require customer login or redirect */
function require_customer(): void
{
    if (!is_customer_logged_in()) {
        header('Location: ' . STOREFRONT_BASE . '/index.php?page=login');
        exit;
    }
}

/** Flash message helpers */
function flash_set(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}
