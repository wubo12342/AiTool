<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

$ids = array_filter(array_map('intval', $data['ids'] ?? []));
if (empty($ids)) {
    http_response_code(400);
    echo json_encode(['error' => '未選擇任何評論']);
    exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("DELETE FROM tool_reviews WHERE review_ID IN ($placeholders)");
    $stmt->execute(array_values($ids));
    echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
} catch (Throwable $e) {
    send_server_error($e);
}
