<?php
// backend/classes/Database.php

class Database {
    private $host = 'localhost';
    private $db_name = 'pos_inventory_system';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $pdo = null;

    // The Constructor handles internal auto-initialization securely
    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (\PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            die("<div style='font-family:sans-serif; padding:20px; background:#fff0f0; border-left:5px solid #ff4d4d; margin:20px;'><strong>Database Connection Failed!</strong></div>");
        }
    }

    // Encapsulation: Provides public access to the private PDO handle
    public function getConnection() {
        return $this->pdo;
    }
}