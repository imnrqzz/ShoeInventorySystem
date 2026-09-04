<?php
// backend/handlers/process_login.php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../utils/validate.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ShoeInventorySystem/frontend/login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validate fields using the shared validation rules
verify_csrf();
$errors = validateForm('login', $_POST);
if ($errors) {
    $redirectUsername = rawurlencode($username);
    header('Location: /ShoeInventorySystem/frontend/login.php?err=1&username=' . $redirectUsername);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, password_hash, role, status FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $status = strtolower((string)($user['status'] ?? ''));
        $isActiveAccount = $status === '' || $status === 'active' || $status === 'enabled';

        if (!$isActiveAccount) {
            $redirectUsername = rawurlencode($username);
            header('Location: /ShoeInventorySystem/frontend/login.php?err=disabled&username=' . $redirectUsername);
            exit;
        }

        if (password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'] ?? 'User';

            header('Location: /ShoeInventorySystem/frontend/index.php');
            exit;
        }
    }
} catch (\PDOException $e) {
    error_log("Login processing error: " . $e->getMessage());
}

$redirectUsername = rawurlencode($username);
header('Location: /ShoeInventorySystem/frontend/login.php?err=invalid&username=' . $redirectUsername);
exit;