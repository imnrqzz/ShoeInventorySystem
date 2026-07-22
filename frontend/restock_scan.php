<?php
// frontend/restock_scan.php - Clean landing page for QR restock scans
// Scanned URL: /ShoeInventorySystem/frontend/restock_scan.php?id=1

require_once __DIR__ . '/../backend/Classes/Database.php';
require_once __DIR__ . '/../backend/Classes/TransactionManager.php';

$itemId = (int)($_GET['id'] ?? 0);
$success = false;
$message = '';
$itemName = '';
$newQty = 0;

if ($itemId > 0) {
    $db = new Database();
    $pdo = $db->getConnection();

    // Check item exists
    $stmt = $pdo->prepare("SELECT id, name, quantity FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();

    if ($item) {
        $transactionManager = new TransactionManager($pdo);
        $result = $transactionManager->logTransaction($itemId, 'Restock', 1, null, 'QR code scan restock');

        if ($result) {
            $stmt = $pdo->prepare("SELECT quantity FROM items WHERE id = ?");
            $stmt->execute([$itemId]);
            $updated = $stmt->fetch();
            $success = true;
            $itemName = $item['name'];
            $newQty = $updated['quantity'];
        } else {
            $message = 'Failed to restock. Please try again.';
        }
    } else {
        $message = 'Item not found.';
    }
} else {
    $message = 'Invalid item ID.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $success ? 'Restocked!' : 'Error' ?> - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px 32px;
            max-width: 360px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }
        .icon.success { background: #dcfce7; color: #16a34a; }
        .icon.error { background: #fee2e2; color: #dc2626; }
        h1 { font-size: 1.4rem; margin-bottom: 8px; color: #1a1a1a; }
        .item-name { font-size: 1rem; color: #555; margin-bottom: 4px; }
        .qty { font-size: 2rem; font-weight: 700; color: #16a34a; margin: 12px 0; }
        .qty span { font-size: 1rem; font-weight: 400; color: #999; }
        .msg { color: #666; font-size: 0.9rem; margin-bottom: 16px; }
        .brand { font-size: 0.75rem; color: #aaa; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($success): ?>
        <div class="icon success">&#10003;</div>
        <h1>Restocked!</h1>
        <p class="item-name"><?= htmlspecialchars($itemName) ?></p>
        <div class="qty"><?= $newQty ?> <span>units</span></div>
        <p class="msg">Stock increased by 1</p>
        <?php else: ?>
        <div class="icon error">&#10007;</div>
        <h1>Failed</h1>
        <p class="msg"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <p class="brand">ShoeInventory System</p>
    </div>
</body>
</html>
