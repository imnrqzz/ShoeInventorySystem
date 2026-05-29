<?php
// item.php

$host = 'localhost';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$database_name = 'db_item';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

function createDatabaseAndTable(PDO $pdo, string $dbName): void
{
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            category VARCHAR(100) DEFAULT '',
            unit VARCHAR(50) DEFAULT '',
            unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            supplier VARCHAR(255) DEFAULT '',
            stock DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            min_threshold DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

try {
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, $options);
    createDatabaseAndTable($pdo, $database_name);
} catch (\PDOException $e) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fff0f0; border-left:5px solid #ff4d4d; margin:20px;'><strong>Database Connection Failed!</strong><br>" . htmlspecialchars($e->getMessage()) . "</div>");
}

// Create new item (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name          = trim($_POST['item_name'] ?? '');
    $category      = trim($_POST['category'] ?? '');
    $unit          = trim($_POST['unit'] ?? '');
    $unit_price    = floatval($_POST['unit_price'] ?? 0);
    $supplier      = trim($_POST['supplier'] ?? '');
    $min_threshold = floatval($_POST['min_threshold'] ?? 0);

    if ($name !== '') {
        $stmt = $pdo->prepare('INSERT INTO items (name, category, unit, unit_price, supplier, stock, min_threshold) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $category, $unit, $unit_price, $supplier, 0.00, $min_threshold]);
    }
    header('Location: item.php');
    exit;
}

// Delete item (GET)
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare('DELETE FROM items WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: item.php');
    exit;
}

// Search & filter
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category_filter'] ?? 'All Categories');

$sql = 'SELECT * FROM items WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND name LIKE ?';
    $params[] = "%$search%";
}

if ($category_filter !== '' && $category_filter !== 'All Categories') {
    $sql .= ' AND category = ?';
    $params[] = $category_filter;
}

$sql .= ' ORDER BY id DESC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fff9e6; border-left:5px solid #ffcc00; margin:20px;'><strong>Error processing data:</strong> " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoes Inventory System</title>
    <link rel="stylesheet" href="Item.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <svg class="shoe-logo-svg" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                <path d="M502.7 266.5c-7.7-14.7-22.3-25.1-39.3-27.9l-79.6-12.9c-14.1-2.3-28.5 2.1-39.1 11.9l-45.4 42c-15.1 14-36.1 19.8-56.3 15.6l-85.1-17.7c-17.8-3.7-36.1 1.7-49.4 14.5L14.7 334c-12.2 11.8-16.1 29.8-9.8 45.7l19.6 49.1c6.3 15.8 21.6 26.2 38.6 26.2H456c24.3 0 44.9-17.2 49.2-41.1l14.9-82.3c2.9-16.1-2.4-32.5-17.4-45.1zM448 400H64c-3.4 0-6.5-2.1-7.7-5.3l-12-30.1 25.1-24.3c19.9-19.3 47.4-27.5 74.2-22l85.1 17.7c30.3 6.3 61.8-2.4 84.5-23.4l45.4-42c2.1-2 5-2.9 7.8-2.4l79.6 12.9c3.4.6 6.3 2.6 7.9 5.6L464 321.4 451.7 389c-.9 4.8-5 8.2-9.7 8.2z"/>
            </svg>
            <span class="nav-brand">Shoes Inventory System</span>
        </div>
        <ul class="nav-menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="item.php" class="active">Items</a></li>
            <li><a href="suppliers.php">Suppliers</a></li>
            <li><a href="stock.php">Stock</a></li>
            <li><a href="transactions.php">Transactions</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="reports.php">Reports</a></li>
        </ul>
        <div class="nav-right">
            <div class="user-profile">
                <svg class="profile-avatar-glyph" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5-4-8-4z"/></svg>
                <span class="username">Admin</span>
                <span class="role-badge">Manager</span>
            </div>
            <button class="logout-pill-btn" onclick="alert('Logging out...');">Logout</button>
        </div>
    </nav>

    <main class="purple-canvas-panel">
        <div class="section-heading-row">
            <h1 class="page-title-label">Items Management</h1>
            <a href="#addItemModal" class="btn-add-item-trigger">+ Add New Item</a>
        </div>

        <form method="GET" action="item.php" class="search-filter-pill-capsule">
            <input type="text" name="search" class="search-box-field" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
            <div class="dropdown-wrapper-control">
                <select name="category_filter" class="category-select-menu">
                    <option value="All Categories" <?php echo $category_filter === 'All Categories' ? 'selected' : ''; ?>>All Categories</option>
                    <option value="Dairy" <?php echo $category_filter === 'Dairy' ? 'selected' : ''; ?>>Dairy</option>
                    <option value="Flavoring" <?php echo $category_filter === 'Flavoring' ? 'selected' : ''; ?>>Flavoring</option>
                    <option value="Sweetener" <?php echo $category_filter === 'Sweetener' ? 'selected' : ''; ?>>Sweetener</option>
                    <option value="Coffee" <?php echo $category_filter === 'Coffee' ? 'selected' : ''; ?>>Coffee</option>
                    <option value="Equipment" <?php echo $category_filter === 'Equipment' ? 'selected' : ''; ?>>Equipment</option>
                </select>
            </div>
            <button type="submit" class="action-btn execution-search-btn">Search</button>
            <button type="button" class="action-btn execution-reset-btn" onclick="window.location.href='item.php';">Reset</button>
        </form>

        <div class="central-showcase-label">Inventory Ledger</div>

        <div class="curved-ledger-table-card">
            <div class="table-scroll-axis-frame">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Unit Price</th>
                            <th>Supplier</th>
                            <th>Stock</th>
                            <th>Min</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): 
                                $stock = floatval($item['stock']);
                                $min = floatval($item['min_threshold']);
                                $lowClass = $stock <= $min ? 'low' : '';
                            ?>
                            <tr>
                                <td><span class="row-index-id"><?php echo (int)$item['id']; ?></span></td>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($item['supplier'] ?: '—'); ?></td>
                                <td><span class="qty-indicator <?php echo $lowClass; ?>"><?php echo number_format($stock, 2) . ' ' . htmlspecialchars($item['unit']); ?></span></td>
                                <td><?php echo number_format($min, 2); ?></td>
                                <td>
                                    <div class="action-buttons-inline-flex">
                                        <button class="row-btn edit-action-btn" onclick="alert('Edit implementation for ID <?php echo (int)$item['id']; ?>');">Edit</button>
                                        <button class="row-btn delete-action-btn" onclick="if(confirm('Are you sure?')) window.location.href='item.php?delete_id=<?php echo (int)$item['id']; ?>';">Del</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" style="text-align:center;padding:24px;color:#8e8e93;">No matching items found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="addItemModal">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title-text">✦ Add New Item</div>
                <a href="#" class="close-frame-btn">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="item.php">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-form-grid-layout">
                        <div class="input-form-block">
                            <label>Item Name *</label>
                            <input type="text" name="item_name" placeholder="Sugar" required>
                        </div>
                        <div class="input-form-block">
                            <label>Category</label>
                            <input type="text" name="category" placeholder="Dairy">
                        </div>
                        <div class="input-form-block">
                            <label>Unit</label>
                            <input type="text" name="unit" placeholder="kg, bottle">
                        </div>
                        <div class="input-form-block">
                            <label>Unit Price (₱)</label>
                            <input type="number" step="0.01" name="unit_price" value="0.00">
                        </div>
                        <div class="input-form-block full-width-span-row">
                            <label>Supplier</label>
                            <select name="supplier">
                                <option value="">— None —</option>
                                <option value="SugarSweet Co."></option>
                                <option value="BenCafe Roasters"></option>
                            </select>
                        </div>
                        <div class="input-form-block full-width-span-row">
                            <label>Min. Threshold</label>
                            <input type="number" step="0.01" name="min_threshold" value="10.00">
                        </div>
                    </div>
                    <div class="modal-action-footer">
                        <a href="#" class="modal-footer-btn btn-modal-cancel">Cancel</a>
                        <button type="submit" class="modal-footer-btn btn-modal-confirm">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>