<?php
// backend/suppliertab.php

// 1. Secure Class Injection
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/SupplierManager.php';

// Instantiate Core Drivers
$database = new Database();
$pdo = $database->getConnection(); // Expose connection through encapsulated getter method
$supplierManager = new SupplierManager($pdo);

// 2. ACTION: ADD NEW SUPPLIER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $company_name = trim($_POST['supplier_name'] ?? '');
    $status       = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

    $supplierManager->addSupplier($company_name, $status);
    header("Location: Supplier.php");
    exit();
}

// 3. ACTION: UPDATE (EDIT) EXISTING SUPPLIER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id           = intval($_POST['id'] ?? 0);
    $company_name = trim($_POST['supplier_name'] ?? '');
    $status       = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

    $supplierManager->updateSupplier($id, $company_name, $status);
    header("Location: Supplier.php");
    exit();
}

// 4. ACTION: DELETE SUPPLIER
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $supplierManager->deleteSupplier($id);
    header("Location: Supplier.php");
    exit();
}

// 5. HELPER: FETCH SUPPLIER FOR ACTIVE EDIT STATE
$editing_supplier = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    $editing_supplier = $supplierManager->getSupplierById($id);
}

// 6. VIEW: FETCH ALL OR SEARCHED RECORD ROWS FOR USER DASHBOARD DISPLAY
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$suppliers = $supplierManager->getAllSuppliers($search);
