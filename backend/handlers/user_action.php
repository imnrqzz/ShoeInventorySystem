<?php
// backend/handlers/user_action.php

require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['username'])) {
    header('Location: /ShoeInventorySystem/frontend/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    requireAdmin();
    $action = $_POST['action'] ?? '';

    if ($action === 'disable' || $action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute(['Inactive', $id]);
        }
        header("Location: ../frontend/user.php");
        exit;
    } elseif ($action === 'enable') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute(['Active', $id]);
        }
        header("Location: ../frontend/user.php");
        exit;
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        if ($id > 0 && $username !== '' && $email !== '') {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, name = ?, email = ?, status = ? WHERE id = ?");
            $stmt->execute([$username, $name, $email, $status, $id]);
        }
        header("Location: ../frontend/user.php");
        exit;
    }
}

header("Location: ../frontend/user.php");
exit;
