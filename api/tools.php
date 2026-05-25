<?php
// api/tools.php — 回傳所有工具，給比較頁等元件使用
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response_helper.php';

try {
    $query = "
        SELECT
            t.tool_ID as id,
            t.name,
            t.description,
            t.logo_url as logoUrl,
            t.website_url,
            c.name as category_name,
            COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 0) as rating
        FROM ai_tools t
        LEFT JOIN categories c ON t.CID = c.CID
        ORDER BY t.tool_ID DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tools = $stmt->fetchAll();

    foreach ($tools as &$tool) {
        $tool['id'] = (int)$tool['id'];
        $tool['rating'] = round((float)$tool['rating'], 1);
        $tool['tags'] = $tool['category_name'] ? [$tool['category_name']] : [];
        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . urlencode($tool['name']);
        }
    }

    echo json_encode($tools);

} catch (Throwable $e) {
    send_server_error($e);
}
