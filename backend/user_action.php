<?php
// backend/user_action.php

// Best Practice: Always start a session and verify login before handling user actions.
// This prevents unauthenticated users from deleting or modifying accounts.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: /ShoeInventorySystem/frontend/login.php');
    exit;
}

require_once 'classes/Database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Best Practice: Use the null coalescing operator (??) to safely access POST values.
    // This prevents "undefined index" PHP warnings if a field is missing.
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        // Best Practice: Cast user-supplied IDs to int to ensure they're valid numbers.
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: ../frontend/user.php");
        exit;
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        $stmt = $db->prepare("UPDATE users SET username = ?, name = ?, email = ?, status = ? WHERE id = ?");
        $stmt->execute([$username, $name, $email, $status, $id]);
        header("Location: ../frontend/user.php");
        exit;
    }
}

// Best Practice: If this file is accessed without a valid POST, redirect back.
header("Location: ../frontend/user.php");
exit;
?>
