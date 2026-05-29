<?php
// Start session for auth across pages if not already active
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Database connection settings
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'pos_inventory_system';
const DB_CHAR = 'utf8mb4';

try {
    // Establish PDO Connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (\PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die("<div style='font-family:sans-serif; padding:20px; background:#fff0f0; border-left:5px solid #ff4d4d; margin:20px;'><strong>Database Connection Failed!</strong></div>");
}


// ==========================================================================
//  BACKEND INVENTORY FETCHING LOGIC
// ==========================================================================

// Helper: Safe row fetcher function
function qRows($pdo, $sql) {
    if (!$pdo) return [];
    try {
        $stmt = $pdo->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    } catch (Exception $e) {
        return [];
    }
}

// Helper: Safe count aggregation fetcher function
function qCount($pdo, $sql) {
    if (!$pdo) return 0;
    try {
        $stmt = $pdo->query($sql);
        if ($stmt) {
            $row = $stmt->fetch();
            return $row ? (int)$row['cnt'] : 0;
        }
    } catch (Exception $e) {
        return 0;
    }
    return 0;
}

// Global safe escaping string function
function safe($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// 1. Fetch Stats Counters
$totalItems        = qCount($pdo, 'SELECT COUNT(*) AS cnt FROM items');
$activeSuppliers   = qCount($pdo, "SELECT COUNT(*) AS cnt FROM suppliers WHERE status='Active'");
$systemUsers       = qCount($pdo, 'SELECT COUNT(*) AS cnt FROM users');
$transactionsCount = qCount($pdo, 'SELECT COUNT(*) AS cnt FROM transactions');
$lowStockAlerts    = qCount($pdo, 'SELECT COUNT(*) AS cnt FROM items WHERE quantity<=min_quantity');

// 2. Fetch Low Stock Items Panel List
$lowStockItems = qRows($pdo, "SELECT i.name AS item_name, i.quantity, i.min_quantity, COALESCE(s.company_name, 'Unknown') AS supplier_name 
                              FROM items i 
                              LEFT JOIN suppliers s ON i.supplier_id=s.order_id 
                              WHERE i.quantity<=i.min_quantity 
                              ORDER BY i.quantity ASC LIMIT 5");

// 3. Fetch Recent Transactions Activity Feed
$recentTransactions = qRows($pdo, "SELECT i.name AS item_name, t.transaction_type, t.quantity, COALESCE(u.username, 'Unknown') AS user_name, t.created_at 
                                    FROM transactions t 
                                    LEFT JOIN items i ON t.item_id=i.id 
                                    LEFT JOIN users u ON t.user_id=u.id 
                                    ORDER BY t.created_at DESC LIMIT 5");

// 4. Fetch Inventory Stock Master Preview Table 
$items = qRows($pdo, "SELECT i.id, i.name, i.quantity, i.min_quantity, i.price, COALESCE(s.company_name, 'Unknown') AS supplier_name 
                      FROM items i 
                      LEFT JOIN suppliers s ON i.supplier_id=s.order_id 
                      ORDER BY i.name ASC LIMIT 50");
