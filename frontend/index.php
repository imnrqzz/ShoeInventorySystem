<?php
// frontend/index.php

// Best Practice: Use a shared auth component instead of repeating session/cache/redirect code.
require_once __DIR__ . '/components/auth.php';

// db.php loads Database, InventoryManager, TransactionManager
// and fetches all dashboard data ($totalItems, $lowStockItems, etc.)
require_once __DIR__ . '/../backend/db.php';

// Set component variables
$pageTitle = 'Dashboard';          // used by head.php
$pageCss = 'dashboard_style.css';  // used by head.php
$activePage = 'dashboard';         // used by sidebar.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/components/head.php'; ?>
</head>
<body>
    <div class="page-wrapper">
        <?php require __DIR__ . '/components/sidebar.php'; ?>

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
    <?php require __DIR__ . '/components/footer.php'; ?>
