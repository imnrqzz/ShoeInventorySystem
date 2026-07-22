<?php
// frontend/import_xml.php - Import inventory items from XML
require_once __DIR__ . '/components/auth.php';
require_once __DIR__ . '/../backend/itemtab.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xml_file'])) {
    verify_csrf();
    $file = $_FILES['xml_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = 'File upload failed.';
        $messageType = 'error';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xml') {
            $message = 'Please upload a valid XML file.';
            $messageType = 'error';
        } else {
            $xml = simplexml_load_file($file['tmp_name']);
            if ($xml === false) {
                $message = 'Failed to parse XML file.';
                $messageType = 'error';
            } else {
                $imported = 0;
                $skipped = 0;
                foreach ($xml->item as $row) {
                    $name = trim((string)$row->name);
                    if ($name === '') { $skipped++; continue; }

                    $quantity    = intval((string)($row->quantity ?? 0));
                    $minQuantity = intval((string)($row->min_quantity ?? 5));
                    $price       = floatval((string)($row->price ?? 0));
                    $supplierName = trim((string)($row->supplier ?? ''));

                    // Find supplier by name if provided
                    $supplierId = null;
                    if ($supplierName !== '') {
                        $stmt = $pdo->prepare("SELECT order_id FROM suppliers WHERE company_name = ? LIMIT 1");
                        $stmt->execute([$supplierName]);
                        $sup = $stmt->fetch();
                        if ($sup) $supplierId = $sup['order_id'];
                    }

                    // Check if item already exists by name
                    $check = $pdo->prepare("SELECT id FROM items WHERE name = ? LIMIT 1");
                    $check->execute([$name]);
                    if ($check->fetch()) {
                        // Update existing item
                        $stmt = $pdo->prepare("UPDATE items SET quantity = ?, min_quantity = ?, price = ?, supplier_id = ? WHERE name = ?");
                        $stmt->execute([$quantity, $minQuantity, $price, $supplierId, $name]);
                    } else {
                        // Insert new item
                        $nextId = $itemManager->getNextAvailableId();
                        $stmt = $pdo->prepare("INSERT INTO items (id, name, quantity, min_quantity, price, supplier_id) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$nextId, $name, $quantity, $minQuantity, $price, $supplierId]);
                    }
                    $imported++;
                }
                $message = "Import complete: $imported item(s) imported" . ($skipped > 0 ? ", $skipped skipped" : '') . ".";
                $messageType = 'success';
            }
        }
    }
}

header('Location: item.php?import_msg=' . urlencode($message) . '&import_type=' . $messageType);
exit;