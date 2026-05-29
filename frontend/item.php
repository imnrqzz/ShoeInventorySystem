<?php
// item.php
// --- 1. CONFIGURATION & DATABASE CONNECTION ---
$host = '127.0.0.1'; // 127.0.0.1 prevents IPv6 lookup delays in XAMPP
$db   = 'db_items';
$user = 'root';
$pass = '';
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
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// --- 2. HANDLE SUBMITTING A NEW ITEM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['item_name']);
    $category = trim($_POST['category']);
    $unit = trim($_POST['unit']);
    $unit_price = floatval($_POST['unit_price']);
    $supplier = trim($_POST['supplier']);
    $min_threshold = floatval($_POST['min_threshold']);
    $initial_stock = 0.00; 

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO items (name, category, unit, unit_price, supplier, stock, min_threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $unit, $unit_price, $supplier, $initial_stock, $min_threshold]);
    }
    
    header("Location: item.php");
    exit;
}

// --- 3. HANDLE DELETING AN ITEM ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([$delete_id]);
    
    header("Location: item.php");
    exit;
}

// --- 4. HANDLE SEARCH & FILTERS CONTROLS ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category_filter']) ? trim($_GET['category_filter']) : 'All Categories';

$query = "SELECT * FROM items WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND name LIKE ?";
    $params[] = "%$search%";
}

if ($category_filter !== 'All Categories' && !empty($category_filter)) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
}

$query .= " ORDER BY id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoes Inventory System</title>
    <link rel="stylesheet" href="Item.css">
</head>
<body id="items-view">

    <header class="navbar">
        <div class="brand">
            <svg class="shoe-logo-svg" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                <path d="M502.7 266.5c-7.7-14.7-22.3-25.1-39.3-27.9l-79.6-12.9c-14.1-2.3-28.5 2.1-39.1 11.9l-45.4 42c-15.1 14-36.1 19.8-56.3 15.6l-85.1-17.7c-17.8-3.7-36.1 1.7-49.4 14.5L14.7 334c-12.2 11.8-16.1 29.8-9.8 45.7l19.6 49.1c6.3 15.8 21.6 26.2 38.6 26.2H456c24.3 0 44.9-17.2 49.2-41.1l14.9-82.3c2.9-16.1-2.4-32.5-17.4-45.1zM448 400H64c-3.4 0-6.5-2.1-7.7-5.3l-12-30.1 25.1-24.3c19.9-19.3 47.4-27.5 74.2-22l85.1 17.7c30.3 6.3 61.8-2.4 84.5-23.4l45.4-42c2.1-2 5-2.9 7.8-2.4l79.6 12.9c3.4.6 6.3 2.6 7.9 5.6L464 321.4 451.7 389c-.9 4.8-5 8.2-9.7 8.2z"/>
            </svg>
            <span class="brand-title">Shoes Inventory System</span>
        </div>
        
        <nav>
            <a href="index.php" id="nav-dash">Dashboard</a>
            <a href="item.php" id="nav-items">Items</a>
            <a href="suppliers.php">Supplier</a>
            <a href="stock.php">Stock</a>
            <a href="transactions.php">Transactions</a>
            <a href="users.php">Users</a>
            <a href="reports.php">Reports</a>
        </nav>

        <div class="actions">
            <div class="user-profile">
                <svg class="profile-avatar-glyph" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5-4-8-4z"/></svg>
                <span class="username">Admin Manager</span>
            </div>
            <button class="logout-pill-btn" onclick="alert('Logging out...');">
                <svg viewBox="0 0 24 24" style="width:14px; height:14px; fill:currentColor;"><path d="M13 3h-2v10h2V3zm4.45 2.14l-1.42 1.42C17.46 7.73 18 9.3 18 11c0 3.31-2.69 6-6 6s-6-2.69-6-6c0-1.7.54-3.27 1.48-4.43L6.05 5.14C4.74 6.71 4 8.76 4 11c0 4.42 3.58 8 8 8s8-3.58 8-8c0-2.24-.74-4.29-2.05-5.86z"/></svg>
                Logout
            </button>
        </div>
    </header>

    <main class="purple-canvas-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
            <h1 class="page-title-label">
                <span class="package-box-icon">📦</span> Items Management
            </h1>
            <a href="#addItemModal" style="background-color: #ffffff; color: #5c6bc0; text-decoration: none; padding: 10px 20px; border-radius: 25px; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">+ Add New Item</a>
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

        <div style="color: rgba(255,255,255,0.85); font-weight: 600; font-size: 1.1rem; margin: 25px 0 12px 5px;">Inventory Ledger</div>

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
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($items) > 0): ?>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($item['supplier'] ?: '—'); ?></td>
                                <td>
                                    <span style="<?php echo ($item['stock'] <= $item['min_threshold']) ? 'color: #ff1744; font-weight: bold;' : ''; ?>">
                                        <?php echo number_format($item['stock'], 2) . ' ' . htmlspecialchars($item['unit']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($item['min_threshold'], 2); ?></td>
                                <td>
                                    <div class="action-buttons-inline-flex" style="justify-content: center;">
                                        <button class="row-btn edit-action-btn" onclick="alert('Edit implementation for ID <?php echo $item['id']; ?>');">Edit</button>
                                        <button class="row-btn delete-action-btn" onclick="if(confirm('Are you sure you want to delete this item?')) window.location.href='item.php?delete_id=<?php echo $item['id']; ?>';">Del</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 24px; color: #8e8e93;">No matching items found in inventory database.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <style>
        .modal-overlay {
            position: fixed; top: 0; bottom: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(3px);
            opacity: 0; pointer-events: none; transition: opacity 0.2s ease; z-index: 1000;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-overlay:target { opacity: 1; pointer-events: auto; }
        .modal-box {
            background: white; padding: 30px; border-radius: 16px; width: 500px; max-width: 90%;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2); position: relative;
        }
        .modal-form-grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 20px 0; }
        .input-form-block { display: flex; flex-direction: column; gap: 6px; }
        .input-form-block.full-width-span-row { grid-column: span 2; }
        .input-form-block label { font-size: 0.82rem; font-weight: 600; color: #4a5568; }
        .input-form-block input, .input-form-block select { padding: 10px; border: 1px solid #cbd5e0; border-radius: 6px; outline: none; font-size: 0.9rem; }
        .modal-action-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .modal-footer-btn { padding: 10px 20px; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 0.88rem; cursor: pointer; border: none; }
        .btn-modal-cancel { background: #edf2f7; color: #4a5568; }
        .btn-modal-confirm { background: #5c6bc0; color: white; }
    </style>

    <div class="modal-overlay" id="addItemModal">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                <h3 style="color: #1a202c; font-size: 1.2rem;">✦ Add New Item</h3>
                <a href="#" style="text-decoration: none; color: #a0aec0; font-size: 1.5rem; line-height: 1;">&times;</a>
            </div>
            <form method="POST" action="item.php">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-form-grid-layout">
                    <div class="input-form-block full-width-span-row">
                        <label>Item Name *</label>
                        <input type="text" name="item_name" placeholder="e.g. Air Max 90" required>
                    </div>
                    <div class="input-form-block">
                        <label>Category</label>
                        <input type="text" name="category" placeholder="e.g. Running">
                    </div>
                    <div class="input-form-block">
                        <label>Unit</label>
                        <input type="text" name="unit" placeholder="e.g. Pairs">
                    </div>
                    <div class="input-form-block">
                        <label>Unit Price (₱)</label>
                        <input type="number" step="0.01" name="unit_price" value="0.00">
                    </div>
                    <div class="input-form-block">
                        <label>Min. Threshold</label>
                        <input type="number" step="0.01" name="min_threshold" value="10.00">
                    </div>
                    <div class="input-form-block full-width-span-row">
                        <label>Supplier</label>
                        <select name="supplier">
                            <option value="">— None —</option>
                            <option value="SugarSweet Co.">SugarSweet Co.</option>
                            <option value="BenCafe Roasters">BenCafe Roasters</option>
                            <option value="Supplier A">Supplier A</option>
                            <option value="Supplier B">Supplier B</option>
                        </select>
                    </div>
                </div>
                <div class="modal-action-footer">
                    <a href="#" class="modal-footer-btn btn-modal-cancel">Cancel</a>
                    <button type="submit" class="modal-footer-btn btn-modal-confirm">✓ Save Item</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>