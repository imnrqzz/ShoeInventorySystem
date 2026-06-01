<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once '../backend/classes/Database.php';

$db = (new Database())->getConnection();

$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? 'All Types';

// Query joining items and suppliers based on your database structure
$query = "SELECT t.transaction_date, 
                 i.name as item_name, 
                 s.company_name as supplier_name, 
                 t.transaction_type, 
                 t.quantity, 
                 u.username as user_name
          FROM transactions t
          JOIN items i ON t.item_id = i.id
          JOIN suppliers s ON i.supplier_id = s.order_id
          JOIN users u ON t.user_id = u.id
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND i.name LIKE :search";
}
if ($type !== 'All Types') {
    $query .= " AND t.transaction_type = :type";
}

$query .= " ORDER BY t.transaction_date DESC";

$stmt = $db->prepare($query);

if (!empty($search)) $stmt->bindValue(':search', "%$search%");
if ($type !== 'All Types') $stmt->bindValue(':type', $type);

$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Shoes Inventory System</title>
    <link rel="stylesheet" href="../css/reportanalysis.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page">
        <nav class="navbar">
            <div class="nav-left">
                <div class="logo"><img src="../images/shoes.png" alt="Logo"></div>
                <div class="nav-brand">Shoes Inventory System</div>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="Item.php">Items</a></li>
                <li><a href="Supplier.php">Suppliers</a></li>
                <li><a href="stock.php">Stock</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="user.php">Users</a></li>
                <li><a href="reports.php" class="active">Reports</a></li>
            </ul>
            <div class="nav-right">
                <div class="user-profile">
                    <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <button class="logout-pill-btn" onclick="window.location.href='logout.php';"></button>
                </div>
            </div>
        </nav>

        <section class="purple-canvas-panel">
            <div class="section-heading-row">
                <h2 class="page-title-label">Reports Analysis</h2>
            </div>
            
            <div class="search-filter-pill-capsule">
                <form method="GET" style="display: flex; gap: 12px; width: 100%;">
                    <input type="text" name="search" class="search-box-field" placeholder="Item Name..." value="<?= htmlspecialchars($search) ?>">
                    <select name="type" class="search-box-field" style="width: auto;">
                        <option value="All Types" <?= $type == 'All Types' ? 'selected' : '' ?>>All Types</option>
                        <option value="Sale" <?= $type == 'Sale' ? 'selected' : '' ?>>Sale</option>
                        <option value="Restock" <?= $type == 'Restock' ? 'selected' : '' ?>>Restock</option>
                    </select>
                    <button type="submit" class="action-btn execution-search-btn">Filter</button>
                    <a href="reports.php" class="action-btn execution-reset-btn" style="text-decoration:none;">Reset</a>
                </form>
            </div>

            <div class="curved-ledger-table-card">
                <div class="table-scroll-axis-frame">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Date</th><th>Item</th><th>Supplier</th><th>Type</th><th>Qty</th><th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($reports) > 0): foreach ($reports as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['transaction_date']) ?></td>
                                    <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                    <td><?= htmlspecialchars($row['transaction_type']) ?></td>
                                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="6" style="text-align:center;">No records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button class="btn-add-item-trigger" onclick="window.print()">Print Report</button>
                
                <a href="export_xml.php" class="btn-add-item-trigger" 
                style="text-decoration:none; background-color: #3b82f6; color: white;">
                Export to XML
                </a>
            </div>
        </section>
    </div>
</body>
</html>