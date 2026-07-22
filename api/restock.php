<?php
// api/restock.php - Quick restock endpoint (no auth needed for QR scanning)
// GET /api/restock.php?id={item_id} - Add 1 to stock for the item
// Works from any device scanning the QR code

require_once __DIR__ . '/../backend/Classes/Database.php';
require_once __DIR__ . '/../backend/Classes/TransactionManager.php';

header('Content-Type: application/json; charset=utf-8');

$itemId = (int)($_GET['id'] ?? 0);

if ($itemId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

// Check item exists
$stmt = $pdo->prepare("SELECT id, name, quantity FROM items WHERE id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit;
}

// Log restock transaction (adds 1 to quantity)
$transactionManager = new TransactionManager($pdo);
$result = $transactionManager->logTransaction($itemId, 'Restock', 1, null, 'QR code scan restock');

if ($result) {
    // Get updated quantity
    $stmt = $pdo->prepare("SELECT quantity FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $updated = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => "Restocked: {$item['name']}",
        'item_id' => $itemId,
        'item_name' => $item['name'],
        'previous_qty' => $item['quantity'],
        'new_qty' => $updated['quantity']
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to restock']);
}
