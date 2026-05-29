<?php
// edit.php

$host    = 'localhost';
$db      = 'shoes_inventory';
$user    = 'root'; 
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get the item ID from the URL parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the current item details
$stmt = $pdo->prepare("SELECT * FROM stock WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item not found!");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_qty = $_POST['current_qty'];
    $min_threshold = $_POST['min_threshold'];

    $updateStmt = $pdo->prepare("UPDATE stock SET current_qty = ?, min_threshold = ? WHERE id = ?");
    if ($updateStmt->execute([$current_qty, $min_threshold, $id])) {
        // Redirect back to the main stock panel on success
        header("Location: stock.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Item - <?= htmlspecialchars($item['item_name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .edit-box {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .actions-row { display: flex; gap: 10px; margin-top: 20px; }
    </style>
</head>
<body style="background-color: #5d6cc6;">

    <div class="edit-box">
        <h2>Edit Stock Item</h2>
        <p style="margin-bottom: 20px; color: #555;">Item: <strong><?= htmlspecialchars($item['item_name']) ?></strong></p>

        <form method="POST">
            <div class="form-group">
                <label>Current Quantity</label>
                <input type="number" step="0.01" name="current_qty" value="<?= htmlspecialchars($item['current_qty']) ?>" required>
            </div>

            <div class="form-group">
                <label>Minimum Threshold</label>
                <input type="number" step="0.01" name="min_threshold" value="<?= htmlspecialchars($item['min_threshold']) ?>" required>
            </div>

            <div class="actions-row">
                <button type="submit" class="btn btn-filter" style="border-radius: 4px;">Save Changes</button>
                <a href="stock.php" class="btn btn-reset" style="border-radius: 4px; text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancel</a>
            </div>
        </form>
    </div>

</body>
</html>