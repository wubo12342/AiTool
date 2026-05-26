<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

$id = (int)($data['id'] ?? 0);
if (!$id) send_bad_request('無效的評論 ID');

try {
    $pdo->prepare("DELETE FROM tool_reviews WHERE review_ID = ?")->execute([$id]);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    send_server_error($e);
}
