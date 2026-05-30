<?php
// Database Connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=shoes_inventory;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Preserve current search/category filters across redirects/links
$filters = array_filter(['search' => trim($_GET['search'] ?? ''), 'category' => trim($_GET['category'] ?? '')]);

// Handle Inline Edit Submission
if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['action'] ?? '') === 'update_stock') {
    $stmt = $pdo->prepare("UPDATE stock SET current_qty = ?, min_threshold = ? WHERE id = ?");
    if ($stmt->execute([$_POST['current_qty'], $_POST['min_threshold'], (int)$_POST['item_id']])) {
        header("Location: stock.php" . ($filters ? '?' . http_build_query($filters) : ''));
        exit;
    }
}

// Fetch KPI Metrics & Categories
$totalItems = $pdo->query("SELECT COUNT(*) FROM stock")->fetchColumn();
$okStock    = $pdo->query("SELECT COUNT(*) FROM stock WHERE current_qty >= min_threshold")->fetchColumn();
$lowStock   = $totalItems - $okStock;
$categories = $pdo->query("SELECT DISTINCT category FROM stock ORDER BY category ASC")->fetchAll();

// Build Inventory Query
$queryStr = "SELECT s.*, sup.name AS supplier_name FROM stock s JOIN suppliers sup ON s.supplier_id = sup.id WHERE 1=1";
$params = [];

if (!empty($filters['search'])) {
    $queryStr .= " AND s.item_name LIKE :search";
    $params['search'] = '%' . $filters['search'] . '%';
}
if (!empty($filters['category']) && $filters['category'] !== 'All Categories') {
    $queryStr .= " AND s.category = :category";
    $params['category'] = $filters['category'];
}

$stmt = $pdo->prepare($queryStr . " ORDER BY s.id DESC");
$stmt->execute($params);
$inventoryItems = $stmt->fetchAll();

// Check Edit Mode
$editItem = isset($_GET['edit_id']) ? $pdo->query("SELECT * FROM stock WHERE id = " . (int)$_GET['edit_id'])->fetch() : null;
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
    <link rel="stylesheet" href="stockstyle.css">
</head>
<body>

    <header class="navbar">
        <div class="nav-left">
            <i class="logo-icon"></i>
            <span class="system-title">Shoes Inventory System</span>
        </div>
        <nav class="nav-menu">
            <a href="#"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="#"><i class="fa-solid fa-boxes-stacked"></i> Items</a>
            <a href="#"><i class="fa-solid fa-truck-field"></i> Suppliers</a>
            <a href="#" class="active"><i class="fa-solid fa-chart-simple"></i> Stock</a>
            <a href="#"><i class="fa-solid fa-square-poll-horizontal"></i> Transactions</a>
            <a href="#"><i class="fa-solid fa-users"></i> Users</a>
        </nav>
        <div class="nav-right"><div class="user-profile"><i class="profile-icon"></i><span>User1</span></div><button class="logout-btn"><i class="fa-solid fa-power-off"></i></button></div>
    </header>

    <main class="main-container">
        <section class="summary-cards">
            <div class="card"><div class="card-icon"><i class="fa-solid fa-shoe-prints"></i></div><div class="card-value"><?= htmlspecialchars($totalItems) ?></div><div class="card-label">Total Items Tracked</div></div>
            <div class="card"><div class="card-icon text-success"><i class="fa-solid fa-square-check"></i></div><div class="card-value text-success"><?= htmlspecialchars($okStock) ?></div><div class="card-label">OK Stock</div></div>
            <div class="card"><div class="card-icon text-alert"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="card-value text-alert"><?= htmlspecialchars($lowStock) ?></div><div class="card-label">Low / Critical Alerts</div></div>
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
            <a href="stock.php" class="btn btn-reset" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">Reset</a>
        </form>

        <section class="table-responsive">
            <table class="inventory-table">
                <thead>
                    <tr><th>#</th><th>Item</th><th>Category</th><th>Supplier</th><th>Current Qty</th><th>Min Threshold</th><th>Status</th><th>Last Updated</th><th>Actions</th></tr>
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
                                    <span class="<?= $isLow ? 'text-alert' : 'text-success' ?> font-weight-bold"><?= htmlspecialchars(number_format($row['current_qty'], 0)) . ' ' . htmlspecialchars($row['unit']) ?></span>
                                    <div class="progress-bar style-bar" style="width: 100%;"><div class="progress-bar-fill <?= $isLow ? 'bar-alert' : 'bar-success' ?>" style="width: <?= $fillPercentage ?>%;"></div></div>
                                </td>
                                <td><?= htmlspecialchars(number_format($row['min_threshold'], 0)) . ' ' . htmlspecialchars($row['unit']) ?></td>
                                <td>
                                    <span class="badge <?= $isLow ? 'badge-low' : 'badge-ok' ?>"><i class="fa-solid <?= $isLow ? 'fa-triangle-exclamation' : 'fa-check' ?>"></i> <?= $isLow ? 'Low' : 'OK' ?></span>
                                </td>
                                <td class="text-muted text-sm"><?= date('M d, Y H:i', strtotime($row['last_updated'])) ?></td>
                                <td>
                                    <a href="stock.php?<?= http_build_query(array_merge($filters, ['edit_id' => $row['id']])) ?>" class="btn-action btn-edit"><i class="fa-solid fa-pencil"></i> Edit</a>
                                    <a href="stock_delete.php?id=<?= $row['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" style="text-align: center; padding: 30px; color: #888;">No matching inventory items found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <?php if ($editItem): ?>
        <div class="modal-overlay">
            <div class="edit-box">
                <div class="edit-header">
                    <div style="display: flex; align-items: center; gap: 8px;"><span class="modal-sparkle">✦</span><h2>Modify Stock Level</h2></div>
                    <a href="<?= $cancelUrl ?>" class="close-modal-btn">&times;</a>
                </div>
                <div class="item-preview-badge"><span class="item-preview-label">Active Item</span><strong><?= htmlspecialchars($editItem['item_name']) ?></strong></div>
                <form method="POST" action="stock.php">
                    <input type="hidden" name="action" value="update_stock">
                    <input type="hidden" name="item_id" value="<?= htmlspecialchars($editItem['id']) ?>">
                    <div class="form-group"><label>Current Quantity</label><input type="number" step="0.01" name="current_qty" value="<?= htmlspecialchars($editItem['current_qty']) ?>" required autofocus></div>
                    <div class="form-group"><label>Minimum Threshold</label><input type="number" step="0.01" name="min_threshold" value="<?= htmlspecialchars($editItem['min_threshold']) ?>" required></div>
                    <div class="actions-row"><a href="<?= $cancelUrl ?>" class="btn btn-reset">Cancel</a><button type="submit" class="btn btn-save-modal">✓ Save Level</button></div>
                </form>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>
