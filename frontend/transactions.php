<?php
// frontend/transactions.php

// Best Practice: Use a shared auth component instead of repeating session/cache/redirect code.
require_once __DIR__ . '/components/auth.php';

require_once __DIR__ . '/../backend/Classes/Transaction.php';

$txHandler = new Transaction($pdo);
$items = $pdo->query("SELECT id, name FROM items")->fetchAll(PDO::FETCH_ASSOC);
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? 'All Types';
$transactions = $txHandler->getAll($search, $type);

// Set component variables
$pageTitle = 'Transactions';                // used by head.php
$pageCss = 'transactions.css';        // used by head.php
$activePage = 'transactions';               // used by sidebar.php
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
$pageSubtitle = 'Log and track all inventory movements';
$headerAction = ['label' => '+ Log Transaction', 'onclick' => "document.getElementById('addTxModal').style.display='flex'"];
require __DIR__ . '/components/page_header.php';
?>

<?php
$toolbarAction = 'transactions.php';
$toolbarSearch = $search;
$toolbarPlaceholder = 'Search...';
$toolbarFilter = [
    'name' => 'type',
    'value' => $type,
    'options' => [
        ['value' => 'All Types', 'label' => 'All Types'],
        ['value' => 'Sold',      'label' => 'Sold'],
        ['value' => 'Restock',   'label' => 'Restock'],
    ]
];
require __DIR__ . '/components/toolbar.php';
?>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th class="col-nowrap">Date</th><th>Item</th><th>Type</th><th>Qty</th><th>By</th><th>Reason</th></tr></thead>
                        <tbody>
                            <?php if (!empty($transactions)): foreach ($transactions as $tx): ?>
                            <tr>
                                <td>#<?= safe($tx['id']) ?></td>
                                <td class="text-muted col-nowrap"><?= safe($tx['transaction_date']) ?></td>
                                <td><strong><?= safe($tx['item_name']) ?></strong></td>
                                <td><span class="badge <?= $tx['transaction_type'] === 'Sold' ? 'badge-warning' : 'badge-success' ?>"><?= safe($tx['transaction_type']) ?></span></td>
                                <td><?= safe($tx['quantity']) ?></td>
                                <td><?= safe($tx['user_name']) ?></td>
                                <td class="text-muted"><?= safe($tx['reason']) ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="7">No transactions found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Print Button -->
            <div style="margin-top:16px;display:flex;gap:10px;">
                <button class="btn btn-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Invoice</button>
            </div>

            <!-- Invoice Print Layout (visible only when printing) -->
            <?php
            // Build detailed summaries by type and item
            $soldItems = [];
            $restockItems = [];
            $soldTotal = 0;
            $restockTotal = 0;
            $totalTransactions = 0;

            foreach ($transactions as $tx) {
                $totalTransactions++;
                $itemName = $tx['item_name'];
                $qty = (int)$tx['quantity'];
                $type = $tx['transaction_type'];

                if ($type === 'Sold') {
                    if (!isset($soldItems[$itemName])) $soldItems[$itemName] = 0;
                    $soldItems[$itemName] += $qty;
                    $soldTotal += $qty;
                } elseif ($type === 'Restock') {
                    if (!isset($restockItems[$itemName])) $restockItems[$itemName] = 0;
                    $restockItems[$itemName] += $qty;
                    $restockTotal += $qty;
                }
            }

            // Sort by quantity descending
            arsort($soldItems);
            arsort($restockItems);
            ?>
            <div class="print-invoice" style="display:none;">
                <div style="border:2px solid #111;padding:30px;max-width:700px;margin:0 auto;font-family:'Inter',sans-serif;color:#111;">
                    <!-- Header -->
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #111;padding-bottom:16px;margin-bottom:20px;">
                        <div>
                            <h1 style="margin:0;font-size:22px;letter-spacing:1px;">ShoeInventory</h1>
                            <p style="margin:4px 0 0;font-size:12px;color:#555;">Transaction Report</p>
                        </div>
                        <div style="text-align:right;">
                            <p style="margin:0;font-size:13px;font-weight:600;"><?= date('F j, Y') ?></p>
                            <p style="margin:4px 0 0;font-size:11px;color:#555;">Generated at <?= date('g:i A') ?></p>
                            <?php if ($search !== '' || $type !== 'All Types'): ?>
                            <p style="margin:6px 0 0;font-size:11px;color:#555;">
                                Filters: <?= $search !== '' ? '"' . safe($search) . '"' : '' ?> <?= $type !== 'All Types' ? "Type: " . safe($type) : '' ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Overview Summary -->
                    <div style="display:flex;gap:20px;margin-bottom:24px;">
                        <div style="flex:1;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:12px;text-align:center;">
                            <div style="font-size:11px;text-transform:uppercase;color:#166534;font-weight:600;margin-bottom:4px;">Restocked</div>
                            <div style="font-size:24px;font-weight:700;color:#166534;"><?= $restockTotal ?></div>
                            <div style="font-size:11px;color:#16a34a;">pairs</div>
                        </div>
                        <div style="flex:1;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:12px;text-align:center;">
                            <div style="font-size:11px;text-transform:uppercase;color:#92400e;font-weight:600;margin-bottom:4px;">Sold</div>
                            <div style="font-size:24px;font-weight:700;color:#92400e;"><?= $soldTotal ?></div>
                            <div style="font-size:11px;color:#d97706;">pairs</div>
                        </div>
                        <div style="flex:1;background:#f0f4ff;border:1px solid #bfdbfe;border-radius:6px;padding:12px;text-align:center;">
                            <div style="font-size:11px;text-transform:uppercase;color:#1e40af;font-weight:600;margin-bottom:4px;">Transactions</div>
                            <div style="font-size:24px;font-weight:700;color:#1e40af;"><?= $totalTransactions ?></div>
                            <div style="font-size:11px;color:#3b82f6;">total</div>
                        </div>
                    </div>

                    <!-- Sold Items Detail -->
                    <div style="margin-bottom:24px;">
                        <h3 style="margin:0 0 10px;font-size:14px;color:#92400e;border-bottom:2px solid #fde68a;padding-bottom:6px;">
                            <i class="fa-solid fa-tag"></i> Items Sold (<?= count($soldItems) ?> types, <?= $soldTotal ?> pairs)
                        </h3>
                        <?php if (!empty($soldItems)): ?>
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#fffbeb;">
                                    <th style="padding:8px 12px;text-align:left;border-bottom:1px solid #e5e5e5;font-size:11px;text-transform:uppercase;">Item</th>
                                    <th style="padding:8px 12px;text-align:center;border-bottom:1px solid #e5e5e5;font-size:11px;text-transform:uppercase;">Qty Sold</th>
                                    <th style="padding:8px 12px;text-align:right;border-bottom:1px solid #e5e5e5;font-size:11px;text-transform:uppercase;">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($soldItems as $itemName => $qty): ?>
                                <tr>
                                    <td style="padding:8px 12px;border-bottom:1px solid #f5f5f5;font-weight:500;"><?= safe($itemName) ?></td>
                                    <td style="padding:8px 12px;border-bottom:1px solid #f5f5f5;text-align:center;font-weight:700;color:#92400e;"><?= $qty ?></td>
                                    <td style="padding:8px 12px;border-bottom:1px solid #f5f5f5;text-align:right;color:#666;"><?= $soldTotal > 0 ? round(($qty / $soldTotal) * 100, 1) : 0 ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p style="color:#999;font-size:12px;padding:8px 0;">No items sold in this period.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Restock Items Detail -->
                    <div style="margin-bottom:24px;">
                        <h3 style="margin:0 0 10px;font-size:14px;color:#166534;border-bottom:2px solid #bbf7d0;padding-bottom:6px;">
                            <i class="fa-solid fa-truck"></i> Items Restocked (<?= count($restockItems) ?> types, <?= $restockTotal ?> pairs)
                        </h3>
                        <?php if (!empty($restockItems)): ?>
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f0fdf4;">
                                    <th style="padding:8px 12px;text-align:left;border-bottom:1px solid #e5e5e5;font-size:11px;text-transform:uppercase;">Item</th>
                                    <th style="padding:8px 12px;text-align:center;border-bottom:1px solid #e5e5e5;font-size:11px;text-transform:uppercase;">Qty Restocked</th>
                                    <th style="padding:8px 12px;text-align:right;border-bottom:1px solid #e5e5e5;font-size:11px;text-transform:uppercase;">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($restockItems as $itemName => $qty): ?>
                                <tr>
                                    <td style="padding:8px 12px;border-bottom:1px solid #f5f5f5;font-weight:500;"><?= safe($itemName) ?></td>
                                    <td style="padding:8px 12px;border-bottom:1px solid #f5f5f5;text-align:center;font-weight:700;color:#166534;"><?= $qty ?></td>
                                    <td style="padding:8px 12px;border-bottom:1px solid #f5f5f5;text-align:right;color:#666;"><?= $restockTotal > 0 ? round(($qty / $restockTotal) * 100, 1) : 0 ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p style="color:#999;font-size:12px;padding:8px 0;">No items restocked in this period.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Footer -->
                    <div style="border-top:1px solid #ccc;padding-top:12px;display:flex;justify-content:space-between;">
                        <p style="margin:0;font-size:11px;color:#888;">ShoeInventory System &mdash; Transaction Summary</p>
                        <p style="margin:0;font-size:11px;color:#888;">Page 1 of 1</p>
                    </div>
                </div>
            </div>

            <style>
                @media print {
                    .sidebar, .mobile-topbar, .sidebar-overlay, .page-header,
                    .toolbar, .modal-overlay, .btn, .actions-cell, .table-card, .no-print { display: none !important; }
                    .main-content { margin-left: 0 !important; padding: 20px !important; }
                    .page-wrapper { display: block !important; }
                    .print-invoice { display: block !important; }
                    body { background: #fff; margin: 0; }
                    @page { margin: 1.5cm; }
                }
            </style>
        </main>
    </div>

    <!-- Add Transaction Modal -->
    <div id="addTxModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header"><h2>Log Transaction</h2><button class="modal-close" onclick="document.getElementById('addTxModal').style.display='none'">&times;</button></div>
            <div class="modal-body">
                <form method="POST" action="../backend/handlers/process_transaction.php" data-validate novalidate>
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Item *</label><select name="item_id" required><?php foreach($items as $i): ?><option value="<?= $i['id'] ?>"><?= safe($i['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Type *</label><select name="type"><option value="Restock">Restock</option><option value="Sold">Sold</option></select></div>
                        <div class="form-group"><label>Quantity *</label><input type="number" name="quantity" required min="1" step="1"><span class="field-error"></span></div>
                        <div class="form-group full-width"><label>Reason</label><input type="text" name="reason" placeholder="Optional note"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addTxModal').style.display='none'">Cancel</button><?= csrf_field() ?><button type="submit" class="btn btn-primary">Add Transaction</button></div>
                </form>
            </div>
        </div>
    </div>
    <?php require __DIR__ . '/components/footer.php'; ?>
