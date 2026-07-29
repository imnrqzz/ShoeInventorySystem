<?php
// backend/config.php - Database configuration
// For production: change these values or use environment variables

return [
    'db_host'     => getenv('DB_HOST') ?: 'localhost',
    'db_name'     => getenv('DB_NAME') ?: 'pos_inventory_system',
    'db_username' => getenv('DB_USER') ?: 'root',
    'db_password' => getenv('DB_PASS') ?: '',
    'db_charset'  => 'utf8mb4',

    // Security settings
    'session_secure'   => false,  // Set to true if using HTTPS
    'session_httponly'  => true,
    'session_samesite' => 'Lax',
    'session_path'     => '/ShoeInventorySystem',
];
