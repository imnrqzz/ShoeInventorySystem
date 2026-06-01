<?php
// frontend/reports.php

// Best Practice: Use a shared auth component instead of repeating session/cache/redirect code.
require_once __DIR__ . '/components/auth.php';

require_once '../backend/classes/Database.php';

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

// Set component variables
$pageTitle = 'Reports';              // used by head.php
$pageCss = 'reportanalysis.css';     // used by head.php
$activePage = 'reports';             // used by sidebar.php
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
    <?php require __DIR__ . '/components/footer.php'; ?>
