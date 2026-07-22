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

// If user is not logged in, redirect to the login page
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
