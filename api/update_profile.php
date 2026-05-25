<?php
// api/update_profile.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/jwt_helper.php';
require_once __DIR__ . '/response_helper.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$token = $input['token'] ?? '';

$decoded = JWT::decode($token);
if (!$decoded || !isset($decoded['uid'])) {
    send_unauthorized('登入無效');
}

$uid = $decoded['uid'];

try {
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
            'success' => true,
            'system_prompt' => $user['system_prompt'] ?? '',
            'reviews' => $reviews
        ]);
    } elseif ($action === 'save') {
        $prompt = $input['system_prompt'] ?? '';
        $stmt = $pdo->prepare("UPDATE user SET system_prompt = ? WHERE UID = ?");
        $success = $stmt->execute([$prompt, $uid]);
        echo json_encode(['success' => $success]);
    } else {
        send_bad_request('未知的 action');
    }
} catch (Throwable $e) {
    send_server_error($e);
}
