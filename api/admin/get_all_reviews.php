<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

try {
    $stmt = $pdo->query("
        SELECT
            r.review_ID AS review_id,
            r.tool_ID   AS tool_id,
            r.UID       AS uid,
            r.stars,
            r.comment,
            r.comment_time AS created_at,
            t.name  AS tool_name,
            u.username
        FROM tool_reviews r
        LEFT JOIN ai_tools t ON r.tool_ID = t.tool_ID
        LEFT JOIN user     u ON r.UID     = u.UID
        ORDER BY r.comment_time DESC
    ");

    $reviews = $stmt->fetchAll();
    foreach ($reviews as &$r) {
        $r['review_id'] = (int)$r['review_id'];
        $r['stars']     = (int)$r['stars'];
    }

    echo json_encode(['reviews' => $reviews]);
} catch (Throwable $e) {
    send_server_error($e);
}
