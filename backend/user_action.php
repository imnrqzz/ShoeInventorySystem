<?php
// backend/user_action.php

require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['username'])) {
    header('Location: /ShoeInventorySystem/frontend/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('CSRF DEBUG: POST token=' . ($_POST['_csrf_token'] ?? 'EMPTY') . ' Session token=' . ($_SESSION['csrf_token'] ?? 'NONE') . ' POST=' . json_encode($_POST));
    verify_csrf();
    requireAdmin();
    // This prevents "undefined index" PHP warnings if a field is missing.
    $action = $_POST['action'] ?? '';

    if ($action === 'disable' || $action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute(['Inactive', $id]);
        header("Location: ../frontend/user.php");
        exit;
    } elseif ($action === 'enable') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute(['Active', $id]);
        header("Location: ../frontend/user.php");
        exit;
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        $stmt = $pdo->prepare("UPDATE users SET username = ?, name = ?, email = ?, status = ? WHERE id = ?");
        $stmt->execute([$username, $name, $email, $status, $id]);
        header("Location: ../frontend/user.php");
        exit;
    }
}

header("Location: ../frontend/user.php");
exit;
?>
