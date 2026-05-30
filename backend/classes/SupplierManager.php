<?php
// backend/classes/SupplierManager.php

class SupplierManager {
    private $db;

    // Use constructor dependency injection to feed the unified PDO handle
    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    // 1. Core ID Recycling Loop (Finds first available deleted index gap)
    public function getNextAvailableId() {
        $gapQuery = "SELECT MIN(unused.order_id) AS next_id 
                     FROM (
                         SELECT 1 AS order_id 
                         UNION ALL 
                         SELECT order_id + 1 FROM suppliers
                     ) AS unused 
                     LEFT JOIN suppliers USING (order_id) 
                     WHERE suppliers.order_id IS NULL";
        
        $stmt = $this->db->query($gapQuery);
        $result = $stmt->fetch();
        return isset($result['next_id']) ? intval($result['next_id']) : 1;
    }

    // 2. Create: Add New Supplier
    public function addSupplier($company_name, $status) {
        if (empty($company_name)) return false;

        $next_id = $this->getNextAvailableId();
        $query = "INSERT INTO suppliers (order_id, company_name, status) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$next_id, $company_name, $status]);
    }

    // 3. Update: Edit Existing Supplier
    public function updateSupplier($id, $company_name, $status) {
        if ($id <= 0 || empty($company_name)) return false;

        $query = "UPDATE suppliers SET company_name = ?, status = ? WHERE order_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$company_name, $status, $id]);
    }

    // 4. Delete: Remove Supplier Record
    public function deleteSupplier($id) {
        $id = intval($id);
        $query = "DELETE FROM suppliers WHERE order_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    // 5. Read: Fetch Single Item Details for Active Edit State
    public function getSupplierById($id) {
        $id = intval($id);
        $query = "SELECT * FROM suppliers WHERE order_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $raw_edit = $stmt->fetch();

        if ($raw_edit) {
            return [
                'id'           => $raw_edit['order_id'],
                'company_name' => $raw_edit['company_name'],
                'status'       => $raw_edit['status']
            ];
        }
        return null;
    }

    // 6. Read: Master Search and Dataset Array Delivery 
    public function getAllSuppliers($search = '') {
        $params = [];
        $query = "SELECT * FROM suppliers WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND company_name LIKE ?";
            $params[] = "%" . trim($search) . "%";
        }

        $query .= " ORDER BY order_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}