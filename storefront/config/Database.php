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
