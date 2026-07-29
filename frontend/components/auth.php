<?>
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

// If user is not logged in, redirect them to the login page
if (!isset($_SESSION['username']) || $_SESSION['username'] === 'Guest') {
    // Adjust the path to your actual login page file if needed
    header('Location: /login.php');
    exit;
}
