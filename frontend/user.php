<?php
// frontend/user.php

// Best Practice: Use a shared auth component instead of repeating session/cache/redirect code.
require_once __DIR__ . '/components/auth.php';

if (!isset($_SESSION['role']) || !in_array(strtolower((string)$_SESSION['role']), ['admin'], true)) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../backend/Classes/UserManager.php';

$userManager = new UserManager($pdo);
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
$pageCss = 'users.css';     // used by head.php
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
            <form id="disableUserForm" method="POST" action="../backend/handlers/user_action.php" style="display:none;">
                <input type="hidden" name="action" value="disable">
                <input type="hidden" name="id" id="disableUserId">
                <?= csrf_field() ?>
            </form>
            <form id="enableUserForm" method="POST" action="../backend/handlers/user_action.php" style="display:none;">
                <input type="hidden" name="action" value="enable">
                <input type="hidden" name="id" id="enableUserId">
                <?= csrf_field() ?>
            </form>
<?php
$pageSubtitle = 'View and manage system user accounts';
$headerAction = ['label' => '+ Add User', 'onclick' => 'openAddModal()'];
require __DIR__ . '/components/page_header.php';
?>

<?php
$statCards = [
    ['label' => 'Total Users',     'value' => $total_users],
    ['label' => 'Administrators',  'value' => $admins],
    ['label' => 'Active Users',    'value' => $active_users],
];
require __DIR__ . '/components/stat_cards.php';
?>

<?php
$toolbarAction = 'user.php';
$toolbarSearch = $search;
$toolbarPlaceholder = 'Search name or email...';
$toolbarFilter = [
    'name' => 'role',
    'value' => $role,
    'options' => [
        ['value' => '',      'label' => 'All Roles'],
        ['value' => 'Admin', 'label' => 'Admin'],
        ['value' => 'Staff', 'label' => 'Staff'],
        ['value' => 'User',  'label' => 'User'],
    ]
];
require __DIR__ . '/components/toolbar.php';
?>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th class="actions-cell">Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($users)): foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= safe($user['id']) ?></td>
                                <td><?= safe($user['username'] ?? 'N/A') ?></td>
                                <td><strong><?= safe($user['name'] ?? '') ?></strong></td>
                                <td class="text-muted"><?= safe($user['email'] ?? 'N/A') ?></td>
                                <td><?= safe(ucfirst($user['role'] ?? 'User')) ?></td>
                                <td><span class="badge <?= strtolower($user['status'] ?? '') === 'active' ? 'badge-success' : 'badge-neutral' ?>"><?= safe(ucfirst($user['status'] ?? '')) ?></span></td>
                                <td class="actions-cell">
                                    <button class="btn btn-secondary btn-sm" onclick="openEditModal(<?= (int)$user['id'] ?>, '<?= safe($user['username'] ?? '') ?>', '<?= safe($user['name'] ?? '') ?>', '<?= safe($user['email'] ?? '') ?>', '<?= safe($user['status'] ?? 'active') ?>')">Edit</button>
                                    <?php if (strtolower($user['status'] ?? '') === 'inactive'): ?>
                                        <button class="btn btn-success btn-sm" onclick="enableUser(<?= (int)$user['id'] ?>)">Enable</button>
                                    <?php else: ?>
                                        <button class="btn btn-danger btn-sm" onclick="disableUser(<?= (int)$user['id'] ?>)">Disable</button>
                                    <?php endif; ?>
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

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header"><h2>Add New User</h2><button class="modal-close" onclick="document.getElementById('addUserModal').style.display='none'">&times;</button></div>
            <div class="modal-body">
                <form method="POST" action="../backend/handlers/process_user.php" data-validate novalidate>
                    <input type="hidden" name="action" value="add">
                    <div class="form-grid">
                        <div class="form-group"><label>Username *</label><input type="text" name="username" required minlength="3"><span class="field-error"></span></div>
                        <div class="form-group"><label>Name *</label><input type="text" name="name" required minlength="2"><span class="field-error"></span></div>
                        <div class="form-group"><label>Email *</label><input type="email" name="email" required><span class="field-error"></span></div>
                        <div class="form-group"><label>Password *</label><input type="password" name="password" required minlength="6"><span class="field-error"></span></div>
                        <div class="form-group"><label>Role</label><select name="role"><option value="User">User</option><option value="Staff">Staff</option><option value="Admin">Admin</option></select></div>
                        <div class="form-group"><label>Status</label><select name="status"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addUserModal').style.display='none'">Cancel</button><?= csrf_field() ?><button type="submit" class="btn btn-primary">Create User</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header"><h2>Edit User</h2><button class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</button></div>
            <div class="modal-body">
                <form method="POST" action="../backend/handlers/user_action.php" data-validate novalidate>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="editUserId">
                    <div class="form-grid">
                        <div class="form-group"><label>Username *</label><input type="text" name="username" id="editUsername" required minlength="3"><span class="field-error"></span></div>
                        <div class="form-group"><label>Name *</label><input type="text" name="name" id="editUserName" required minlength="2"><span class="field-error"></span></div>
                        <div class="form-group"><label>Email *</label><input type="email" name="email" id="editUserEmail" required><span class="field-error"></span></div>
                        <div class="form-group"><label>Status</label><select name="status" id="editUserStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button><?= csrf_field() ?><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openAddModal() {
        document.getElementById('addUserModal').style.display = 'flex';
    }

    function openEditModal(id, username, name, email, status) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editUsername').value = username;
        document.getElementById('editUserName').value = name;
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserStatus').value = status;
        document.getElementById('editModal').style.display = 'flex';
    }
    function disableUser(id) {
        document.getElementById('disableUserId').value = id;
        confirmAction(
            'Are you sure you want to disable this user account? The account will remain in the system but will no longer be able to sign in.',
            function() {
                document.getElementById('disableUserForm').submit();
            },
            'danger'
        );
    }

    function enableUser(id) {
        document.getElementById('enableUserId').value = id;
        confirmAction(
            'Are you sure you want to enable this user account so they can sign in again?',
            function() {
                document.getElementById('enableUserForm').submit();
            },
            'warning'
        );
    }
    </script>
    <?php require __DIR__ . '/components/footer.php'; ?>
