<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

$name = trim($data['name'] ?? '');
if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => '標籤名稱不得為空']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
    $stmt->execute([$name]);
    echo json_encode(['id' => (int)$pdo->lastInsertId(), 'name' => $name]);
} catch (Throwable $e) {
    if ($e->getCode() === '23000') {
        http_response_code(409);
        echo json_encode(['error' => '標籤名稱已存在']);
    } else {
        send_server_error($e);
    }
}
