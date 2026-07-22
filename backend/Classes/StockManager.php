<?php
// backend/classes/StockManager.php

class StockManager {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    // Auto-sync: create stock records for items missing them
    public function syncMissingStock() {
        $this->db->exec("
            INSERT IGNORE INTO stock (item_id, category, supplier_id, current_qty, min_threshold, unit)
            SELECT i.id, 'Shoes', i.supplier_id, i.quantity, i.min_quantity, 'pairs'
            FROM items i
            LEFT JOIN stock s ON i.id = s.item_id
            WHERE s.id IS NULL
        ");
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

            // Get current quantity for transaction logging
            $stmtCurrent = $this->db->prepare("SELECT quantity FROM items WHERE id = ?");
            $stmtCurrent->execute([(int)$itemId]);
            $currentItemQty = (int)$stmtCurrent->fetchColumn();
            $newQty = (int)$currentQty;
            $qtyDiff = $newQty - $currentItemQty;

            // 1. Synchronize the catalog table (items)
            $stmtItem = $this->db->prepare("UPDATE items SET name = ?, supplier_id = ?, quantity = ?, min_quantity = ? WHERE id = ?");
            $stmtItem->execute([$itemName, $supplierId ?: null, $newQty, (int)$minThreshold, (int)$itemId]);

            // 2. Synchronize the operational table (stock)
            $stmtStock = $this->db->prepare("UPDATE stock SET supplier_id = ?, current_qty = ?, min_threshold = ? WHERE id = ?");
            $stmtStock->execute([$supplierId ?: null, $currentQty, $minThreshold, (int)$stockId]);

            // 3. Synchronize corporate vendor profiles (suppliers)
            if (!empty($supplierId)) {
                $stmtSup = $this->db->prepare("UPDATE suppliers SET company_name = ? WHERE order_id = ?");
                $stmtSup->execute([$companyName, (int)$supplierId]);
            }

            // 4. Create transaction record if quantity changed
            if ($qtyDiff !== 0) {
                $userId = $_SESSION['user_id'] ?? null;
                if ($qtyDiff > 0) {
                    $txType = 'Restock';
                    $txQty = $qtyDiff;
                } else {
                    $txType = 'Sold';
                    $txQty = abs($qtyDiff);
                }
                $stmtTx = $this->db->prepare("INSERT INTO transactions (item_id, transaction_type, quantity, user_id, reason) VALUES (?, ?, ?, ?, ?)");
                $stmtTx->execute([(int)$itemId, $txType, $txQty, $userId, 'Stock adjustment via admin']);
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

    /**
     * Get all variants for a stock item
     */
    public function getVariantsByItemId($itemId) {
        $stmt = $this->db->prepare("
            SELECT iv.*, i.name AS item_name
            FROM item_variants iv
            JOIN items i ON iv.item_id = i.id
            WHERE iv.item_id = ?
            ORDER BY iv.color ASC, iv.size ASC
        ");
        $stmt->execute([(int)$itemId]);
        return $stmt->fetchAll();
    }

    /**
     * Update a variant's quantity
     */
    public function updateVariant($variantId, $color, $size, $quantity) {
        try {
            $this->db->beginTransaction();

            // Update the variant
            $stmt = $this->db->prepare("UPDATE item_variants SET color = ?, size = ?, quantity = ? WHERE id = ?");
            $stmt->execute([$color, $size, (int)$quantity, (int)$variantId]);

            // Get item_id for this variant
            $stmtItem = $this->db->prepare("SELECT item_id FROM item_variants WHERE id = ?");
            $stmtItem->execute([(int)$variantId]);
            $itemId = (int)$stmtItem->fetchColumn();

            // Sync total quantity to items and stock tables
            $stmtTotal = $this->db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM item_variants WHERE item_id = ?");
            $stmtTotal->execute([$itemId]);
            $totalQty = (int)$stmtTotal->fetchColumn();

            $this->db->prepare("UPDATE items SET quantity = ? WHERE id = ?")->execute([$totalQty, $itemId]);
            $this->db->prepare("UPDATE stock SET current_qty = ? WHERE item_id = ?")->execute([$totalQty, $itemId]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Variant update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update stock and all variants in one transaction
     */
    public function updateStockAndVariants($stockId, $itemId, $supplierId, $itemName, $companyName, $minThreshold, $variantIds, $variantColors, $variantSizes, $variantQuantities) {
        try {
            $this->db->beginTransaction();

            // Get old quantity for transaction logging
            $stmtOld = $this->db->prepare("SELECT quantity FROM items WHERE id = ?");
            $stmtOld->execute([(int)$itemId]);
            $oldQty = (int)$stmtOld->fetchColumn();

            // Update min threshold in items and stock
            $this->db->prepare("UPDATE items SET min_quantity = ? WHERE id = ?")->execute([(int)$minThreshold, (int)$itemId]);
            $this->db->prepare("UPDATE stock SET supplier_id = ?, min_threshold = ? WHERE id = ?")->execute([$supplierId ?: null, (int)$minThreshold, (int)$stockId]);

            // Update supplier name if provided
            if (!empty($supplierId) && !empty($companyName)) {
                $this->db->prepare("UPDATE suppliers SET company_name = ? WHERE order_id = ?")->execute([$companyName, (int)$supplierId]);
            }

            // Update all variants
            $newTotal = 0;
            for ($i = 0; $i < count($variantIds); $i++) {
                $vid = (int)$variantIds[$i];
                $color = trim($variantColors[$i] ?? '');
                $size = trim($variantSizes[$i] ?? '');
                $qty = (int)($variantQuantities[$i] ?? 0);

                if ($vid > 0) {
                    $this->db->prepare("UPDATE item_variants SET color = ?, size = ?, quantity = ? WHERE id = ?")->execute([$color, $size, $qty, $vid]);
                    $newTotal += $qty;
                }
            }

            // Sync total to items and stock
            $this->db->prepare("UPDATE items SET quantity = ? WHERE id = ?")->execute([$newTotal, (int)$itemId]);
            $this->db->prepare("UPDATE stock SET current_qty = ? WHERE item_id = ?")->execute([$newTotal, (int)$itemId]);

            // Create transaction if quantity changed
            $qtyDiff = $newTotal - $oldQty;
            if ($qtyDiff !== 0) {
                $userId = $_SESSION['user_id'] ?? null;
                $txType = $qtyDiff > 0 ? 'Restock' : 'Sold';
                $txQty = abs($qtyDiff);
                $this->db->prepare("INSERT INTO transactions (item_id, transaction_type, quantity, user_id, reason) VALUES (?, ?, ?, ?, ?)")
                    ->execute([(int)$itemId, $txType, $txQty, $userId, 'Stock adjustment via admin']);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Stock and variants update failed: " . $e->getMessage());
            return false;
        }
    }
}