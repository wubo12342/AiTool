<?php
header('Content-Type: application/json');
require_once 'db.php';
require_once 'jwt_helper.php';

$input = json_decode(file_get_contents('php://input'), true);
$rid = $input['review_id'] ?? 0;
$token = $input['token'] ?? '';

$decoded = JWT::decode($token);
if (!$decoded || !isset($decoded['uid'])) {
    echo json_encode(['success' => false, 'error' => '權限不足']);
    exit;
}

$uid = $decoded['uid'];

try {
    // 確保使用者只能刪除自己的評論
    $stmt = $pdo->prepare("DELETE FROM tool_reviews WHERE review_ID = ? AND UID = ?");
    $success = $stmt->execute([$rid, $uid]);
    echo json_encode(['success' => $success]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
