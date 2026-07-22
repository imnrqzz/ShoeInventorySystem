<?php
// backend/upload_image.php - Handle item image uploads

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

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error']);
    exit;
}

$file = $_FILES['image'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP']);
    exit;
}

// Validate file size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB']);
    exit;
}

// Create upload directory if not needed
$uploadDir = __DIR__ . '/../uploads/items/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'item_' . $itemId . '_' . time() . '.' . $ext;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save image']);
    exit;
}

// Delete old image if exists
$stmt = $pdo->prepare("SELECT image FROM items WHERE id = ?");
$stmt->execute([$itemId]);
$oldItem = $stmt->fetch();
if ($oldItem && $oldItem['image']) {
    $oldPath = $uploadDir . $oldItem['image'];
    if (file_exists($oldPath)) {
        unlink($oldPath);
    }
}

// Update database
$relativePath = 'uploads/items/' . $filename;
$stmt = $pdo->prepare("UPDATE items SET image = ? WHERE id = ?");
$stmt->execute([$relativePath, $itemId]);

echo json_encode([
    'success' => true,
    'message' => 'Image uploaded successfully',
    'image_path' => $relativePath
]);
