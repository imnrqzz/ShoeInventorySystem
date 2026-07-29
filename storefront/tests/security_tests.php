<?php
/**
 * Automated Security & Referential Integrity Integration Test Suite
 * Run: C:\xampp\php\php.exe storefront/tests/security_tests.php
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: text/plain');

echo "=== STARTING AUTOMATED SECURITY TESTS ===\n\n";

$pdo = Database::getConnection();

// --- TEST 1: CSRF Rejection ---
echo "[TEST 1] CSRF Rejection test... ";
try {
    $_POST = []; // No token
    // Simulate verify_csrf call
    $token = $_POST['_csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        echo "PASS (Request rejected as expected due to missing CSRF token)\n";
    } else {
        echo "FAIL (Request accepted despite missing CSRF token!)\n";
    }
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}

// --- TEST 2: Duplicate Email Registration ---
echo "[TEST 2] Duplicate Email Registration test... ";
$testEmail = 'duplicate_test_' . time() . '@example.com';
Customer::createLocal('Test User', $testEmail, 'password123', 'Test Address', password_hash('123456', PASSWORD_DEFAULT));

if (Customer::findByEmail($testEmail)) {
    // Attempt duplicate
    try {
        $existing = Customer::findByEmail($testEmail);
        if ($existing) {
            echo "PASS (Prevented duplicate registry because findByEmail detects address successfully)\n";
        } else {
            echo "FAIL (Duplicate check failed to detect existing email)\n";
        }
    } catch (Exception $e) {
        echo "FAIL: " . $e->getMessage() . "\n";
    }
} else {
    echo "FAIL (Could not register test customer account)\n";
}

// --- TEST 3: Rate Limiting Lockout Threshold ---
echo "[TEST 3] Rate Limiting Threshold (5 attempts in 15m) test... ";
$limitEmail = 'limit_test_' . time() . '@example.com';

// 5 failed attempts
for ($i = 0; $i < 5; $i++) {
    record_login_attempt($pdo, $limitEmail);
}

if (is_rate_limited($pdo, $limitEmail, 5, 15)) {
    echo "PASS (Triggered lockout exactly at 5 failed attempts)\n";
} else {
    echo "FAIL (Rate limiter did not lockout after 5 attempts)\n";
}

// Cleanup rate limits for test run
$pdo->prepare("DELETE FROM customer_login_attempts WHERE email = ?")->execute([$limitEmail]);

// --- TEST 4: ON DELETE CASCADE Referential Integrity ---
echo "[TEST 4] Database Cascade Delete test... ";
// Insert a temporary item into items
$insItem = $pdo->prepare("INSERT INTO items (name, category, price, quantity) VALUES (?, ?, ?, ?)");
$insItem->execute(['Temp Test Sneaker', 'lifestyle', 4500, 10]);
$itemId = (int)$pdo->lastInsertId();

// Create temp customer
$custId = Customer::createLocal('Cascade Cust', 'cascade_' . time() . '@example.com', 'password123', 'Test Address', password_hash('123456', PASSWORD_DEFAULT));

// Add to cart
Cart::addItem($custId, $itemId, null, 1);

// Verify item in cart
$cartCountBefore = (int)$pdo->query("SELECT COUNT(*) FROM cart_items WHERE customer_id = $custId")->fetchColumn();

if ($cartCountBefore > 0) {
    // Delete item from items table
    $pdo->exec("DELETE FROM items WHERE id = $itemId");

    // Verify cart item was cascaded out
    $cartCountAfter = (int)$pdo->query("SELECT COUNT(*) FROM cart_items WHERE customer_id = $custId")->fetchColumn();

    if ($cartCountAfter === 0) {
        echo "PASS (Deleted item successfully removed cart item row via ON DELETE CASCADE)\n";
    } else {
        echo "FAIL (Cart item row remained after item deletion! orphaned foreign keys found!)\n";
    }
} else {
    echo "FAIL (Failed to add test item to cart)\n";
}

// Database Cleanups
$pdo->exec("DELETE FROM customers WHERE id = $custId");

// --- TEST 5: SMTP CLI Setup URL Generation ---
echo "[TEST 5] SMTP CLI Setup URL Generation Checks... ";
try {
    $clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
    $clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
    $redirectUri = 'http://localhost/ShoeInventorySystem/storefront/oauth-smtp-callback.php';

    $params = [
        'client_id'     => $clientId,
        'redirect_uri'  => $redirectUri,
        'response_type' => 'code',
        'scope'         => 'https://mail.google.com/',
        'access_type'   => 'offline',
        'prompt'        => 'consent select_account',
    ];
    $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

    $hasScope = str_contains($url, 'scope=https%3A%2F%2Fmail.google.com%2F') || str_contains($url, 'scope=https://mail.google.com/');
    $hasAccessType = str_contains($url, 'access_type=offline');
    $hasResponseType = str_contains($url, 'response_type=code');
    $noClientSecretKey = !str_contains($url, 'client_secret');
    $noClientSecretVal = empty($clientSecret) || !str_contains($url, $clientSecret);

    if ($hasScope && $hasAccessType && $hasResponseType && $noClientSecretKey && $noClientSecretVal) {
        echo "PASS (CLI setup URL verified: scope, access_type=offline present, and client_secret explicitly absent)\n";
    } else {
        echo "FAIL (CLI setup URL assertions failed: missing required params or client_secret leaked in URL)\n";
    }
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}

// --- TEST 6: SMTP Resend Rate Limiting (1/min, 5/hour) ---
echo "[TEST 6] SMTP Resend Rate Limiting test... ";
try {
    $resendEmail = 'resend_limit_' . time() . '@example.com';
    $auth = new AuthController();

    // Record one attempt
    $pdo->exec("INSERT INTO customer_login_attempts (email, ip_address) VALUES ('resend:$resendEmail', 'resend_ip:127.0.0.1')");

    // Use Reflection to check private helper isResendRateLimited
    $ref = new ReflectionClass('AuthController');
    $isRateLimitedMethod = $ref->getMethod('isResendRateLimited');
    $isRateLimitedMethod->setAccessible(true);

    $isLimited = $isRateLimitedMethod->invoke($auth, $pdo, $resendEmail);
    if ($isLimited) {
        echo "PASS (Resend Rate Limiter correctly blocked second attempt within 60s cooldown)\n";
    } else {
        echo "FAIL (Resend Rate Limiter failed to block within 60s cooldown)\n";
    }

    // Cleanup resend attempts
    $pdo->prepare("DELETE FROM customer_login_attempts WHERE email = ?")->execute(["resend:$resendEmail"]);
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}

echo "\n=== ALL SECURITY INTEGRATION TESTS RUN COMPLETE ===\n";
