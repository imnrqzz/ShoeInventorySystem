<?php
// You can include your database connection or authentication checks here
// include('config.php');
// include('auth.php');

// Mock data simulating a database fetch for users
$users = [
    [
        'id' => 1,
        'name' => 'Admin User',
        'email' => 'admin@inventory.com',
        'role' => 'Admin',
        'status' => 'Active'
    ],
    [
        'id' => 2,
        'name' => 'John Doe',
        'email' => 'john.doe@shoes.com',
        'role' => 'Staff',
        'status' => 'Active'
    ],
    [
        'id' => 3,
        'name' => 'Jane Smith',
        'email' => 'jane.s@shoes.com',
        'role' => 'Staff',
        'status' => 'Inactive'
    ]
];

// Mock metrics calculation
$total_users = count($users);
$admins = 0;
$active_users = 0;

foreach ($users as $user) {
    if ($user['role'] === 'Admin') $admins++;
    if ($user['status'] === 'Active') $active_users++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoes Inventory System - Users</title>
    <!-- Google Fonts & FontAwesome for system styling -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Link to external stylesheet -->
    <link rel="stylesheet" href="userstyle.css">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo-area">
            <div class="logo-icon">S</div>
            <div class="logo-text">Shoes Inventory System</div>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="items.php">Items</a>
            <a href="suppliers.php">Suppliers</a>
            <a href="stock.php">Stock</a>
            <a href="transactions.php">Transactions</a>
            <a href="users.php" class="active">Users</a>
        </div>
        <div class="user-profile">
            <div class="avatar">U</div>
            <span class="profile-name">User1</span>
            <button class="logout-btn"><i class="fa-solid fa-power-off"></i></button>
        </div>
    </nav>

    <!-- USER METRICS CONTAINER -->
    <section class="hero-banner">
        <h2 class="banner-title">User Metrics</h2>
        <div class="metrics-container">
            <div class="metric-card">
                <div class="metric-icon"><i class="fa-solid fa-users"></i></div>
                <div>
                    <div class="metric-value"><?php echo $total_users; ?></div>
                    <div class="metric-label">Total Users</div>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i class="fa-solid fa-user-shield"></i></div>
                <div>
                    <div class="metric-value"><?php echo $admins; ?></div>
                    <div class="metric-label">Administrators</div>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i class="fa-solid fa-user-check"></i></div>
                <div>
                    <div class="metric-value"><?php echo $active_users; ?></div>
                    <div class="metric-label">Active Users</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ACTIONS & FILTERS BAR -->
    <div class="action-bar">
        <div class="search-wrapper">
            <input type="text" placeholder="Search user name or email...">
        </div>
        <div class="filter-controls">
            <select class="filter-select">
                <option value="">All Roles</option>
                <option value="Admin">Admin</option>
                <option value="Staff">Staff</option>
            </select>
            <button class="btn btn-filter">Filter</button>
            <button class="btn btn-reset">Reset</button>
        </div>
    </div>

    <!-- DATA GRID GRID CONTAINER -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%">#</th>
                    <th style="width: 25%">Name</th>
                    <th style="width: 25%">Email</th>
                    <th style="width: 14%">Role</th>
                    <th style="width: 14%">Status</th>
                    <th style="width: 14%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td class="user-name"><?php echo htmlspecialchars($user['name']); ?></td>
                    <td class="user-email"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="user-role"><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <?php if ($user['status'] === 'Active'): ?>
                            <span class="status-pill active">
                                <i class="fa-solid fa-circle status-dot"></i> Active
                            </span>
                        <?php else: ?>
                            <span class="status-pill inactive">
                                <i class="fa-solid fa-triangle-exclamation status-dot"></i> Inactive
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <button class="action-btn action-edit">
                            <i class="fa-solid fa-pen"></i> Edit
                        </button>
                        <button class="action-btn action-delete">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>