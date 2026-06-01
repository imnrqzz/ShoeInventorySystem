<?php
class Transaction {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($search = '', $type = 'All Types') {
        $query = "SELECT t.*, i.name as item_name, u.username as user_name 
                FROM transactions t 
                JOIN items i ON t.item_id = i.id 
                JOIN users u ON t.user_id = u.id 
                WHERE 1=1";

        $params = [];

        // Use positional placeholders (?) instead of named ones (:search)
        if (!empty($search)) {
            $query .= " AND (i.name LIKE ? 
                            OR t.transaction_type LIKE ? 
                            OR t.reason LIKE ? 
                            OR u.username LIKE ? 
                            OR CAST(t.id AS CHAR) LIKE ?)";
            // Add the SAME search term for every '?'
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

        $stmt = $this->conn->prepare($query);
        // Execute passing the array of values
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummaryByType() {
        $query = "SELECT transaction_type, COUNT(*) as count, SUM(quantity) as total_qty 
                  FROM transactions GROUP BY transaction_type";
        return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTransaction($item_id, $user_id, $type, $quantity, $reason) {
        // 1. Insert the transaction record
        $sql = "INSERT INTO transactions (item_id, user_id, transaction_type, quantity, reason, transaction_date) 
                VALUES (:item_id, :user_id, :type, :qty, :reason, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':item_id' => $item_id,
            ':user_id' => $user_id,
            ':type'    => $type,
            ':qty'     => $quantity,
            ':reason'  => $reason
        ]);

        // 2. Update stock levels based on transaction type
        // If it's a Sale or Waste, we subtract; if Restock, we add.
        $stockChange = ($type === 'Restock') ? $quantity : -$quantity;
        
        $updateStock = "UPDATE items SET quantity = quantity + (:change) WHERE id = :item_id";
        $stmtStock = $this->conn->prepare($updateStock);
        $stmtStock->execute([':change' => $stockChange, ':item_id' => $item_id]);
        
        return true;
    }
}