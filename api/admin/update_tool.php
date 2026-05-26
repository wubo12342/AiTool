<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

$id            = (int)($data['id'] ?? 0);
$name          = trim($data['name'] ?? '');
$cid           = (int)($data['cid'] ?? 0);
$description   = trim($data['description'] ?? '');
$logo_url      = trim($data['logo_url'] ?? '');
$website_url   = trim($data['website_url'] ?? '');
$video_url     = trim($data['video_url'] ?? '');
$pricing_plans = $data['pricing_plans'] ?? '{}';

if (!$id || !$name || !$cid) {
    send_bad_request('缺少必要欄位');
}

if (json_decode($pricing_plans) === null) {
    send_bad_request('定價方案格式錯誤');
}

try {
    $stmt = $pdo->prepare("
        UPDATE ai_tools
        SET CID=?, name=?, description=?, logo_url=?, website_url=?, video_url=?, pricing_plans=?
        WHERE tool_ID=?
    ");
    $stmt->execute([$cid, $name, $description, $logo_url, $website_url, $video_url, $pricing_plans, $id]);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    send_server_error($e);
}
