<?php
// Supplier.php

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

// Handle Form Actions (Add OR Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // ACTION: ADD NEW SUPPLIER
    if ($action === 'add') {
        $name   = trim($_POST['supplier_name'] ?? '');
        $status = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

        if ($name !== '') {
            $gapQuery = "SELECT MIN(unused.order_id) AS next_id 
                         FROM (
                             SELECT 1 AS order_id 
                             UNION ALL 
                             SELECT order_id + 1 FROM suppliers
                         ) AS unused 
                         LEFT JOIN suppliers USING (order_id) 
                         WHERE suppliers.order_id IS NULL";
            
            $gapStmt = $pdo->query($gapQuery);
            $result = $gapStmt->fetch();
            $next_id = isset($result['next_id']) ? intval($result['next_id']) : 1;

            $stmt = $pdo->prepare('INSERT INTO suppliers (order_id, company_name, status) VALUES (?, ?, ?)');
            $stmt->execute([$next_id, $name, $status]);
        }
        header('Location: Supplier.php');
        exit;
    }
    
    // ACTION: UPDATE EXISTING SUPPLIER
    if ($action === 'edit') {
        $id     = intval($_POST['id'] ?? 0);
        $name   = trim($_POST['supplier_name'] ?? '');
        $status = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

        if ($id > 0 && $name !== '') {
            $stmt = $pdo->prepare('UPDATE suppliers SET company_name = ?, status = ? WHERE order_id = ?');
            $stmt->execute([$name, $status, $id]);
        }
        header('Location: Supplier.php');
        exit;
    }
}

// ACTION: DELETE SUPPLIER
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare('DELETE FROM suppliers WHERE order_id = ?');
    $stmt->execute([$id]);
    header('Location: Supplier.php');
    exit;
}

// HELPER: FETCH INDIVIDUAL ENTRY FOR ACTIVE EDIT STATE
$editing_supplier = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $pdo->prepare('SELECT * FROM suppliers WHERE order_id = ?');
    $stmt->execute([$edit_id]);
    $editing_supplier = $stmt->fetch();
}

// SEARCH & RENDER QUERY DATATABLE
$search = trim($_GET['search'] ?? '');
$sql = 'SELECT * FROM suppliers WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND company_name LIKE ?';
    $params[] = "%$search%";
}
$sql .= ' ORDER BY order_id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$suppliers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoes Inventory System - Suppliers</title>
    <link rel="stylesheet" href="../css/Item.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <div class="shoe-logo-svg">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>
            </div>
            <span class="nav-brand">Shoes Inventory System</span>
        </div>

        <ul class="nav-menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="item.php">Items</a></li>
            <li><a href="Supplier.php" class="active">Suppliers</a></li>
            <li><a href="stock.php">Stock</a></li>
            <li><a href="transactions.php">Transactions</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="reports.php">Reports</a></li>
        </ul>

        <div class="nav-right">
            <div class="user-profile">
                <div class="profile-avatar-glyph">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <span class="username">mark</span>
            </div>
            <button class="logout-pill-btn" onclick="window.location.href='logout.php';" title="Logout"></button>
        </div>
    </nav>

    <main class="purple-canvas-panel">
        <div class="section-heading-row">
            <h1 class="page-title-label">Suppliers Management</h1>
            <?php if (!$editing_supplier): ?>
                <a href="#add-supplier-modal" class="btn-add-item-trigger">+ Add New Supplier</a>
            <?php endif; ?>
        </div>

        <?php if ($editing_supplier): ?>
        <div class="curved-ledger-table-card" style="margin-bottom: 25px;">
            <div class="modal-header" style="padding: 12px 0 20px 0;">
                <h2 class="modal-title-text">Update Existing Supplier Details (#<?php echo (int)$editing_supplier['order_id']; ?>)</h2>
            </div>
            <form method="POST" action="Supplier.php" style="margin-top: 15px;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo (int)$editing_supplier['order_id']; ?>">
                <div class="modal-form-grid-layout">
                    <div class="input-form-block">
                        <label>Supplier Brand Name *</label>
                        <input type="text" name="supplier_name" value="<?php echo htmlspecialchars($editing_supplier['company_name']); ?>" required>
                    </div>
                    <div class="input-form-block">
                        <label>Operational Status</label>
                        <select name="active">
                            <option value="1" <?php echo $editing_supplier['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo $editing_supplier['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-action-footer" style="margin-top: 20px;">
                    <a href="Supplier.php" class="modal-footer-btn btn-modal-cancel">Cancel</a>
                    <button type="submit" class="modal-footer-btn btn-modal-confirm" style="background-color: #3b82f6;">Update Details</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <form method="GET" action="Supplier.php" class="search-filter-pill-capsule">
            <input type="text" name="search" class="search-box-field" placeholder="Search suppliers by name..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="action-btn execution-search-btn">Search</button>
            <button type="button" class="action-btn execution-reset-btn" onclick="window.location.href='Supplier.php';">Reset</button>
        </form>

        <div class="curved-ledger-table-card">
            <div class="table-scroll-axis-frame">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 12%;">ID</th>
                            <th>Supplier Brand Name</th>
                            <th>Status Badge</th>
                            <th style="text-align: center; width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($suppliers)): ?>
                            <?php foreach ($suppliers as $row): 
                                $isLow = ($row['status'] !== 'Active');
                            ?>
                            <tr>
                                <td class="row-index-id">#<?php echo (int)$row['order_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['company_name']); ?></strong></td>
                                <td>
                                    <span class="qty-indicator <?php echo $isLow ? 'low' : ''; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons-inline-flex" style="justify-content: center;">
                                        <a href="Supplier.php?edit_id=<?php echo (int)$row['order_id']; ?>" class="row-btn edit-action-btn">Edit</a>
                                        <a href="Supplier.php?delete_id=<?php echo (int)$row['order_id']; ?>" class="row-btn delete-action-btn" onclick="return confirm('Delete this supplier relationship permanently?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; padding:32px; color:#6b7280;">No records found matching your selection.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="add-supplier-modal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title-text">Add New Supplier Connection</span>
                <a href="#" class="close-frame-btn">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="Supplier.php">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-form-grid-layout">
                        <div class="input-form-block full-width-span-row">
                            <label>Supplier Brand Name *</label>
                            <input type="text" name="supplier_name" placeholder="e.g., Nike, Adidas" required>
                        </div>
                        <div class="input-form-block full-width-span-row">
                            <label>Operational Status</label>
                            <select name="active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-action-footer">
                        <a href="#" class="modal-footer-btn btn-modal-cancel">Cancel</a>
                        <button type="submit" class="modal-footer-btn btn-modal-confirm">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
