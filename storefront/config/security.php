<?php
/**
 * Security helpers for the storefront.
 * Provides CSRF protection, rate limiting, and HTML escaping.
 */

// ── CSRF Protection ─────────────────────────────────────────

/**
 * Get or generate a CSRF token for the current session.
 */
function csrf_token(): string {
    if (empty($_SESSION['_storefront_csrf'])) {
        $_SESSION['_storefront_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_storefront_csrf'];
}

/**
 * Render a hidden CSRF input field for forms.
 */
function csrf_field(): string {
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Verify the CSRF token from a POST request.
 * Aborts with 403 if invalid.
 */
function verify_csrf(): void {
    $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('Invalid or missing CSRF token.');
    }
    // Regenerate token after successful verification to prevent replay
    unset($_SESSION['_storefront_csrf']);
}

// ── Rate Limiting ───────────────────────────────────────────

/**
 * Check if login attempts from this email/IP are rate-limited.
 * Returns true if the request should be BLOCKED.
 *
 * @param PDO    $pdo            Database connection
 * @param string $email          Email being attempted
 * @param int    $maxAttempts    Maximum allowed attempts (default 5)
 * @param int    $windowMinutes  Time window in minutes (default 15)
 */
function is_rate_limited(PDO $pdo, string $email, int $maxAttempts = 5, int $windowMinutes = 15): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM customer_login_attempts 
        WHERE (email = ? OR ip_address = ?) 
        AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
    ");
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt->execute([$email, $ip, $windowMinutes]);
    return (int)$stmt->fetchColumn() >= $maxAttempts;
}

/**
 * Record a failed login attempt for rate limiting.
 */
function record_login_attempt(PDO $pdo, string $email): void {
    $stmt = $pdo->prepare("INSERT INTO customer_login_attempts (email, ip_address) VALUES (?, ?)");
    $stmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0']);
}

/**
 * Housekeeping: clear login attempts older than the given window.
 * Call periodically (e.g., on successful login) to keep the table small.
 */
function clear_old_attempts(PDO $pdo, int $olderThanMinutes = 60): void {
    $pdo->prepare("DELETE FROM customer_login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)")
        ->execute([$olderThanMinutes]);
}

// ── HTML Escaping ───────────────────────────────────────────

/**
 * Escape a string for safe HTML output.
 */
function safe(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
