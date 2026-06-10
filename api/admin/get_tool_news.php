<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

$file = __DIR__ . '/../data/tool_news.json';
if (!file_exists($file)) {
    echo json_encode(['news' => []]);
    exit;
}

$json = json_decode(file_get_contents($file), true);
echo json_encode(['news' => $json['news'] ?? []]);
