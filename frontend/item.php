<?php
// item.php

$host = 'localhost';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$database_name = 'pos_inventory_system';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database_name;charset=$charset", $user, $pass, $options);
} catch (\PDOException $e) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fff0f0; border-left:5px solid #ff4d4d; margin:20px;'><strong>Database Connection Failed!</strong><br>" . htmlspecialchars($e->getMessage()) . "</div>");
}

// Fetch all active suppliers for form dropdowns
try {
    $supplierStmt = $pdo->query("SELECT id, name FROM suppliers WHERE active = 1 ORDER BY name ASC");
    $suppliers = $supplierStmt->fetchAll();
} catch (\PDOException $e) {
    $suppliers = [];
}

// Handle Form Submissions (Add OR Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CREATE NEW ITEM WITH AUTOMATIC ID REUSE
    if ($action === 'add') {
        $name          = trim($_POST['item_name'] ?? '');
        $supplier_id   = $_POST['supplier_id'] !== '' ? intval($_POST['supplier_id']) : null;
        $min_quantity  = intval($_POST['min_quantity'] ?? 0);
        $price         = floatval($_POST['price'] ?? 0);

        if ($name !== '') {
            // Find the lowest missing sequence ID to fill the gap
            $gapQuery = "SELECT MIN(unused.id) AS next_id 
                         FROM (
                             SELECT 1 AS id 
                             UNION ALL 
                             SELECT id + 1 FROM items
                         ) AS unused 
                         LEFT JOIN items USING (id) 
                         WHERE items.id IS NULL";
            
            $gapStmt = $pdo->query($gapQuery);
            $result = $gapStmt->fetch();
            $next_id = isset($result['next_id']) ? intval($result['next_id']) : 1;

            // Force insert using the missing ID number
            $stmt = $pdo->prepare('INSERT INTO items (id, name, supplier_id, quantity, min_quantity, price) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$next_id, $name, $supplier_id, 0, $min_quantity, $price]);
        }
        header('Location: item.php');
        exit;
    }
    
    // UPDATE EXISTING ITEM
    if ($action === 'edit') {
        $id            = intval($_POST['id'] ?? 0);
        $name          = trim($_POST['item_name'] ?? '');
        $supplier_id   = $_POST['supplier_id'] !== '' ? intval($_POST['supplier_id']) : null;
        $min_quantity  = intval($_POST['min_quantity'] ?? 0);
        $price         = floatval($_POST['price'] ?? 0);

        if ($id > 0 && $name !== '') {
            $stmt = $pdo->prepare('UPDATE items SET name = ?, supplier_id = ?, min_quantity = ?, price = ? WHERE id = ?');
            $stmt->execute([$name, $supplier_id, $min_quantity, $price, $id]);
        }
        header('Location: item.php');
        exit;
    }
}

// DELETE ITEM
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare('DELETE FROM items WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: item.php');
    exit;
}

// FETCH INDIVIDUAL ITEM DATA IF EDITING
$editing_item = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $pdo->prepare('SELECT * FROM items WHERE id = ?');
    $stmt->execute([$edit_id]);
    $editing_item = $stmt->fetch();
}

// SEARCH & LIST ITEMS
$search = trim($_GET['search'] ?? '');
$sql = 'SELECT items.*, suppliers.name AS supplier_name 
        FROM items 
        LEFT JOIN suppliers ON items.supplier_id = suppliers.id 
        WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND items.name LIKE ?';
    $params[] = "%$search%";
}
$sql .= ' ORDER BY items.id DESC';

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
    <link rel="stylesheet" href="../css/Item.css">
    <style>
        /* Automatically opens edit popup frame dynamically if data is active */
        <?php if ($editing_item): ?>
        #editItemModal {
            opacity: 1;
            pointer-events: auto;
        }
        <?php endif; ?>
    </style>
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
            </div>
            <button class="logout-pill-btn" onclick="alert('Logging out...');"></button>
        </div>
    </nav>

    <main class="purple-canvas-panel">
        <div class="section-heading-row">
            <h1 class="page-title-label">Items Management</h1>
            <a href="#addItemModal" class="btn-add-item-trigger">+ Add New Shoe Item</a>
        </div>

        <form method="GET" action="item.php" class="search-filter-pill-capsule">
            <input type="text" name="search" class="search-box-field" placeholder="Search shoes by name..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="action-btn execution-search-btn">Search</button>
            <button type="button" class="action-btn execution-reset-btn" onclick="window.location.href='item.php';">Reset</button>
        </form>

        <div class="curved-ledger-table-card">
            <div class="table-scroll-axis-frame">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Shoe Model Name</th>
                            <th>Price</th>
                            <th>Supplier</th>
                            <th>Current Stock</th>
                            <th>Min. Alert Threshold</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): 
                                $quantity = (int)$item['quantity'];
                                $min_qty = (int)$item['min_quantity'];
                                $lowClass = $quantity <= $min_qty ? 'low' : '';
                            ?>
                            <tr>
                                <td><span class="row-index-id"><?php echo (int)$item['id']; ?></span></td>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($item['supplier_name'] ?: '—'); ?></td>
                                <td><span class="qty-indicator <?php echo $lowClass; ?>"><?php echo $quantity; ?> pairs</span></td>
                                <td><?php echo $min_qty; ?> pairs</td>
                                <td>
                                    <div class="action-buttons-inline-flex">
                                        <a href="item.php?edit_id=<?php echo (int)$item['id']; ?>" class="row-btn edit-action-btn" style="text-decoration: none;">Edit</a>
                                        <button class="row-btn delete-action-btn" onclick="if(confirm('Are you sure?')) window.location.href='item.php?delete_id=<?php echo (int)$item['id']; ?>';">Del</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center;padding:24px;color:#8e8e93;">No matching shoes found in inventory.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="addItemModal">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title-text">✦ Add New Shoe Item</div>
                <a href="#" class="close-frame-btn">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="item.php">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-form-grid-layout">
                        <div class="input-form-block full-width-span-row">
                            <label>Shoe Model Name *</label>
                            <input type="text" name="item_name" placeholder="e.g. Air Max 90" required>
                        </div>
                        <div class="input-form-block">
                            <label>Retail Price ($)</label>
                            <input type="number" step="0.01" name="price" value="0.00" min="0">
                        </div>
                        <div class="input-form-block">
                            <label>Min. Alert Threshold</label>
                            <input type="number" name="min_quantity" value="5" min="0">
                        </div>
                        <div class="input-form-block full-width-span-row">
                            <label>Supplier</label>
                            <select name="supplier_id">
                                <option value="">— None —</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo (int)$supplier['id']; ?>">
                                        <?php echo htmlspecialchars($supplier['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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

    <?php if ($editing_item): ?>
    <div class="modal-overlay" id="editItemModal">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title-text">✦ Edit Shoe Item (#<?php echo (int)$editing_item['id']; ?>)</div>
                <a href="item.php" class="close-frame-btn">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="item.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo (int)$editing_item['id']; ?>">
                    
                    <div class="modal-form-grid-layout">
                        <div class="input-form-block full-width-span-row">
                            <label>Shoe Model Name *</label>
                            <input type="text" name="item_name" value="<?php echo htmlspecialchars($editing_item['name']); ?>" required>
                        </div>
                        <div class="input-form-block">
                            <label>Retail Price ($)</label>
                            <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($editing_item['price']); ?>" min="0">
                        </div>
                        <div class="input-form-block">
                            <label>Min. Alert Threshold</label>
                            <input type="number" name="min_quantity" value="<?php echo (int)$editing_item['min_quantity']; ?>" min="0">
                        </div>
                        <div class="input-form-block full-width-span-row">
                            <label>Supplier</label>
                            <select name="supplier_id">
                                <option value="">— None —</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo (int)$supplier['id']; ?>" <?php echo $editing_item['supplier_id'] == $supplier['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($supplier['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-action-footer">
                        <a href="item.php" class="modal-footer-btn btn-modal-cancel">Cancel</a>
                        <button type="submit" class="modal-footer-btn btn-modal-confirm">Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
