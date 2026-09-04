<?php
require_once __DIR__ . '/../bootstrap.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /ShoeInventorySystem/frontend/login.php");
    exit();
}

require_once __DIR__ . '/../Classes/Transaction.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $tx = new Transaction($pdo);

        $itemId = (int)($_POST['item_id'] ?? 0);
        $type = trim($_POST['type'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($itemId <= 0 || $type === '' || $quantity <= 0) {
            header("Location: ../frontend/transactions.php?error=invalid");
            exit();
        }

        if (!in_array($type, ['Restock', 'Sold'], true)) {
            header("Location: ../frontend/transactions.php?error=invalid_type");
            exit();
        }

        $tx->addTransaction($itemId, $_SESSION['user_id'], $type, $quantity, $reason);
        header("Location: ../frontend/transactions.php?status=success");
        exit();

    } catch (Exception $e) {
        error_log("Transaction error: " . $e->getMessage());
        header("Location: ../frontend/transactions.php?error=failed");
        exit();
    }
} else {
    header("Location: ../frontend/transactions.php");
    exit();
}
