<?php
// api/setup.php - One-time setup script (admin only)
require_once __DIR__ . '/../backend/bootstrap.php';
requireLogin();
requireAdmin();

$db = new Database();
$pdo = $db->getConnection();
$message = '';
$apiKey = '';

// Handle table creation
if (isset($_POST['create_table'])) {
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `api_keys` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `key_hash` varchar(255) NOT NULL,
                `label` varchar(100) NOT NULL,
                `user_id` int(10) UNSIGNED DEFAULT NULL,
                `status` enum('active','revoked') NOT NULL DEFAULT 'active',
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `key_hash` (`key_hash`),
                KEY `user_id` (`user_id`),
                CONSTRAINT `fk_api_keys_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $message = '<div class="msg success">api_keys table created successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="msg error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Handle key generation
if (isset($_POST['generate_key'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $label = trim($_POST['label'] ?? 'My API Key');

    if ($username === '' || $password === '') {
        $message = '<div class="msg error">Username and password are required.</div>';
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $message = '<div class="msg error">Invalid credentials.</div>';
        } else {
            $rawKey = bin2hex(random_bytes(32));
            $keyHash = hash('sha256', $rawKey);

            $stmt = $pdo->prepare("INSERT INTO api_keys (key_hash, label, user_id, status) VALUES (?, ?, ?, 'active')");
            $stmt->execute([$keyHash, $label, $user['id']]);

            $apiKey = $rawKey;
            $message = '<div class="msg success">API key generated! Copy it below — it cannot be retrieved later.</div>';
        }
    }
}

// Check if table exists
$tableExists = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'api_keys'");
    $tableExists = $stmt->rowCount() > 0;
} catch (Exception $e) {
    // Table doesn't exist
}

// List existing keys
$existingKeys = [];
if ($tableExists) {
    try {
        $stmt = $pdo->query("SELECT ak.id, ak.label, ak.status, ak.created_at, u.username FROM api_keys ak LEFT JOIN users u ON ak.user_id = u.id ORDER BY ak.created_at DESC");
        $existingKeys = $stmt->fetchAll();
    } catch (Exception $e) {
        // Ignore
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Setup - Shoe Inventory System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: #f5f5f5; padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin-bottom: 8px; color: #1a1a1a; }
        .subtitle { color: #666; margin-bottom: 24px; }
        .card { background: white; border-radius: 12px; padding: 24px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card h2 { font-size: 1.1rem; margin-bottom: 16px; color: #333; }
        label { display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 4px; color: #444; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px;
            font-size: 0.9rem; margin-bottom: 12px;
        }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        button {
            padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.9rem;
            font-weight: 500; cursor: pointer; transition: background 0.15s;
        }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #16a34a; color: white; }
        .btn-success:hover { background: #15803d; }
        .msg { padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem; }
        .msg.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .msg.error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .api-key-box {
            background: #f0f9ff; border: 2px dashed #2563eb; border-radius: 8px;
            padding: 16px; margin: 12px 0; font-family: monospace; font-size: 0.85rem;
            word-break: break-all; color: #1e40af;
        }
        .step { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
        .step-num {
            width: 28px; height: 28px; border-radius: 50%; background: #2563eb;
            color: white; display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 600; flex-shrink: 0;
        }
        .step-text { padding-top: 3px; }
        .step-text h3 { font-size: 0.95rem; margin-bottom: 4px; }
        .step-text p { font-size: 0.85rem; color: #666; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { font-weight: 600; color: #555; }
        .badge { padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-revoked { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>API Setup</h1>
        <p class="subtitle">Configure the REST API for your Shoe Inventory System</p>

        <?= $message ?>

        <!-- Step 1: Create Table -->
        <div class="card">
            <h2>Step 1: Create API Keys Table</h2>
            <?php if ($tableExists): ?>
                <p style="color:#16a34a;font-size:0.9rem;"><i class="fa-solid fa-check"></i> api_keys table already exists.</p>
            <?php else: ?>
                <p style="color:#666;font-size:0.9rem;margin-bottom:12px;">This will create the <code>api_keys</code> table in your database.</p>
                <form method="POST">
                    <button type="submit" name="create_table" value="1" class="btn btn-primary">Create Table</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Step 2: Generate API Key -->
        <div class="card">
            <h2>Step 2: Generate API Key</h2>
            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
                <label>Label (optional)</label>
                <input type="text" name="label" placeholder="e.g. Mobile App Key" value="My API Key">
                <button type="submit" name="generate_key" value="1" class="btn btn-success">Generate API Key</button>
            </form>

            <?php if ($apiKey): ?>
                <div class="api-key-box">
                    <?= htmlspecialchars($apiKey) ?>
                </div>
                <p style="font-size:0.85rem;color:#666;margin-top:8px;">
                    Use this key in requests: <code>X-API-Key: <?= htmlspecialchars($apiKey) ?></code>
                </p>
            <?php endif; ?>
        </div>

        <!-- Existing Keys -->
        <?php if (!empty($existingKeys)): ?>
        <div class="card">
            <h2>Existing API Keys</h2>
            <table>
                <thead>
                    <tr><th>Label</th><th>User</th><th>Status</th><th>Created</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($existingKeys as $key): ?>
                    <tr>
                        <td><?= htmlspecialchars($key['label']) ?></td>
                        <td><?= htmlspecialchars($key['username'] ?? '—') ?></td>
                        <td><span class="badge badge-<?= $key['status'] ?>"><?= ucfirst($key['status']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($key['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Usage Examples -->
        <div class="card">
            <h2>Quick Test (PowerShell)</h2>
            <pre style="background:#f8f8f8;padding:12px;border-radius:8px;font-size:0.8rem;overflow-x:auto;color:#333;">
# List items
curl http://localhost/ShoeInventorySystem/api/index.php/items `
  -H "X-API-Key: YOUR_KEY_HERE"

# Create item
curl -X POST http://localhost/ShoeInventorySystem/api/index.php/items `
  -H "Content-Type: application/json" `
  -H "X-API-Key: YOUR_KEY_HERE" `
  -d '{"name":"Test Shoe","min_quantity":5,"price":99.99}'

# List stock
curl http://localhost/ShoeInventorySystem/api/index.php/stock `
  -H "X-API-Key: YOUR_KEY_HERE"
            </pre>
        </div>
    </div>
</body>
</html>
