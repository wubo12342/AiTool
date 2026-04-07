<?php
// api/register.php — 負責處理新使用者的註冊功能

// 設定目前的 HTTP 回傳格式為 JSON (讓前端知道我們回傳的是資料，不是網頁)
header('Content-Type: application/json');
// 允許特定的網域名稱存取 API (CORS 設定，* 代表允許所有網址，開發時很常用)
header('Access-Control-Allow-Origin: *');
// 允許前端使用的請求方式 (這裡主要用 POST 來傳送資料)
header('Access-Control-Allow-Methods: POST, OPTIONS');
// 允許前端傳送的 Header 內容 (例如 Content-Type)
header('Access-Control-Allow-Headers: Content-Type');

// 如果瀏覽器傳送的是 OPTIONS 預檢請求 (Preflight)，直接回傳成功並結束
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // 回傳 HTTP 200 狀態碼
    exit; // 結束腳本
}

// 載入資料庫連線設定檔 (讓我們可以使用 $pdo 物件去操作資料庫)
require_once 'db.php';

// 從 PHP 的輸入流 (Input Stream) 讀取前端傳來的 JSON 字串，並轉換成 PHP 的陣列 (Array)
$data = json_decode(file_get_contents('php://input'), true);

// 判斷前端傳來的資料中，是否缺少帳號 (username) 或 密碼 (password)
if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400); // 設定 HTTP 狀態碼為 400 (錯誤請求)
    echo json_encode(['error' => '請提供帳號與密碼']); // 回傳錯誤訊息的 JSON
    exit; // 結束腳本
}

// 去除帳號前後的空白字元 (為了防呆，避免使用者不小心輸入空白)
$username = trim($data['username']);
// 取得密碼原文
$password = $data['password'];

// 再次檢查帳號或密碼是否經過處理後變成了空字串
if (empty($username) || empty($password)) {
    http_response_code(400); // 傳回 400 錯誤狀態
    echo json_encode(['error' => '帳號或密碼不能為空']); // 回傳錯誤提示
    exit;
}

try {
    // 步驟 1: 檢查資料庫中是否已經存在相同的帳號程式碼
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?"); // 準備一個查詢語句 (使用 ? 避免 SQL 注入攻擊)
    $stmt->execute([$username]); // 執行查詢，把 $username 代入對應的問號中
    
    // 如果查詢結果有抓到資料，代表帳號已被註冊
    if ($stmt->fetch()) {
        http_response_code(409); // 設定 HTTP 狀態碼為 409 (衝突)
        echo json_encode(['error' => '該帳號已被註冊使用']); // 回傳警告訊息
        exit;
    }

    // 步驟 2: 安全地處理密碼
    // 使用 PHP 內建的 password_hash 函數進行雜湊加密 (這是目前網頁最安全且標準的做法)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 步驟 3: 將新使用者資訊寫入資料庫
    $stmt = $pdo->prepare("INSERT INTO user (username, password_hash) VALUES (?, ?)"); // 準備插入資料的 SQL
    $stmt->execute([$username, $password_hash]); // 執行 SQL 語句，並帶入參數

    // 若以上步驟都成功，回傳一個成功的狀態 JSON 給前端
    echo json_encode(['message' => '註冊成功', 'username' => $username]);

} catch (\PDOException $e) {
    // 如果資料庫執行過程中出錯，會跳到這裡處理
    http_response_code(500); // 設定 HTTP 狀態碼為 500 (伺服器錯誤)
    echo json_encode(['error' => '資料庫執行錯誤: ' . $e->getMessage()]); // 回傳詳細報錯
}
