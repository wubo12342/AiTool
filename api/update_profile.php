<?php
header('Content-Type: application/json');
require_once 'db.php';
require_once 'jwt_helper.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$token = $input['token'] ?? '';

// 解析 Token
$decoded = JWT::decode($token);
if (!$decoded || !isset($decoded['uid'])) {
    echo json_encode(['success' => false, 'error' => '登入無效']);
    exit;
}

$uid = $decoded['uid'];

if ($action === 'get') {
    $stmt = $pdo->prepare("SELECT system_prompt FROM user WHERE UID = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
    
    $stmt = $pdo->prepare("
        SELECT r.*, t.name as toolName, t.logo_url, t.CID 
        FROM tool_reviews r 
        JOIN ai_tools t ON r.tool_ID = t.tool_ID 
        WHERE r.UID = ? 
        ORDER BY r.comment_time DESC
    ");
    $stmt->execute([$uid]);
    $reviews = $stmt->fetchAll();

    echo json_encode([
        'system_prompt' => $user['system_prompt'] ?? '',
        'reviews' => $reviews
    ]);
} 
elseif ($action === 'save') {
    $prompt = $input['system_prompt'] ?? '';
    $stmt = $pdo->prepare("UPDATE user SET system_prompt = ? WHERE UID = ?");
    $success = $stmt->execute([$prompt, $uid]);
    echo json_encode(['success' => $success]);
}
