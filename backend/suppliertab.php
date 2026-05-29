<?php
// 1. DATABASE CONNECTION
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "inventory";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// 2. ACTION: ADD NEW SUPPLIER
if (isset($_POST['add_supplier'])) {
    $company_name   = $_POST['company_name'];
    $contact_person = $_POST['contact_person'];
    $category       = $_POST['category'];
    $phone_email    = $_POST['phone_email'];
    $status         = $_POST['status'];

    $query = "INSERT INTO suppliers (company_name, contact_person, category, phone_email, status) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$company_name, $contact_person, $category, $phone_email, $status]);
    
    header("Location: Supplier.php");
    exit();
}

// 3. ACTION: UPDATE (EDIT) EXISTING SUPPLIER
if (isset($_POST['update_supplier'])) {
    $id             = intval($_POST['order_id']);
    $company_name   = $_POST['company_name'];
    $contact_person = $_POST['contact_person'];
    $category       = $_POST['category'];
    $phone_email    = $_POST['phone_email'];
    $status         = $_POST['status'];

    $query = "UPDATE suppliers SET 
              company_name = ?, 
              contact_person = ?, 
              category = ?, 
              phone_email = ?, 
              status = ? 
              WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$company_name, $contact_person, $category, $phone_email, $status, $id]);
    
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
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    
    $query = "SELECT * FROM suppliers WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(); 
}

// 6. VIEW: FETCH ALL/SEARCHED SUPPLIERS FOR THE TABLE
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

if (!empty($search)) {
    $query = "SELECT * FROM suppliers WHERE company_name LIKE ?";
    $stmt = $conn->prepare($query);
    $stmt->execute(["%$search%"]);
} else {
    $query = "SELECT * FROM suppliers";
    $stmt = $conn->query($query); 
}

/* FIX FOR THE FATAL ERROR:
   We fetch all matching records into a native array. 
   This lets us interact with $result identically to a standard MySQL database record array 
   without disrupting your row validation or table loops!
*/
$result = $stmt->fetchAll(); 
?>