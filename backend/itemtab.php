<?php
// backend/itemtab.php

// 1. DATABASE CONNECTION
$host          = 'localhost';
$user          = 'root';
$pass          = '';
$charset       = 'utf8mb4';
$database_name = 'pos_inventory_system';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database_name;charset=$charset", $user, $pass, $options);
} catch (\PDOException $e) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fff0f0; border-left:5px solid #ff4d4d; margin:20px;'><strong>Database Connection Failed!</strong><br>" . htmlspecialchars($e->getMessage()) . "</div>");
}

// 2. FETCH ACTIVE SUPPLIERS FOR DROPDOWNS
try {
    $supplierStmt = $pdo->query("SELECT order_id AS id, company_name AS name FROM suppliers WHERE status = 'Active' ORDER BY company_name ASC");
    $suppliers = $supplierStmt->fetchAll();
} catch (\PDOException $e) {
    $suppliers = [];
}

// 3. ACTION: HANDLE FORM SUBMISSIONS (ADD OR UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CREATE NEW ITEM WITH AUTOMATIC SEQUENTIAL ID REUSE
    if ($action === 'add') {
        $name         = trim($_POST['item_name'] ?? '');
        $supplier_id  = $_POST['supplier_id'] !== '' ? intval($_POST['supplier_id']) : null;
        $min_quantity = intval($_POST['min_quantity'] ?? 0);
        $price        = floatval($_POST['price'] ?? 0);

        if ($name !== '') {
            // Find the lowest missing sequence ID to fill any database table gaps
            $gapQuery = "SELECT MIN(unused.id) AS next_id 
                         FROM (
                             SELECT 1 AS id 
                             UNION ALL 
                             SELECT id + 1 FROM items
                         ) AS unused 
                         LEFT JOIN items USING (id) 
                         WHERE items.id IS NULL";
            
            $gapStmt = $pdo->query($gapQuery);
            $result = $gapStmt->fetch();
            $next_id = isset($result['next_id']) ? intval($result['next_id']) : 1;

            // Force insert using the extracted missing key value
            $stmt = $pdo->prepare('INSERT INTO items (id, name, supplier_id, quantity, min_quantity, price) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$next_id, $name, $supplier_id, 0, $min_quantity, $price]);
        }
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

        if ($id > 0 && $name !== '') {
            $stmt = $pdo->prepare('UPDATE items SET name = ?, supplier_id = ?, min_quantity = ?, price = ? WHERE id = ?');
            $stmt->execute([$name, $supplier_id, $min_quantity, $price, $id]);
        }
        header('Location: item.php');
        exit;
    }
}

// 4. ACTION: DELETE ITEM RELATIONSHIP
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare('DELETE FROM items WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: item.php');
    exit;
}

// 5. HELPER: FETCH INDIVIDUAL ITEM OBJECT FOR ACTIVE POPUP STATE
$editing_item = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $pdo->prepare('SELECT * FROM items WHERE id = ?');
    $stmt->execute([$edit_id]);
    $editing_item = $stmt->fetch();
}

// 6. VIEW: SEARCH FILTER & MASTER ITEMS LIST ROWS
$search = trim($_GET['search'] ?? '');
$sql = 'SELECT items.*, suppliers.company_name AS supplier_name 
        FROM items 
        LEFT JOIN suppliers ON items.supplier_id = suppliers.order_id 
        WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND items.name LIKE ?';
    $params[] = "%$search%";
}
$sql .= ' ORDER BY items.id DESC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fff9e6; border-left:5px solid #ffcc00; margin:20px;'><strong>Error processing data:</strong> " . htmlspecialchars($e->getMessage()) . "</div>");
}