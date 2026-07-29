<?php
/**
 * OAuth configuration for the storefront.
 * Redirect URIs are scoped to /ShoeInventorySystem/storefront/
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost/ShoeInventorySystem/storefront', '/');
$oauthEnabled = filter_var($_ENV['OAUTH_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN);

return [
    'enabled'  => $oauthEnabled,
    'base_url' => $appUrl,
    'google' => [
        'client_id'     => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
        'redirect_path' => '/auth-google-callback',
        'auth_url'      => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url'     => 'https://oauth2.googleapis.com/token',
        'userinfo_url'  => 'https://www.googleapis.com/oauth2/v3/userinfo',
        'scope'         => 'openid email profile',
    ],
    'facebook' => [
        'app_id'        => $_ENV['FACEBOOK_APP_ID'] ?? '',
        'app_secret'    => $_ENV['FACEBOOK_APP_SECRET'] ?? '',
        'redirect_path' => '/auth-facebook-callback',
        'auth_url'      => 'https://www.facebook.com/v19.0/dialog/oauth',
        'token_url'     => 'https://graph.facebook.com/v19.0/oauth/access_token',
        'userinfo_url'  => 'https://graph.facebook.com/me',
        'scope'         => 'email,public_profile',
    ],
];
