<?php
// api/submit_review.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/jwt_helper.php';
require_once __DIR__ . '/response_helper.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$tid = $input['tool_id'] ?? 0;
$stars = $input['stars'] ?? 5;
$comment = $input['comment'] ?? '';
$token = $input['token'] ?? '';

$decoded = JWT::decode($token);
if (!$decoded || !isset($decoded['uid'])) {
    send_unauthorized('登入逾時或無效，請重新登入');
}

$uid = $decoded['uid'];

if (!$tid) {
    send_bad_request('資料不完整');
}

try {
    $sql = "INSERT INTO tool_reviews (tool_ID, UID, stars, comment, comment_time)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            stars = VALUES(stars),
            comment = VALUES(comment),
            comment_time = NOW()";

    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([(int)$tid, (int)$uid, (int)$stars, $comment]);

    echo json_encode(['success' => $success]);
} catch (Throwable $e) {
    send_server_error($e);
}
