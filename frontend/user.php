<?php
// frontend/user.php

// Best Practice: Use a shared auth component instead of repeating session/cache/redirect code.
require_once __DIR__ . '/components/auth.php';

require_once '../backend/classes/Database.php';
require_once '../backend/classes/UserManager.php';

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

// Set component variables
$pageTitle = 'Users';            // used by head.php
$pageCss = 'userstyle.css';     // used by head.php
$activePage = 'users';          // used by sidebar.php
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
        confirmDeletePost(
            'Are you sure you want to delete this user? This action cannot be undone.',
            '../backend/user_action.php',
            { action: 'delete', id: id }
        );
    }
    </script>
    <?php require __DIR__ . '/components/footer.php'; ?>
