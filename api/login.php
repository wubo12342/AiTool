<?php
// api/login.php — 處理登入並核發 JWT
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/jwt_helper.php';
require_once __DIR__ . '/response_helper.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password'])) {
    send_bad_request('請輸入帳號與密碼');
}

$username = $data['username'];
$password = $data['password'];

try {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => '帳號或密碼錯誤']);
        exit;
    }

    $payload = [
        'uid' => $user['UID'],
        'username' => $user['username'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24),
    ];

    $token = JWT::encode($payload);

    echo json_encode([
        'message' => '登入成功',
        'token' => $token,
        'user' => [
            'uid' => $user['UID'],
            'username' => $user['username'],
            'role' => $user['role']
        ]
    ]);

} catch (Throwable $e) {
    send_server_error($e);
}
