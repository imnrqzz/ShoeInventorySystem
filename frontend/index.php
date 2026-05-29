<?php
// Pulls the PDO instance, query logic, and pre-loaded dataset arrays straight into the page variables
require_once __DIR__.'/../backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shoes Inventory System - Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard_style.css?v=<?php echo time(); ?>" />
</head>
<body>
    <div class="page">
        <header class="topbar">
            <div class="brand">
                <div class="logo">
                    <img src="shoes.png" alt="Shoes Logo" onerror="this.style.display='none';" />
                </div>
                <div class="title">
                    <span>Shoes Inventory System</span>
                </div>
            </div>

            <nav class="nav-links">
                <a href="index.php" class="active">Dashboard</a>
                <a href="item.php">Items</a>
                <a href="Supplier.php">Suppliers</a>
                <a href="stock.php">Stock</a>
                <a href="transactions.php">Transactions</a>
                <a href="users.php">Users</a>
                <a href="reports.php">Reports</a>
            </nav>

            <div class="top-actions">
                <div class="user-badge">
                    <span class="avatar">U</span>
                    <span><?php echo !empty($_SESSION['username']) ? safe($_SESSION['username']) : 'mark'; ?></span>
                </div>
                <a href="logout.php" class="logout-button" title="Logout" style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </a>
            </div>
        </header>

        <section class="dashboard-panel">
            <h2>Dashboard Overview</h2>
            <div class="summary">
                <article class="dashboard-card">
                    <div class="icon">📦</div>
                    <div class="value"><?= safe($totalItems) ?></div>
                    <div class="label">Total Items</div>
                </article>
                <article class="dashboard-card">
                    <div class="icon">🚚</div>
                    <div class="value"><?= safe($activeSuppliers) ?></div>
                    <div class="label">Active Suppliers</div>
                </article>
                <article class="dashboard-card">
                    <div class="icon">👥</div>
                    <div class="value"><?= safe($systemUsers) ?></div>
                    <div class="label">System Users</div>
                </article>
                <article class="dashboard-card">
                    <div class="icon">🔁</div>
                    <div class="value"><?= safe($transactionsCount) ?></div>
                    <div class="label">Transactions</div>
                </article>
                <article class="dashboard-card danger">
                    <div class="icon">⚠️</div>
                    <div class="value"><?= safe($lowStockAlerts) ?></div>
                    <div class="label">Low Stock Alerts</div>
                </article>
            </div>
        </section>

        <div class="tables-row">
            <section class="panel">
                <div class="panel-header alert">
                    <span class="dot"></span>
                    Low Stock Alerts
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Current</th>
                                    <th>Min Qty</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($lowStockItems)): foreach($lowStockItems as $item): ?>
                                    <tr>
                                        <td><strong><?= safe($item['item_name']) ?></strong></td>
                                        <td style="color: #ef4444; font-weight: 600;"><?= safe($item['quantity']) ?></td>
                                        <td><?= safe($item['min_quantity']) ?></td>
                                        <td><?= safe($item['supplier_name']) ?></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="4" style="color: var(--text-muted); text-align: center; padding: 20px;">No low stock items at the moment.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <span class="dot" style="background-color: #3b82f6;"></span>
                    Recent Transactions
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Processed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($recentTransactions)): foreach($recentTransactions as $tx): ?>
                                    <tr>
                                        <td><?= safe($tx['item_name']) ?></td>
                                        <td>
                                            <span style="font-weight: 600; color: <?= $tx['transaction_type'] === 'In' ? '#10b981' : '#3b82f6' ?>;">
                                                <?= safe($tx['transaction_type']) ?>
                                            </span>
                                        </td>
                                        <td><?= safe($tx['quantity']) ?></td>
                                        <td><?= safe($tx['user_name']) ?></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="4" style="color: var(--text-muted); text-align: center; padding: 20px;">No recent transactions log found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
        
        <section class="panel items-panel" style="margin-top: 24px;">
            <div class="panel-header">
                <span class="dot" style="background-color: #4d57db;"></span>
                Inventory Master Stock Preview
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th>Item Name</th>
                                <th>In Stock</th>
                                <th>Threshold (Min)</th>
                                <th>Supplier/Brand</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($items)): foreach($items as $it): ?>
                                <tr>
                                    <td>#<?= safe($it['id']) ?></td>
                                    <td><strong><?= safe($it['name']) ?></strong></td>
                                    <td><?= safe($it['quantity']) ?></td>
                                    <td><?= safe($it['min_quantity']) ?></td>
                                    <td><?= safe($it['supplier_name']) ?></td>
                                    <td style="font-weight: 600; color: #1e293b;">$<?= number_format((float)$it['price'], 2) ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="6" style="color: var(--text-muted); text-align: center; padding: 20px;">No master stock items initialized yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
