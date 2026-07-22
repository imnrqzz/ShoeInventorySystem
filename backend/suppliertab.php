<?php
// backend/suppliertab.php
// Requires auth.php (which loads bootstrap.php) to be included first by the parent page.

require_once __DIR__ . '/utils/validate.php';
require_once __DIR__ . '/classes/SupplierManager.php';
$supplierManager = new SupplierManager($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();
    $action = $_POST['action'];

    if ($action === 'add' || $action === 'edit') {
        // Validate supplier fields using shared rules
        $errors = validateForm('supplier', $_POST);
        if ($errors) {
            header("Location: Supplier.php");
            exit();
        }

        $company_name = trim($_POST['supplier_name'] ?? '');
        $contact      = trim($_POST['contact_person'] ?? '');
        $category     = trim($_POST['category'] ?? '');
        $phone_email  = trim($_POST['phone_email'] ?? '');
        $status       = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

        if ($action === 'add') {
            $supplierManager->addSupplier($company_name, $contact, $category, $phone_email, $status);
        } else {
            $id = intval($_POST['id'] ?? 0);
            $supplierManager->updateSupplier($id, $company_name, $contact, $category, $phone_email, $status);
        }
        header("Location: Supplier.php");
        exit();
    }
}

$editing_supplier = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    $editing_supplier = $supplierManager->getSupplierById($id);
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$suppliers = $supplierManager->getAllSuppliers($search);