<?php
// api/comments.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response_helper.php';

$tool_id = isset($_GET['tool_id']) ? (int)$_GET['tool_id'] : 0;

if ($tool_id <= 0) {
    echo json_encode([]);
    exit;
}

try {
    // H6 — 補上 date 欄位（YYYY-MM-DD）讓前端的 comment.date 可以直接用
    $query = "
        SELECT
            u.username as user,
            r.comment as content,
            r.stars as rating,
            DATE_FORMAT(r.comment_time, '%Y-%m-%d') as date,
            r.comment_time
        FROM tool_reviews r
        JOIN user u ON r.UID = u.UID
        WHERE r.tool_ID = :tool_id
        ORDER BY r.comment_time DESC
        LIMIT 20
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['tool_id' => $tool_id]);
    $comments = $stmt->fetchAll();

    echo json_encode($comments ?: []);
} catch (Throwable $e) {
    error_log('[comments] ' . $e->getMessage());
    echo json_encode([]);
}
