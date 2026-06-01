<?php
// frontend/user.php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
require_once '../backend/classes/Database.php';
require_once '../backend/classes/UserManager.php';
if (!function_exists('safe')) { function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

$db = new Database();
$userManager = new UserManager($db->getConnection());
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$users = $userManager->getFilteredUsers($search, $role);
$total_users = count($users);
$admins = 0; $active_users = 0;
foreach ($users as $u) {
    if (strtolower($u['role'] ?? '') === 'admin') $admins++;
    if (strtolower($u['status'] ?? '') === 'active') $active_users++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/userstyle.css">
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
                <li><a href="user.php" class="active"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info"><div class="user-name"><?= safe($_SESSION['username']) ?></div><div class="user-role">User</div></div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header"><h1>User Management</h1><p>View and manage system user accounts</p></div>

            <div class="stat-cards">
                <div class="stat-card"><div class="stat-label">Total Users</div><div class="stat-value"><?= $total_users ?></div></div>
                <div class="stat-card"><div class="stat-label">Administrators</div><div class="stat-value"><?= $admins ?></div></div>
                <div class="stat-card"><div class="stat-label">Active Users</div><div class="stat-value"><?= $active_users ?></div></div>
            </div>

            <form method="GET" action="user.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search name or email..." value="<?= safe($search) ?>">
                <select name="role" style="padding:10px 14px;border:1px solid var(--color-border);border-radius:var(--radius-md);font-size:var(--font-size-sm);font-family:var(--font-family);background:var(--color-surface);">
                    <option value="">All Roles</option>
                    <option value="Admin" <?= $role == 'Admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="Staff" <?= $role == 'Staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="User" <?= $role == 'User' ? 'selected' : '' ?>>User</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="user.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th style="text-align:center;">Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($users)): foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= safe($user['id']) ?></td>
                                <td><?= safe($user['username'] ?? 'N/A') ?></td>
                                <td><strong><?= safe($user['name'] ?? '') ?></strong></td>
                                <td class="text-muted"><?= safe($user['email'] ?? 'N/A') ?></td>
                                <td><?= safe(ucfirst($user['role'] ?? 'User')) ?></td>
                                <td><span class="badge <?= strtolower($user['status'] ?? '') === 'active' ? 'badge-success' : 'badge-neutral' ?>"><?= safe(ucfirst($user['status'] ?? '')) ?></span></td>
                                <td style="text-align:center;">
                                    <button class="btn btn-secondary btn-sm" onclick="openEditModal(<?= (int)$user['id'] ?>, '<?= safe($user['username'] ?? '') ?>', '<?= safe($user['name'] ?? '') ?>', '<?= safe($user['email'] ?? '') ?>', '<?= safe($user['status'] ?? 'active') ?>')">Edit</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= (int)$user['id'] ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="7">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header"><h2>Edit User</h2><button class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</button></div>
            <div class="modal-body">
                <form method="POST" action="../backend/user_action.php">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="editUserId">
                    <div class="form-grid">
                        <div class="form-group"><label>Username</label><input type="text" name="username" id="editUsername" required></div>
                        <div class="form-group"><label>Name</label><input type="text" name="name" id="editUserName" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" id="editUserEmail" required></div>
                        <div class="form-group"><label>Status</label><select name="status" id="editUserStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openEditModal(id, username, name, email, status) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editUsername').value = username;
        document.getElementById('editUserName').value = name;
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserStatus').value = status;
        document.getElementById('editModal').style.display = 'flex';
    }
    function deleteUser(id) {
        if (!confirm('Delete this user?')) return;
        var f = document.createElement('form');
        f.method = 'POST'; f.action = '../backend/user_action.php';
        f.innerHTML = '<input name="action" value="delete"><input name="id" value="'+id+'">';
        document.body.appendChild(f); f.submit();
    }
    </script>
</body>
</html>
