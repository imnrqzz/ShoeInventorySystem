<?php
// frontend/index.php

require_once __DIR__ . '/components/auth.php';

// db.php loads Database, InventoryManager, TransactionManager
// and fetches all dashboard data ($totalItems, $lowStockItems, etc.)
require_once __DIR__ . '/../backend/db.php';

// Set component variables
$pageTitle = 'Dashboard';
$pageCss = 'dashboard.css';
$activePage = 'dashboard';

// Encode chart data as JSON for JavaScript
$chartItemsJson = json_encode($chartItems);
$chartTxJson = json_encode($chartTransactions);
$chartHealthJson = json_encode($chartStockHealth);
$chartBestSellersJson = json_encode($chartBestSellers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/components/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <style>
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 24px;
        }
        .chart-card {
            background: var(--color-surface);
            border-radius: var(--radius-lg);
            padding: 20px;
            border: 1px solid var(--color-border);
        }
        .chart-card-full {
            grid-column: 1 / -1;
        }
        .chart-card h3 {
            font-size: var(--font-size-sm);
            color: var(--color-text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
        }
        .chart-wrap {
            position: relative;
            width: 100%;
            height: 220px;
        }
        .chart-wrap-wide {
            height: 260px;
        }
        .date-filter {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--color-bg);
            padding: 14px 0;
        }
        .date-filter label {
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--color-text-secondary);
        }
        .date-filter input[type="date"] {
            padding: 7px 12px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            font-family: var(--font-family);
            color: var(--color-text);
            background: var(--color-surface);
        }
        .date-filter input[type="date"]:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .date-filter .btn-filter {
            padding: 7px 16px;
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            font-weight: 600;
            cursor: pointer;
        }
        .date-filter .btn-filter:hover { background: var(--color-primary-hover); }
        .date-filter .btn-clear {
            padding: 7px 12px;
            background: transparent;
            color: var(--color-text-muted);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            cursor: pointer;
            text-decoration: none;
        }
        .date-filter .btn-clear:hover { color: var(--color-text); border-color: var(--color-text-muted); }
        .date-filter .filter-hint {
            font-size: var(--font-size-xs);
            color: var(--color-text-muted);
            margin-left: 4px;
        }
        @media (max-width: 768px) {
            .charts-grid { grid-template-columns: 1fr; }
            .date-filter { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <?php require __DIR__ . '/components/sidebar.php'; ?>

        <main class="main-content">
<?php $pageSubtitle = 'Overview of your inventory'; require __DIR__ . '/components/page_header.php'; ?>

            <!-- ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ Date Range Filter ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ -->
            <form method="GET" action="index.php" class="date-filter">
                <label>From</label>
                <input type="date" name="date_from" value="<?= safe($dateFrom) ?>">
                <label>To</label>
                <input type="date" name="date_to" value="<?= safe($dateTo) ?>">
                <button type="submit" class="btn-filter"><i class="fa-solid fa-filter"></i> Filter</button>
                <?php if ($hasDateFilter): ?>
                <a href="index.php" class="btn-clear"><i class="fa-solid fa-xmark"></i> Clear</a>
                <span class="filter-hint">
                    Showing
                    <?= $dateFrom !== '' ? safe($dateFrom) : '...' ?>
                    to
                    <?= $dateTo !== '' ? safe($dateTo) : '...' ?>
                </span>
                <?php endif; ?>
            </form>

            <!-- ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ Charts ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ -->
            <div class="charts-grid">
                <!-- Inventory Levels Bar Chart -->
                <div class="chart-card chart-card-full">
                    <h3>Inventory Levels</h3>
                    <div class="chart-wrap chart-wrap-wide">
                        <canvas id="inventoryChart"></canvas>
                    </div>
                </div>

                <!-- Transaction Types Doughnut -->
                <div class="chart-card">
                    <h3>Transactions by Type<?= $hasDateFilter ? ' (Filtered)' : '' ?></h3>
                    <div class="chart-wrap">
                        <canvas id="transactionChart"></canvas>
                    </div>
                </div>

                <!-- Stock Health Comparison -->
                <div class="chart-card">
                    <h3>Stock Health</h3>
                    <div class="chart-wrap">
                        <canvas id="stockHealthChart"></canvas>
                    </div>
                </div>

                <!-- Best Sellers Chart -->
                <div class="chart-card">
                    <h3>Top Selling Products (by Transaction Count)</h3>
                    <div class="chart-wrap">
                        <canvas id="bestSellersChart"></canvas>
                    </div>
                </div>

                <!-- Least Sellers Chart -->
                <div class="chart-card">
                    <h3>Least Selling Products (by Transaction Count)</h3>
                    <div class="chart-wrap">
                        <canvas id="leastSellersChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart Raw Data -->
            <div class="grid-2col" style="margin-bottom:24px;">
                <div class="table-card">
                    <div class="table-card-header">Inventory Data</div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>Item</th><th>Quantity</th></tr></thead>
                            <tbody>
                                <?php if (!empty($chartItems)): foreach ($chartItems as $ci): ?>
                                <tr>
                                    <td><strong><?= safe($ci['name']) ?></strong></td>
                                    <td><?= safe($ci['quantity']) ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr class="empty-row"><td colspan="2">No data.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="table-card">
                    <div class="table-card-header">Transactions by Type<?= $hasDateFilter ? ' (Filtered)' : '' ?></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>Type</th><th>Count</th></tr></thead>
                            <tbody>
                                <?php if (!empty($chartTransactions)): foreach ($chartTransactions as $ct): ?>
                                <tr>
                                    <td><span class="font-bold"><?= safe($ct['transaction_type']) ?></span></td>
                                    <td><?= safe($ct['cnt']) ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr class="empty-row"><td colspan="2">No transactions found<?= $hasDateFilter ? ' in this date range' : '' ?>.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Two-column table row -->
            <div class="grid-2col">
                <!-- Low Stock Alerts -->
                <div class="table-card">
                    <div class="table-card-header">Low Stock Alerts</div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>Item</th><th>Qty</th><th>Min</th><th>Supplier</th></tr></thead>
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
                    <div class="table-card-header">Recent Transactions<?= $hasDateFilter ? ' (Filtered)' : '' ?></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>Item</th><th>Supplier</th><th>Type</th><th>Qty</th><th>By</th></tr></thead>
                            <tbody>
                                <?php if (!empty($recentTransactions)): foreach ($recentTransactions as $tx): ?>
                                <tr>
                                    <td><?= safe($tx['item_name']) ?></td>
                                    <td><?= safe($tx['supplier_name']) ?></td>
                                    <td><span class="font-bold"><?= safe($tx['transaction_type']) ?></span></td>
                                    <td><?= safe($tx['quantity']) ?></td>
                                    <td><?= safe($tx['user_name']) ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr class="empty-row"><td colspan="5">No transactions found<?= $hasDateFilter ? ' in this date range' : '' ?>.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="table-card">
                <div class="table-card-header">Inventory Overview</div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr><th>ID</th><th>Item</th><th>Stock</th><th>Min</th><th>Supplier</th><th class="col-nowrap">Price</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): foreach ($items as $it): ?>
                            <tr>
                                <td>#<?= safe($it['id']) ?></td>
                                <td><strong><?= safe($it['name']) ?></strong></td>
                                <td><?= safe($it['quantity']) ?></td>
                                <td><?= safe($it['min_quantity']) ?></td>
                                <td><?= safe($it['supplier_name']) ?></td>
                                <td class="font-bold col-nowrap">$<?= number_format((float)$it['price'], 2) ?></td>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var chartDefaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { font: { family: "'Inter', sans-serif", size: 12 } } } }
        };

        // 1. Inventory Levels Bar Chart
        var inventoryData = <?= $chartItemsJson ?>;
        new Chart(document.getElementById('inventoryChart'), {
            type: 'bar',
            data: {
                labels: inventoryData.map(function(d) { return d.name; }),
                datasets: [{
                    label: 'Quantity',
                    data: inventoryData.map(function(d) { return parseInt(d.quantity); }),
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                    barThickness: 32
                }]
            },
            options: Object.assign({}, chartDefaults, {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { family: "'Inter', sans-serif" } } },
                    x: { grid: { display: false }, ticks: { font: { family: "'Inter', sans-serif", size: 11 }, maxRotation: 45 } }
                }
            })
        });

        // 2. Transaction Types Doughnut
        var txData = <?= $chartTxJson ?>;
        var txColors = { Sold: '#d97706', Restock: '#16a34a' };
        new Chart(document.getElementById('transactionChart'), {
            type: 'doughnut',
            data: {
                labels: txData.map(function(d) { return d.transaction_type; }),
                datasets: [{
                    data: txData.map(function(d) { return parseInt(d.cnt); }),
                    backgroundColor: txData.map(function(d) { return txColors[d.transaction_type] || '#6b7280'; }),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: Object.assign({}, chartDefaults, {
                cutout: '55%',
                plugins: { legend: { position: 'bottom', labels: { padding: 16 } } }
            })
        });

        // 3. Stock Health Comparison
        var healthData = <?= $chartHealthJson ?>;
        new Chart(document.getElementById('stockHealthChart'), {
            type: 'bar',
            data: {
                labels: healthData.map(function(d) { return d.label; }),
                datasets: [{
                    label: 'Items',
                    data: healthData.map(function(d) { return parseInt(d.value); }),
                    backgroundColor: ['#16a34a', '#dc2626'],
                    borderRadius: 6,
                    barThickness: 48
                }]
            },
            options: Object.assign({}, chartDefaults, {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { family: "'Inter', sans-serif" } } },
                    x: { grid: { display: false }, ticks: { font: { family: "'Inter', sans-serif", size: 13, weight: '600' } } }
                }
            })
        });

        // 4. Best Sellers Horizontal Bar Chart
        var bestSellersData = <?= $chartBestSellersJson ?>;
        new Chart(document.getElementById('bestSellersChart'), {
            type: 'bar',
            data: {
                labels: bestSellersData.map(function(d) { return d.name; }),
                datasets: [{
                    label: 'Sales',
                    data: bestSellersData.map(function(d) { return parseInt(d.sale_count); }),
                    backgroundColor: '#16a34a',
                    borderRadius: 4
                }]
            },
            options: Object.assign({}, chartDefaults, {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { family: "'Inter', sans-serif" } } },
                    y: { grid: { display: false }, ticks: { font: { family: "'Inter', sans-serif", size: 11 } } }
                }
            })
        });

        // 5. Least Sellers Horizontal Bar Chart (reverse order)
        var leastSellersData = bestSellersData.slice().reverse();
        new Chart(document.getElementById('leastSellersChart'), {
            type: 'bar',
            data: {
                labels: leastSellersData.map(function(d) { return d.name; }),
                datasets: [{
                    label: 'Sales',
                    data: leastSellersData.map(function(d) { return parseInt(d.sale_count); }),
                    backgroundColor: '#dc2626',
                    borderRadius: 4
                }]
            },
            options: Object.assign({}, chartDefaults, {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { family: "'Inter', sans-serif" } } },
                    y: { grid: { display: false }, ticks: { font: { family: "'Inter', sans-serif", size: 11 } } }
                }
            })
        });
    });
    </script>
    <?php if ($lowStockAlerts > 0): ?>
    <!-- Low Stock Warning Modal -->
    <div id="lowStockWarningModal" class="modal-overlay" style="display: flex; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div class="modal-box" style="background: var(--color-surface); border-radius: var(--radius-lg); max-width: 480px; width: 90%; border: 1px solid var(--color-border); padding: 0;">
            <div class="modal-header" style="background: #fee2e2; color: #dc2626; padding: 16px 20px; border-top-left-radius: var(--radius-lg); border-top-right-radius: var(--radius-lg); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border);">
                <h2 style="font-size: 1.1rem; margin: 0; display: flex; align-items: center; gap: 8px; color: #dc2626;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Low Stock Warning!
                </h2>
                <button class="modal-close" style="background: none; border: none; font-size: 1.5rem; color: #dc2626; cursor: pointer; line-height: 1;" onclick="document.getElementById('lowStockWarningModal').style.display='none'">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p style="margin-bottom: 12px; font-size: 0.9rem; color: var(--color-text-secondary);">
                    The following items have reached or dropped below their minimum stock thresholds:
                </p>
                <div style="max-height: 250px; overflow-y: auto; border: 1px solid var(--color-border); border-radius: var(--radius-sm); margin-bottom: 20px;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                        <thead>
                            <tr style="background: var(--color-bg); border-bottom: 1px solid var(--color-border);">
                                <th style="padding: 10px 12px; font-weight: 600;">Item Name</th>
                                <th style="padding: 10px 12px; font-weight: 600; text-align: center;">Stock</th>
                                <th style="padding: 10px 12px; font-weight: 600; text-align: center;">Min</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockItems as $item): ?>
                            <tr style="border-bottom: 1px solid var(--color-border);">
                                <td style="padding: 10px 12px; font-weight: 500;"><?= safe($item['item_name']) ?></td>
                                <td style="padding: 10px 12px; text-align: center; color: #dc2626; font-weight: bold;"><?= $item['quantity'] ?></td>
                                <td style="padding: 10px 12px; text-align: center; color: var(--color-text-muted);"><?= $item['min_quantity'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <?php if (isAdmin()): ?>
                        <a href="stock.php" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; padding: 8px 16px;">
                            Manage Stock
                        </a>
                        <button class="btn btn-secondary" style="font-size: 0.85rem; padding: 8px 16px;" onclick="document.getElementById('lowStockWarningModal').style.display='none'">
                            Dismiss
                        </button>
                    <?php else: ?>
                        <button class="btn btn-primary" style="font-size: 0.85rem; padding: 8px 16px; width: 100px; justify-content: center; display: inline-flex;" onclick="document.getElementById('lowStockWarningModal').style.display='none'">
                            Dismiss
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php require __DIR__ . '/components/footer.php'; ?>
