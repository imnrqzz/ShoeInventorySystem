<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/Classes/Transaction.php';

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
    <title>Transactions - Shoes Inventory System</title>
    <link rel="stylesheet" href="../css/transactions_style.css?v=<?php echo time(); ?>">
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
            <li><a href="Supplier.php">Suppliers</a></li>
            <li><a href="transactions.php" class="active">Transactions</a></li>
            <li><a href="stock.php">Stock</a></li>
            <li><a href="user.php">Users</a></li>
            <li><a href="reports.php">Reports</a></li>
        </ul>
        <div class="nav-right">
            <div class="user-badge" style="display: flex; align-items: center; gap: 10px; padding: 5px 15px;">
                <i class="fa-solid fa-user"></i> <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <button class="logout-pill-btn" onclick="window.location.href='logout.php';"><i class="fa-solid fa-right-from-bracket"></i></button>
        </div>
    </nav>

    <main class="purple-canvas-panel">
        <div class="section-heading-row">
            <h1 class="page-title-label">Transactions</h1>
            <a href="#" class="btn-add-item-trigger" onclick="openModal(event)">+ Log Transaction</a>
        </div>

        <form method="GET" action="transactions.php" class="search-filter-pill-capsule">
            <input type="text" name="search" class="search-box-field" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
            <select name="type" class="search-box-field">
                <option value="All Types">All Types</option>
                <option value="Sale" <?= $type == 'Sale' ? 'selected' : '' ?>>Sale</option>
                <option value="Restock" <?= $type == 'Restock' ? 'selected' : '' ?>>Restock</option>
                <option value="Waste" <?= $type == 'Waste' ? 'selected' : '' ?>>Waste</option>
            </select>
            <button type="submit" class="action-btn execution-search-btn">Filter</button>
        </form>

        <div class="curved-ledger-table-card">
            <table>
                <thead>
                    <tr><th>#</th><th>Date</th><th>Item</th><th>Type</th><th>Qty</th><th>By</th><th>Reason</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($tx['id']) ?></td>
                        <td><?= htmlspecialchars($tx['transaction_date']) ?></td>
                        <td><strong><?= htmlspecialchars($tx['item_name']) ?></strong></td>
                        <td><?= htmlspecialchars($tx['transaction_type']) ?></td>
                        <td><?= htmlspecialchars($tx['quantity']) ?></td>
                        <td><?= htmlspecialchars($tx['user_name']) ?></td>
                        <td><?= htmlspecialchars($tx['reason']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="addTransactionModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h2>Log New Transaction</h2>
                <button onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" action="../backend/process_transaction.php">
                <div class="modal-body">
                    <label>Item *</label>
                    <select name="item_id" required>
                        <?php foreach($items as $i): ?>
                            <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Type *</label>
                    <select name="type">
                        <option value="Restock">Restock</option>
                        <option value="Sale">Sale</option>
                        <option value="Waste">Waste</option>
                    </select>
                    <label>Quantity *</label>
                    <input type="number" name="quantity" required min="1">
                    <label>Reason</label>
                    <input type="text" name="reason">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-add">Add Transaction</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal(e) { 
        e.preventDefault(); 
        const modal = document.getElementById("addTransactionModal");
        modal.style.display = "flex"; 
        console.log("Modal opened!"); // Check your browser console!
    }
    
    function closeModal() { 
        document.getElementById("addTransactionModal").style.display = "none"; 
    }
</script>
</body>
</html>