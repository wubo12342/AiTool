<?php
// api/db.php
// 資料庫連線設定

$host = 'localhost';
$db   = 'ai_tools';
$user = 'root';
$pass = ''; // XAMPP 預設為空
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
    header('Content-Type: application/json');
    echo json_encode(['error' => '資料庫連線失敗: ' . $e->getMessage()]);
    exit;
}
