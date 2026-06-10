<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

$name = trim($data['name'] ?? '');
$description = trim($data['description'] ?? '');
if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => '分類名稱不得為空']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $description ?: null]);
    echo json_encode(['id' => (int)$pdo->lastInsertId(), 'name' => $name, 'description' => $description]);
} catch (Throwable $e) {
    send_server_error($e);
}
