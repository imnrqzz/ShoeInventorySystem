<?php
// backend/itemtab.php
// Requires auth.php (which loads bootstrap.php) to be included first by the parent page.

require_once __DIR__ . '/validate.php';
require_once __DIR__ . '/classes/ItemManager.php';
$itemManager = new ItemManager($pdo);

$suppliers = $itemManager->getActiveSuppliers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        // Validate item fields using shared rules
        $errors = validateForm('item', $_POST);
        if ($errors) {
            header('Location: item.php');
            exit;
        }

        $name         = trim($_POST['item_name'] ?? '');
        $supplier_id  = $_POST['supplier_id'] !== '' ? intval($_POST['supplier_id']) : null;
        $min_quantity = intval($_POST['min_quantity'] ?? 0);
        $price        = floatval($_POST['price'] ?? 0);

        if ($action === 'add') {
            $itemManager->addItem($name, $supplier_id, $min_quantity, $price);
        } else {
            $id = intval($_POST['id'] ?? 0);
            $itemManager->updateItem($id, $name, $supplier_id, $min_quantity, $price);
        }
        header('Location: item.php');
        exit;
    }
}

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $itemManager->deleteItem($id);
    header('Location: item.php');
    exit;
}

$editing_item = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $editing_item = $itemManager->getItemById($edit_id);
}

$search = trim($_GET['search'] ?? '');
try {
    $items = $itemManager->getAllItems($search);
} catch (\PDOException $e) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fff9e6; border-left:5px solid #ffcc00; margin:20px;'><strong>Error processing data:</strong> " . htmlspecialchars($e->getMessage()) . "</div>");
}