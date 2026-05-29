<?php
session_start();
require_once __DIR__.'/../backend/db.php';

function safe($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function qRows($m,$sql){if(!$m) return []; $r=$m->query($sql); $o=[]; if($r){while($row=$r->fetch_assoc())$o[]=$row; $r->free();} return $o;}
function qCount($m,$sql){if(!$m) return 0; $r=$m->query($sql); if($r && ($row=$r->fetch_assoc())) return (int)$row['cnt']; return 0;}

$totalItems=qCount($mysqli,'SELECT COUNT(*) AS cnt FROM items');
$activeSuppliers=qCount($mysqli,'SELECT COUNT(*) AS cnt FROM suppliers WHERE active=1');
$systemUsers=qCount($mysqli,'SELECT COUNT(*) AS cnt FROM users');
$transactionsCount=qCount($mysqli,'SELECT COUNT(*) AS cnt FROM transactions');
$lowStockAlerts=qCount($mysqli,'SELECT COUNT(*) AS cnt FROM items WHERE quantity<=min_quantity');
$lowStockItems=qRows($mysqli,"SELECT i.name AS item_name,i.quantity,i.min_quantity,COALESCE(s.name,'Unknown') AS supplier_name FROM items i LEFT JOIN suppliers s ON i.supplier_id=s.id WHERE i.quantity<=i.min_quantity ORDER BY i.quantity ASC LIMIT 5");
$recentTransactions=qRows($mysqli,"SELECT i.name AS item_name,t.transaction_type,t.quantity,COALESCE(u.username,'Unknown') AS user_name,t.created_at FROM transactions t LEFT JOIN items i ON t.item_id=i.id LEFT JOIN users u ON t.user_id=u.id ORDER BY t.created_at DESC LIMIT 5");
$items=qRows($mysqli,"SELECT i.id,i.name,i.quantity,i.min_quantity,i.price,COALESCE(s.name,'Unknown') AS supplier_name FROM items i LEFT JOIN suppliers s ON i.supplier_id=s.id ORDER BY i.name ASC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shoes Inventory System</title>
    <link rel="stylesheet" href="../css/dashboard_style.css" />
</head>
<body>
    <div class="page">
        <header class="topbar">
            <div class="brand">
                <div class="logo">S</div>
                <div class="title">
                    <span>Shoes Inventory System</span>
                </div>
            </div>

            <nav class="nav-links">
                <a href="index.php" class="active">Dashboard</a>
                <a href="#">Items</a>
                <a href="#">Suppliers</a>
                <a href="#">Stock</a>
                <a href="#">Transactions</a>
                <a href="#">Users</a>
            </nav>

            <div class="top-actions">
                <div class="user-badge">
                    <span class="avatar">U</span>
                    <span><?php echo !empty($_SESSION['username']) ? safe($_SESSION['username']) : 'User1'; ?></span>
                </div>
                <form method="post" action="../backend/logout.php" style="display:inline">
                    <button class="logout-button" title="Logout">⏻</button>
                </form>
            </div>
        </header>

        <section class="dashboard-panel">
            <h2>Dashboard</h2>
            <div class="summary">
                <article class="dashboard-card">
                    <div class="icon">📦</div>
                    <div class="value"><?= safe($totalItems) ?></div>
                    <div class="label">Total Items</div>
                </article>
                <article class="dashboard-card">
                    <div class="icon">🚚</div>
                    <div class="value"><?= safe($activeSuppliers) ?></div>
                    <div class="label">Active Suppliers</div>
                </article>
                <article class="dashboard-card">
                    <div class="icon">👥</div>
                    <div class="value"><?= safe($systemUsers) ?></div>
                    <div class="label">System Users</div>
                </article>
                <article class="dashboard-card">
                    <div class="icon">🔁</div>
                    <div class="value"><?= safe($transactionsCount) ?></div>
                    <div class="label">Transactions</div>
                </article>
                <article class="dashboard-card danger">
                    <div class="icon">⚠️</div>
                    <div class="value"><?= safe($lowStockAlerts) ?></div>
                    <div class="label">Low Stock Alerts</div>
                </article>
            </div>
        </section>

        <div class="tables-row">
            <section class="panel">
                <div class="panel-header alert">
                    <span class="dot"></span>
                    Low Stock Alerts
                </div>
                <div class="panel-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Current</th>
                                <th>Min</th>
                                <th>Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($lowStockItems): foreach($lowStockItems as $item): ?>
                                <tr>
                                    <td><?= safe($item['item_name']) ?></td>
                                    <td><?= safe($item['quantity']) ?></td>
                                    <td><?= safe($item['min_quantity']) ?></td>
                                    <td><?= safe($item['supplier_name']) ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" style="color:var(--text-muted);">No low stock data yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <span class="dot"></span>
                    Recent Transactions
                </div>
                <div class="panel-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recentTransactions): foreach($recentTransactions as $tx): ?>
                                <tr>
                                    <td><?= safe($tx['item_name']) ?></td>
                                    <td><?= safe($tx['transaction_type']) ?></td>
                                    <td><?= safe($tx['quantity']) ?></td>
                                    <td><?= safe($tx['user_name']) ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" style="color:var(--text-muted);">No recent transactions yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        
        <section class="panel items-panel">
            <div class="panel-header">
                <span class="dot"></span>
                Items
            </div>
            <div class="panel-body">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Qty</th>
                            <th>Min</th>
                            <th>Supplier</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($items): foreach($items as $it): ?>
                            <tr>
                                <td><?= safe($it['id']) ?></td>
                                <td><?= safe($it['name']) ?></td>
                                <td><?= safe($it['quantity']) ?></td>
                                <td><?= safe($it['min_quantity']) ?></td>
                                <td><?= safe($it['supplier_name']) ?></td>
                                <td><?= safe($it['price']) ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" style="color:var(--text-muted);">No items found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>