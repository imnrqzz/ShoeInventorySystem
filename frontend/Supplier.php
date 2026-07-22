<?php
// frontend/Supplier.php

// Best Practice: Use a shared auth component instead of repeating session/cache/redirect code.
require_once __DIR__ . '/components/auth.php';

require_once __DIR__ . '/../backend/suppliertab.php';

// Set component variables
$pageTitle = 'Suppliers';              // used by head.php
$pageCss = 'Supplierstyle.css';        // used by head.php
$activePage = 'suppliers';             // used by sidebar.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/components/head.php'; ?>
</head>
<body>
    <div class="page-wrapper">
        <?php require __DIR__ . '/components/sidebar.php'; ?>

        <main class="main-content">
<?php
$pageSubtitle = 'Manage your supplier contacts and details';
$headerAction = ['label' => '+ Add Supplier', 'href' => '#add-supplier-modal'];
require __DIR__ . '/components/page_header.php';
?>

<?php
$toolbarAction = 'Supplier.php';
$toolbarSearch = $search ?? '';
$toolbarPlaceholder = 'Search suppliers...';
require __DIR__ . '/components/toolbar.php';
?>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Company</th><th>Contact</th><th>Category</th><th>Phone/Email</th><th>Status</th><th class="actions-cell">Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($suppliers)): foreach ($suppliers as $row): ?>
                            <tr>
                                <td><?= (int)$row['order_id'] ?></td>
                                <td><strong><?= safe($row['company_name']) ?></strong></td>
                                <td><?= safe($row['contact_person'] ?? '') ?></td>
                                <td><?= safe($row['category'] ?? '') ?></td>
                                <td><?= safe($row['phone_email'] ?? '') ?></td>
                                <td><span class="badge <?= $row['status'] === 'Active' ? 'badge-success' : 'badge-neutral' ?>"><?= safe($row['status']) ?></span></td>
                                <td class="actions-cell">
                                    <a href="Supplier.php?edit_id=<?= (int)$row['order_id'] ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                                    <button class="btn btn-danger btn-sm" onclick="confirmDelete('Are you sure you want to delete this supplier? This action cannot be undone.', 'Supplier.php?delete_id=<?= (int)$row['order_id'] ?>')">Delete</button>
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
                <form method="POST" action="Supplier.php" data-validate novalidate>
                    <input type="hidden" name="action" value="add">
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Company Name *</label><input type="text" name="supplier_name" required minlength="2" maxlength="64"><span class="field-error"></span></div>
                        <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person"></div>
                        <div class="form-group"><label>Category</label><input type="text" name="category"></div>
                        <div class="form-group full-width"><label>Phone / Email</label><input type="text" name="phone_email"></div>
                        <div class="form-group full-width"><label>Status</label><select name="active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                    </div>
                    <div class="modal-footer"><a href="#" class="btn btn-secondary">Cancel</a><?= csrf_field() ?><button type="submit" class="btn btn-primary">Add Supplier</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div id="edit-supplier-modal" class="modal-overlay hash-modal">
        <div class="modal-box">
            <div class="modal-header"><h2>Edit Supplier</h2><a href="Supplier.php" class="modal-close">&times;</a></div>
            <div class="modal-body">
                <form method="POST" action="Supplier.php" data-validate novalidate>
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= (int)($editing_supplier['order_id'] ?? 0) ?>">
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Company Name *</label><input type="text" name="supplier_name" value="<?= safe($editing_supplier['company_name'] ?? '') ?>" required minlength="2" maxlength="64"><span class="field-error"></span></div>
                        <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person" value="<?= safe($editing_supplier['contact_person'] ?? '') ?>"></div>
                        <div class="form-group"><label>Category</label><input type="text" name="category" value="<?= safe($editing_supplier['category'] ?? '') ?>"></div>
                        <div class="form-group full-width"><label>Phone / Email</label><input type="text" name="phone_email" value="<?= safe($editing_supplier['phone_email'] ?? '') ?>"></div>
                        <div class="form-group full-width"><label>Status</label><select name="active"><option value="1" <?= ($editing_supplier['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option><option value="0" <?= ($editing_supplier['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
                    </div>
                    <div class="modal-footer"><a href="Supplier.php" class="btn btn-secondary">Cancel</a><?= csrf_field() ?><button type="submit" class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($editing_supplier): ?>
    <script>window.location.hash = "edit-supplier-modal";</script>
    <?php endif; ?>
    <?php require __DIR__ . '/components/footer.php'; ?>
