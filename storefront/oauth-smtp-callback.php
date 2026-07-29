<?php
/**
 * Standalone callback receiver for the one-time SMTP OAuth flow.
 * Place in storefront/oauth-smtp-callback.php.
 * IMPORTANT: Delete or move this file outside the web root after setup.
 */

require_once __DIR__ . '/env.php';

$clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
$redirectUri = 'http://localhost/ShoeInventorySystem/storefront/oauth-smtp-callback.php';

$code = $_GET['code'] ?? null;
$error = $_GET['error'] ?? null;

if ($error) {
    http_response_code(400);
    die("<h1>OAuth Error</h1><p>Authorization failed: " . htmlspecialchars($error) . "</p>");
}

if (!$code || !is_string($code)) {
    http_response_code(400);
    die("<h1>Invalid Request</h1><p>Missing or invalid authorization code.</p>");
}

// Exchange code for tokens
$tokenUrl = 'https://oauth2.googleapis.com/token';
$fields = [
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'code'          => $code,
    'grant_type'    => 'authorization_code',
    'redirect_uri'  => $redirectUri,
];

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($fields),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 15,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    die("<h1>OAuth Error</h1><p>Failed to connect to Google OAuth service.</p>");
}

$tokenResponse = json_decode((string)$response, true);

if (!is_array($tokenResponse) || isset($tokenResponse['error'])) {
    $errorMsg = is_array($tokenResponse) && isset($tokenResponse['error_description'])
        ? htmlspecialchars($tokenResponse['error_description'])
        : 'Token exchange failed.';
    http_response_code(400);
    die("<h1>OAuth Error</h1><p>" . $errorMsg . "</p>");
}

if (empty($tokenResponse['refresh_token'])) {
    http_response_code(400);
    echo "<h1>OAuth Successful, but No Refresh Token Returned</h1>";
    echo "<p>Google only returns a refresh token on the first consent flow. If this account was authorized previously, you must either:</p>";
    echo "<ul>";
    echo "  <li>Revoke access to the application in your Google Account Security Settings and try again, or</li>";
    echo "  <li>Force consent by setting <code>prompt=consent</code> (which is included in the CLI script instructions).</li>";
    echo "</ul>";
    echo "<p>Access Token returned: <code>" . htmlspecialchars($tokenResponse['access_token'] ?? 'none') . "</code></p>";
    exit;
}

// Display the refresh token to copy
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SMTP OAuth Complete</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #fafafa; color: #333; padding: 40px; }
        .container { max-width: 650px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin: 0 auto; border: 1px solid #eaeaea; }
        h1 { color: #2e7d32; font-size: 1.6rem; margin-top: 0; }
        code { background: #f5f5f5; padding: 3px 6px; border-radius: 4px; font-family: monospace; font-size: 0.95rem; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 6px; border: 1px solid #e0e0e0; font-weight: bold; font-size: 0.95rem; overflow-x: auto; }
        .warning { background: #fff3cd; border: 1px solid #ffebaa; border-left: 6px solid #dc3545; padding: 18px; margin: 24px 0; border-radius: 6px; font-size: 0.95rem; color: #333; }
        .warning-title { font-weight: bold; color: #721c24; font-size: 1.1rem; margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="container">
    <h1>SMTP OAuth Configuration Complete!</h1>
    <p>Use the credentials below to configure your <code>storefront/.env</code> file:</p>
    
    <pre>GMAIL_SMTP_USER=YOUR_GMAIL_ADDRESS@gmail.com
GMAIL_SMTP_REFRESH_TOKEN=<?= htmlspecialchars($tokenResponse['refresh_token']) ?></pre>

    <div class="warning">
        <div class="warning-title">⚠️ SETUP COMPLETE — Delete or move this file (oauth-smtp-callback.php) out of the web root now. It should not remain accessible.</div>
        <p style="margin: 6px 0 0 0; color: #444;">Once you have copied the <code>GMAIL_SMTP_REFRESH_TOKEN</code> into your <code>storefront/.env</code> file, remove this <code>storefront/oauth-smtp-callback.php</code> file from your web server directory to maintain application security.</p>
    </div>

    <p><a href="index.php">Go to Storefront Home</a></p>
</div>
</body>
</html>
