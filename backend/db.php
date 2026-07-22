<?php
// backend/db.php - Dashboard data queries.
// Session, cache headers, DB connection, and safe() are loaded via bootstrap.

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/classes/InventoryManager.php';
require_once __DIR__ . '/classes/TransactionManager.php';

$manager = new InventoryManager($pdo);
$transManager = new TransactionManager($pdo);

// Date range filter (from dashboard filter form)
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo   = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$dateWhere = '';
$dateParams = [];

if ($dateFrom !== '') {
    $dateWhere .= " AND t.created_at >= :date_from";
    $dateParams['date_from'] = $dateFrom . ' 00:00:00';
}
if ($dateTo !== '') {
    $dateWhere .= " AND t.created_at <= :date_to";
    $dateParams['date_to'] = $dateTo . ' 23:59:59';
}
$hasDateFilter = ($dateFrom !== '' || $dateTo !== '');

// 1. Fetch Stats Counters
$totalItems        = $manager->getCount('SELECT COUNT(*) AS cnt FROM items');
$activeSuppliers   = $manager->getCount("SELECT COUNT(*) AS cnt FROM suppliers WHERE status='Active'");
$systemUsers       = $manager->getCount('SELECT COUNT(*) AS cnt FROM users');
$lowStockAlerts    = $manager->getCount('SELECT COUNT(*) AS cnt FROM items WHERE quantity<=min_quantity');

// Transaction count respects date filter
if ($hasDateFilter) {
    $txCountStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM transactions t WHERE 1=1 $dateWhere");
    $txCountStmt->execute($dateParams);
    $transactionsCount = (int)$txCountStmt->fetchColumn();
} else {
    $transactionsCount = $manager->getCount('SELECT COUNT(*) AS cnt FROM transactions');
}

// 2. Fetch Low Stock Items Panel List
$lowStockItems = $manager->getRows("SELECT i.name AS item_name, i.quantity, i.min_quantity, COALESCE(s.company_name, 'Unknown') AS supplier_name 
                                    FROM items i 
                                    LEFT JOIN suppliers s ON i.supplier_id=s.order_id 
                                    WHERE i.quantity<=i.min_quantity 
                                    ORDER BY i.quantity ASC LIMIT 5");

// 3. Fetch Recent Transactions (filtered by date range)
$recentTxSql = "SELECT i.name AS item_name, t.transaction_type, t.quantity, COALESCE(u.username, 'Unknown') AS user_name, t.created_at 
                FROM transactions t 
                LEFT JOIN items i ON t.item_id=i.id 
                LEFT JOIN users u ON t.user_id=u.id 
                WHERE 1=1 $dateWhere
                ORDER BY t.created_at DESC LIMIT 5";
$recentTxStmt = $pdo->prepare($recentTxSql);
$recentTxStmt->execute($dateParams);
$recentTransactions = $recentTxStmt->fetchAll();

// 4. Fetch Inventory Stock Master Preview Table
$items = $manager->getRows("SELECT i.id, i.name, i.quantity, i.min_quantity, i.price, COALESCE(s.company_name, 'Unknown') AS supplier_name 
                            FROM items i 
                            LEFT JOIN suppliers s ON i.supplier_id=s.order_id 
                            ORDER BY i.name ASC LIMIT 50");

// 5. Chart Data: Top 8 items by quantity for bar chart
$chartItems = $manager->getRows("SELECT i.name, i.quantity FROM items i ORDER BY i.quantity DESC LIMIT 8");

// 6. Chart Data: Transaction counts by type (filtered by date range)
$chartTxSql = "SELECT t.transaction_type, COUNT(*) AS cnt FROM transactions t WHERE 1=1 $dateWhere GROUP BY t.transaction_type ORDER BY cnt DESC";
$chartTxStmt = $pdo->prepare($chartTxSql);
$chartTxStmt->execute($dateParams);
$chartTransactions = $chartTxStmt->fetchAll();

// 7. Chart Data: Stock health summary for comparison chart
$okStockCount   = $manager->getCount('SELECT COUNT(*) AS cnt FROM items WHERE quantity > min_quantity');
$chartStockHealth = [['label' => 'OK Stock', 'value' => $okStockCount], ['label' => 'Low Stock', 'value' => $lowStockAlerts]];

// 8. Chart Data: Best selling items (by sale transaction count)
$bestSellersSql = "SELECT i.name, COUNT(t.id) AS sale_count
                   FROM transactions t
                   LEFT JOIN items i ON t.item_id = i.id
                   WHERE t.transaction_type = 'Sold' AND i.name IS NOT NULL
                   GROUP BY i.id, i.name
                   ORDER BY sale_count DESC
                   LIMIT 10";
$bestSellersStmt = $pdo->prepare($bestSellersSql);
$bestSellersStmt->execute();
$chartBestSellers = $bestSellersStmt->fetchAll();