<?php
class Transaction {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    public function getAll($search = '', $type = 'All Types') {
        $query = "SELECT t.*, i.name as item_name, u.username as user_name 
                FROM transactions t 
                JOIN items i ON t.item_id = i.id 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $query .= " AND (i.name LIKE ? 
                            OR t.transaction_type LIKE ? 
                            OR t.reason LIKE ? 
                            OR u.username LIKE ? 
                            OR CAST(t.id AS CHAR) LIKE ?)";
            $searchTerm = "%" . trim($search) . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($type !== 'All Types' && !empty($type)) {
            $query .= " AND t.transaction_type = ?";
            $params[] = $type;
        }
        
        $query .= " ORDER BY t.transaction_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummaryByType() {
        $query = "SELECT transaction_type, COUNT(*) as count, SUM(quantity) as total_qty 
                  FROM transactions GROUP BY transaction_type";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTransaction($item_id, $user_id, $type, $quantity, $reason) {
        $this->db->beginTransaction();
        try {
            // 1. Insert transaction record
            $stmt = $this->db->prepare("
                INSERT INTO transactions (item_id, user_id, transaction_type, quantity, reason, transaction_date) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$item_id, $user_id, $type, $quantity, $reason]);

            // 2. Update items.quantity
            $stockChange = ($type === 'Restock') ? $quantity : -$quantity;
            $stmtItem = $this->db->prepare("UPDATE items SET quantity = quantity + ? WHERE id = ?");
            $stmtItem->execute([$stockChange, $item_id]);

            // 3. Sync stock.current_qty to match items.quantity
            $stmtStock = $this->db->prepare("
                UPDATE stock SET current_qty = (
                    SELECT quantity FROM items WHERE id = ?
                ) WHERE item_id = ?
            ");
            $stmtStock->execute([$item_id, $item_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Transaction Failed: " . $e->getMessage());
            return false;
        }
    }
}
