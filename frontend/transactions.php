<?php
// frontend/transactions.php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/Classes/Transaction.php';
if (!function_exists('safe')) { function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

$db = (new Database())->getConnection();
$txHandler = new Transaction($db);
$items = $db->query("SELECT id, name FROM items")->fetchAll(PDO::FETCH_ASSOC);
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? 'All Types';
$transactions = $txHandler->getAll($search, $type);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/transactions_style.css">
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand"><div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div><span>ShoeInventory</span></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php" class="active"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
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
                <div><h1>Transactions</h1><p>Log and track all inventory movements</p></div>
                <button class="btn btn-primary" onclick="document.getElementById('addTxModal').style.display='flex'">+ Log Transaction</button>
            </div>

            <form method="GET" action="transactions.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search..." value="<?= safe($search) ?>">
                <select name="type" style="padding:10px 14px;border:1px solid var(--color-border);border-radius:var(--radius-md);font-size:var(--font-size-sm);font-family:var(--font-family);background:var(--color-surface);">
                    <option value="All Types">All Types</option>
                    <option value="Sale" <?= $type == 'Sale' ? 'selected' : '' ?>>Sale</option>
                    <option value="Restock" <?= $type == 'Restock' ? 'selected' : '' ?>>Restock</option>
                    <option value="Waste" <?= $type == 'Waste' ? 'selected' : '' ?>>Waste</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="transactions.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

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
                <form method="POST" action="../backend/process_transaction.php">
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Item *</label><select name="item_id" required><?php foreach($items as $i): ?><option value="<?= $i['id'] ?>"><?= safe($i['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Type *</label><select name="type"><option value="Restock">Restock</option><option value="Sale">Sale</option><option value="Waste">Waste</option></select></div>
                        <div class="form-group"><label>Quantity *</label><input type="number" name="quantity" required min="1"></div>
                        <div class="form-group full-width"><label>Reason</label><input type="text" name="reason" placeholder="Optional note"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addTxModal').style.display='none'">Cancel</button><button type="submit" class="btn btn-primary">Add Transaction</button></div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
