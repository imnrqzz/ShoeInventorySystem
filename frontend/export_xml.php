<?php
// Best Practice: Even file-download endpoints need authentication.
// Without this, anyone could visit this URL and export all transaction data.
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once '../backend/classes/Database.php';

$db = (new Database())->getConnection();

// Fetch all transactions
$query = "SELECT t.transaction_date, i.name as item_name, s.company_name as supplier_name, 
                 t.transaction_type, t.quantity, u.username as user_name
          FROM transactions t
          JOIN items i ON t.item_id = i.id
          JOIN suppliers s ON i.supplier_id = s.order_id
          JOIN users u ON t.user_id = u.id";

$stmt = $db->query($query);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create XML using DOM
$dom = new DOMDocument("1.0", "UTF-8");
$dom->formatOutput = true;

$root = $dom->createElement("transactions");
$dom->appendChild($root);

foreach ($transactions as $row) {
    $item = $dom->createElement("transaction");
    
    $item->appendChild($dom->createElement("date", $row['transaction_date']));
    $item->appendChild($dom->createElement("item", $row['item_name']));
    $item->appendChild($dom->createElement("supplier", $row['supplier_name']));
    $item->appendChild($dom->createElement("type", $row['transaction_type']));
    $item->appendChild($dom->createElement("quantity", $row['quantity']));
    $item->appendChild($dom->createElement("user", $row['user_name']));
    
    $root->appendChild($item);
}

// Set headers to force download
header("Content-Type: application/xml");
header("Content-Disposition: attachment; filename=transactions_export.xml");
echo $dom->saveXML();
?>