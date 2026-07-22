<?php
// backend/delete_image.php - Remove item image

require_once __DIR__ . '/bootstrap.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$itemId = (int)($_POST['item_id'] ?? 0);
if ($itemId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

// Get current image
$stmt = $pdo->prepare("SELECT image FROM items WHERE id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit;
}

if ($item['image']) {
    $imagePath = __DIR__ . '/../' . $item['image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Clear image from database
$stmt = $pdo->prepare("UPDATE items SET image = NULL WHERE id = ?");
$stmt->execute([$itemId]);

echo json_encode(['success' => true, 'message' => 'Image removed']);
