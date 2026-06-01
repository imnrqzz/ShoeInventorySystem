<?php
require_once 'classes/Database.php';
require_once 'classes/UserManager.php';

$db = new Database();
$userMgr = new UserManager($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $userMgr->addUser($_POST['username'], $_POST['password'], $_POST['role']);
    } elseif ($action === 'delete') {
        $userMgr->deleteUser($_POST['id']);
    }
    // Add logic for update similarly
    header("Location: ../frontend/users.php");
}
?>