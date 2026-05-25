<?php
// api/get_favorites.php
// H12 — 改用 POST + token 放在 body，避免 Token 進 URL 歷史/日誌
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/jwt_helper.php';
require_once __DIR__ . '/response_helper.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$token = $data['token'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

// 容許 "Bearer xxx" 形式
if (is_string($token) && stripos($token, 'Bearer ') === 0) {
    $token = substr($token, 7);
}

if (!$token) {
    send_unauthorized('缺少 Token');
}

try {
    $decoded = JWT::decode($token);
    if (!$decoded || !isset($decoded['uid'])) {
        send_unauthorized('Token 無效');
    }
    $uid = $decoded['uid'];

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
        $f['tags'] = $f['category_name'] ? [$f['category_name']] : [];
        if (!$f['logoUrl']) {
            $f['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . urlencode($f['name']);
        }
    }

    echo json_encode(['success' => true, 'favorites' => $favorites]);

} catch (Throwable $e) {
    send_server_error($e);
}
