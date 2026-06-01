<?php
// frontend/Supplier.php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/suppliertab.php';
if (!function_exists('safe')) { function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/Supplierstyle.css">
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand"><div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div><span>ShoeInventory</span></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php" class="active"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info"><div class="user-name"><?= safe($_SESSION['username']) ?></div><div class="user-role">User</div></div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;">
                <div><h1>Suppliers Management</h1><p>Manage your supplier contacts and details</p></div>
                <a href="#add-supplier-modal" class="btn btn-primary">+ Add Supplier</a>
            </div>

            <form method="GET" action="Supplier.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search suppliers..." value="<?= safe($search ?? '') ?>">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <a href="Supplier.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Company</th><th>Contact</th><th>Category</th><th>Phone/Email</th><th>Status</th><th style="text-align:center;">Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($suppliers)): foreach ($suppliers as $row): ?>
                            <tr>
                                <td><?= (int)$row['order_id'] ?></td>
                                <td><strong><?= safe($row['company_name']) ?></strong></td>
                                <td><?= safe($row['contact_person'] ?? '') ?></td>
                                <td><?= safe($row['category'] ?? '') ?></td>
                                <td><?= safe($row['phone_email'] ?? '') ?></td>
                                <td><span class="badge <?= $row['status'] === 'Active' ? 'badge-success' : 'badge-neutral' ?>"><?= safe($row['status']) ?></span></td>
                                <td style="text-align:center;">
                                    <a href="Supplier.php?edit_id=<?= (int)$row['order_id'] ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                                    <a href="Supplier.php?delete_id=<?= (int)$row['order_id'] ?>" class="btn btn-danger btn-sm" style="text-decoration:none;" onclick="return confirm('Delete this supplier?');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="7">No suppliers found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Supplier Modal -->
    <div id="add-supplier-modal" class="modal-overlay hash-modal">
        <div class="modal-box">
            <div class="modal-header"><h2>Add Supplier</h2><a href="#" class="modal-close">&times;</a></div>
            <div class="modal-body">
                <form method="POST" action="Supplier.php">
                    <input type="hidden" name="action" value="add">
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Company Name *</label><input type="text" name="supplier_name" required></div>
                        <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person"></div>
                        <div class="form-group"><label>Category</label><input type="text" name="category"></div>
                        <div class="form-group full-width"><label>Phone / Email</label><input type="text" name="phone_email"></div>
                        <div class="form-group full-width"><label>Status</label><select name="active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                    </div>
                    <div class="modal-footer"><a href="#" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary">Add Supplier</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div id="edit-supplier-modal" class="modal-overlay hash-modal">
        <div class="modal-box">
            <div class="modal-header"><h2>Edit Supplier</h2><a href="Supplier.php" class="modal-close">&times;</a></div>
            <div class="modal-body">
                <form method="POST" action="Supplier.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= (int)($editing_supplier['order_id'] ?? 0) ?>">
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Company Name *</label><input type="text" name="supplier_name" value="<?= safe($editing_supplier['company_name'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person" value="<?= safe($editing_supplier['contact_person'] ?? '') ?>"></div>
                        <div class="form-group"><label>Category</label><input type="text" name="category" value="<?= safe($editing_supplier['category'] ?? '') ?>"></div>
                        <div class="form-group full-width"><label>Phone / Email</label><input type="text" name="phone_email" value="<?= safe($editing_supplier['phone_email'] ?? '') ?>"></div>
                        <div class="form-group full-width"><label>Status</label><select name="active"><option value="1" <?= ($editing_supplier['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option><option value="0" <?= ($editing_supplier['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
                    </div>
                    <div class="modal-footer"><a href="Supplier.php" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($editing_supplier): ?>
    <script>window.location.hash = "edit-supplier-modal";</script>
    <?php endif; ?>
</body>
</html>
