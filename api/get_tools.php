<?php
// api/get_tools.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // 允許前端跨域請求

require_once 'db.php';

try {
    // 獲取熱門工具 (依據收藏次數排序，取前 4 個)
    $query = "
        SELECT 
            t.tool_ID as id, 
            t.name, 
            t.description, 
            t.logo_url as logoUrl, 
            t.website_url,
            c.name as category_name,
            COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 4.5) as rating,
            (SELECT COUNT(*) FROM user_likes WHERE tool_ID = t.tool_ID) as usage_count
        FROM ai_tools t
        LEFT JOIN categories c ON t.CID = c.CID
        ORDER BY usage_count DESC, t.tool_ID DESC
        LIMIT 4
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tools = $stmt->fetchAll();

    // 格式化資料以符合前端需求
    foreach ($tools as &$tool) {
        $tool['id'] = (int)$tool['id'];
        $tool['rating'] = round((float)$tool['rating'], 1);
        
        // 由於資料庫目前 tags 是空的，我們先根據分類名稱給一個預設 tag
        $tool['tags'] = [$tool['category_name']];
        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . $tool['name'];
        }
    }

    echo json_encode($tools);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
