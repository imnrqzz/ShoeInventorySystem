<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
require_once '../backend/classes/Database.php';
require_once '../backend/classes/UserManager.php';

$db = new Database();
$userManager = new UserManager($db->getConnection());

// Filter logic: Fetch filtered users or all users
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$users = $userManager->getFilteredUsers($search, $role); // Ensure your UserManager supports this

$total_users = count($users);
$admins = 0;
$active_users = 0;

foreach ($users as $user) {
    if (isset($user['role']) && strtolower($user['role']) === 'admin') $admins++;
    if (isset($user['status']) && strtolower($user['status']) === 'active') $active_users++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoes Inventory System - Users</title>
    <link rel="stylesheet" href="../css/userstyle.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page">
       <header class="topbar">
        <div class="brand">
            <div class="logo">
                <img src="../images/shoes.png" alt="Shoes Logo" />
            </div>
            <div class="title"><span>Shoes Inventory System</span></div>
        </div>
    
    <nav class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="Item.php">Items</a>
        <a href="Supplier.php">Suppliers</a>
        <a href="transactions.php">Transactions</a>
        <a href="stock.php">Stock</a>
        <a href="user.php" class="active">Users</a>
        <a href="reports.php">Reports</a>
    </nav>
    
    <div class="top-actions">
        <div class="user-badge">
            <span class="avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></span>
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
        <a href="../backend/logout.php" class="logout-button">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</header>

        <section class="dashboard-panel">
            <h2>USER MANAGEMENT</h2>
            <div class="summary">
                <article class="dashboard-card"><div class="value"><?= $total_users ?></div><div class="label">Total Users</div></article>
                <article class="dashboard-card"><div class="value"><?= $admins ?></div><div class="label">Administrators</div></article>
                <article class="dashboard-card"><div class="value"><?= $active_users ?></div><div class="label">Active Users</div></article>
            </div>
        </section>

        <form method="GET" action="user.php" class="filters-container">
            <input type="text" name="search" class="search-bar" placeholder="Search user name or email..." value="<?= htmlspecialchars($search) ?>">
            <select name="role" class="category-select">
                <option value="">All Roles</option>
                <option value="Admin" <?= ($role == 'Admin') ? 'selected' : '' ?>>Admin</option>
                <option value="Staff" <?= ($role == 'Staff') ? 'selected' : '' ?>>Staff</option>
            </select>
            <button type="submit" class="btn btn-filter">Filter</button>
            <a href="user.php" class="btn btn-reset" style="text-decoration:none; display:inline-block; line-height:42px;">Reset</a>
        </form>

        <section class="panel">
            <div class="panel-header">System User Directory</div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>#</th><th>USERNAME</th><th>NAME</th><th>EMAIL</th><th>ROLE</th><th>STATUS</th><th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username'] ?? 'N/A') ?></td>
                                <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                                <td><?= ucfirst($user['role'] ?? 'User') ?></td>
                                <td><span class="badge <?= ($user['status'] === 'active') ? 'ok' : 'low' ?>"><?= ucfirst($user['status']) ?></span></td>
                                <td>
                                    <button class="btn-action edit" onclick="openModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['name']) ?>', '<?= htmlspecialchars($user['email']) ?>', '<?= $user['status'] ?>')">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <button class="btn-action delete" onclick="deleteUser(<?= $user['id'] ?>)">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div id="editModal" class="modal-overlay" style="display:none;">
        <div class="edit-box">
            <h2>Edit User</h2>
            <form method="POST" action="../backend/user_action.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editUserId">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="editUsername" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="editUserName" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="editUserEmail" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="editUserStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="actions-row">
                    <button type="submit" class="btn btn-save-modal">Save Changes</button>
                    <button type="button" class="btn btn-reset" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id, username, name, email, status) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editUsername').value = username;
            document.getElementById('editUserName').value = name;
            document.getElementById('editUserEmail').value = email;
            document.getElementById('editUserStatus').value = status;
            document.getElementById('editModal').style.display = 'flex';
        }
        function closeModal() { document.getElementById('editModal').style.display = 'none'; }
        function deleteUser(id) {
    if(confirm('Are you sure you want to delete this user?')) {
        // Create a hidden form and submit it via POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../backend/user_action.php';
        
        const actionInput = document.createElement('input');
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const idInput = document.createElement('input');
        idInput.name = 'id';
        idInput.value = id;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}
    </script>
</body>
</html>