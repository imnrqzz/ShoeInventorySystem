<?php
// backend/process_register.php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/validate.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/register.php');
    exit;
}

verify_csrf();
$name            = trim($_POST['name'] ?? '');
$email           = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$username        = trim($_POST['username'] ?? '');
$password        = $_POST['password'] ?? '';
$repeatPassword  = $_POST['repeatpassword'] ?? '';

// Validate all fields using the shared validation rules
$errors = validateForm('register', $_POST);
if ($errors) {
    if (isset($errors['email']) || isset($errors['name']) || isset($errors['username'])) {
        header('Location: ../frontend/register.php?err=invalid_input');
    } elseif (isset($errors['password']) && strpos($errors['password'][0], 'at least') !== false) {
        header('Location: ../frontend/register.php?err=password_short');
    }
    exit;
}

if ($password !== $repeatPassword) {
    header('Location: ../frontend/register.php?err=password_mismatch');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) { 
        header('Location: ../frontend/register.php?err=exists'); 
        exit; 
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = 'INSERT INTO users (name, email, username, password_hash, role) VALUES (?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$name, $email, $username, $hash, 'Staff'])) {
        header('Location: ../frontend/login.php?registered=1');
        exit;
    }
    
} catch (\PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    header('Location: ../frontend/register.php?err=server_error');
    exit;
}

header('Location: ../frontend/register.php?err=save');
exit;