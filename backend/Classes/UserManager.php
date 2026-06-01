<?php
class UserManager {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // CREATE
    public function addUser($username, $password, $role) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $hash, $role]);
    }

    // READ
    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, username, role, 'Active' as status FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE
    public function updateUser($id, $username, $role) {
        $stmt = $this->db->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        return $stmt->execute([$username, $role, $id]);
    }

    // DELETE
    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public function getFilteredUsers($search, $role) {
    $sql = "SELECT * FROM users WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR username LIKE ? OR email LIKE ?)";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
    }
    
    if (!empty($role)) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
    
?>