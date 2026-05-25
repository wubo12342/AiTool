<?php
// api/register.php — 處理新使用者註冊
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response_helper.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password'])) {
    send_bad_request('請提供帳號與密碼');
}

$username = trim($data['username']);
$password = $data['password'];

if ($username === '' || $password === '') {
    send_bad_request('帳號或密碼不能為空');
}

try {
    $stmt = $pdo->prepare("SELECT UID FROM user WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => '該帳號已被註冊使用']);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO user (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username, $password_hash]);

    echo json_encode(['message' => '註冊成功', 'username' => $username]);

} catch (Throwable $e) {
    send_server_error($e);
}
