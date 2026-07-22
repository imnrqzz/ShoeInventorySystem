<?php
// api/index.php - Central API router

header('Content-Type: application/json; charset=utf-8');

// Handle preflight CORS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// --- JSON Response Helpers ---

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonSuccess($data = null, string $message = ''): void {
    jsonResponse(['success' => true, 'data' => $data, 'message' => $message]);
}

function jsonError(string $message, int $code = 400, array $errors = []): void {
    $resp = ['success' => false, 'message' => $message];
    if (!empty($errors)) {
        $resp['errors'] = $errors;
    }
    jsonResponse($resp, $code);
}

function getInput(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// --- Route Parsing ---

$uri = $_SERVER['REQUEST_URI'];
$base = '/ShoeInventorySystem/api';
$path = parse_url($uri, PHP_URL_PATH);
$path = substr($path, strlen($base));
$path = '/' . trim($path, '/');

$method = $_SERVER['REQUEST_METHOD'];

// Split path into segments: [resource, id, sub-resource]
$segments = array_values(array_filter(explode('/', $path)));
$resource = $segments[0] ?? '';
$id = isset($segments[1]) && is_numeric($segments[1]) ? (int) $segments[1] : null;
$sub = $segments[2] ?? null;

// --- Route to Endpoint ---

switch ($resource) {
    case 'auth':
        require __DIR__ . '/auth_endpoint.php';
        break;
    case 'items':
        require __DIR__ . '/items.php';
        break;
    case 'stock':
        require __DIR__ . '/stock.php';
        break;
    case 'suppliers':
        require __DIR__ . '/suppliers.php';
        break;
    case 'transactions':
        require __DIR__ . '/transactions.php';
        break;
    case 'users':
        require __DIR__ . '/users.php';
        break;
    default:
        jsonError('Not found', 404);
        break;
}
