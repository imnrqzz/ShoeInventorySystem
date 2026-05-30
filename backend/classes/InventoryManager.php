<?php
// backend/classes/InventoryManager.php

class InventoryManager {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    // Encapsulated safe query aggregator method
    public function getCount($sql) {
        try {
            $stmt = $this->db->query($sql);
            if ($stmt) {
                $row = $stmt->fetch();
                return $row ? (int)$row['cnt'] : 0;
            }
        } catch (Exception $e) {
            return 0;
        }
        return 0;
    }

    // Encapsulated safe multi-row fetcher method
    public function getRows($sql) {
        try {
            $stmt = $this->db->query($sql);
            return $stmt ? $stmt->fetchAll() : [];
        } catch (Exception $e) {
            return [];
        }
    }
}