<?php
// delete.php

// Database Configuration
$host    = 'localhost';
$db      = 'shoes_inventory';
$user    = 'root'; 
$pass    = ''; // Update with your DB password if applicable
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if an ID parameter is provided in the URL query string
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        // Prepare a secure parameterized delete statement
        $stmt = $pdo->prepare("DELETE FROM stock WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
    } catch (\PDOException $e) {
        die("Error deleting record: " . $e->getMessage());
    }
}

// Redirect back to the main management panel immediately after execution
header("Location: stock.php");
exit;   
