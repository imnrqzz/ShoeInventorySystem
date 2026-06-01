<?php
// backend/suppliertab.php

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/SupplierManager.php';

$database = new Database();
$pdo = $database->getConnection();
$supplierManager = new SupplierManager($pdo);

// 2. ACTION: ADD NEW SUPPLIER (Updated)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $company_name = trim($_POST['supplier_name'] ?? '');
    $contact      = trim($_POST['contact_person'] ?? ''); // New
    $category     = trim($_POST['category'] ?? '');       // New
    $phone_email  = trim($_POST['phone_email'] ?? '');    // New
    $status       = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

    // Update your addSupplier method in SupplierManager.php to accept these new arguments
    $supplierManager->addSupplier($company_name, $contact, $category, $phone_email, $status);
    header("Location: Supplier.php");
    exit();
}

// 3. ACTION: UPDATE (EDIT) EXISTING SUPPLIER (Updated)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id           = intval($_POST['id'] ?? 0);
    $company_name = trim($_POST['supplier_name'] ?? '');
    $contact      = trim($_POST['contact_person'] ?? ''); // New
    $category     = trim($_POST['category'] ?? '');       // New
    $phone_email  = trim($_POST['phone_email'] ?? '');    // New
    $status       = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

    // Update your updateSupplier method in SupplierManager.php to accept these new arguments
    $supplierManager->updateSupplier($id, $company_name, $contact, $category, $phone_email, $status);
    header("Location: Supplier.php");
    exit();
}

// In backend/suppliertab.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $company_name = trim($_POST['supplier_name'] ?? '');
    $contact      = trim($_POST['contact_person'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $phone_email  = trim($_POST['phone_email'] ?? '');
    $status       = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

    // Ensure your SupplierManager class has this method signature
    $supplierManager->addSupplier($company_name, $contact, $category, $phone_email, $status);
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