<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: register.php'); exit; }
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
if ($username === '' || $password === '') { header('Location: register.php?err=1'); exit; }
// check exists
$stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s',$username); $stmt->execute(); $stmt->store_result();
if ($stmt->num_rows > 0) { $stmt->close(); header('Location: register.php?err=exists'); exit; }
$stmt->close();
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
$stmt->bind_param('ss',$username,$hash);
if ($stmt->execute()) {
    $stmt->close();
    header('Location: login.php?registered=1');
    exit;
}
$stmt->close();
header('Location: register.php?err=save');
