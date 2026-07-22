<?php
require_once __DIR__ . '/bootstrap.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/classes/Transaction.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
    try {
        $tx = new Transaction($pdo);
        
        // Ensure the fields match your HTML form names (item_id, type, quantity, reason)
        $tx->addTransaction(
            $_POST['item_id'],
            $_SESSION['user_id'], // Ensure this is set in login.php
            $_POST['type'],
            $_POST['quantity'],
            $_POST['reason']
        );
        
        // Success redirect
        header("Location: ../frontend/transactions.php?status=success");
        exit();
        
    } catch (Exception $e) {
        // Error handling
        echo "Error: " . $e->getMessage();
    }
} else {
    // If someone tries to access this file directly via URL
    header("Location: transactions.php");
    exit();
}
?>