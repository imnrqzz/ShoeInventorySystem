<?php
// api/items.php - Items CRUD endpoint
// GET    /api/items          - List all items (?search=)
// GET    /api/items/{id}     - Get single item
// POST   /api/items          - Create item
// PUT    /api/items/{id}     - Update item
// DELETE /api/items/{id}     - Delete item

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../backend/Classes/ItemManager.php';

$userId = requireApiAuth();
$pdo = getApiDb();
$itemManager = new ItemManager($pdo);

switch ($method) {
    case 'GET':
        if ($id !== null) {
            $item = $itemManager->getItemById($id);
            if (!$item) {
                jsonError('Item not found', 404);
            }
            jsonSuccess($item);
        }

        $search = $_GET['search'] ?? '';
        $items = $itemManager->getAllItems($search);
        jsonSuccess($items);
        break;

    case 'POST':
        $input = getInput();
        $name = trim($input['name'] ?? '');
        $supplierId = $input['supplier_id'] ?? null;
        $minQuantity = (int) ($input['min_quantity'] ?? 5);
        $price = (float) ($input['price'] ?? 0);

        if ($name === '') {
            jsonError('Item name is required');
        }

        if ($supplierId !== null) {
            $supplierId = (int) $supplierId;
        }

        $result = $itemManager->addItem($name, $supplierId, $minQuantity, $price);
        if (!$result) {
            jsonError('Failed to create item', 500);
        }

        $newId = $itemManager->getNextAvailableId() - 1;
        $newItem = $itemManager->getItemById($newId);
        jsonResponse(['success' => true, 'data' => $newItem, 'message' => 'Item created'], 201);
        break;

    case 'PUT':
        if ($id === null) {
            jsonError('Item ID is required');
        }

        $existing = $itemManager->getItemById($id);
        if (!$existing) {
            jsonError('Item not found', 404);
        }

        $input = getInput();
        $name = trim($input['name'] ?? $existing['name']);
        $supplierId = $input['supplier_id'] ?? $existing['supplier_id'];
        $minQuantity = (int) ($input['min_quantity'] ?? $existing['min_quantity']);
        $price = (float) ($input['price'] ?? $existing['price']);

        if ($supplierId !== null) {
            $supplierId = (int) $supplierId;
        }

        $result = $itemManager->updateItem($id, $name, $supplierId, $minQuantity, $price);
        if (!$result) {
            jsonError('Failed to update item', 500);
        }

        $updated = $itemManager->getItemById($id);
        jsonSuccess($updated, 'Item updated');
        break;

    case 'DELETE':
        if ($id === null) {
            jsonError('Item ID is required');
        }

        $existing = $itemManager->getItemById($id);
        if (!$existing) {
            jsonError('Item not found', 404);
        }

        $result = $itemManager->deleteItem($id);
        if (!$result) {
            jsonError('Failed to delete item', 500);
        }

        jsonSuccess(null, 'Item deleted');
        break;

    default:
        jsonError('Method not allowed', 405);
        break;
}
