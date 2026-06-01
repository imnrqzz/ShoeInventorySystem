<?php
// frontend/stock.php

// Best Practice: Use a shared auth component instead of repeating session/cache/redirect code.
require_once __DIR__ . '/components/auth.php';

require_once __DIR__ . '/../backend/classes/Database.php';
require_once __DIR__ . '/../backend/classes/StockManager.php';

$database = new Database();
$pdo = $database->getConnection();
$stockManager = new StockManager($pdo);

$filters = array_filter(['search' => trim($_GET['search'] ?? ''), 'category' => trim($_GET['category'] ?? '')]);

// Handle stock update
if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['action'] ?? '') === 'update_stock') {
    $success = $stockManager->updateGlobalInventorySync(
        (int)($_POST['stock_id'] ?? 0), (int)($_POST['item_id'] ?? 0), (int)($_POST['supplier_id'] ?? 0),
        trim($_POST['item_name'] ?? ''), trim($_POST['company_name'] ?? ''),
        (float)($_POST['current_qty'] ?? 0), (float)($_POST['min_threshold'] ?? 0)
    );
    if ($success) { header("Location: stock.php" . ($filters ? '?' . http_build_query($filters) : '')); exit; }
}

$totalItems = $stockManager->getTotalItemsCount();
$okStock = $stockManager->getOkStockCount();
$lowStock = $totalItems - $okStock;
$categories = $stockManager->getDistinctCategories();
$inventoryItems = $stockManager->getFilteredStock($filters);
$editItem = isset($_GET['edit_id']) ? $stockManager->getStockById($_GET['edit_id']) : null;
$cancelUrl = 'stock.php' . ($filters ? '?' . http_build_query($filters) : '');

// Set component variables
$pageTitle = 'Stock';            // used by head.php
$pageCss = 'stockstyle.css';    // used by head.php
$activePage = 'stock';          // used by sidebar.php
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
<?php $pageSubtitle = 'Monitor stock levels and set thresholds'; require __DIR__ . '/components/page_header.php'; ?>

<?php
$statCards = [
    ['label' => 'Total Items',    'value' => $totalItems],
    ['label' => 'OK Stock',       'value' => $okStock,  'type' => 'success'],
    ['label' => 'Low / Critical', 'value' => $lowStock, 'type' => 'danger'],
];
require __DIR__ . '/components/stat_cards.php';
?>

<?php
$toolbarAction = 'stock.php';
$toolbarSearch = $filters['search'] ?? '';
$toolbarPlaceholder = 'Search shoe name...';
$categoryOptions = [['value' => 'All Categories', 'label' => 'All Categories']];
foreach ($categories as $cat) {
    $categoryOptions[] = ['value' => $cat['category'], 'label' => $cat['category']];
}
$toolbarFilter = ['name' => 'category', 'value' => $filters['category'] ?? '', 'options' => $categoryOptions];
require __DIR__ . '/components/toolbar.php';
?>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Supplier</th><th>Qty</th><th>Min</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($inventoryItems)): foreach ($inventoryItems as $row):
                                $isLow = $row['current_qty'] < $row['min_threshold'];
                                $maxCap = max($row['current_qty'], $row['min_threshold'] * 2);
                                $fill = ($maxCap > 0) ? min(($row['current_qty'] / $maxCap) * 100, 100) : 0;
                            ?>
                            <tr>
                                <td><?= safe($row['id']) ?></td>
                                <td><strong><?= safe($row['item_name']) ?></strong></td>
                                <td class="text-muted"><?= safe($row['category']) ?></td>
                                <td><?= safe($row['supplier_name']) ?></td>
                                <td>
                                    <span class="<?= $isLow ? 'text-danger' : 'text-success' ?> font-bold"><?= number_format($row['current_qty'], 0) ?> <?= safe($row['unit'] ?? 'pairs') ?></span>
                                    <div class="progress-bar"><div class="fill <?= $isLow ? 'danger' : 'success' ?>" style="width:<?= $fill ?>%"></div></div>
                                </td>
                                <td><?= number_format($row['min_threshold'], 0) ?> <?= safe($row['unit'] ?? 'pairs') ?></td>
                                <td><span class="badge <?= $isLow ? 'badge-danger' : 'badge-success' ?>"><?= $isLow ? 'Low' : 'OK' ?></span></td>
                                <td class="text-muted"><?= date('M d, Y', strtotime($row['last_updated'])) ?></td>
                                <td>
                                    <a href="stock.php?<?= http_build_query(array_merge($filters, ['edit_id' => $row['id']])) ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                                    <button class="btn btn-danger btn-sm" onclick="confirmDelete('Are you sure you want to delete this stock item? This action cannot be undone.', '../backend/stock_delete.php?<?= http_build_query(array_merge($filters, ['id' => $row['id']])) ?>')">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="9">No stock items found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php if ($editItem): ?>
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header"><h2>Edit Stock Level</h2><a href="<?= $cancelUrl ?>" class="modal-close">&times;</a></div>
            <div class="modal-body">
                <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);margin-bottom:16px;">Editing: <strong style="color:var(--color-text)"><?= safe($editItem['item_name']) ?></strong></p>
                <form method="POST" action="stock.php?<?= http_build_query($filters) ?>">
                    <input type="hidden" name="action" value="update_stock">
                    <input type="hidden" name="stock_id" value="<?= safe($editItem['id']) ?>">
                    <input type="hidden" name="item_id" value="<?= safe($editItem['item_id']) ?>">
                    <input type="hidden" name="supplier_id" value="<?= safe($editItem['supplier_id'] ?? '') ?>">
                    <input type="hidden" name="item_name" value="<?= safe($editItem['item_name']) ?>">
                    <input type="hidden" name="company_name" value="<?= safe($editItem['supplier_name']) ?>">
                    <div class="form-grid">
                        <div class="form-group"><label>Current Quantity</label><input type="number" step="0.01" name="current_qty" value="<?= safe($editItem['current_qty']) ?>" required></div>
                        <div class="form-group"><label>Min Threshold</label><input type="number" step="0.01" name="min_threshold" value="<?= safe($editItem['min_threshold']) ?>" required></div>
                    </div>
                    <div class="modal-footer"><a href="<?= $cancelUrl ?>" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary">Save</button></div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php require __DIR__ . '/components/footer.php'; ?>
