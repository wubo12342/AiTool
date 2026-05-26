<?php
// api/admin/_auth.php — shared admin token verification (included by other admin endpoints)
require_once __DIR__ . '/../jwt_helper.php';
require_once __DIR__ . '/../response_helper.php';

function verify_admin(string $token): array {
    if (!$token) send_unauthorized('請先登入');
    $payload = JWT::decode($token);
    if (!$payload) send_unauthorized('登入已過期，請重新登入');
    if ((int)($payload['role'] ?? 0) !== 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => '權限不足，需要管理員帳號']);
        exit;
    }
    return $payload;
}
