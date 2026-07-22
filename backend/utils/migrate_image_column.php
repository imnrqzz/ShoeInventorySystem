<?php
// backend/migrate_image_column.php - Add image column to items table (admin only)
require_once __DIR__ . '/bootstrap.php';
requireLogin();
requireAdmin();

$db = new Database();
$pdo = $db->getConnection();

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM items LIKE 'image'");
    if ($stmt->rowCount() > 0) {
        echo "Column 'image' already exists in items table.<br>";
    } else {
        $pdo->exec("ALTER TABLE items ADD COLUMN `image` varchar(255) DEFAULT NULL AFTER `price`");
        echo "Column 'image' added to items table successfully!<br>";
    }

    $uploadDir = dirname(__DIR__) . '/uploads/items/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "Created uploads/items/ directory.<br>";
    } else {
        echo "uploads/items/ directory exists.<br>";
    }

    echo "<br><a href='../frontend/item.php'>Go to Items Page</a>";

} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
