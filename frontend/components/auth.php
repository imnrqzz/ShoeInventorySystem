<?php
/**
 * components/auth.php — Authentication Guard
 *
 * Best Practice: Include this file at the top of every protected page.
 * It handles three things in one place so you don't repeat them on every page:
 *
 * 1. Starts the session (needed to check if user is logged in)
 * 2. Sets no-cache headers (prevents Back button from showing pages after logout)
 * 3. Redirects to login if no valid session exists
 *
 * It also defines the safe() helper function for escaping output (prevents XSS).
 *
 * Usage: require_once __DIR__ . '/components/auth.php';
 */

// Start session if not already active
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Best Practice: Tell the browser to never cache this page.
// Without this, pressing Back after logout shows the old cached page.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// If user is not logged in, redirect to the login page
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Best Practice: Define a global helper to escape output and prevent XSS attacks.
// htmlspecialchars() converts special characters like < > " ' & into safe HTML entities
// so user-supplied data can never be interpreted as HTML or JavaScript.
if (!function_exists('safe')) {
    function safe($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
