<?php
// backend/process_user.php

// Best Practice: Session check before any data modification.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: /ShoeInventorySystem/frontend/login.php');
    exit;
}

require_once 'classes/Database.php';
require_once 'classes/UserManager.php';

$db = new Database();
$userMgr = new UserManager($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Best Practice: Use ?? operator to provide safe defaults for all POST data.
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = trim($_POST['role'] ?? 'User');

        // Best Practice: Validate required fields are not empty before database operations.
        if ($username !== '' && $password !== '') {
            $userMgr->addUser($username, $password, $role);
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $userMgr->deleteUser($id);
        }
    }

    header("Location: ../frontend/user.php");
    exit;
}

header("Location: ../frontend/user.php");
exit;
?>
