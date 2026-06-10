<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

$id = trim($data['id'] ?? '');
if ($id === '') {
    http_response_code(400);
    echo json_encode(['error' => '無效的消息 ID']);
    exit;
}

$file = __DIR__ . '/../data/tool_news.json';
$json = file_exists($file) ? json_decode(file_get_contents($file), true) : ['news' => []];
$news = $json['news'] ?? [];

$news = array_values(array_filter($news, fn($n) => ($n['id'] ?? '') !== $id));

$json['news'] = $news;
file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo json_encode(['ok' => true]);
