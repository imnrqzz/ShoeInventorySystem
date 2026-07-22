<?php
// api/suppliers.php - Suppliers CRUD endpoint
// GET    /api/suppliers          - List all suppliers (?search=)
// GET    /api/suppliers/{id}     - Get single supplier
// POST   /api/suppliers          - Create supplier
// PUT    /api/suppliers/{id}     - Update supplier
// DELETE /api/suppliers/{id}     - Delete supplier

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../backend/Classes/SupplierManager.php';

$userId = requireApiAuth();
$db = new Database();
$pdo = $db->getConnection();
$supplierManager = new SupplierManager($pdo);

switch ($method) {
    case 'GET':
        if ($id !== null) {
            $supplier = $supplierManager->getSupplierById($id);
            if (!$supplier) {
                jsonError('Supplier not found', 404);
            }
            jsonSuccess($supplier);
        }

        $search = $_GET['search'] ?? '';
        $suppliers = $supplierManager->getAllSuppliers($search);
        jsonSuccess($suppliers);
        break;

    case 'POST':
        $input = getInput();
        $name = trim($input['company_name'] ?? '');
        $contact = trim($input['contact_person'] ?? '');
        $category = trim($input['category'] ?? '');
        $phone = trim($input['phone_email'] ?? '');
        $status = trim($input['status'] ?? 'Active');

        if ($name === '') {
            jsonError('Company name is required');
        }
        if ($contact === '') {
            jsonError('Contact person is required');
        }
        if ($category === '') {
            jsonError('Category is required');
        }
        if ($phone === '') {
            jsonError('Phone/email is required');
        }

        $result = $supplierManager->addSupplier($name, $contact, $category, $phone, $status);
        if (!$result) {
            jsonError('Failed to create supplier', 500);
        }

        $newId = $supplierManager->getNextAvailableId() - 1;
        $newSupplier = $supplierManager->getSupplierById($newId);
        jsonResponse(['success' => true, 'data' => $newSupplier, 'message' => 'Supplier created'], 201);
        break;

    case 'PUT':
        if ($id === null) {
            jsonError('Supplier ID is required');
        }

        $existing = $supplierManager->getSupplierById($id);
        if (!$existing) {
            jsonError('Supplier not found', 404);
        }

        $input = getInput();
        $name = trim($input['company_name'] ?? $existing['company_name']);
        $contact = trim($input['contact_person'] ?? $existing['contact_person']);
        $category = trim($input['category'] ?? $existing['category']);
        $phone = trim($input['phone_email'] ?? $existing['phone_email']);
        $status = trim($input['status'] ?? $existing['status']);

        $result = $supplierManager->updateSupplier($id, $name, $contact, $category, $phone, $status);
        if (!$result) {
            jsonError('Failed to update supplier', 500);
        }

        $updated = $supplierManager->getSupplierById($id);
        jsonSuccess($updated, 'Supplier updated');
        break;

    case 'DELETE':
        if ($id === null) {
            jsonError('Supplier ID is required');
        }

        $existing = $supplierManager->getSupplierById($id);
        if (!$existing) {
            jsonError('Supplier not found', 404);
        }

        $result = $supplierManager->deleteSupplier($id);
        if (!$result) {
            jsonError('Failed to delete supplier', 500);
        }

        jsonSuccess(null, 'Supplier deleted');
        break;

    default:
        jsonError('Method not allowed', 405);
        break;
}
