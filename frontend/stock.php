<?php
// frontend/stock.php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
require_once __DIR__ . '/../backend/classes/Database.php';
require_once __DIR__ . '/../backend/classes/StockManager.php';

// 2. Instantiate connection and operational layers
$database = new Database();
$pdo = $database->getConnection();
$stockManager = new StockManager($pdo);

// Preserve current search/category filters across redirects/links
$filters = array_filter(['search' => trim($_GET['search'] ?? ''), 'category' => trim($_GET['category'] ?? '')]);

// Handle Inline Edit Submission via OOP Interface
if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['action'] ?? '') === 'update_stock') {
    $stockId      = (int)($_POST['stock_id'] ?? 0);
    $itemId       = (int)($_POST['item_id'] ?? 0);
    $supplierId   = (int)($_POST['supplier_id'] ?? 0);
    $itemName     = trim($_POST['item_name'] ?? '');
    $companyName  = trim($_POST['company_name'] ?? '');
    $currentQty   = (float)($_POST['current_qty'] ?? 0);
    $minThreshold = (float)($_POST['min_threshold'] ?? 0);

    // Call the atomic multi-table transaction update
    $success = $stockManager->updateGlobalInventorySync(
        $stockId, 
        $itemId, 
        $supplierId, 
        $itemName, 
        $companyName, 
        $currentQty, 
        $minThreshold
    );
    
    if ($success) {
        header("Location: stock.php" . ($filters ? '?' . http_build_query($filters) : ''));
        exit;
    }
}

// Fetch KPI Metrics & Categories via Encapsulated Class Methods
$totalItems = $stockManager->getTotalItemsCount();
$okStock    = $stockManager->getOkStockCount();
$lowStock   = $totalItems - $okStock;
$categories = $stockManager->getDistinctCategories();

// Process Filtered Arrays Engine
$inventoryItems = $stockManager->getFilteredStock($filters);

// Check Edit Mode Overlay Map
$editItem = isset($_GET['edit_id']) ? $stockManager->getStockById($_GET['edit_id']) : null;
$cancelUrl = 'stock.php' . ($filters ? '?' . http_build_query($filters) : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoes Inventory System - Stock Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght=400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/stockstyle.css">
</head>
<body>

    <div class="main-container">
        <header class="navbar">
            <div class="nav-left">
                <div class="logo">
                    <img src="../images/shoes.png" alt="Shoes Logo" />
                </div>
                <span class="system-title">Shoes Inventory System</span>
            </div>
            <nav class="nav-menu">
                <a href="index.php"> Dashboard</a>
                <a href="Item.php"> Items</a>
                <a href="Supplier.php"> Suppliers</a>
                <a href="transactions.php">Transactions</a>
                <a href="stock.php" class="active"> Stock</a>
                <a href="user.php"> Users</a>
                <a href="reports.php"> Reports</a>
            </nav>
            <div class="nav-right">
                <div class="user-profile" style="display: flex; align-items: center; gap: 10px;">
                    <div class="profile-icon" style="display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
                
                <button class="logout-btn" onclick="location.href='logout.php'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </button>
            </div>
        </header>

        <section class="summary-cards">
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-shoe-prints"></i></div>
                <div class="card-value"><?= htmlspecialchars($totalItems) ?></div>
                <div class="card-label">Total Items Tracked</div>
            </div>
            <div class="card">
                <div class="card-icon text-success"><i class="fa-solid fa-square-check"></i></div>
                <div class="card-value text-success"><?= htmlspecialchars($okStock) ?></div>
                <div class="card-label">OK Stock</div>
            </div>
            <div class="card">
                <div class="card-icon text-alert"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="card-value text-alert"><?= htmlspecialchars($lowStock) ?></div>
                <div class="card-label">Low / Critical Alerts</div>
            </div>
        </section>

        <form method="GET" action="stock.php" class="filters-container">
            <input type="text" name="search" class="search-bar" placeholder="Search shoe name..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            <div class="select-wrapper">
                <select name="category" class="category-select">
                    <option value="All Categories">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['category']) ?>" <?= ($filters['category'] ?? '') === $cat['category'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-filter">Filter</button>
            <a href="stock.php" class="btn btn-reset" style="text-decoration:none;">Reset</a>
        </form>

        <section class="table-responsive">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Current Qty</th>
                        <th>Min Threshold</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inventoryItems)): ?>
                        <?php foreach ($inventoryItems as $row): 
                            $isLow = $row['current_qty'] < $row['min_threshold'];
                            $maxCapacity = max($row['current_qty'], $row['min_threshold'] * 2);
                            $fillPercentage = ($maxCapacity > 0) ? min(($row['current_qty'] / $maxCapacity) * 100, 100) : 0;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                                <td class="text-muted"><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                <td>
                                    <span class="<?= $isLow ? 'text-alert' : 'text-success' ?> font-weight-bold">
                                        <?= htmlspecialchars(number_format($row['current_qty'], 0)) . ' ' . htmlspecialchars($row['unit'] ?? 'pairs') ?>
                                    </span>
                                    <div class="progress-bar style-bar" style="width: 100%;">
                                        <div class="progress-bar-fill <?= $isLow ? 'bar-alert' : 'bar-success' ?>" style="width: <?= $fillPercentage ?>%;"></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars(number_format($row['min_threshold'], 0)) . ' ' . htmlspecialchars($row['unit'] ?? 'pairs') ?></td>
                                <td>
                                    <span class="badge <?= $isLow ? 'badge-low' : 'badge-ok' ?>">
                                        <i class="fa-solid <?= $isLow ? 'fa-triangle-exclamation' : 'fa-check' ?>"></i> <?= $isLow ? 'Low' : 'OK' ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($row['last_updated'])) ?></td>
                                <td>
                                    <a href="stock.php?<?= http_build_query(array_merge($filters, ['edit_id' => $row['id']])) ?>" class="btn-action btn-edit"><i class="fa-solid fa-pencil"></i> Edit</a>
                                    <a href="../backend/stock_delete.php?<?= http_build_query(array_merge($filters, ['id' => $row['id']])) ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" style="text-align: center; padding: 30px; color: #888;">No matching inventory items found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <?php if ($editItem): ?>
        <div class="modal-overlay">
            <div class="edit-box">
                <div class="edit-header">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="modal-sparkle">✦</span>
                        <h2>Modify Stock Level</h2>
                    </div>
                    <a href="<?= $cancelUrl ?>" class="close-modal-btn">&times;</a>
                </div>
                
                <div class="item-preview-badge">
                    <span class="item-preview-label">ACTIVE ITEM</span>
                    <strong><?= htmlspecialchars($editItem['item_name']) ?></strong>
                </div>
                
                <form method="POST" action="stock.php?<?= http_build_query($filters) ?>">
                    <input type="hidden" name="action" value="update_stock">
                    
                    <input type="hidden" name="stock_id" value="<?= htmlspecialchars($editItem['id']) ?>">
                    <input type="hidden" name="item_id" value="<?= htmlspecialchars($editItem['item_id']) ?>">
                    <input type="hidden" name="supplier_id" value="<?= htmlspecialchars($editItem['supplier_id'] ?? '') ?>">
                    <input type="hidden" name="item_name" value="<?= htmlspecialchars($editItem['item_name']) ?>">
                    <input type="hidden" name="company_name" value="<?= htmlspecialchars($editItem['supplier_name']) ?>">
                    
                    <div class="form-group">
                        <label>Current Quantity</label>
                        <input type="number" step="0.01" name="current_qty" value="<?= htmlspecialchars($editItem['current_qty']) ?>" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label>Minimum Threshold</label>
                        <input type="number" step="0.01" name="min_threshold" value="<?= htmlspecialchars($editItem['min_threshold']) ?>" required>
                    </div>
                    
                    <div class="actions-row">
                        <a href="<?= $cancelUrl ?>" class="btn btn-reset" style="text-decoration: none;">Cancel</a>
                        <button type="submit" class="btn btn-save-modal">✓ Save Level</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>