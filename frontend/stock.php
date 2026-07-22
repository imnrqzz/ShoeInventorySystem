<?php
// frontend/stock.php

require_once __DIR__ . '/components/auth.php';

require_once __DIR__ . '/../backend/classes/StockManager.php';

// Only admins can edit/delete stock
$isAdmin = isAdmin();
$stockManager = new StockManager($pdo);

// Auto-sync missing stock records
$stockManager->syncMissingStock();

$filters = array_filter(['search' => trim($_GET['search'] ?? '')]);

// Handle stock update
if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['action'] ?? '') === 'update_stock') {
    verify_csrf();
    $success = $stockManager->updateGlobalInventorySync(
        (int)($_POST['stock_id'] ?? 0), (int)($_POST['item_id'] ?? 0), (int)($_POST['supplier_id'] ?? 0),
        trim($_POST['item_name'] ?? ''), trim($_POST['company_name'] ?? ''),
        (float)($_POST['current_qty'] ?? 0), (float)($_POST['min_threshold'] ?? 0)
    );
    if ($success) { header("Location: stock.php" . ($filters ? '?' . http_build_query($filters) : '')); exit; }
}

// Handle combined stock + variants update
if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['action'] ?? '') === 'update_stock_and_variants') {
    verify_csrf();
    $success = $stockManager->updateStockAndVariants(
        (int)($_POST['stock_id'] ?? 0),
        (int)($_POST['item_id'] ?? 0),
        (int)($_POST['supplier_id'] ?? 0),
        trim($_POST['item_name'] ?? ''),
        trim($_POST['company_name'] ?? ''),
        (int)($_POST['min_threshold'] ?? 0),
        $_POST['variant_ids'] ?? [],
        $_POST['variant_colors'] ?? [],
        $_POST['variant_sizes'] ?? [],
        $_POST['variant_quantities'] ?? []
    );
    header("Location: stock.php" . ($filters ? '?' . http_build_query($filters) : ''));
    exit;
}

$totalItems = $stockManager->getTotalItemsCount();
$okStock = $stockManager->getOkStockCount();
$lowStock = $totalItems - $okStock;
$inventoryItems = $stockManager->getFilteredStock($filters);
$editItem = isset($_GET['edit_id']) ? $stockManager->getStockById($_GET['edit_id']) : null;
$cancelUrl = 'stock.php' . ($filters ? '?' . http_build_query($filters) : '');

// Set component variables
$pageTitle = 'Stock';            // used by head.php
$pageCss = 'stock.css';    // used by head.php
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
require __DIR__ . '/components/toolbar.php';
?>

            <div style="margin-bottom:12px;display:flex;gap:8px;">
                <button class="btn btn-secondary" onclick="window.qrScanner.open()">
                    <i class="fa-solid fa-qrcode"></i> Scan QR / Barcode
                </button>
            </div>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Item</th><th>Supplier</th><th>Qty</th><th>Min</th><th>Status</th><th>Updated</th><?php if ($isAdmin): ?><th class="actions-cell">Actions</th><?php endif; ?></tr></thead>
                        <tbody>
                            <?php if (!empty($inventoryItems)): foreach ($inventoryItems as $row):
                                $qty = (int)$row['current_qty'];
                                $min = (int)$row['min_threshold'];
                                $isLow = $qty < $min;

                                /*
                                 * Stock Level Bar Calculation
                                 *
                                 * The bar shows stock health relative to the minimum threshold:
                                 *   - 100% of the bar = 2Ãƒâ€” the min threshold ("comfortable" level)
                                 *   - 50% mark = exactly at the min threshold (warning zone)
                                 *   - Below 50% = under threshold (danger zone)
                                 *   - Capped at 100% for items well above threshold
                                 *
                                 * This gives a meaningful visual: you can see at a glance
                                 * how far each item is from its danger line.
                                 */
                                $comfortLevel = max($min * 2, 1); // 2Ãƒâ€” threshold = "full" bar
                                $fillPct = min(($qty / $comfortLevel) * 100, 100);

                                // Color: red if below threshold, amber if at/near threshold, green if healthy
                                if ($qty < $min) {
                                    $barColor = 'danger';       // Red Ã¢â‚¬â€ below minimum
                                } elseif ($qty < $min * 1.5) {
                                    $barColor = 'warning';      // Amber Ã¢â‚¬â€ near minimum
                                } else {
                                    $barColor = 'success';      // Green Ã¢â‚¬â€ healthy stock
                                }
                            ?>
                            <tr>
                                <td><?= safe($row['id']) ?></td>
                                <td><strong><?= safe($row['item_name']) ?></strong></td>
                                <td><?= safe($row['supplier_name']) ?></td>
                                <td>
                                    <div class="stock-level">
                                        <div class="stock-level-text">
                                            <span class="stock-level-qty <?= $isLow ? 'text-danger' : '' ?>"><?= $qty ?></span>
                                            <span class="stock-level-unit"><?= safe($row['unit'] ?? 'pairs') ?></span>
                                        </div>
                                        <div class="stock-bar">
                                            <div class="stock-bar-fill <?= $barColor ?>" style="width:<?= round($fillPct) ?>%"></div>
                                            <div class="stock-bar-threshold" title="Min: <?= $min ?>"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $min ?></td>
                                <td><span class="badge <?= $isLow ? 'badge-danger' : ($barColor === 'warning' ? 'badge-warning' : 'badge-success') ?>"><?= $isLow ? 'Low' : ($barColor === 'warning' ? 'Near Low' : 'OK') ?></span></td>
                                <td class="text-muted"><?= date('M d, Y', strtotime($row['last_updated'])) ?></td>
                                <?php if ($isAdmin): ?>
                                <td class="actions-cell">
                                    <a href="stock.php?<?= http_build_query(array_merge($filters, ['edit_id' => $row['id']])) ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="<?= $isAdmin ? 8 : 7 ?>">No stock items found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php if ($editItem):
        // Get variants for this item
        $variants = $stockManager->getVariantsByItemId($editItem['item_id']);
        $variantTotal = 0;
        foreach ($variants as $v) $variantTotal += (int)$v['quantity'];
    ?>
    <div class="modal-overlay">
        <div class="modal-box" style="max-width: 600px;">
            <div class="modal-header"><h2>Edit Stock Level</h2><a href="<?= $cancelUrl ?>" class="modal-close">&times;</a></div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);margin-bottom:16px;">Editing: <strong style="color:var(--color-text)"><?= safe($editItem['item_name']) ?></strong></p>

                <!-- Single Form for Stock + Variants -->
                <form method="POST" action="stock.php?<?= http_build_query($filters) ?>" id="stockEditForm" data-validate novalidate>
                    <input type="hidden" name="action" value="update_stock_and_variants">
                    <input type="hidden" name="stock_id" value="<?= safe($editItem['id']) ?>">
                    <input type="hidden" name="item_id" value="<?= safe($editItem['item_id']) ?>">
                    <input type="hidden" name="supplier_id" value="<?= safe($editItem['supplier_id'] ?? '') ?>">
                    <input type="hidden" name="item_name" value="<?= safe($editItem['item_name']) ?>">
                    <input type="hidden" name="company_name" value="<?= safe($editItem['supplier_name']) ?>">

                    <!-- Stock Info -->
                    <div class="form-grid" style="margin-bottom: 16px;">
                        <div class="form-group">
                            <label>Min Threshold *</label>
                            <input type="number" step="1" min="0" max="500" name="min_threshold" value="<?= (int)$editItem['min_threshold'] ?>" required>
                            <span class="field-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Total Quantity</label>
                            <input type="number" id="totalQtyDisplay" value="<?= $variantTotal ?>" readonly style="background: var(--color-bg); font-weight: 600;">
                        </div>
                    </div>

                    <!-- Variants Section -->
                    <?php if (!empty($variants)): ?>
                    <div style="border-top: 1px solid var(--color-border); padding-top: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h3 style="font-size: 0.95rem; font-weight: 600; color: var(--color-text-muted); margin: 0;">
                                <i class="fa-solid fa-layer-group"></i> Variants (<?= count($variants) ?>)
                            </h3>
                            <div id="variantValidation" style="font-size: 0.8rem; padding: 4px 10px; border-radius: 12px; display: none;"></div>
                        </div>

                        <!-- Header -->
                        <div style="display: grid; grid-template-columns: 1fr 80px 70px; gap: 8px; padding: 6px 8px; font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">
                            <div>Color / Size</div>
                            <div style="text-align: center;">Qty</div>
                            <div></div>
                        </div>

                        <!-- Variant Rows -->
                        <div id="variantsContainer" style="display: flex; flex-direction: column; gap: 6px;">
                            <?php foreach ($variants as $variant): ?>
                            <div class="variant-row" style="display: grid; grid-template-columns: 1fr 80px 70px; gap: 8px; align-items: center; padding: 8px; background: var(--color-bg); border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                                <input type="hidden" name="variant_ids[]" value="<?= (int)$variant['id'] ?>">
                                <div style="display: flex; gap: 6px;">
                                    <input type="text" name="variant_colors[]" value="<?= safe($variant['color']) ?>" style="flex: 1; padding: 6px 8px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.85rem;" placeholder="Color">
                                    <input type="text" name="variant_sizes[]" value="<?= safe($variant['size']) ?>" style="width: 80px; padding: 6px 8px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.85rem;" placeholder="Size">
                                </div>
                                <input type="number" name="variant_quantities[]" class="variant-qty-input" value="<?= (int)$variant['quantity'] ?>" min="0" style="padding: 6px 8px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.85rem; text-align: center;">
                                <div></div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Validation Message -->
                        <div id="variantMatchStatus" style="margin-top: 10px; padding: 8px 12px; border-radius: var(--radius-sm); font-size: 0.85rem; display: none;"></div>
                    </div>
                    <?php endif; ?>

                    <?= csrf_field() ?>
                    <div class="modal-footer" style="margin-top: 20px;">
                        <a href="<?= $cancelUrl ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="saveBtn"><i class="fa-solid fa-save"></i> Save All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var qtyInputs = document.querySelectorAll('.variant-qty-input');
        var totalDisplay = document.getElementById('totalQtyDisplay');
        var matchStatus = document.getElementById('variantMatchStatus');

        function updateTotal() {
            var total = 0;
            qtyInputs.forEach(function(input) {
                total += parseInt(input.value) || 0;
            });
            totalDisplay.value = total;

            // Check if variants sum matches the expected total
            var expectedTotal = <?= $variantTotal ?>;
            if (total === expectedTotal) {
                matchStatus.style.display = 'block';
                matchStatus.style.background = '#f0fdf4';
                matchStatus.style.color = '#166534';
                matchStatus.style.border = '1px solid #bbf7d0';
                matchStatus.innerHTML = '<i class="fa-solid fa-check-circle"></i> Variants match total stock (' + total + ' pairs)';
            } else {
                matchStatus.style.display = 'block';
                matchStatus.style.background = '#fef2f2';
                matchStatus.style.color = '#991b1b';
                matchStatus.style.border = '1px solid #fecaca';
                matchStatus.innerHTML = '<i class="fa-solid fa-exclamation-triangle"></i> Variants (' + total + ') do not match original total (<?= $variantTotal ?>). Total will be updated.';
            }
        }

        qtyInputs.forEach(function(input) {
            input.addEventListener('input', updateTotal);
        });

        // Initial check
        updateTotal();
    })();
    </script>
    <?php endif; ?>
    <?php require __DIR__ . '/components/footer.php'; ?>
