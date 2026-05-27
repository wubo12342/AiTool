<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("
        SELECT t.TID as id, t.name,
               COUNT(m.tool_ID) as tool_count
        FROM tags t
        LEFT JOIN tool_tag_map m ON t.TID = m.TID
        GROUP BY t.TID, t.name
        ORDER BY t.TID
    ");
    $tags = $stmt->fetchAll();
    foreach ($tags as &$tag) {
        $tag['id'] = (int)$tag['id'];
        $tag['tool_count'] = (int)$tag['tool_count'];
    }
    echo json_encode($tags);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
