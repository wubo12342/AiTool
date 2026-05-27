<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../jwt_helper.php';
require_once __DIR__ . '/../response_helper.php';

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? '';

$decoded = JWT::decode($token);
if (!$decoded || ($decoded['role'] ?? 0) < 1) {
    send_unauthorized('權限不足');
}

try {
    $logs = $pdo->query("
        SELECT
            l.log_id,
            COALESCE(u.username, '已刪除') AS username,
            l.prompt_tokens,
            l.completion_tokens,
            l.total_tokens,
            l.model,
            l.created_at
        FROM ai_token_log l
        LEFT JOIN user u ON l.uid = u.UID
        ORDER BY l.created_at DESC
        LIMIT 200
    ")->fetchAll();

    $totals = $pdo->query("
        SELECT
            COUNT(*)         AS calls,
            SUM(prompt_tokens)     AS prompt,
            SUM(completion_tokens) AS completion,
            SUM(total_tokens)      AS total
        FROM ai_token_log
    ")->fetch();

    $today = $pdo->query("
        SELECT
            COUNT(*)         AS calls,
            SUM(prompt_tokens)     AS prompt,
            SUM(completion_tokens) AS completion,
            SUM(total_tokens)      AS total
        FROM ai_token_log
        WHERE DATE(created_at) = CURDATE()
    ")->fetch();

    // cast to int
    foreach (['calls','prompt','completion','total'] as $k) {
        $totals[$k] = (int)($totals[$k] ?? 0);
        $today[$k]  = (int)($today[$k]  ?? 0);
    }
    foreach ($logs as &$row) {
        $row['prompt_tokens']     = (int)$row['prompt_tokens'];
        $row['completion_tokens'] = (int)$row['completion_tokens'];
        $row['total_tokens']      = (int)$row['total_tokens'];
    }

    echo json_encode(['logs' => $logs, 'totals' => $totals, 'today' => $today]);
} catch (Throwable $e) {
    send_server_error($e);
}
