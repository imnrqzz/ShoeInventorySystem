<?php
// frontend/index.php

// Best Practice: Check login status before showing any page content.
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// db.php loads Database, InventoryManager, TransactionManager
// and fetches all dashboard data ($totalItems, $lowStockItems, etc.)
require_once __DIR__ . '/../backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard_style.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- ── Sidebar Navigation ──────────────────────────────── -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div>
                <span>ShoeInventory</span>
            </div>
            <ul class="sidebar-nav">
                <li><a href="index.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= safe($_SESSION['username']) ?></div>
                    <div class="user-role">User</div>
                </div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </aside>

        <!-- ── Main Content ────────────────────────────────────── -->
        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Overview of your inventory</p>
            </div>

            <!-- KPI Stat Cards -->
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-label">Total Items</div>
                    <div class="stat-value"><?= safe($totalItems) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active Suppliers</div>
                    <div class="stat-value"><?= safe($activeSuppliers) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">System Users</div>
                    <div class="stat-value"><?= safe($systemUsers) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Transactions</div>
                    <div class="stat-value"><?= safe($transactionsCount) ?></div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-label">Low Stock Alerts</div>
                    <div class="stat-value"><?= safe($lowStockAlerts) ?></div>
                </div>
            </div>

            <!-- Two-column table row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Low Stock Alerts -->
                <div class="table-card">
                    <div class="table-card-header">Low Stock Alerts</div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>Item</th><th>Current</th><th>Min</th><th>Supplier</th></tr></thead>
                            <tbody>
                                <?php if (!empty($lowStockItems)): foreach ($lowStockItems as $item): ?>
                                <tr>
                                    <td><strong><?= safe($item['item_name']) ?></strong></td>
                                    <td class="text-danger font-bold"><?= safe($item['quantity']) ?></td>
                                    <td><?= safe($item['min_quantity']) ?></td>
                                    <td><?= safe($item['supplier_name']) ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr class="empty-row"><td colspan="4">No low stock items.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="table-card">
                    <div class="table-card-header">Recent Transactions</div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>By</th></tr></thead>
                            <tbody>
                                <?php if (!empty($recentTransactions)): foreach ($recentTransactions as $tx): ?>
                                <tr>
                                    <td><?= safe($tx['item_name']) ?></td>
                                    <td><span class="font-bold"><?= safe($tx['transaction_type']) ?></span></td>
                                    <td><?= safe($tx['quantity']) ?></td>
                                    <td><?= safe($tx['user_name']) ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr class="empty-row"><td colspan="4">No recent transactions.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Inventory Master Table -->
            <div class="table-card">
                <div class="table-card-header">Inventory Overview</div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr><th>ID</th><th>Item Name</th><th>In Stock</th><th>Min</th><th>Supplier</th><th>Price</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): foreach ($items as $it): ?>
                            <tr>
                                <td>#<?= safe($it['id']) ?></td>
                                <td><strong><?= safe($it['name']) ?></strong></td>
                                <td><?= safe($it['quantity']) ?></td>
                                <td><?= safe($it['min_quantity']) ?></td>
                                <td><?= safe($it['supplier_name']) ?></td>
                                <td class="font-bold">$<?= number_format((float)$it['price'], 2) ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="6">No items in inventory.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
