<?php
// frontend/stock.php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
require_once __DIR__ . '/../backend/classes/Database.php';
require_once __DIR__ . '/../backend/classes/StockManager.php';
if (!function_exists('safe')) { function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

$database = new Database();
$pdo = $database->getConnection();
$stockManager = new StockManager($pdo);

$filters = array_filter(['search' => trim($_GET['search'] ?? ''), 'category' => trim($_GET['category'] ?? '')]);

// Handle stock update
if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['action'] ?? '') === 'update_stock') {
    $success = $stockManager->updateGlobalInventorySync(
        (int)($_POST['stock_id'] ?? 0), (int)($_POST['item_id'] ?? 0), (int)($_POST['supplier_id'] ?? 0),
        trim($_POST['item_name'] ?? ''), trim($_POST['company_name'] ?? ''),
        (float)($_POST['current_qty'] ?? 0), (float)($_POST['min_threshold'] ?? 0)
    );
    if ($success) { header("Location: stock.php" . ($filters ? '?' . http_build_query($filters) : '')); exit; }
}

$totalItems = $stockManager->getTotalItemsCount();
$okStock = $stockManager->getOkStockCount();
$lowStock = $totalItems - $okStock;
$categories = $stockManager->getDistinctCategories();
$inventoryItems = $stockManager->getFilteredStock($filters);
$editItem = isset($_GET['edit_id']) ? $stockManager->getStockById($_GET['edit_id']) : null;
$cancelUrl = 'stock.php' . ($filters ? '?' . http_build_query($filters) : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/stockstyle.css">
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand"><div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div><span>ShoeInventory</span></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php" class="active"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info"><div class="user-name"><?= safe($_SESSION['username']) ?></div><div class="user-role">User</div></div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout" onclick="event.preventDefault(); confirmLogout();"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header"><h1>Stock Management</h1><p>Monitor stock levels and set thresholds</p></div>

            <div class="stat-cards">
                <div class="stat-card"><div class="stat-label">Total Items</div><div class="stat-value"><?= safe($totalItems) ?></div></div>
                <div class="stat-card"><div class="stat-label" style="color:var(--color-success)">OK Stock</div><div class="stat-value" style="color:var(--color-success)"><?= safe($okStock) ?></div></div>
                <div class="stat-card danger"><div class="stat-label">Low / Critical</div><div class="stat-value"><?= safe($lowStock) ?></div></div>
            </div>

            <form method="GET" action="stock.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search shoe name..." value="<?= safe($filters['search'] ?? '') ?>">
                <select name="category" style="padding:10px 14px;border:1px solid var(--color-border);border-radius:var(--radius-md);font-size:var(--font-size-sm);font-family:var(--font-family);background:var(--color-surface);">
                    <option value="All Categories">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= safe($cat['category']) ?>" <?= ($filters['category'] ?? '') === $cat['category'] ? 'selected' : '' ?>><?= safe($cat['category']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="stock.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Supplier</th><th>Qty</th><th>Min</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($inventoryItems)): foreach ($inventoryItems as $row):
                                $isLow = $row['current_qty'] < $row['min_threshold'];
                                $maxCap = max($row['current_qty'], $row['min_threshold'] * 2);
                                $fill = ($maxCap > 0) ? min(($row['current_qty'] / $maxCap) * 100, 100) : 0;
                            ?>
                            <tr>
                                <td><?= safe($row['id']) ?></td>
                                <td><strong><?= safe($row['item_name']) ?></strong></td>
                                <td class="text-muted"><?= safe($row['category']) ?></td>
                                <td><?= safe($row['supplier_name']) ?></td>
                                <td>
                                    <span class="<?= $isLow ? 'text-danger' : 'text-success' ?> font-bold"><?= number_format($row['current_qty'], 0) ?> <?= safe($row['unit'] ?? 'pairs') ?></span>
                                    <div class="progress-bar"><div class="fill <?= $isLow ? 'danger' : 'success' ?>" style="width:<?= $fill ?>%"></div></div>
                                </td>
                                <td><?= number_format($row['min_threshold'], 0) ?> <?= safe($row['unit'] ?? 'pairs') ?></td>
                                <td><span class="badge <?= $isLow ? 'badge-danger' : 'badge-success' ?>"><?= $isLow ? 'Low' : 'OK' ?></span></td>
                                <td class="text-muted"><?= date('M d, Y', strtotime($row['last_updated'])) ?></td>
                                <td>
                                    <a href="stock.php?<?= http_build_query(array_merge($filters, ['edit_id' => $row['id']])) ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                                    <button class="btn btn-danger btn-sm" onclick="confirmDelete('Are you sure you want to delete this stock item? This action cannot be undone.', '../backend/stock_delete.php?<?= http_build_query(array_merge($filters, ['id' => $row['id']])) ?>')">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="9">No stock items found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php if ($editItem): ?>
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header"><h2>Edit Stock Level</h2><a href="<?= $cancelUrl ?>" class="modal-close">&times;</a></div>
            <div class="modal-body">
                <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);margin-bottom:16px;">Editing: <strong style="color:var(--color-text)"><?= safe($editItem['item_name']) ?></strong></p>
                <form method="POST" action="stock.php?<?= http_build_query($filters) ?>">
                    <input type="hidden" name="action" value="update_stock">
                    <input type="hidden" name="stock_id" value="<?= safe($editItem['id']) ?>">
                    <input type="hidden" name="item_id" value="<?= safe($editItem['item_id']) ?>">
                    <input type="hidden" name="supplier_id" value="<?= safe($editItem['supplier_id'] ?? '') ?>">
                    <input type="hidden" name="item_name" value="<?= safe($editItem['item_name']) ?>">
                    <input type="hidden" name="company_name" value="<?= safe($editItem['supplier_name']) ?>">
                    <div class="form-grid">
                        <div class="form-group"><label>Current Quantity</label><input type="number" step="0.01" name="current_qty" value="<?= safe($editItem['current_qty']) ?>" required></div>
                        <div class="form-group"><label>Min Threshold</label><input type="number" step="0.01" name="min_threshold" value="<?= safe($editItem['min_threshold']) ?>" required></div>
                    </div>
                    <div class="modal-footer"><a href="<?= $cancelUrl ?>" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary">Save</button></div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <script src="../js/confirm-modal.js"></script>
</body>
</html>
