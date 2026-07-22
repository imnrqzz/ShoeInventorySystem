<?php
// api/auth.php - API key authentication middleware

require_once __DIR__ . '/../backend/Classes/Database.php';

// Shared database connection for API
function getApiDb(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $db = new Database();
        $pdo = $db->getConnection();
    }
    return $pdo;
}

function getApiUser(): ?int {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if ($apiKey === '') {
        return null;
    }

    $pdo = getApiDb();
    $hash = hash('sha256', $apiKey);

    $stmt = $pdo->prepare(
        "SELECT id, user_id FROM api_keys WHERE key_hash = ? AND status = 'active' LIMIT 1"
    );
    $stmt->execute([$hash]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    return $row['user_id'] ? (int) $row['user_id'] : null;
}

function requireApiAuth(): int {
    $userId = getApiUser();
    if ($userId === null) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid or missing API key']);
        exit;
    }
    return $userId;
}

function requireApiAdmin(): int {
    $userId = requireApiAuth();
    $pdo = getApiDb();

    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if (!$row || strtolower($row['role']) !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit;
    }

    return $userId;
}
