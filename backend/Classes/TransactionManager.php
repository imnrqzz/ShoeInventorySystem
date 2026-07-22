<?php
// backend/classes/TransactionManager.php

class TransactionManager {
    private $db;

    /**
     * Constructor injects the PDO connection
     */
    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    /**
     * CREATE: Log Transaction & Update Inventory
     * Uses a transaction block to ensure data integrity
     */
    public function logTransaction($item_id, $type, $qty, $user_id, $reason = '') {
        // Map frontend types to database ENUM values ('in', 'out', 'adjust')
        $type_map = ['Restock' => 'in', 'Sale' => 'out', 'Waste' => 'adjust'];
        $db_type = $type_map[$type] ?? 'adjust';

        try {
            $this->db->beginTransaction();

            // 1. Insert record into transactions table
            $stmt = $this->db->prepare("
                INSERT INTO transactions (item_id, transaction_type, quantity, user_id) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$item_id, $db_type, $qty, $user_id]);

            // 2. Sync Inventory in the 'items' table
            // Restock ('in') adds quantity, others subtract
            $modifier = ($db_type === 'in') ? '+' : '-';
            $updateQuery = "UPDATE items SET quantity = quantity $modifier ? WHERE id = ?";
            
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->execute([$qty, $item_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Transaction Failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * READ: Fetch all transactions with joined item and user details
     * This query uses INNER/LEFT JOINS to retrieve readable data for your UI
     */
    public function getAllTransactions() {
        $query = "SELECT t.*, i.name as item_name, u.username 
                  FROM transactions t 
                  JOIN items i ON t.item_id = i.id 
                  LEFT JOIN users u ON t.user_id = u.id 
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * DELETE: Remove a specific transaction record
     */
    public function deleteTransaction($id) {
        $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = ?");
        return $stmt->execute([intval($id)]);
    }
}