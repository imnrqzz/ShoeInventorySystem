<?php
// backend/process_register.php

// 1. Include your OOP architecture classes safely
require_once __DIR__ . '/classes/Database.php';

// Ensure this script only accepts POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: ../frontend/register.php'); 
    exit; 
}

// 2. Capture and sanitize inputs
$name            = trim($_POST['name'] ?? '');
$email           = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$username        = trim($_POST['username'] ?? '');
$password        = $_POST['password'] ?? '';
$repeatPassword  = $_POST['repeatpassword'] ?? '';

// Best Practice: Server-side validation is the real security layer.
// Client-side (JavaScript) validation can be bypassed, so we must
// re-check every rule here on the server before touching the database.

// Check all required fields are present and email is valid format
if ($name === '' || $username === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../frontend/register.php?err=invalid_input');
    exit;
}

// Validate username length (min 3 characters)
if (strlen($username) < 3) {
    header('Location: ../frontend/register.php?err=username_short');
    exit;
}

// Validate password length (min 6 characters)
if (strlen($password) < 6) {
    header('Location: ../frontend/register.php?err=password_short');
    exit;
}

// Best Practice: Always verify password confirmation matches on the server.
// Even though JavaScript checks this, a user could submit the form directly.
if ($password !== $repeatPassword) {
    header('Location: ../frontend/register.php?err=password_mismatch');
    exit;
}

// 3. Instantiate your Database class wrapper and fetch the PDO handle
$database = new Database();
$pdo = $database->getConnection();

try {
    // 4. Check if user or email already exists using clean PDO Prepared Statements
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) { 
        header('Location: ../frontend/register.php?err=exists'); 
        exit; 
    }

    // 5. Securely hash the password string
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // 6. Insert new user record into the database
    // Ensure your 'users' table has the 'name' and 'email' columns
    $sql = 'INSERT INTO users (name, email, username, password_hash, role) VALUES (?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$name, $email, $username, $hash, 'User'])) {
        header('Location: ../frontend/login.php?registered=1');
        exit;
    }
    
} catch (\PDOException $e) {
    // Log database errors internally for debugging
    error_log("Registration error: " . $e->getMessage());
    header('Location: ../frontend/register.php?err=server_error');
    exit;
}

// Fallback error redirection
header('Location: ../frontend/register.php?err=save');
exit;