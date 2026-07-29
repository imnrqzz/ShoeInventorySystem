<?php
/**
 * Standalone CLI script to set up Google SMTP OAuth credentials.
 * Run: php storefront/scripts/setup_smtp_oauth.php
 */

require_once __DIR__ . '/../env.php';

$clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';

if (empty($clientId) || empty($clientSecret)) {
    echo "Error: GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET must be configured in storefront/.env first.\n";
    exit(1);
}

// Redirect URI specifically for the standalone callback file
$redirectUri = 'http://localhost/ShoeInventorySystem/storefront/oauth-smtp-callback.php';

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
$params = [
    'client_id'     => $clientId,
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'scope'         => 'https://mail.google.com/',
    'access_type'   => 'offline',
    'prompt'        => 'consent select_account',
];

$url = $authUrl . '?' . http_build_query($params);

echo "\n=================================================================\n";
echo "           SOLEHAUS SMTP GOOGLE OAUTH SETUP SCRIPT\n";
echo "=================================================================\n\n";
echo "1. Ensure you have registered the following Redirect URI in your\n";
echo "   Google Cloud Console (under APIs & Services > Credentials):\n\n";
echo "   " . $redirectUri . "\n\n";
echo "2. Open the following URL in your web browser:\n\n";
echo "   " . $url . "\n\n";
echo "3. Log in with the Gmail account you wish to send emails from\n";
echo "   and grant the requested permissions.\n\n";
echo "4. After authorization, you will be redirected to the callback page\n";
echo "   which will display the refresh token and instructions to update .env.\n";
echo "=================================================================\n\n";
