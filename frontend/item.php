<?php
// frontend/item.php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/itemtab.php';

// Best Practice: Define a helper for escaping output if not already loaded.
if (!function_exists('safe')) {
    function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/Item.css">
    <?php if ($editing_item): ?>
    <!-- Best Practice: Show edit modal immediately when edit_id is in URL -->
    <style>#editItemModal { opacity: 1; pointer-events: auto; }</style>
    <?php endif; ?>
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div>
                <span>ShoeInventory</span>
            </div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php" class="active"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= safe($_SESSION['username']) ?></div>
                    <div class="user-role">User</div>
                </div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header" style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h1>Items Management</h1>
                    <p>Add, edit, and manage shoe inventory items</p>
                </div>
                <a href="#addItemModal" class="btn btn-primary">+ Add New Item</a>
            </div>

            <form method="GET" action="item.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search shoes by name..." value="<?= safe($search) ?>">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <a href="item.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr><th>#</th><th>Shoe Model</th><th>Price</th><th>Supplier</th><th>Stock</th><th>Min</th><th style="text-align:center;">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): foreach ($items as $item):
                                $qty = (int)$item['quantity'];
                                $min = (int)$item['min_quantity'];
                            ?>
                            <tr>
                                <td><?= (int)$item['id'] ?></td>
                                <td><strong><?= safe($item['name']) ?></strong></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td><?= safe($item['supplier_name'] ?: '—') ?></td>
                                <td>
                                    <span class="badge <?= $qty <= $min ? 'badge-danger' : 'badge-success' ?>">
                                        <?= $qty ?> pairs
                                    </span>
                                </td>
                                <td><?= $min ?> pairs</td>
                                <td style="text-align:center;">
                                    <a href="item.php?edit_id=<?= (int)$item['id'] ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                                    <button class="btn btn-danger btn-sm" onclick="if(confirm('Delete this item?')) window.location.href='item.php?delete_id=<?= (int)$item['id'] ?>';">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="7">No items found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Item Modal -->
    <div class="modal-overlay hash-modal" id="addItemModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Add New Item</h2>
                <a href="#" class="modal-close">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="item.php">
                    <input type="hidden" name="action" value="add">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Shoe Model Name *</label>
                            <input type="text" name="item_name" placeholder="e.g. Air Max 90" required>
                        </div>
                        <div class="form-group">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" name="price" value="0.00" min="0">
                        </div>
                        <div class="form-group">
                            <label>Min. Alert Threshold</label>
                            <input type="number" name="min_quantity" value="5" min="0">
                        </div>
                        <div class="form-group full-width">
                            <label>Supplier</label>
                            <select name="supplier_id">
                                <option value="">— None —</option>
                                <?php foreach ($suppliers as $s): ?>
                                <option value="<?= (int)$s['id'] ?>"><?= safe($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($editing_item): ?>
    <!-- Edit Item Modal -->
    <div class="modal-overlay" id="editItemModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Edit Item #<?= (int)$editing_item['id'] ?></h2>
                <a href="item.php" class="modal-close">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="item.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= (int)$editing_item['id'] ?>">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Shoe Model Name *</label>
                            <input type="text" name="item_name" value="<?= safe($editing_item['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" name="price" value="<?= safe($editing_item['price']) ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label>Min. Alert Threshold</label>
                            <input type="number" name="min_quantity" value="<?= (int)$editing_item['min_quantity'] ?>" min="0">
                        </div>
                        <div class="form-group full-width">
                            <label>Supplier</label>
                            <select name="supplier_id">
                                <option value="">— None —</option>
                                <?php foreach ($suppliers as $s): ?>
                                <option value="<?= (int)$s['id'] ?>" <?= $editing_item['supplier_id'] == $s['id'] ? 'selected' : '' ?>><?= safe($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="item.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
