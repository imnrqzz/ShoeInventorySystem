<?php
// backend/classes/SupplierManager.php

class SupplierManager {
    private $db;

    // Use constructor dependency injection
    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    // 1. Core ID Recycling Loop
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
    public function addSupplier($name, $contact, $category, $phone, $status) {
        $sql = "INSERT INTO suppliers (company_name, contact_person, category, phone_email, status) 
                VALUES (:name, :contact, :cat, :phone, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name'    => $name,
            ':contact' => $contact,
            ':cat'     => $category,
            ':phone'   => $phone,
            ':status'  => $status
        ]);
    }

    // 3. Update: Edit Existing Supplier
    public function updateSupplier($id, $name, $contact, $category, $phone, $status) {
        $sql = "UPDATE suppliers SET 
                company_name = :name, 
                contact_person = :contact, 
                category = :cat, 
                phone_email = :phone, 
                status = :status 
                WHERE order_id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'      => $id,
            ':name'    => $name,
            ':contact' => $contact,
            ':cat'     => $category,
            ':phone'   => $phone,
            ':status'  => $status
        ]);
    }

    // 4. Delete: Remove Supplier Record
    public function deleteSupplier($id) {
        $id = intval($id);
        $query = "DELETE FROM suppliers WHERE order_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    // 5. Read: Fetch Single Item Details
    public function getSupplierById($id) {
        $id = intval($id);
        $query = "SELECT * FROM suppliers WHERE order_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 6. Read: Master Search and Dataset Delivery 
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
} // This single closing bracket closes the class