<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/suppliertab.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoes Inventory System - Suppliers</title>
    <link rel="stylesheet" href="../css/Supplierstyle.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <div class="logo"><img src="../images/shoes.png" alt="Shoes Logo" /></div>
            <span class="nav-brand">Shoes Inventory System</span>
        </div>
        <ul class="nav-menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="item.php">Items</a></li>
            <li><a href="Supplier.php" class="active">Suppliers</a></li>
            <li><a href="transactions.php">Transactions</a></li>
            <li><a href="stock.php">Stock</a></li>
            <li><a href="user.php">Users</a></li>
            <li><a href="reports.php">Reports</a></li>
        </ul>
        <div class="nav-right">
            <div class="user-badge" style="display: flex; align-items: center; gap: 10px; padding: 5px 15px;">
                <div class="profile-avatar-glyph" style="background-color: #e5e7eb; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #4b5563;">
                    <i class="fa-solid fa-user"></i> 
                </div>
                <span style="font-weight: 500;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            
            <button class="logout-pill-btn" onclick="window.location.href='logout.php';" title="Logout" style="margin-left: 10px;">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </div>
    </nav>

    <main class="purple-canvas-panel">
        <div class="section-heading-row">
            <h1 class="page-title-label">Suppliers Management</h1>
            <a href="#add-supplier-modal" class="btn-add-item-trigger">+ Add New Supplier</a>
        </div>

        <form method="GET" action="Supplier.php" class="search-filter-pill-capsule">
            <input type="text" name="search" class="search-box-field" placeholder="Search suppliers..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            <button type="submit" class="action-btn execution-search-btn">Search</button>
            <button type="button" class="action-btn execution-reset-btn" onclick="window.location.href='Supplier.php';">Reset</button>
        </form>

        <div class="curved-ledger-table-card">
            <div class="table-scroll-axis-frame">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Supplier Brand Name</th><th>Contact Person</th>
                            <th>Category</th><th>Phone / Email</th><th>Status</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($suppliers)): ?>
                            <?php foreach ($suppliers as $row): ?>
                            <tr>
                                <td class="row-index-id">#<?php echo (int)$row['order_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['company_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['contact_person'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['category'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['phone_email'] ?? ''); ?></td>
                                <td>
                                    <span class="qty-indicator <?php echo ($row['status'] !== 'Active') ? 'low' : ''; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons-inline-flex" style="display: flex; gap: 8px; justify-content: center;">
                                        <a href="Supplier.php?edit_id=<?php echo (int)$row['order_id']; ?>" class="row-btn edit-action-btn">Edit</a>
                                        <a href="Supplier.php?delete_id=<?php echo (int)$row['order_id']; ?>" 
                                           class="row-btn delete-action-btn" 
                                           style="background: rgba(239, 68, 68, 0.08); color: #ef4444; padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 0.85rem;"
                                           onclick="return confirm('Delete this supplier relationship permanently?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center; padding:32px; color:#6b7280;">No records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="add-supplier-modal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title-text">Add New Supplier</span>
                <a href="#" class="close-frame-btn">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="Supplier.php">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="modal-form-grid-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="input-form-block" style="grid-column: span 2;">
                            <label>Supplier Brand Name *</label>
                            <input type="text" name="supplier_name" required>
                        </div>
                        <div class="input-form-block"><label>Contact Person</label><input type="text" name="contact_person"></div>
                        <div class="input-form-block"><label>Category</label><input type="text" name="category"></div>
                        <div class="input-form-block" style="grid-column: span 2;"><label>Phone / Email</label><input type="text" name="phone_email"></div>
                        <div class="input-form-block" style="grid-column: span 2;">
                            <label>Initial Status</label>
                            <select name="active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-action-footer">
                        <a href="#" class="modal-footer-btn btn-modal-cancel">Cancel</a>
                        <button type="submit" class="modal-footer-btn btn-modal-confirm">Add Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="edit-supplier-modal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title-text">Edit Supplier Details</span>
                <a href="#" class="close-frame-btn">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="Supplier.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo (int)($editing_supplier['order_id'] ?? 0); ?>">
                    
                    <div class="modal-form-grid-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="input-form-block" style="grid-column: span 2;">
                            <label>Supplier Brand Name *</label>
                            <input type="text" name="supplier_name" value="<?php echo htmlspecialchars($editing_supplier['company_name'] ?? ''); ?>" required>
                        </div>
                        <div class="input-form-block"><label>Contact Person</label><input type="text" name="contact_person" value="<?php echo htmlspecialchars($editing_supplier['contact_person'] ?? ''); ?>"></div>
                        <div class="input-form-block"><label>Category</label><input type="text" name="category" value="<?php echo htmlspecialchars($editing_supplier['category'] ?? ''); ?>"></div>
                        <div class="input-form-block" style="grid-column: span 2;"><label>Phone / Email</label><input type="text" name="phone_email" value="<?php echo htmlspecialchars($editing_supplier['phone_email'] ?? ''); ?>"></div>
                        <div class="input-form-block" style="grid-column: span 2;">
                            <label>Operational Status</label>
                            <select name="active">
                                <option value="1" <?php echo ($editing_supplier['status'] ?? '') === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($editing_supplier['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-action-footer">
                        <a href="Supplier.php" class="modal-footer-btn btn-modal-cancel">Cancel</a>
                        <button type="submit" class="modal-footer-btn btn-modal-confirm">Update Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($editing_supplier): ?>
    <script>window.location.hash = "edit-supplier-modal";</script>
    <?php endif; ?>
</body>
</html>