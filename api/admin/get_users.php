<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

try {
    $stmt = $pdo->query("
        SELECT
            u.UID      AS uid,
            u.username,
            u.role,
            (SELECT COUNT(*) FROM tool_reviews WHERE UID = u.UID) AS review_count,
            (SELECT COUNT(*) FROM user_likes   WHERE UID = u.UID) AS favorite_count
        FROM user u
        ORDER BY u.UID ASC
    ");

    $users = $stmt->fetchAll();
    foreach ($users as &$u) {
        $u['uid']            = (int)$u['uid'];
        $u['review_count']   = (int)$u['review_count'];
        $u['favorite_count'] = (int)$u['favorite_count'];
    }

    echo json_encode(['users' => $users]);
} catch (Throwable $e) {
    send_server_error($e);
}
