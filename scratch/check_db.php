<?php
require_once 'api/config.php';
$pdo = getDB();
// 注意：資料庫名稱在 config.php 裡是 ai_tools
$stmt = $pdo->query("DESC user");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols, JSON_PRETTY_PRINT);
