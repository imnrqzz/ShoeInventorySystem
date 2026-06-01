<?php
// frontend/item.php

// Best Practice: Use a shared auth component instead of repeating session/cache/redirect code.
require_once __DIR__ . '/components/auth.php';

require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/itemtab.php';

// Set component variables
$pageTitle = 'Items';      // used by head.php
$pageCss = 'Item.css';     // used by head.php
$activePage = 'items';     // used by sidebar.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/components/head.php'; ?>
    <?php if ($editing_item): ?>
    <!-- Best Practice: Show edit modal immediately when edit_id is in URL -->
    <style>#editItemModal { opacity: 1; pointer-events: auto; }</style>
    <?php endif; ?>
</head>
<body>
    <div class="page-wrapper">
        <?php require __DIR__ . '/components/sidebar.php'; ?>

        <main class="main-content">
<?php
$pageSubtitle = 'Add, edit, and manage shoe inventory items';
$headerAction = ['label' => '+ Add New Item', 'href' => '#addItemModal'];
require __DIR__ . '/components/page_header.php';
?>

<?php
$toolbarAction = 'item.php';
$toolbarSearch = $search;
$toolbarPlaceholder = 'Search shoes by name...';
require __DIR__ . '/components/toolbar.php';
?>

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
                                    <button class="btn btn-danger btn-sm" onclick="confirmDelete('Are you sure you want to delete this item? This action cannot be undone.', 'item.php?delete_id=<?= (int)$item['id'] ?>')">Delete</button>
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
                            <!-- Best Practice: step="0.01" allows cents (e.g. $129.99).
                                 min="0" prevents negative prices. -->
                            <input type="number" step="0.01" name="price" value="0.00" min="0">
                        </div>
                        <div class="form-group">
                            <label>Min. Alert Threshold</label>
                            <!-- Best Practice: step="1" restricts to whole numbers.
                                 Shoes are counted in pairs, not fractions. -->
                            <input type="number" step="1" name="min_quantity" value="5" min="0">
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
                            <input type="number" step="0.01" name="price" value="<?= number_format((float)$editing_item['price'], 2, '.', '') ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label>Min. Alert Threshold</label>
                            <input type="number" step="1" name="min_quantity" value="<?= (int)$editing_item['min_quantity'] ?>" min="0">
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
    <?php require __DIR__ . '/components/footer.php'; ?>
