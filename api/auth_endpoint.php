<?php
// api/auth_endpoint.php - Token generation endpoint
// POST /api/auth/token - Generate API key (username + password)

if ($method !== 'POST') {
    jsonError('Method not allowed', 405);
}

$input = getInput();
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if ($username === '' || $password === '') {
    jsonError('Username and password are required');
}

require_once __DIR__ . '/../backend/Classes/Database.php';

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT id, password_hash, role, status FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    jsonError('Invalid credentials', 401);
}

if (!password_verify($password, $user['password_hash'])) {
    jsonError('Invalid credentials', 401);
}

$status = strtolower($user['status'] ?? '');
if ($status !== '' && $status !== 'active' && $status !== 'enabled') {
    jsonError('Account is disabled', 403);
}

// Generate a random API key
$rawKey = bin2hex(random_bytes(32));
$keyHash = hash('sha256', $rawKey);

$label = $input['label'] ?? 'API Key';

$stmt = $pdo->prepare(
    "INSERT INTO api_keys (key_hash, label, user_id, status) VALUES (?, ?, ?, 'active')"
);
$stmt->execute([$keyHash, $label, $user['id']]);

jsonSuccess([
    'api_key' => $rawKey,
    'label' => $label,
    'user_id' => $user['id'],
    'role' => $user['role'],
], 'API key generated. Store it securely — it cannot be retrieved later.');
