<?php
// backend/get_variants.php - Fetch variants for an item
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$itemId = (int)($_GET['item_id'] ?? 0);
if ($itemId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

try {
    // Get item info
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }

    // Get variants grouped by color
    $stmt = $pdo->prepare("
        SELECT color, size, quantity 
        FROM item_variants 
        WHERE item_id = ? 
        ORDER BY color ASC, 
        CASE 
            WHEN size LIKE 'US %' THEN CAST(SUBSTRING(size, 4) AS DECIMAL(10,1))
            ELSE 0 
        END ASC
    ");
    $stmt->execute([$itemId]);
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by color and calculate total
    $colors = [];
    $totalVariantQty = 0;
    foreach ($variants as $v) {
        $color = $v['color'];
        if (!isset($colors[$color])) {
            $colors[$color] = [];
        }
        $qty = (int)$v['quantity'];
        $totalVariantQty += $qty;
        $colors[$color][] = [
            'size' => $v['size'],
            'quantity' => $qty,
            'in_stock' => $qty > 0
        ];
    }

    echo json_encode([
        'success' => true,
        'item' => $item,
        'colors' => $colors,
        'total_variant_qty' => $totalVariantQty
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
