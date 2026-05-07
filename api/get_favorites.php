<?php
// api/get_favorites.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';
require_once 'jwt_helper.php';

// 處理預檢請求 (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$token = $_GET['token'] ?? '';

if (!$token) {
    echo json_encode(['success' => false, 'message' => '缺少 Token']);
    exit;
}

try {
    $decoded = JWT::decode($token);
    if (!$decoded || !isset($decoded['uid'])) {
        echo json_encode(['success' => false, 'message' => 'Token 無效']);
        exit;
    }
    $uid = $decoded['uid'];

    // 取得該使用者的所有收藏工具 ID
    $stmt = $pdo->prepare("
        SELECT t.tool_ID as id, t.name, t.description, t.logo_url as logoUrl, t.website_url, c.name as category_name
        FROM user_likes l
        JOIN ai_tools t ON l.tool_ID = t.tool_ID
        LEFT JOIN categories c ON t.CID = c.CID
        WHERE l.UID = ?
    ");
    $stmt->execute([$uid]);
    $favorites = $stmt->fetchAll();

    foreach ($favorites as &$f) {
        $f['id'] = (int)$f['id'];
        $f['isFavorited'] = true;
        $f['tags'] = [$f['category_name']];
        if (!$f['logoUrl']) {
            $f['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . $f['name'];
        }
    }

    echo json_encode([
        'success' => true,
        'favorites' => $favorites
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
