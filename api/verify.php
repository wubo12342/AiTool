<?php
// api/verify.php — 驗證前端送來的 JWT 是否仍有效
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/jwt_helper.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['token'])) {
    echo json_encode(['valid' => false, 'error' => 'No token provided']);
    exit;
}

$decoded = JWT::decode($data['token']);

if ($decoded) {
    echo json_encode(['valid' => true, 'payload' => $decoded]);
} else {
    echo json_encode(['valid' => false, 'error' => 'Invalid or expired token']);
}
