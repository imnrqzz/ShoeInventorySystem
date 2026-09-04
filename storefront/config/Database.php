<?php
/**
 * Database connection singleton for the storefront.
 * Reuses the same database (pos_inventory_system) as the admin panel.
 */
class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Load config from the shared inventory system config
            $config = require __DIR__ . '/../../backend/utils/config.php';

            // DEBUG: write resolved config to debug.txt
            $envPath = __DIR__ . '/../../storefront/.env';
            $rawEnv = @file_get_contents($envPath);
            $rawPreview = $rawEnv === false ? 'READ FAILED' : bin2hex(substr($rawEnv, 0, 60));
            @file_put_contents(
                __DIR__ . '/../../debug.txt',
                "DB_HOST=" . var_export($config['db_host'], true) . "\n" .
                "DB_NAME=" . var_export($config['db_name'], true) . "\n" .
                "DB_USER=" . var_export($config['db_username'], true) . "\n" .
                "getenv DB_HOST=" . var_export(getenv('DB_HOST'), true) . "\n" .
                "env file exists storefront/.env=" . var_export(file_exists($envPath), true) . "\n" .
                "env file size storefront/.env=" . var_export(@filesize($envPath), true) . "\n" .
                "env raw hex (first 60 bytes)=" . $rawPreview . "\n" .
                "PARSED ENV=" . var_export($env, true) . "\n" .
                "env loaded FROM=" . var_export($config['_env_loaded_from'] ?? 'NONE', true) . "\n"
            );

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['db_host'],
                $config['db_name'],
                $config['db_charset'] ?? 'utf8mb4'
            );

            self::$instance = new PDO($dsn, $config['db_username'], $config['db_password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }
}
