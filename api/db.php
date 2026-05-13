<?php
// api/db.php
require_once 'env_loader.php'; // 確保環境變數有被載入

// 優先從環境變數讀取，若無則使用預設值 (與 Docker Compose 一致)
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'ai_tools';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root';
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
    error_log("資料庫連線失敗: " . $e->getMessage()); 
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => '資料庫連線失敗: ' . $e->getMessage()]);
    exit;
}
