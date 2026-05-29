<?php
// suppliertab.php

// 1. DATABASE CONNECTION
$host          = "localhost";
$username      = "root";
$password      = "";
$dbname        = "pos_inventory_system";
$charset       = "utf8mb4";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// 2. ACTION: ADD NEW SUPPLIER (WITH UNUSED IDENTITY GAP FILLER)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $company_name = trim($_POST['supplier_name'] ?? '');
    $status       = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

    if (!empty($company_name)) {
        // Run lookups for any deleted keys to safely recycle order_id keys sequentially
        $gapQuery = "SELECT MIN(unused.order_id) AS next_id 
                     FROM (
                         SELECT 1 AS order_id 
                         UNION ALL 
                         SELECT order_id + 1 FROM suppliers
                     ) AS unused 
                     LEFT JOIN suppliers USING (order_id) 
                     WHERE suppliers.order_id IS NULL";
        
        $gapStmt = $conn->query($gapQuery);
        $result = $gapStmt->fetch();
        $next_id = isset($result['next_id']) ? intval($result['next_id']) : 1;

        $query = "INSERT INTO suppliers (order_id, company_name, status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$next_id, $company_name, $status]);
    }
    
    header("Location: Supplier.php");
    exit();
}

// 3. ACTION: UPDATE (EDIT) EXISTING SUPPLIER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id           = intval($_POST['id'] ?? 0);
    $company_name = trim($_POST['supplier_name'] ?? '');
    $status       = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

    if ($id > 0 && !empty($company_name)) {
        $query = "UPDATE suppliers SET company_name = ?, status = ? WHERE order_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$company_name, $status, $id]);
    }
    
    header("Location: Supplier.php");
    exit();
}

// 4. ACTION: DELETE SUPPLIER
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    $query = "DELETE FROM suppliers WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    
    header("Location: Supplier.php");
    exit();
}

// 5. HELPER: FETCH SUPPLIER FOR ACTIVE EDIT STATE
$editing_supplier = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    
    $query = "SELECT * FROM suppliers WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $raw_edit = $stmt->fetch();
    
    if ($raw_edit) {
        // Map elements explicitly to match input structures inside Supplier.php forms
        $editing_supplier = [
            'id'           => $raw_edit['order_id'],
            'company_name' => $raw_edit['company_name'],
            'status'       => $raw_edit['status']
        ];
    }
}

// 6. VIEW: FETCH ALL OR SEARCHED RECORD ROWS
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [];

$query = "SELECT * FROM suppliers WHERE 1=1";

if (!empty($search)) {
    $query .= " AND company_name LIKE ?";
    $params[] = "%$search%";
}

$query .= " ORDER BY order_id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$suppliers = $stmt->fetchAll();
