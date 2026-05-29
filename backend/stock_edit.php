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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - <?= htmlspecialchars($item['item_name']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="stockstyle.css">
</head>
<body class="edit-body">

    <div class="edit-box">
        <div class="edit-header">
            <div class="edit-icon">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
            <div>
                <h2>Modify Stock Level</h2>
            </div>
        </div>

        <div class="item-preview-badge">
            <span class="item-preview-label">Product Title</span>
            <strong><?= htmlspecialchars($item['item_name']) ?></strong>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Current Quantity</label>
                <input type="number" step="0.01" name="current_qty" value="<?= htmlspecialchars($item['current_qty']) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label>Minimum Threshold</label>
                <input type="number" step="0.01" name="min_threshold" value="<?= htmlspecialchars($item['min_threshold']) ?>" required>
            </div>

            <div class="actions-row">
                <a href="stock.php" class="btn btn-reset">Cancel</a>
                <button type="submit" class="btn btn-filter">Save Changes</button>
            </div>
        </form>
    </div>

</body>
</html>
