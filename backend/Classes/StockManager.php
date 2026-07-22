<?php
// backend/classes/StockManager.php

class StockManager {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    public function getTotalItemsCount() {
        return (int)$this->db->query("SELECT COUNT(*) FROM stock")->fetchColumn();
    }

    public function getOkStockCount() {
        return (int)$this->db->query("SELECT COUNT(*) FROM stock WHERE current_qty >= min_threshold")->fetchColumn();
    }

    public function getDistinctCategories() {
        return $this->db->query("SELECT DISTINCT category FROM stock WHERE category IS NOT NULL AND category != '' ORDER BY category ASC")->fetchAll();
    }

    public function getAllSuppliers() {
        return $this->db->query("SELECT order_id AS id, company_name FROM suppliers ORDER BY company_name ASC")->fetchAll();
    }

    public function getFilteredStock($filters) {
        $queryStr = "SELECT s.*, i.name AS item_name, COALESCE(sup.company_name, 'None') AS supplier_name 
                     FROM stock s 
                     JOIN items i ON s.item_id = i.id
                     LEFT JOIN suppliers sup ON s.supplier_id = sup.order_id 
                     WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $queryStr .= " AND i.name LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category']) && $filters['category'] !== 'All Categories') {
            $queryStr .= " AND s.category = :category";
            $params['category'] = $filters['category'];
        }

        $stmt = $this->db->prepare($queryStr . " ORDER BY s.id DESC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getStockById($id) {
        $stmt = $this->db->prepare("
            SELECT s.*, i.name AS item_name, COALESCE(sup.company_name, 'None') AS supplier_name 
            FROM stock s 
            JOIN items i ON s.item_id = i.id 
            LEFT JOIN suppliers sup ON s.supplier_id = sup.order_id
            WHERE s.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function updateGlobalInventorySync($stockId, $itemId, $supplierId, $itemName, $companyName, $currentQty, $minThreshold) {
        try {
            $this->db->beginTransaction();

            // 1. Synchronize the catalog table (items)
            $stmtItem = $this->db->prepare("UPDATE items SET name = ?, supplier_id = ?, quantity = ?, min_quantity = ? WHERE id = ?");
            $stmtItem->execute([$itemName, $supplierId ?: null, (int)$currentQty, (int)$minThreshold, (int)$itemId]);

            // 2. Synchronize the operational table (stock)
            $stmtStock = $this->db->prepare("UPDATE stock SET supplier_id = ?, current_qty = ?, min_threshold = ? WHERE id = ?");
            $stmtStock->execute([$supplierId ?: null, $currentQty, $minThreshold, (int)$stockId]);

            // 3. Synchronize corporate vendor profiles (suppliers)
            if (!empty($supplierId)) {
                $stmtSup = $this->db->prepare("UPDATE suppliers SET company_name = ? WHERE order_id = ?");
                $stmtSup->execute([$companyName, (int)$supplierId]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Global multi-table atomic operation crashed: " . $e->getMessage());
            return false;
        }
    }
}