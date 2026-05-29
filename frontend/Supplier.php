<?php include('suppliertab.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoes Inventory System - Suppliers</title>
    <link rel="stylesheet" href="Supplierstyle.css?v=<?php echo time(); ?>">
</head>
<body>

    <header class="navbar">
        <div class="nav-container">
            <div class="logo">Shoes Inventory System</div>
            <nav class="nav-links">
                <a href="#">Dashboard</a>
                <a href="#">Items</a>
                <a href="#" class="active">Suppliers</a>
                <a href="#">Stock</a>
                <a href="#">Transactions</a>
                <a href="#">Users</a>
            </nav>
            <div class="user-profile">
                <div class="avatar"></div>
                <span>User1</span>
            </div>
        </div>
    </header>

    <main class="main-content">
        
        <h1 class="page-title">Suppliers Management</h1>

        <div class="form-card">
            <h3><?php echo $edit_data ? "Edit Supplier (ID: ".$edit_data['order_id'].")" : "Add New Supplier"; ?></h3>
            <form method="POST" action="Supplier.php">
                
                <?php if ($edit_data): ?>
                    <input type="hidden" name="order_id" value="<?php echo $edit_data['order_id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="company_name" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['company_name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Contact Person</label>
                        <input type="text" name="contact_person" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['contact_person']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="category" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['category']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Phone / Email</label>
                        <input type="text" name="phone_email" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['phone_email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Active" <?php echo ($edit_data && $edit_data['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($edit_data && $edit_data['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <?php if ($edit_data): ?>
                    <button type="submit" name="update_supplier" class="btn-update">Update Supplier</button>
                    <a href="Supplier.php" class="btn-cancel">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_supplier" class="btn-add">+ Add Supplier</button>
                <?php endif; ?>
            </form>
        </div>

        <form method="GET" action="Supplier.php" class="filter-bar">
            <input type="text" name="search" class="search-input" placeholder="Search by Company Name..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-search">Search</button>
            <a href="Supplier.php" class="btn btn-reset" style="text-decoration: none; padding: 10px 15px;">Reset</a>
        </form>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Category</th>
                        <th>Phone / Email</th>
                        <th>Status</th>
                        <th style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($result) && count($result) > 0): ?>
                        <?php foreach ($result as $row): ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact_person']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_email']); ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="Supplier.php?edit_id=<?php echo $row['order_id']; ?>" class="btn-action edit" style="text-decoration: none;">Edit</a>
                                    <a href="Supplier.php?delete_id=<?php echo $row['order_id']; ?>" class="btn-action delete" style="text-decoration: none;" onclick="return confirm('Delete this supplier?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">No suppliers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>