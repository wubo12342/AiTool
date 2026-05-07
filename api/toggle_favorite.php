<?php
// api/toggle_favorite.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'db.php';
require_once 'jwt_helper.php';

// 處理預檢請求 (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 取得 POST 資料
$data = json_decode(file_get_contents('php://input'), true);
$tool_id = $data['tool_id'] ?? null;
$token = $data['token'] ?? null;

if (!$tool_id || !$token) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

try {
    // 驗證 Token 並取得 UID
    $decoded = JWT::decode($token);
    if (!$decoded || !isset($decoded['uid'])) {
        echo json_encode(['success' => false, 'message' => '無效的 Token，請重新登入']);
        exit;
    }
    $uid = $decoded['uid'];

    // 檢查是否已經收藏
    $stmt = $pdo->prepare("SELECT * FROM user_likes WHERE UID = ? AND tool_ID = ?");
    $stmt->execute([$uid, $tool_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // 已收藏，則移除
        $stmt = $pdo->prepare("DELETE FROM user_likes WHERE UID = ? AND tool_ID = ?");
        $stmt->execute([$uid, $tool_id]);
        $status = 'unliked';
    } else {
        // 未收藏，則新增
        $stmt = $pdo->prepare("INSERT INTO user_likes (UID, tool_ID) VALUES (?, ?)");
        $stmt->execute([$uid, $tool_id]);
        $status = 'liked';
    }

    echo json_encode([
        'success' => true,
        'status' => $status,
        'message' => $status === 'liked' ? '已加入收藏' : '已取消收藏'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '伺服器錯誤: ' . $e->getMessage()]);
}
