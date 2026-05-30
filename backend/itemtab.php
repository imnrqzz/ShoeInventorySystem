<?php
// backend/itemtab.php

// 1. Secure Object Architecture Injections
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/ItemManager.php';

// Instantiate Connection and Domain Logic Managers
$database = new Database();
$pdo = $database->getConnection();
$itemManager = new ItemManager($pdo);

// 2. FETCH ACTIVE SUPPLIERS FOR DROPDOWNS
$suppliers = $itemManager->getActiveSuppliers();

// 3. ACTION: HANDLE FORM SUBMISSIONS (ADD OR UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CREATE NEW ITEM
    if ($action === 'add') {
        $name         = trim($_POST['item_name'] ?? '');
        $supplier_id  = $_POST['supplier_id'] !== '' ? intval($_POST['supplier_id']) : null;
        $min_quantity = intval($_POST['min_quantity'] ?? 0);
        $price        = floatval($_POST['price'] ?? 0);

        $itemManager->addItem($name, $supplier_id, $min_quantity, $price);
        header('Location: item.php');
        exit;
    }
    
    // UPDATE EXISTING ITEM DATA DETAILS
    if ($action === 'edit') {
        $id           = intval($_POST['id'] ?? 0);
        $name         = trim($_POST['item_name'] ?? '');
        $supplier_id  = $_POST['supplier_id'] !== '' ? intval($_POST['supplier_id']) : null;
        $min_quantity = intval($_POST['min_quantity'] ?? 0);
        $price        = floatval($_POST['price'] ?? 0);

        $itemManager->updateItem($id, $name, $supplier_id, $min_quantity, $price);
        header('Location: item.php');
        exit;
    }
}

// 4. ACTION: DELETE ITEM RELATIONSHIP
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $itemManager->deleteItem($id);
    header('Location: item.php');
    exit;
}

// 5. HELPER: FETCH INDIVIDUAL ITEM OBJECT FOR ACTIVE POPUP STATE
$editing_item = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $editing_item = $itemManager->getItemById($edit_id);
}

// 6. VIEW: SEARCH FILTER & MASTER ITEMS LIST ROWS
$search = trim($_GET['search'] ?? '');
try {
    $items = $itemManager->getAllItems($search);
} catch (\PDOException $e) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fff9e6; border-left:5px solid #ffcc00; margin:20px;'><strong>Error processing data:</strong> " . htmlspecialchars($e->getMessage()) . "</div>");
}
