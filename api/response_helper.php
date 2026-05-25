<?php
// api/response_helper.php — 統一回應與錯誤處理
// 真正的錯誤細節寫進 error_log，前端只收到通用訊息，避免洩漏資料庫結構或 stack trace。

/**
 * 回報伺服器錯誤：log 詳細訊息、回前端通用文字
 */
function send_server_error(Throwable $e, string $userMessage = '伺服器發生錯誤，請稍後再試'): void
{
    error_log('[API ERROR] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $userMessage]);
    exit;
}

function send_bad_request(string $message): void
{
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function send_unauthorized(string $message = '未登入或登入逾時'): void
{
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function send_not_found(string $message = '找不到資源'): void
{
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}
