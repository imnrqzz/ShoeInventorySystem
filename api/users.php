<?php
// api/users.php - Users endpoint (admin only)
// GET    /api/users          - List all users
// POST   /api/users          - Create user
// PUT    /api/users/{id}     - Update user

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../backend/Classes/UserManager.php';

$userId = requireApiAdmin();
$pdo = getApiDb();
$userManager = new UserManager($pdo);

switch ($method) {
    case 'GET':
        if ($id !== null) {
            $stmt = $pdo->prepare("SELECT id, username, role, name, email, status, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            if (!$user) {
                jsonError('User not found', 404);
            }
            jsonSuccess($user);
        }

        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $users = $userManager->getFilteredUsers($search, $role);
        jsonSuccess($users);
        break;

    case 'POST':
        $input = getInput();
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $role = trim($input['role'] ?? 'user');
        $status = trim($input['status'] ?? 'Active');

        if ($username === '') {
            jsonError('Username is required');
        }
        if ($password === '' || strlen($password) < 6) {
            jsonError('Password must be at least 6 characters');
        }
        if ($name === '') {
            jsonError('Name is required');
        }
        if ($email === '') {
            jsonError('Email is required');
        }

        // Check duplicate username
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            jsonError('Username already exists');
        }

        $result = $userManager->addUser($username, $password, $name, $email, $role, $status);
        if (!$result) {
            jsonError('Failed to create user', 500);
        }

        // Get the new user
        $stmt = $pdo->prepare("SELECT id, username, role, name, email, status, created_at FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $newUser = $stmt->fetch();

        jsonResponse(['success' => true, 'data' => $newUser, 'message' => 'User created'], 201);
        break;

    case 'PUT':
        if ($id === null) {
            jsonError('User ID is required');
        }

        $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            jsonError('User not found', 404);
        }

        $input = getInput();
        $username = trim($input['username'] ?? $existing['username']);
        $role = trim($input['role'] ?? $existing['role']);

        $result = $userManager->updateUser($id, $username, $role);
        if (!$result) {
            jsonError('Failed to update user', 500);
        }

        $stmt = $pdo->prepare("SELECT id, username, role, name, email, status, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $updated = $stmt->fetch();

        jsonSuccess($updated, 'User updated');
        break;

    default:
        jsonError('Method not allowed', 405);
        break;
}
