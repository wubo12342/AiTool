<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

try {
    $tools      = $pdo->query("SELECT COUNT(*) FROM ai_tools")->fetchColumn();
    $reviews    = $pdo->query("SELECT COUNT(*) FROM tool_reviews")->fetchColumn();
    $users      = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
    $categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

    echo json_encode([
        'tools'      => (int)$tools,
        'reviews'    => (int)$reviews,
        'users'      => (int)$users,
        'categories' => (int)$categories,
    ]);
} catch (Throwable $e) {
    send_server_error($e);
}
