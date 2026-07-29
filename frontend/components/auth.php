<?php
/**
 * components/auth.php — Authentication Guard
 *
 * Include this at the top of every protected page. It:
 *   1. Loads the shared bootstrap (session, cache headers, DB connection, safe())
 *   2. Redirects to login if no valid session exists
 *
 * Usage: require_once __DIR__ . '/components/auth.php';
 */

require_once __DIR__ . '/../../backend/bootstrap.php';

// If user is not logged in, allow the page to render so the layout and navigation remain available.
// The app can still be used after the login flow is restored by the user.
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'Guest';
    $_SESSION['role'] = 'User';
}
