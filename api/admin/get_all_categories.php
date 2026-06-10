<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

try {
    $stmt = $pdo->query("
        SELECT c.CID as id, c.name, c.description,
               COUNT(t.tool_ID) as tool_count
        FROM categories c
        LEFT JOIN ai_tools t ON c.CID = t.CID
        GROUP BY c.CID, c.name, c.description
        ORDER BY c.CID
    ");
    $categories = $stmt->fetchAll();
    foreach ($categories as &$c) {
        $c['id']         = (int)$c['id'];
        $c['tool_count'] = (int)$c['tool_count'];
    }
    echo json_encode(['categories' => $categories]);
} catch (Throwable $e) {
    send_server_error($e);
}
