<?php
// backend/classes/ItemManager.php

class ItemManager {
    private $db;

    // Standard constructor dependency injection
    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    // 1. Fetch Active Suppliers For Selection Form Dropdowns
    public function getActiveSuppliers() {
        try {
            $stmt = $this->db->query("SELECT order_id AS id, company_name AS name FROM suppliers WHERE status = 'Active' ORDER BY company_name ASC");
            return $stmt ? $stmt->fetchAll() : [];
        } catch (\PDOException $e) {
            return [];
        }
    }

    // 2. Fetch the lowest missing sequence ID to recycle deleted row indices
    public function getNextAvailableId() {
        $gapQuery = "SELECT MIN(unused.id) AS next_id 
                     FROM (
                         SELECT 1 AS id 
                         UNION ALL 
                         SELECT id + 1 FROM items
                     ) AS unused 
                     LEFT JOIN items USING (id) 
                     WHERE items.id IS NULL";
        
        $stmt = $this->db->query($gapQuery);
        $result = $stmt->fetch();
        return isset($result['next_id']) ? intval($result['next_id']) : 1;
    }

    // 3. Create: Add a New Item
    public function addItem($name, $supplier_id, $min_quantity, $price) {
        if ($name === '') return false;

        $next_id = $this->getNextAvailableId();
        $stmt = $this->db->prepare('INSERT INTO items (id, name, supplier_id, quantity, min_quantity, price) VALUES (?, ?, ?, ?, ?, ?)');
        return $stmt->execute([$next_id, $name, $supplier_id, 0, $min_quantity, $price]);
    }

    // 4. Update: Edit Existing Item Settings
    public function updateItem($id, $name, $supplier_id, $min_quantity, $price) {
        if ($id <= 0 || $name === '') return false;

        $stmt = $this->db->prepare('UPDATE items SET name = ?, supplier_id = ?, min_quantity = ?, price = ? WHERE id = ?');
        return $stmt->execute([$name, $supplier_id, $min_quantity, $price, $id]);
    }

    // 5. Delete: Drop an Item Row Relationship
    public function deleteItem($id) {
        $id = intval($id);
        $stmt = $this->db->prepare('DELETE FROM items WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // 6. Read: Fetch Single Item For Active Frame Modal Target
    public function getItemById($id) {
        $id = intval($id);
        $stmt = $this->db->prepare('SELECT * FROM items WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // 7. Read: Filtered Dashboard Table Retrieval Engine
    public function getAllItems($search = '') {
        $sql = 'SELECT items.*, suppliers.company_name AS supplier_name 
                FROM items 
                LEFT JOIN suppliers ON items.supplier_id = suppliers.order_id 
                WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND items.name LIKE ?';
            $params[] = "%" . trim($search) . "%";
        }
        $sql .= ' ORDER BY items.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}