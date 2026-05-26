<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

$name          = trim($data['name'] ?? '');
$cid           = (int)($data['cid'] ?? 0);
$description   = trim($data['description'] ?? '');
$logo_url      = trim($data['logo_url'] ?? '');
$website_url   = trim($data['website_url'] ?? '');
$video_url     = trim($data['video_url'] ?? '');
$pricing_plans = $data['pricing_plans'] ?? '{}';

if (!$name || !$cid) {
    send_bad_request('請填寫工具名稱與分類');
}

if (json_decode($pricing_plans) === null) {
    send_bad_request('定價方案格式錯誤');
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO ai_tools (CID, name, description, logo_url, website_url, video_url, pricing_plans)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$cid, $name, $description, $logo_url, $website_url, $video_url, $pricing_plans]);
    echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
} catch (Throwable $e) {
    send_server_error($e);
}
