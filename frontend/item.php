<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/itemtab.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoes Inventory System</title>
    <link rel="stylesheet" href="../css/Item.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <div class="logo-container" style="background: linear-gradient(135deg, #3b82f6, #6d28d9); padding: 5px; border-radius: 8px; margin-right: 10px;">
                <img src="../images/shoes.png" alt="Logo" style="width: 30px; height: 30px; filter: brightness(0) invert(1);">
            </div>
            <span class="nav-brand">Shoes Inventory System</span>
        </div>
        <ul class="nav-menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="item.php" class="active">Items</a></li>
            <li><a href="Supplier.php">Suppliers</a></li>
            <li><a href="transactions.php">Transactions</a></li>
            <li><a href="stock.php">Stock</a></li>
            <li><a href="user.php">Users</a></li>
            <li><a href="reports.php">Reports</a></li>
        </ul>
        <div class="nav-right">
            <div class="user-badge" style="display: flex; align-items: center; gap: 8px;">
                <div class="profile-avatar-glyph" style="background-color: #e5e7eb; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #4b5563;">
                    <i class="fa-solid fa-user"></i>
                </div>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <button class="logout-pill-btn" onclick="window.location.href='logout.php';">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </button>
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
                            <th>Minimum Item</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): 
                                $quantity = (int)$item['quantity'];
                                $min_qty  = (int)$item['min_quantity'];
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
                                        <button class="row-btn delete-action-btn" onclick="if(confirm('Are you sure you want to delete this shoe line?')) window.location.href='item.php?delete_id=<?php echo (int)$item['id']; ?>';">Del</button>
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