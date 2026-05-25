<?php
// api/cors.php — 統一處理 CORS header 與 OPTIONS preflight
// 把允許的來源寫成白名單，而不是 *，避免任意網站都能呼叫我們的 API。

require_once __DIR__ . '/env_loader.php';

$envOrigins = getenv('CORS_ALLOWED_ORIGINS');
$allowedOrigins = $envOrigins
    ? array_map('trim', explode(',', $envOrigins))
    : [
        'http://localhost:5173',   // Vite dev server
        'http://localhost:8080',   // XAMPP / Apache
        'http://localhost:8081',   // Docker app 服務
    ];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
