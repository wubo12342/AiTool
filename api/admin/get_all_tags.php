<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

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
    echo json_encode(['tags' => $tags]);
} catch (Throwable $e) {
    send_server_error($e);
}
