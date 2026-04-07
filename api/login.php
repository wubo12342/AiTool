<?php
// api/login.php — 負責處理使用者登入並核發 JWT Token

// 設定 HTTP 回傳格式為 JSON
header('Content-Type: application/json');
// 允許跨網域存取 (CORS)
header('Access-Control-Allow-Origin: *');
// 允許 POST 方法與 OPTIONS 預檢
header('Access-Control-Allow-Methods: POST, OPTIONS');
// 允許 Header 包含 Content-Type
header('Access-Control-Allow-Headers: Content-Type');

// 處理瀏覽器的 OPTIONS 預檢請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 載入資料庫連線工具
require_once 'db.php';
// 載入我們自定義的 JWT 解碼/編碼工具類別
require_once 'jwt_helper.php';

// 讀取前端傳來的 JSON 資料並轉為 PHP 陣列
$data = json_decode(file_get_contents('php://input'), true);

// 檢查前端是否有傳入帳號與密碼
if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400); // 400 為錯誤請求
    echo json_encode(['error' => '請輸入帳號與密碼']);
    exit;
}

// 取得前端傳來的資料
$username = $data['username'];
$password = $data['password'];

try {
    // 步驟 1: 從資料庫根據帳號名稱查詢該位使用者的資料
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?"); // 準備查詢 SQL
    $stmt->execute([$username]); // 執行並帶入變數
    $user = $stmt->fetch(); // 取得一筆查詢結果

    // 步驟 2: 驗證帳號是否存在，以及密碼是否正確
    // 使用 password_verify 函數，將「輸入的密碼」與「資料庫儲存的雜湊值」進行比對
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401); // 401 代表授權失敗 (帳密錯誤)
        echo json_encode(['error' => '帳號或密碼錯誤']);
        exit;
    }

    // 步驟 3: 登入成功後，準備要放在 JWT Token 裡的資料 (Payload)
    // 這裡放的是不具敏感性但方便辨識使用者的資訊
    $payload = [
        'uid' => $user['UID'], // 使用者唯一 ID
        'username' => $user['username'], // 帳號名稱
        'role' => $user['role'], // 權限等級 (0: 一般, 1: 管理員)
        'iat' => time(), // Token 核發時間 (Issued At)
        'exp' => time() + (60 * 60 * 24), // 設定過期時間為目前的 24 小時後
    ];

    // 步驟 4: 使用剛才載入的 JWT 類別來生成加密 Token
    $token = JWT::encode($payload);

    // 步驟 5: 將結果回傳給前端
    echo json_encode([
        'message' => '登入成功',
        'token' => $token, // 前端之後需要將這個 Token 存進 localStorage
        'user' => [
            'uid' => $user['UID'],
            'username' => $user['username'],
            'role' => $user['role']
        ]
    ]);

} catch (\PDOException $e) {
    // 發生錯誤時的回報機制
    http_response_code(500);
    echo json_encode(['error' => '伺服器執行錯誤: ' . $e->getMessage()]);
}
