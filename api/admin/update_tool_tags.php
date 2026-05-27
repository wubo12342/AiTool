<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

$toolId = (int)($data['tool_id'] ?? 0);
$tagIds = $data['tag_ids'] ?? [];

if ($toolId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => '無效的工具 ID']);
    exit;
}

try {
    $pdo->beginTransaction();

    $pdo->prepare("DELETE FROM tool_tag_map WHERE tool_ID = ?")->execute([$toolId]);

    if (!empty($tagIds)) {
        $stmt = $pdo->prepare("INSERT INTO tool_tag_map (tool_ID, TID) VALUES (?, ?)");
        foreach ($tagIds as $tid) {
            $tid = (int)$tid;
            if ($tid > 0) $stmt->execute([$toolId, $tid]);
        }
    }

    $pdo->commit();
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    $pdo->rollBack();
    send_server_error($e);
}
