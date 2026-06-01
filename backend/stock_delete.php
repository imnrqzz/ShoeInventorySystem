<?php
// backend/stock_delete.php

// 1. Initialize system-wide database structural layers
require_once __DIR__ . '/classes/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// 2. Capture persistent search filters to maintain user context after deletion
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// 3. Perform secure parameterized multi-table deletion
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $stockId = (int)$_GET['id'];
    
    try {
        // Begin transaction to ensure atomic execution (all or nothing)
        $pdo->beginTransaction();

        // Step A: Find the associated item_id linked to this stock record
        $stmtLookUp = $pdo->prepare("SELECT item_id FROM stock WHERE id = ?");
        $stmtLookUp->execute([$stockId]);
        $stockRow = $stmtLookUp->fetch(PDO::FETCH_ASSOC);

        if ($stockRow) {
            $itemId = (int)$stockRow['item_id'];

            // Step B: Delete the operational row from the stock table
            $stmtDelStock = $pdo->prepare("DELETE FROM stock WHERE id = ?");
            $stmtDelStock->execute([$stockId]);

            // Step C: Delete the catalog row from the items table
            $stmtDelItem = $pdo->prepare("DELETE FROM items WHERE id = ?");
            $stmtDelItem->execute([$itemId]);
        }

        // Commit changes if everything executed perfectly
        $pdo->commit();
        
    } catch (\Exception $e) {
        // Roll back changes if any query crashes to prevent data corruption
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Inventory multi-table deletion failed: " . $e->getMessage());
    }
}

// 4. Build redirection URL to keep current filters active
$redirectUrl = '../frontend/stock.php';
$queryParams = [];

if ($search !== '') {
    $queryParams['search'] = $search;
}
if ($category !== '') {
    $queryParams['category'] = $category;
}

if (!empty($queryParams)) {
    $redirectUrl .= '?' . http_build_query($queryParams);
}

// 5. Redirect back to the stock view panel
header("Location: " . $redirectUrl);
exit;