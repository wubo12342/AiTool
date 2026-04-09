<?php
require_once 'jwt_helper.php';

header('Content-Type: application/json');

// 取得前端傳來的 JSON 資料
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['token'])) {
    echo json_encode(['valid' => false, 'error' => 'No token provided']);
    exit;
}

// 驗證 JWT，如果被竄改或過期，JWT::decode 會回傳 false
$decoded = JWT::decode($data['token']);

if ($decoded) {
    echo json_encode(['valid' => true, 'payload' => $decoded]);
} else {
    echo json_encode(['valid' => false, 'error' => 'Invalid or expired token']);
}
?>
