<?php
// backend/process_register.php

// 1. Include your OOP architecture classes safely
require_once __DIR__ . '/classes/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: register.php'); 
    exit; 
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') { 
    header('Location: register.php?err=1'); 
    exit; 
}

// 2. Instantiate your Database class wrapper and fetch the PDO handle
$database = new Database();
$pdo = $database->getConnection();

try {
    // 3. Check if user already exists using clean PDO Prepared Statements
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) { 
        header('Location: register.php?err=exists'); 
        exit; 
    }

    // 4. Securely hash the password string
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert new user record into the database
    // Note: If you have your Role-Based Access Control column ready, include it here (e.g., 'User')
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
    
    if ($stmt->execute([$username, $hash, 'User'])) {
        header('Location: login.php?registered=1');
        exit;
    }
    
} catch (\PDOException $e) {
    // Gracefully catch database exceptions and log them
    error_log("Registration error processing: " . $e->getMessage());
}

// Default fallback error redirection state
header('Location: register.php?err=save');
exit;
