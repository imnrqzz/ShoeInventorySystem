<?php
// 1. DATABASE CONNECTION
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "pos_inventory_system";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// 2. ACTION: ADD NEW SUPPLIER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $company_name   = trim($_POST['supplier_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? 'N/A');
    $category       = trim($_POST['category'] ?? 'General');
    $phone_email    = trim($_POST['phone_email'] ?? 'N/A');
    // Read numeric active state and map to text badge label state matching code tables
    $active_state   = isset($_POST['active']) ? intval($_POST['active']) : 1;
    $status         = ($active_state === 1) ? 'Active' : 'Inactive';

    if (!empty($company_name)) {
        $query = "INSERT INTO suppliers (company_name, contact_person, category, phone_email, status) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$company_name, $contact_person, $category, $phone_email, $status]);
    }
    
    header("Location: Supplier.php");
    exit();
}

// 3. ACTION: UPDATE (EDIT) EXISTING SUPPLIER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id             = intval($_POST['id'] ?? 0);
    $company_name   = trim($_POST['supplier_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? 'N/A');
    $category       = trim($_POST['category'] ?? 'General');
    $phone_email    = trim($_POST['phone_email'] ?? 'N/A');
    $active_state   = isset($_POST['active']) ? intval($_POST['active']) : 1;
    $status         = ($active_state === 1) ? 'Active' : 'Inactive';

    if ($id > 0 && !empty($company_name)) {
        $query = "UPDATE suppliers SET 
                  company_name = ?, 
                  contact_person = ?, 
                  category = ?, 
                  phone_email = ?, 
                  status = ? 
                  WHERE order_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$company_name, $contact_person, $category, $phone_email, $status, $id]);
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

// 5. HELPER: FETCH SUPPLIER FOR EDITING
$editing_supplier = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    
    $query = "SELECT * FROM suppliers WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $raw_edit = $stmt->fetch();
    
    if ($raw_edit) {
        // Map database records to properties expected by page template forms
        $editing_supplier = [
            'id'     => $raw_edit['order_id'],
            'name'   => $raw_edit['company_name'],
            'active' => (strtolower($raw_edit['status']) === 'active') ? 1 : 0
        ];
    }
}

// 6. VIEW: FETCH ALL/SEARCHED SUPPLIERS FOR THE TABLE
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if (!empty($search)) {
    $query = "SELECT * FROM suppliers WHERE company_name LIKE ? ORDER BY order_id DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute(["%$search%"]);
} else {
    $query = "SELECT * FROM suppliers ORDER BY order_id DESC";
    $stmt = $conn->query($query); 
}

$raw_suppliers = $stmt->fetchAll();
$suppliers = [];

// Format database column structures to array entries read by template tables
foreach ($raw_suppliers as $row) {
    $suppliers[] = [
        'id'         => $row['order_id'],
        'name'       => $row['company_name'],
        'active'     => (strtolower($row['status']) === 'active') ? 1 : 0,
        'created_at' => $row['phone_email'] // Display phone/email or timestamps dynamically
    ];
}
?>
