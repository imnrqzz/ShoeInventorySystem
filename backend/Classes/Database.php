<?php
// backend/classes/Database.php

class Database {
    private $pdo = null;

    public function __construct() {
        $config = require __DIR__ . '/../config.php';

        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset={$config['db_charset']}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $config['db_username'], $config['db_password'], $options);
        } catch (\PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            die("<div style='font-family:sans-serif; padding:20px; background:#fff0f0; border-left:5px solid #ff4d4d; margin:20px;'><strong>Database Connection Failed!</strong></div>");
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}
