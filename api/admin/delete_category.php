<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

$id = (int)($data['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => '無效的分類 ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE CID = ?");
    $stmt->execute([$id]);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    send_server_error($e);
}
