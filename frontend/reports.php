<?php
// frontend/reports.php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
require_once '../backend/classes/Database.php';
if (!function_exists('safe')) { function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

$db = (new Database())->getConnection();
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? 'All Types';

// Best Practice: Use LEFT JOIN instead of INNER JOIN so transactions still show
// even if a supplier is missing (e.g. item has no supplier linked).
$query = "SELECT t.transaction_date, i.name as item_name,
                 COALESCE(s.company_name, '—') as supplier_name,
                 t.transaction_type, t.quantity, u.username as user_name
          FROM transactions t
          JOIN items i ON t.item_id = i.id
          LEFT JOIN suppliers s ON i.supplier_id = s.order_id
          JOIN users u ON t.user_id = u.id
          WHERE 1=1";

if (!empty($search)) $query .= " AND i.name LIKE :search";
if ($type !== 'All Types') $query .= " AND t.transaction_type = :type";
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
    <title>Reports - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/reportanalysis.css">
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
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php" class="active"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info"><div class="user-name"><?= safe($_SESSION['username']) ?></div><div class="user-role">User</div></div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout" onclick="event.preventDefault(); confirmLogout();"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header"><h1>Reports</h1><p>View, filter, and export transaction reports</p></div>

            <form method="GET" action="reports.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search by item name..." value="<?= safe($search) ?>">
                <select name="type" style="padding:10px 14px;border:1px solid var(--color-border);border-radius:var(--radius-md);font-size:var(--font-size-sm);font-family:var(--font-family);background:var(--color-surface);">
                    <option value="All Types" <?= $type == 'All Types' ? 'selected' : '' ?>>All Types</option>
                    <option value="Sale" <?= $type == 'Sale' ? 'selected' : '' ?>>Sale</option>
                    <option value="Restock" <?= $type == 'Restock' ? 'selected' : '' ?>>Restock</option>
                    <option value="Waste" <?= $type == 'Waste' ? 'selected' : '' ?>>Waste</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="reports.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>Date</th><th>Item</th><th>Supplier</th><th>Type</th><th>Qty</th><th>By</th></tr></thead>
                        <tbody>
                            <?php if (!empty($reports)): foreach ($reports as $row): ?>
                            <tr>
                                <td class="text-muted"><?= safe($row['transaction_date']) ?></td>
                                <td><strong><?= safe($row['item_name']) ?></strong></td>
                                <td><?= safe($row['supplier_name']) ?></td>
                                <td><span class="badge <?= $row['transaction_type'] === 'Sale' ? 'badge-warning' : ($row['transaction_type'] === 'Restock' ? 'badge-success' : 'badge-danger') ?>"><?= safe($row['transaction_type']) ?></span></td>
                                <td><?= safe($row['quantity']) ?></td>
                                <td><?= safe($row['user_name']) ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="6">No records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="margin-top:16px;display:flex;gap:10px;">
                <button class="btn btn-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
                <a href="export_xml.php" class="btn btn-primary" style="text-decoration:none;"><i class="fa-solid fa-file-export"></i> Export XML</a>
            </div>
        </main>
    </div>
    <script src="../js/confirm-modal.js"></script>
</body>
</html>
