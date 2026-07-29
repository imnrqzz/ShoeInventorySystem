<?php
// backend/recover_admin.php - Create/recover admin account
// Access: http://localhost/ShoeInventorySystem/backend/recover_admin.php

require_once __DIR__ . '/../Classes/Database.php';

$db = new Database();
$pdo = $db->getConnection();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? 'Admin');

    if ($username === '' || $password === '') {
        $message = 'Username and password are required.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } else {
        // Check if username already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        
        if ($check->fetch()) {
            // Update existing user to admin
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET role = 'admin', password_hash = ?, status = 'Active' WHERE username = ?");
            $stmt->execute([$hash, $username]);
            $message = "User '$username' has been upgraded to admin. Password updated.";
            $messageType = 'success';
        } else {
            // Create new admin user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, name, email, status) VALUES (?, ?, 'admin', ?, 'admin@inventory.com', 'Active')");
            $stmt->execute([$username, $hash, $name]);
            $message = "Admin user '$username' created successfully!";
            $messageType = 'success';
        }
    }
}

// Check current admin count
$adminCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Admin - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px 32px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        h1 { font-size: 1.4rem; margin-bottom: 8px; color: #1a1a1a; }
        .subtitle { color: #666; font-size: 0.9rem; margin-bottom: 20px; }
        .status {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }
        .status.warning { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; }
        .status.ok { background: #dcfce7; border: 1px solid #86efac; color: #166534; }
        label { display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 4px; color: #444; }
        input {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px;
            font-size: 0.9rem; margin-bottom: 12px;
        }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        button {
            width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 0.9rem;
            font-weight: 500; cursor: pointer; background: #2563eb; color: white;
        }
        button:hover { background: #1d4ed8; }
        .msg { padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem; }
        .msg.success { background: #dcfce7; border: 1px solid #86efac; color: #166534; }
        .msg.error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
        .footer { margin-top: 20px; text-align: center; font-size: 0.8rem; color: #999; }
        .footer a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Recover Admin Account</h1>
        <p class="subtitle">Create or restore an admin user</p>

        <?php if ($adminCount === 0): ?>
        <div class="status warning">
            <strong>No admin accounts found!</strong> Create one below.
        </div>
        <?php else: ?>
        <div class="status ok">
            <strong><?= $adminCount ?> admin account(s) exist.</strong> You can still create another.
        </div>
        <?php endif; ?>

        <?php if ($message): ?>
        <div class="msg <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Username *</label>
            <input type="text" name="username" placeholder="e.g. admin" required minlength="3">
            
            <label>Password *</label>
            <input type="password" name="password" placeholder="Min 6 characters" required minlength="6">
            
            <label>Display Name</label>
            <input type="text" name="name" placeholder="System Admin" value="Admin">
            
            <button type="submit">Create / Restore Admin</button>
        </form>

        <div class="footer">
            <a href="../frontend/login.php">Back to Login</a>
        </div>
    </div>
</body>
</html>
