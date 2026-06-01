<?php
require_once 'classes/Database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Delete
    if ($_POST['action'] === 'delete') {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header("Location: ../frontend/user.php");
        exit;
    } 
    // Handle Update
    elseif ($_POST['action'] === 'update') {
        // 1. Capture ALL values, including username
        $id = $_POST['id'];
        $username = $_POST['username']; // New field added
        $name = $_POST['name'];
        $email = $_POST['email']; 
        $status = $_POST['status'];

        // 2. Update the SQL to include the username column
        // Ensure 'username' matches the column name in your database table
        $stmt = $db->prepare("UPDATE users SET username = ?, name = ?, email = ?, status = ? WHERE id = ?");
        
        // 3. Execute with the new parameter included
        $stmt->execute([$username, $name, $email, $status, $id]);
        
        // 4. Redirect back to the user page
        header("Location: ../frontend/user.php");
        exit;
    }
}
?>