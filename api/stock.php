<?php
// api/stock.php - Stock CRUD endpoint
// GET    /api/stock          - List all stock (?search=&category=)
// GET    /api/stock/{id}     - Get single stock record
// POST   /api/stock          - Create stock record
// PUT    /api/stock/{id}     - Update stock (atomic 3-table sync)
// DELETE /api/stock/{id}     - Delete stock

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../backend/Classes/StockManager.php';
require_once __DIR__ . '/../backend/Classes/ItemManager.php';
require_once __DIR__ . '/../backend/Classes/SupplierManager.php';

$userId = requireApiAuth();
$db = new Database();
$pdo = $db->getConnection();
$stockManager = new StockManager($pdo);

// Auto-sync missing stock records
$stockManager->syncMissingStock();

switch ($method) {
    case 'GET':
        if ($id !== null) {
            $stock = $stockManager->getStockById($id);
            if (!$stock) {
                jsonError('Stock record not found', 404);
            }
            jsonSuccess($stock);
        }

        $filters = [
            'search' => $_GET['search'] ?? '',
            'category' => $_GET['category'] ?? '',
        ];
        $stock = $stockManager->getFilteredStock($filters);
        jsonSuccess($stock);
        break;

    case 'POST':
        $input = getInput();
        $itemId = (int) ($input['item_id'] ?? 0);
        $supplierId = !empty($input['supplier_id']) ? (int) $input['supplier_id'] : null;
        $category = trim($input['category'] ?? 'Shoes');
        $currentQty = (float) ($input['current_qty'] ?? 0);
        $minThreshold = (float) ($input['min_threshold'] ?? 0);
        $unit = trim($input['unit'] ?? 'pairs');

        if ($itemId <= 0) {
            jsonError('item_id is required');
        }

        // Verify item exists
        $itemCheck = $pdo->prepare("SELECT id FROM items WHERE id = ?");
        $itemCheck->execute([$itemId]);
        if (!$itemCheck->fetch()) {
            jsonError('Item not found', 404);
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO stock (item_id, category, supplier_id, current_qty, min_threshold, unit)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$itemId, $category, $supplierId, $currentQty, $minThreshold, $unit]);
            $newId = (int) $pdo->lastInsertId();

            // Sync items table
            $stmtItem = $pdo->prepare("UPDATE items SET quantity = ?, min_quantity = ?, supplier_id = ? WHERE id = ?");
            $stmtItem->execute([(int) $currentQty, (int) $minThreshold, $supplierId, $itemId]);

            $pdo->commit();

            $newStock = $stockManager->getStockById($newId);
            jsonResponse(['success' => true, 'data' => $newStock, 'message' => 'Stock created'], 201);
        } catch (\Exception $e) {
            $pdo->rollBack();
            jsonError('Failed to create stock: ' . $e->getMessage(), 500);
        }
        break;

    case 'PUT':
        if ($id === null) {
            jsonError('Stock ID is required');
        }

        $existing = $stockManager->getStockById($id);
        if (!$existing) {
            jsonError('Stock record not found', 404);
        }

        $input = getInput();
        $itemId = (int) ($input['item_id'] ?? $existing['item_id']);
        $supplierId = !empty($input['supplier_id']) ? (int) $input['supplier_id'] : ($existing['supplier_id'] ?? null);
        $itemName = trim($input['item_name'] ?? $existing['item_name']);
        $companyName = trim($input['company_name'] ?? $existing['supplier_name']);
        $currentQty = (float) ($input['current_qty'] ?? $existing['current_qty']);
        $minThreshold = (float) ($input['min_threshold'] ?? $existing['min_threshold']);

        $result = $stockManager->updateGlobalInventorySync(
            $id, $itemId, $supplierId, $itemName, $companyName, $currentQty, $minThreshold
        );

        if (!$result) {
            jsonError('Failed to update stock', 500);
        }

        $updated = $stockManager->getStockById($id);
        jsonSuccess($updated, 'Stock updated');
        break;

    case 'DELETE':
        if ($id === null) {
            jsonError('Stock ID is required');
        }

        $existing = $stockManager->getStockById($id);
        if (!$existing) {
            jsonError('Stock record not found', 404);
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("DELETE FROM stock WHERE id = ?");
            $stmt->execute([$id]);

            // Update items quantity to match remaining stock
            $stmtItem = $pdo->prepare(
                "UPDATE items SET quantity = (SELECT COALESCE(SUM(current_qty), 0) FROM stock WHERE item_id = ?) WHERE id = ?"
            );
            $stmtItem->execute([$existing['item_id'], $existing['item_id']]);

            $pdo->commit();
            jsonSuccess(null, 'Stock deleted');
        } catch (\Exception $e) {
            $pdo->rollBack();
            jsonError('Failed to delete stock: ' . $e->getMessage(), 500);
        }
        break;

    default:
        jsonError('Method not allowed', 405);
        break;
}
