<?php
header('Content-Type: application/json');
require_once 'db.php';
require_once 'jwt_helper.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$tid = $input['tool_id'] ?? 0;
$stars = $input['stars'] ?? 5;
$comment = $input['comment'] ?? '';
$token = $input['token'] ?? '';

// 從 Token 中解析出真實的 UID
$decoded = JWT::decode($token);
if (!$decoded || !isset($decoded['uid'])) {
    echo json_encode(['success' => false, 'error' => '登入逾時或無效，請重新登入']);
    exit;
}

$uid = $decoded['uid'];

if (!$tid || $comment === '') {
    echo json_encode(['success' => false, 'error' => '資料不完整']);
    exit;
}

try {
    $sql = "INSERT INTO tool_reviews (tool_ID, UID, stars, comment, comment_time) 
            VALUES (?, ?, ?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE 
            stars = VALUES(stars), 
            comment = VALUES(comment), 
            comment_time = NOW()";
            
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([ (int)$tid, (int)$uid, (int)$stars, $comment ]);
    
    echo json_encode(['success' => $success]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
