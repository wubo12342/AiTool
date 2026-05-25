<?php
require_once __DIR__ . '/env_loader.php';
require_once __DIR__ . '/response_helper.php';

// 資料庫設定 - 與 db.php 同步預設值
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'ai_tools');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// OpenAI API 設定
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY'));
define('OPENAI_MODEL', getenv('OPENAI_MODEL') ?: 'gpt-4o');

/**
 * 取得資料庫連線
 */
function getDB()
{
    try {
        return new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        send_server_error($e, '資料庫連線失敗');
    }
}
