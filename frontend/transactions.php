<?php
// frontend/transactions.php

// Best Practice: Use a shared auth component instead of repeating session/cache/redirect code.
require_once __DIR__ . '/components/auth.php';

require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/Classes/Transaction.php';

$db = (new Database())->getConnection();
$txHandler = new Transaction($db);
$items = $db->query("SELECT id, name FROM items")->fetchAll(PDO::FETCH_ASSOC);
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
                        <thead><tr><th>#</th><th>Date</th><th>Item</th><th>Type</th><th>Qty</th><th>By</th><th>Reason</th></tr></thead>
                        <tbody>
                            <?php if (!empty($transactions)): foreach ($transactions as $tx): ?>
                            <tr>
                                <td>#<?= safe($tx['id']) ?></td>
                                <td class="text-muted"><?= safe($tx['transaction_date']) ?></td>
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
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addTxModal').style.display='none'">Cancel</button><button type="submit" class="btn btn-primary">Add Transaction</button></div>
                </form>
            </div>
        </div>
    </div>
    <?php require __DIR__ . '/components/footer.php'; ?>
