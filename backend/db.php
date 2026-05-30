<?php
// backend/db.php

// Start session for auth across pages if not already active
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Dynamically reference our object-oriented class architecture
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/InventoryManager.php';

// Instantiate Objects
$database = new Database();
$pdo = $database->getConnection();
$manager = new InventoryManager($pdo);

// Global safe escaping string utility function
function safe($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// 1. Fetch Stats Counters using OOP methods
$totalItems        = $manager->getCount('SELECT COUNT(*) AS cnt FROM items');
$activeSuppliers   = $manager->getCount("SELECT COUNT(*) AS cnt FROM suppliers WHERE status='Active'");
$systemUsers       = $manager->getCount('SELECT COUNT(*) AS cnt FROM users');
$transactionsCount = $manager->getCount('SELECT COUNT(*) AS cnt FROM transactions');
$lowStockAlerts    = $manager->getCount('SELECT COUNT(*) AS cnt FROM items WHERE quantity<=min_quantity');

// 2. Fetch Low Stock Items Panel List using OOP methods
$lowStockItems = $manager->getRows("SELECT i.name AS item_name, i.quantity, i.min_quantity, COALESCE(s.company_name, 'Unknown') AS supplier_name 
                                    FROM items i 
                                    LEFT JOIN suppliers s ON i.supplier_id=s.order_id 
                                    WHERE i.quantity<=i.min_quantity 
                                    ORDER BY i.quantity ASC LIMIT 5");

// 3. Fetch Recent Transactions Activity Feed using OOP methods
$recentTransactions = $manager->getRows("SELECT i.name AS item_name, t.transaction_type, t.quantity, COALESCE(u.username, 'Unknown') AS user_name, t.created_at 
                                          FROM transactions t 
                                          LEFT JOIN items i ON t.item_id=i.id 
                                          LEFT JOIN users u ON t.user_id=u.id 
                                          ORDER BY t.created_at DESC LIMIT 5");

// 4. Fetch Inventory Stock Master Preview Table using OOP methods
$items = $manager->getRows("SELECT i.id, i.name, i.quantity, i.min_quantity, i.price, COALESCE(s.company_name, 'Unknown') AS supplier_name 
                            FROM items i 
                            LEFT JOIN suppliers s ON i.supplier_id=s.order_id 
                            ORDER BY i.name ASC LIMIT 50");
