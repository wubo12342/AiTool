<?php
// api/comments.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

$tool_id = isset($_GET['tool_id']) ? (int)$_GET['tool_id'] : 0;

try {
    $query = "
        SELECT 
            u.username as user,
            r.comment as content,
            r.stars as rating,
            r.comment_time
        FROM tool_reviews r
        JOIN user u ON r.UID = u.UID
        WHERE r.tool_ID = :tool_id
        ORDER BY r.comment_time DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['tool_id' => $tool_id]);
    $comments = $stmt->fetchAll();

    // 如果沒評論，回傳空陣列
    echo json_encode($comments ?: []);

} catch (Exception $e) {
    echo json_encode([]);
}
