<?php
// Database Configuration
$host    = 'localhost';
$db      = 'shoes_inventory';
$user    = 'root'; 
$pass    = ''; // Update with your DB password if applicable
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 1. Fetch KPI metrics dynamically 
$totalItems  = $pdo->query("SELECT COUNT(*) FROM stock")->fetchColumn();
$okStock     = $pdo->query("SELECT COUNT(*) FROM stock WHERE current_qty >= min_threshold")->fetchColumn();
$lowStock    = $pdo->query("SELECT COUNT(*) FROM stock WHERE current_qty < min_threshold")->fetchColumn();

// 2. Dynamic Categories for drop-down element
$categories  = $pdo->query("SELECT DISTINCT category FROM stock ORDER BY category ASC")->fetchAll();

// 3. Process Live Filtering Arguments
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';

// Base query string construction
$queryStr = "SELECT s.*, sup.name AS supplier_name 
             FROM stock s 
             JOIN suppliers sup ON s.supplier_id = sup.id WHERE 1=1";
$params = [];

if ($search !== '') {
    $queryStr .= " AND s.item_name LIKE :search";
    $params['search'] = '%' . $search . '%';
}

if ($categoryFilter !== '' && $categoryFilter !== 'All Categories') {
    $queryStr .= " AND s.category = :category";
    $params['category'] = $categoryFilter;
}

$queryStr .= " ORDER BY s.id DESC"; // Order matching table display sequence

$stmt = $pdo->prepare($queryStr);
$stmt->execute($params);
$inventoryItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoes Inventory System - Stock Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="stockstyle.css">
</head>
<body>

    <header class="navbar">
        <div class="nav-left">
            <i class="fa-solid fa-shoe-prints logo-icon"></i>
            <span class="system-title">Shoes Inventory System</span>
        </div>
        <nav class="nav-menu">
            <a href="#"><i class="fa-solid fa-chart-line text-info"></i> Dashboard</a>
            <a href="#"><i class="fa-solid fa-boxes-stacked text-warning"></i> Items</a>
            <a href="#"><i class="fa-solid fa-truck-field text-danger"></i> Suppliers</a>
            <a href="#" class="active"><i class="fa-solid fa-chart-simple text-primary"></i> Stock</a>
            <a href="#"><i class="fa-solid fa-square-poll-horizontal text-primary"></i> Transactions</a>
            <a href="#"><i class="fa-solid fa-users text-secondary"></i> Users</a>
        </nav>
        <div class="nav-right">
            <div class="user-profile">
                <i class="fa-solid fa-circle-user profile-icon"></i>
                <span>User1</span>
            </div>
            <button class="logout-btn"><i class="fa-solid fa-power-off"></i></button>
        </div>
    </header>

    <main class="main-container">
        <div class="breadcrumbs">
            <span class="page-title">Stock Management</span>
            <span class="file-name">stock.php</span>
        </div>

        <section class="summary-cards">
            <div class="card">
                <i class="fa-solid fa-shoe-prints card-icon text-brown"></i>
                <div class="card-value"><?= htmlspecialchars($totalItems) ?></div>
                <div class="card-label">Total Items Tracked</div>
            </div>
            <div class="card">
                <i class="fa-solid fa-square-check card-icon text-success"></i>
                <div class="card-value text-success"><?= htmlspecialchars($okStock) ?></div>
                <div class="card-label">OK Stock</div>
            </div>
            <div class="card">
                <i class="fa-solid fa-triangle-exclamation card-icon text-alert"></i>
                <div class="card-value text-alert"><?= htmlspecialchars($lowStock) ?></div>
                <div class="card-label">Low / Critical</div>
            </div>
        </section>

        <form method="GET" action="stock.php" class="filters-container">
            <input type="text" name="search" class="search-bar" placeholder="Search shoe name..." value="<?= htmlspecialchars($search) ?>">
            
            <div class="select-wrapper">
                <select name="category" class="category-select">
                    <option value="All Categories">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $categoryFilter === $cat['category'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-filter">Filter</button>
            <a href="stock.php" class="btn btn-reset" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">Reset</a>
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
                    <?php if (count($inventoryItems) > 0): ?>
                        <?php foreach ($inventoryItems as $row): 
                            $isLow = $row['current_qty'] < $row['min_threshold'];
                            $statusClass = $isLow ? 'badge-low' : 'badge-ok';
                            $statusText = $isLow ? 'Low' : 'OK';
                            $statusIcon = $isLow ? 'fa-triangle-exclamation' : 'fa-check';
                            $qtyColor = $isLow ? 'text-alert' : 'text-success';
                            
                            // Visual bar width logic (capped at 100%)
                            $maxCapacity = max($row['current_qty'], $row['min_threshold'] * 2);
                            $fillPercentage = ($maxCapacity > 0) ? min(($row['current_qty'] / $maxCapacity) * 100, 100) : 0;
                            $barClass = $isLow ? 'bar-alert' : 'bar-success';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                                <td class="text-muted"><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                <td>
                                    <span class="<?= $qtyColor ?> font-weight-bold">
                                        <?= htmlspecialchars(number_format($row['current_qty'], 0)) . ' ' . htmlspecialchars($row['unit']) ?>
                                    </span>
                                    <div class="progress-bar <?= $barClass ?>" style="width: <?= $fillPercentage ?>%;"></div>
                                </td>
                                <td><?= htmlspecialchars(number_format($row['min_threshold'], 0)) . ' ' . htmlspecialchars($row['unit']) ?></td>
                                <td>
                                    <span class="badge <?= $statusClass ?>">
                                        <i class="fa-solid <?= $statusIcon ?>"></i> <?= $statusText ?>
                                    </span>
                                </td>
                                <td class="text-muted text-sm">
                                    <?= date('M d, Y H:i', strtotime($row['last_updated'])) ?>
                                </td>
                                <td>
                                    <a href="stock_edit.php?id=<?= $row['id'] ?>" class="btn-action btn-edit" style="text-decoration: none; display: inline-block;">
                                        <i class="fa-solid fa-pencil"></i> Edit
                                    </a>
                                    <a href="stock_delete.php?id=<?= $row['id'] ?>" class="btn-action btn-delete" style="text-decoration: none; display: inline-block;" onclick="return confirm('Are you sure you want to permanently delete \'<?= htmlspecialchars(addslashes($row['item_name'])) ?>\' from inventory records?');">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 30px; color: #888;">No matching inventory items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>
