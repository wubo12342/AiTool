<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
$payload = verify_admin($data['token'] ?? '');

$uid  = (int)($data['uid'] ?? 0);
$role = (int)($data['role'] ?? -1);

if (!$uid || !in_array($role, [0, 1], true)) {
    send_bad_request('無效的參數');
}

if ((int)$payload['uid'] === $uid) {
    send_bad_request('無法修改自己的角色');
}

try {
    $stmt = $pdo->prepare("SELECT role FROM user WHERE UID = ?");
    $stmt->execute([$uid]);
    $target = $stmt->fetch();
    if (!$target) send_bad_request('找不到該用戶');
    if ((int)$target['role'] === 9) send_bad_request('無法修改超級管理員的角色');

    $pdo->prepare("UPDATE user SET role = ? WHERE UID = ?")->execute([$role, $uid]);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    send_server_error($e);
}
