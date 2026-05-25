<?php
// api/db.php — 提供 $pdo 全域連線，與 config.php 共用同一份環境變數預設值
require_once __DIR__ . '/env_loader.php';
require_once __DIR__ . '/response_helper.php';

$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'ai_tools';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // 真正錯誤寫 log，前端只看到通用訊息
    send_server_error($e, '資料庫連線失敗');
}
