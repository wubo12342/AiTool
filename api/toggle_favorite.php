<?php
// api/toggle_favorite.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/jwt_helper.php';
require_once __DIR__ . '/response_helper.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$tool_id = $data['tool_id'] ?? null;
$token = $data['token'] ?? null;

if (!$tool_id || !$token) {
    send_bad_request('缺少必要參數');
}

try {
    $decoded = JWT::decode($token);
    if (!$decoded || !isset($decoded['uid'])) {
        send_unauthorized('無效的 Token，請重新登入');
    }
    $uid = $decoded['uid'];

    $stmt = $pdo->prepare("SELECT * FROM user_likes WHERE UID = ? AND tool_ID = ?");
    $stmt->execute([$uid, $tool_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("DELETE FROM user_likes WHERE UID = ? AND tool_ID = ?");
        $stmt->execute([$uid, $tool_id]);
        $status = 'unliked';
    } else {
        $stmt = $pdo->prepare("INSERT INTO user_likes (UID, tool_ID) VALUES (?, ?)");
        $stmt->execute([$uid, $tool_id]);
        $status = 'liked';
    }

    echo json_encode([
        'success' => true,
        'status' => $status,
        'message' => $status === 'liked' ? '已加入收藏' : '已取消收藏'
    ]);

} catch (Throwable $e) {
    send_server_error($e);
}
