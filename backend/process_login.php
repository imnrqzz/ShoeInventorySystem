<?php
// backend/process_login.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 1. Include your OOP architecture classes
require_once __DIR__ . '/classes/Database.php';

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

// 2. Instantiate your Database object and extract the PDO instance
$database = new Database();
$pdo = $database->getConnection();

try {
    // 3. Switch from MySQLi to secure PDO Prepared Statements as requested by the rubric
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch(); // Automatically retrieves associative array thanks to your PDO options

    // 4. Verify password hash matches safely
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        
        // Optional: If you have a role column for RBAC (Admin/User requirement)
        // $_SESSION['role'] = $user['role']; 

        header('Location: /PosInventorySystem/frontend/index.php'); 
        exit;
    }
} catch (\PDOException $e) {
    error_log("Login processing error: " . $e->getMessage());
}

// If authentication fails, redirect back with error parameter
header('Location: /PosInventorySystem/frontend/login.php?err=invalid');
exit;
