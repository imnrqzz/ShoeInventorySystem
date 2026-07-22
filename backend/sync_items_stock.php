<?php
// backend/sync_items_stock.php - Sync items missing stock records (admin only)
require_once __DIR__ . '/bootstrap.php';
requireLogin();
requireAdmin();

$db = new Database();
$pdo = $db->getConnection();

try {
    $stmt = $pdo->query("
        SELECT i.id, i.name, i.supplier_id, i.min_quantity, i.quantity
        FROM items i
        LEFT JOIN stock s ON i.id = s.item_id
        WHERE s.id IS NULL
    ");
    $missing = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($missing)) {
        echo "All items already have stock records.<br>";
    } else {
        $count = 0;
        foreach ($missing as $item) {
            $stockStmt = $pdo->prepare(
                "INSERT INTO stock (item_id, category, supplier_id, current_qty, min_threshold, unit)
                 VALUES (?, 'Shoes', ?, ?, ?, 'pairs')"
            );
            $stockStmt->execute([
                $item['id'],
                $item['supplier_id'],
                $item['quantity'],
                $item['min_quantity']
            ]);
            echo "Created stock record for: " . htmlspecialchars($item['name']) . " (ID: " . (int)$item['id'] . ")<br>";
            $count++;
        }
        echo "<br>Done! Created $count stock record(s).";
    }

    echo "<br><a href='../frontend/stock.php'>Go to Stock Page</a>";
    echo " | <a href='../frontend/item.php'>Go to Items Page</a>";

} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
