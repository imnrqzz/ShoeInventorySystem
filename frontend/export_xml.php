<?php
// frontend/export_xml.php - Export inventory items as XML
require_once __DIR__ . '/components/auth.php';

$stmt = $pdo->query("SELECT i.id, i.name, i.quantity, i.min_quantity, i.price, 
                            COALESCE(s.company_name, '') AS supplier_name
                     FROM items i 
                     LEFT JOIN suppliers s ON i.supplier_id = s.order_id 
                     ORDER BY i.name ASC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dom = new DOMDocument("1.0", "UTF-8");
$dom->formatOutput = true;

$root = $dom->createElement("items");
$dom->appendChild($root);

foreach ($items as $row) {
    $item = $dom->createElement("item");
    $item->appendChild($dom->createElement("id", $row['id']));
    $item->appendChild($dom->createElement("name", $row['name']));
    $item->appendChild($dom->createElement("quantity", $row['quantity']));
    $item->appendChild($dom->createElement("min_quantity", $row['min_quantity']));
    $item->appendChild($dom->createElement("price", $row['price']));
    $item->appendChild($dom->createElement("supplier", $row['supplier_name']));
    $root->appendChild($item);
}

header("Content-Type: application/xml");
header("Content-Disposition: attachment; filename=inventory_export.xml");
echo $dom->saveXML();