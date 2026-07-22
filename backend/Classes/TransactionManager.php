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
        try {
            $this->db->beginTransaction();

            // 1. Insert record with original type name (Restock, Sale, Waste)
            $stmt = $this->db->prepare("
                INSERT INTO transactions (item_id, transaction_type, quantity, user_id) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$item_id, $type, $qty, $user_id]);

            // 2. Sync Inventory - Restock adds, Sale/Waste subtracts
            $modifier = ($type === 'Restock') ? '+' : '-';
            $updateQuery = "UPDATE items SET quantity = quantity $modifier ? WHERE id = ?";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->execute([$qty, $item_id]);

            // 3. Sync the 'stock' table to match items.quantity
            $syncStmt = $this->db->prepare("
                UPDATE stock SET current_qty = (
                    SELECT quantity FROM items WHERE id = ?
                ) WHERE item_id = ?
            ");
            $syncStmt->execute([$item_id, $item_id]);

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