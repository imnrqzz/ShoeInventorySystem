<?php
// backend/process_user.php

require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['username']) || !isAdmin()) {
    header('Location: /ShoeInventorySystem/frontend/login.php');
    exit;
}

require_once __DIR__ . '/classes/UserManager.php';
$userMgr = new UserManager($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'User');
        $status = trim($_POST['status'] ?? 'Active');

        if ($username !== '' && $password !== '' && $name !== '' && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $userMgr->addUser($username, $password, $name, $email, $role, $status);
        }
    } elseif ($action === 'disable' || $action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $userMgr->disableUser($id);
        }
    }

    header("Location: ../frontend/user.php");
    exit;
}

header("Location: ../frontend/user.php");
exit;
?>
