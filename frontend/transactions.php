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
$pageCss = 'transactions_style.css';        // used by head.php
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
        ['value' => 'Sale',      'label' => 'Sale'],
        ['value' => 'Restock',   'label' => 'Restock'],
        ['value' => 'Waste',     'label' => 'Waste'],
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
                                <td><span class="badge <?= $tx['transaction_type'] === 'Sale' ? 'badge-warning' : ($tx['transaction_type'] === 'Restock' ? 'badge-success' : 'badge-danger') ?>"><?= safe($tx['transaction_type']) ?></span></td>
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
            $summaryByType = [];
            $totalQty = 0;
            $totalTransactions = 0;
            foreach ($transactions as $tx) {
                $t = $tx['transaction_type'];
                if (!isset($summaryByType[$t])) $summaryByType[$t] = ['count' => 0, 'qty' => 0];
                $summaryByType[$t]['count']++;
                $summaryByType[$t]['qty'] += (int)$tx['quantity'];
                $totalQty += (int)$tx['quantity'];
                $totalTransactions++;
            }
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
                                Filters: <?= $search !== '' ? "\"$search\"" : '' ?> <?= $type !== 'All Types' ? "Type: $type" : '' ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Summary Table -->
                    <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                        <thead>
                            <tr style="background:#f5f5f5;">
                                <th style="padding:10px 12px;text-align:left;border-bottom:2px solid #111;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Type</th>
                                <th style="padding:10px 12px;text-align:center;border-bottom:2px solid #111;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Transactions</th>
                                <th style="padding:10px 12px;text-align:right;border-bottom:2px solid #111;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Total Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summaryByType as $typeName => $data): ?>
                            <tr>
                                <td style="padding:10px 12px;border-bottom:1px solid #e5e5e5;font-weight:600;"><?= safe($typeName) ?></td>
                                <td style="padding:10px 12px;border-bottom:1px solid #e5e5e5;text-align:center;"><?= $data['count'] ?></td>
                                <td style="padding:10px 12px;border-bottom:1px solid #e5e5e5;text-align:right;"><?= $data['qty'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight:700;">
                                <td style="padding:10px 12px;border-top:2px solid #111;font-size:14px;">TOTAL</td>
                                <td style="padding:10px 12px;border-top:2px solid #111;text-align:center;font-size:14px;"><?= $totalTransactions ?></td>
                                <td style="padding:10px 12px;border-top:2px solid #111;text-align:right;font-size:14px;"><?= $totalQty ?></td>
                            </tr>
                        </tfoot>
                    </table>

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
                <form method="POST" action="../backend/process_transaction.php" data-validate novalidate>
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Item *</label><select name="item_id" required><?php foreach($items as $i): ?><option value="<?= $i['id'] ?>"><?= safe($i['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Type *</label><select name="type"><option value="Restock">Restock</option><option value="Sale">Sale</option><option value="Waste">Waste</option></select></div>
                        <div class="form-group"><label>Quantity *</label><input type="number" name="quantity" required min="1" step="1"><span class="field-error"></span></div>
                        <div class="form-group full-width"><label>Reason</label><input type="text" name="reason" placeholder="Optional note"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addTxModal').style.display='none'">Cancel</button><?= csrf_field() ?><button type="submit" class="btn btn-primary">Add Transaction</button></div>
                </form>
            </div>
        </div>
    </div>
    <?php require __DIR__ . '/components/footer.php'; ?>
