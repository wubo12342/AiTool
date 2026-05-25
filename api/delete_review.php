<?php
// api/delete_review.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/jwt_helper.php';
require_once __DIR__ . '/response_helper.php';

$input = json_decode(file_get_contents('php://input'), true);
$rid = $input['review_id'] ?? 0;
$token = $input['token'] ?? '';

$decoded = JWT::decode($token);
if (!$decoded || !isset($decoded['uid'])) {
    send_unauthorized('權限不足');
}

$uid = $decoded['uid'];

try {
    // 只能刪自己的評論
    $stmt = $pdo->prepare("DELETE FROM tool_reviews WHERE review_ID = ? AND UID = ?");
    $success = $stmt->execute([$rid, $uid]);
    echo json_encode(['success' => $success]);
} catch (Throwable $e) {
    send_server_error($e);
}
