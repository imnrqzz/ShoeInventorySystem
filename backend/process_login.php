<?php
session_start();

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: /PosInventorySystem/frontend/login.php'); 
    exit; 
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') { 
    header('Location: /PosInventorySystem/frontend/login.php?err=1'); 
    exit; 
}

$stmt = $mysqli->prepare('SELECT id, password_hash FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username); 
$stmt->execute(); 
$stmt->bind_result($id, $hash);

if ($stmt->fetch() && password_verify($password, $hash)) {
    $_SESSION['user_id'] = $id;
    $_SESSION['username'] = $username;
    $stmt->close();
    header('Location: /PosInventorySystem/frontend/index.php'); 
    exit;
}

$stmt->close();
header('Location: /PosInventorySystem/frontend/login.php?err=invalid');
exit;